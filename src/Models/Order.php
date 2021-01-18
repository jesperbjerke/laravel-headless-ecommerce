<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Bjerke\Ecommerce\Enums\OrderStatus;
use Bjerke\Ecommerce\Enums\PaidStatus;
use Bjerke\Ecommerce\Enums\PaymentStatus;
use Bjerke\Ecommerce\Exceptions\MissingBillingOrShipping;
use Bjerke\Ecommerce\Exceptions\OrderNotEditable;
use Bjerke\Ecommerce\Exceptions\OrderNotInAPayableState;
use Bjerke\Ecommerce\Exceptions\PaymentFailed;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Omnipay;

class Order extends BreadModel
{
    use SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'meta' => 'array',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'shipping_attributes' => 'array'
    ];

    public static function boot(): void
    {
        parent::boot();
        static::creating(static function (Order $order) {
            $order->setAttribute($order->getKeyName(), Str::uuid());
        });
    }

    protected function define(): void
    {
        $this->addFieldText('first_name', Lang::get('ecommerce::fields.first_name'), self::$FIELD_REQUIRED);
        $this->addFieldText('last_name', Lang::get('ecommerce::fields.last_name'), self::$FIELD_REQUIRED);
        $this->addFieldText('company', Lang::get('ecommerce::fields.company'), self::$FIELD_OPTIONAL);
        $this->addFieldEmail('email', Lang::get('ecommerce::fields.email'), self::$FIELD_REQUIRED);

        $this->addFieldJSON(
            'billing_address',
            Lang::get('ecommerce::fields.billing_address'),
            self::$FIELD_OPTIONAL,
            false,
            fn (Order $order) => self::defineAddressFields($order)
        );

        $this->addFieldJSON(
            'shipping_address',
            Lang::get('ecommerce::fields.billing_address'),
            self::$FIELD_OPTIONAL,
            false,
            fn (Order $order) => self::defineAddressFields($order)
        );

        $this->addFieldHasOne(
            'shippingMethod',
            Lang::get('ecommerce::models.shipping_method.singular'),
            self::$FIELD_OPTIONAL,
            'name',
            null,
            [
                'fillable' => (!$this->exists || $this->status === OrderStatus::DRAFT),
                'extra_data' => [
                    'prefetch' => true
                ]
            ]
        );
    }

    private static function defineAddressFields(Order $order): void
    {
        $order->addFieldText('address_1', Lang::get('ecommerce::fields.address'), self::$FIELD_REQUIRED);
        $order->addFieldText('address_2', Lang::get('ecommerce::fields.address_2'), self::$FIELD_OPTIONAL);
        $order->addFieldText('city', Lang::get('ecommerce::fields.city'), self::$FIELD_REQUIRED);
        $order->addFieldText('postcode', Lang::get('ecommerce::fields.postcode'), self::$FIELD_REQUIRED);
        $order->addFieldText('state', Lang::get('ecommerce::fields.state'), self::$FIELD_OPTIONAL);
        $order->addFieldText('country', Lang::get('ecommerce::fields.country'), self::$FIELD_REQUIRED);
        $order->addFieldTel('phone', Lang::get('ecommerce::fields.phone'), self::$FIELD_REQUIRED);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.store'));
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(config('ecommerce.models.order_item'));
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.shipping_method'));
    }

    public static function createFromCart($cartId): Order
    {
        $order = new Order();
        $order->status = OrderStatus::DRAFT;
        $order->paid_status = PaidStatus::UNPAID;

        return $order->updateFromCart($cartId);
    }

    public function updateFromCart($cartId): Order
    {
        if (
            $this->status !== OrderStatus::DRAFT ||
            $this->paid_status !== PaidStatus::UNPAID
        ) {
            throw new OrderNotEditable();
        }

        /* @var $cart Cart */
        $cart = Cart::with([
            'cartItems.product.stocks',
            'cartItems.product.prices',
            'cartItems.product.activeDeals'
        ])->whereHas('cartItems')->findOrFail($cartId);

        $cart->validateCart();
        $cart->touch();

        $this->validateOnSave = false;

        $this->currency = $cart->currency;
        $this->store_id = $cart->store_id;
        $this->order_value = $cart->totals['total'];
        $this->order_vat_value = $cart->totals['total'] - $cart->totals['total_ex_vat'];
        $this->cart_id = $cart->id;

        $this->save();

        $this->validateOnSave = true;

        $this->orderItems->each(fn (OrderItem $orderItem) => $orderItem->delete());
        $cart->cartItems->each(function (CartItem $cartItem) use ($cart) {
            $this->orderItems()->create([
                'name' => $cartItem->product->name,
                'reference' => $cartItem->product->sku,
                'product_id' => $cartItem->product_id,
                'value' => $cartItem->totals['total'],
                'discount_value' => $cartItem->totals['original_total'] - $cartItem->totals['total'],
                'vat_value' => $cartItem->totals['total'] - $cartItem->totals['total_ex_vat'],
                'unit_value' => (isset($cartItem->totals['unit'])) ?
                    $cartItem->totals['unit']['total'] :
                    $cartItem->totals['total'],
                'vat_percentage' => $cartItem->getActivePrice($cart->currency, $cart->store_id)->vat_percentage,
                'quantity' => $cartItem->quantity
            ]);
        });

        return $this;
    }

    public function checkout(
        ?string $token,
        ?array $additionalAuthorizeData,
        ?array $additionalCardData
    ): ResponseInterface {
        if (
            (!$this->billing_address || empty($this->billing_address)) &&
            (!$this->shipping_address || empty($this->shipping_address))
        ) {
            throw new MissingBillingOrShipping();
        }

        if ($this->status !== OrderStatus::DRAFT || $this->paid_status === PaidStatus::PAID) {
            throw new OrderNotInAPayableState();
        }

        $payment = Payment::create([
           'currency' => $this->currency,
           'value' => $this->order_value,
           'status' => PaymentStatus::PENDING,
           'order_id' => $this->id
        ]);

        $gateway = Omnipay::create(config('ecommerce.payments.gateway'));
        $gateway->initialize(config('ecommerce.payments.gateway_options'));

        $authorizeData = [
            'transactionId' => $payment->id,
            'amount' => $payment->value,
            'returnUrl' => config('ecommerce.payments.return_url'),
            'cancelUrl' => config('ecommerce.payments.cancel_url'),
            'notifyUrl' => config('ecommerce.payments.notify_url')
        ];

        if ($token) {
            $authorizeData['token'] = $token;
        } else {
            $authorizeData['card'] = $this->setupCardData($additionalCardData);
        }

        $authorizeResponse = $gateway->authorize(array_merge($authorizeData, $additionalAuthorizeData))->send();

        $payment->reference = $authorizeResponse->getTransactionReference();

        if (!$authorizeResponse->isSuccessful()) {
            $payment->status = PaymentStatus::FAILED;
            $payment->save();
            throw new PaymentFailed($authorizeResponse->getMessage());
        }

        $payment->status = PaymentStatus::PENDING;
        $payment->save();

        $this->status = OrderStatus::PENDING;
        $this->save();

        return $authorizeResponse;
    }

    protected function setupCardData(?array $additionalCardData): CreditCard
    {
        $cardData = [
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'company' => $this->company,
            'email' => $this->email
        ];

        if ($this->billing_address && !empty($this->billing_address)) {
            $cardData['billingAddress1'] = $this->billing_address['address_1'];
            $cardData['billingAddress2'] = $this->billing_address['address_2'] ?: null;
            $cardData['billingCity'] = $this->billing_address['city'];
            $cardData['billingPostcode'] = $this->billing_address['postcode'];
            $cardData['billingState'] = $this->billing_address['state'] ?: null;
            $cardData['billingCountry'] = $this->billing_address['country'];
            $cardData['billingPhone'] = $this->billing_address['phone'];
        }

        if ($this->shipping_address && !empty($this->shipping_address)) {
            $cardData['shippingAddress1'] = $this->shipping_address['address_1'];
            $cardData['shippingAddress2'] = $this->shipping_address['address_2'] ?: null;
            $cardData['shippingCity'] = $this->shipping_address['city'];
            $cardData['shippingPostcode'] = $this->shipping_address['postcode'];
            $cardData['shippingState'] = $this->shipping_address['state'] ?: null;
            $cardData['shippingCountry'] = $this->shipping_address['country'];
            $cardData['shippingPhone'] = $this->shipping_address['phone'];
        }

        if ($additionalCardData) {
            $cardData = array_merge($cardData, $additionalCardData);
        }

        return new CreditCard($cardData);
    }
}

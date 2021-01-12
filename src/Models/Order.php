<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Bjerke\Ecommerce\Enums\OrderStatus;
use Bjerke\Ecommerce\Enums\PaidStatus;
use Bjerke\Ecommerce\Exceptions\OrderNotEditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

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
            fn(Order $order) => self::defineAddressFields($order)
        );

        $this->addFieldJSON(
            'shipping_address',
            Lang::get('ecommerce::fields.billing_address'),
            self::$FIELD_OPTIONAL,
            false,
            fn(Order $order) => self::defineAddressFields($order)
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

        $this->orderItems->each(fn(OrderItem $orderItem) => $orderItem->delete());
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
}

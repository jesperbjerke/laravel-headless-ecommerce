<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Bjerke\Ecommerce\Enums\OrderStatus;
use Bjerke\Ecommerce\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
}
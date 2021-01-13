<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Bjerke\Ecommerce\Helpers\PriceHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;

class Price extends BreadModel
{
    protected function define(): void
    {
        $this->addFieldInt('priceable_id', Lang::get('ecommerce::fields.related_id'), self::$FIELD_REQUIRED);
        $this->addFieldSelect(
            'priceable_type',
            Lang::get('ecommerce::fields.related_type'),
            self::$FIELD_REQUIRED,
            [
                'product' => Lang::get('ecommerce::models.product.singular'),
                'shipping_method' => Lang::get('ecommerce::models.shipping_method.singular')
            ],
            [
                'default' => 'product'
            ]
        );

        $this->addFieldHasOne(
            'store',
            Lang::get('ecommerce::models.store.singular'),
            self::$FIELD_OPTIONAL,
            'name',
            null,
            [
                'extra_data' => [
                    'prefetch' => true
                ]
            ]
        );

        $this->addFieldSelect(
            'currency',
            Lang::get('ecommerce::fields.currency'),
            self::$FIELD_REQUIRED,
            config('ecommerce.currencies.available'),
            [
                'default' => config('ecommerce.currencies.default')
            ]
        );

        $this->addFieldInt(
            'value',
            Lang::get('ecommerce::fields.price'),
            self::$FIELD_REQUIRED,
            [
                'description' => Lang::get('ecommerce::fields.descriptions.price')
            ]
        );

        $this->addFieldInt(
            'discounted_value',
            Lang::get('ecommerce::fields.discounted_price'),
            self::$FIELD_REQUIRED,
            [
                'description' => Lang::get('ecommerce::fields.descriptions.discounted_price')
            ]
        );

        $this->addFieldFloat(
            'vat_percentage',
            Lang::get('ecommerce::fields.vat_percentage'),
            self::$FIELD_OPTIONAL
        );
    }

    public function allowedApiAppends(): array
    {
        return [
            'totals'
        ];
    }

    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.store'));
    }

    public function applicableDeals(): Collection
    {
        return $this->product->activeDeals
            ->where('currency', $this->currency)
            ->when($this->store_id, fn (Collection $deals) => $deals->where('store_id', $this->store_id));
    }

    public function calculateTotals(int $quantity = 1, $includeDeals = true): array
    {
        /* @var $deal Deal|null */
        $deal = ($includeDeals) ? $this->applicableDeals->latest()->first() : null;
        return PriceHelper::calculateTotals($this, App::getLocale(), $quantity, $deal, false);
    }

    public function getTotalsAttribute(): array
    {
        return $this->calculateTotals();
    }
}

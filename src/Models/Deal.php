<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Bjerke\Ecommerce\Enums\DealDiscountType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;

class Deal extends BreadModel
{
    use SoftDeletes;

    protected $casts = [
        'rules' => 'array',
        'activates_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    protected function define(): void
    {
        $this->addFieldInt(
            'discount_value',
            Lang::get('ecommerce::fields.discount_value'),
            self::$FIELD_REQUIRED
        );

        $this->addFieldSelect(
            'discount_type',
            Lang::get('ecommerce::fields.type'),
            self::$FIELD_REQUIRED,
            DealDiscountType::getTranslated()
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

        $this->addFieldHasManySelect(
            'products',
            Lang::get('ecommerce::models.product.plural'),
            self::$FIELD_OPTIONAL,
            'name,sku',
            null,
            [
                'extra_data' => [
                    'prefetch' => true,
                    'display_field' => 'name',
                    'extra_display_field' => 'sku'
                ]
            ]
        );

        $this->addFieldHasManySelect(
            'brands',
            Lang::get('ecommerce::models.brand.plural'),
            self::$FIELD_OPTIONAL,
            'name'
        );

        $this->addFieldHasManySelect(
            'categories',
            Lang::get('ecommerce::models.category.plural'),
            self::$FIELD_OPTIONAL,
            'name'
        );
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.store'));
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(config('ecommerce.models.product'), 'dealable');
    }

    public function brands(): MorphToMany
    {
        return $this->morphedByMany(config('ecommerce.models.brand'), 'dealable');
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(config('ecommerce.models.category'), 'dealable');
    }

    public function applicableProducts(): BelongsToMany
    {
        return $this->belongsToMany(config('ecommerce.models.product'), 'deal_product');
    }
}

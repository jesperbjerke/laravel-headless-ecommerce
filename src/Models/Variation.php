<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Variation extends BreadModel
{
    public $timestamps = false;

    protected $fillable = [
        'sync_options'
    ];

    protected $casts = [
        'sync_options' => 'array'
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.property'));
    }

    public function propertyValue(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.property_value'));
    }

    public function mainProduct(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.product'), 'main_product_id');
    }

    public function variantProduct(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.product'), 'variant_product_id');
    }

    public static function getDefaultSyncOptions(): array
    {
        return [
            'attributes' => true,
            'relations' => [
                'stores',
                'categories'
            ],
            'prices' => true,
            'property_values' => true
        ];
    }
}

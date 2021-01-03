<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Lang;

class Stock extends BreadModel
{
    protected function define(): void
    {
        $this->addFieldHasOne(
            'product',
            Lang::get('ecommerce::models.product.singular'),
            self::$FIELD_REQUIRED,
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

        $this->addFieldHasOne(
            'store',
            Lang::get('ecommerce::models.store.singular'),
            self::$FIELD_OPTIONAL,
            'name'
        );

        $this->addFieldInt(
            'current_quantity',
            Lang::get('ecommerce::fields.current_quantity'),
            self::$FIELD_REQUIRED,
            [
                'default' => 0,
                'description' => Lang::get('ecommerce::fields.descriptions.current_quantity')
            ]
        );
        $this->addFieldInt(
            'incoming_quantity',
            Lang::get('ecommerce::fields.incoming_quantity'),
            self::$FIELD_REQUIRED,
            [
                'default' => 0,
                'description' => Lang::get('ecommerce::fields.descriptions.incoming_quantity')
            ]
        );
        $this->addFieldInt(
            'outgoing_quantity',
            Lang::get('ecommerce::fields.outgoing_quantity'),
            self::$FIELD_REQUIRED,
            [
                'default' => 0,
                'description' => Lang::get('ecommerce::fields.descriptions.outgoing_quantity')
            ]
        );
        $this->addFieldInt(
            'low_stock_threshold',
            Lang::get('ecommerce::fields.low_stock_threshold'),
            self::$FIELD_OPTIONAL,
            [
                'description' => Lang::get('ecommerce::fields.descriptions.low_stock_threshold')
            ]
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.product'));
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.store'));
    }
}

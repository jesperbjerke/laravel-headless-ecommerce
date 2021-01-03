<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Support\Facades\Lang;
use Spatie\Translatable\HasTranslations;

class Store extends BreadModel
{
    use HasTranslations;

    protected function define(): void
    {
        $this->addFieldText('name', Lang::get('ecommerce::fields.name'), self::$FIELD_REQUIRED);

        $this->addFieldSelect(
            'currency',
            Lang::get('ecommerce::fields.currency'),
            self::$FIELD_OPTIONAL,
            config('ecommerce.currencies.available'),
            [
                'default' => config('ecommerce.currencies.default')
            ]
        );
    }
}

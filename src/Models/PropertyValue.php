<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Support\Facades\Lang;
use Spatie\Translatable\HasTranslations;

class PropertyValue extends BreadModel
{
    use HasTranslations;
    use AsPivot;

    public $timestamps = false;

    public array $translatable = [
        'value'
    ];

    protected function define(): void
    {
        $this->addFieldHasOne(
            'product',
            Lang::get('ecommerce::models.product.singular'),
            self::$FIELD_REQUIRED,
            'name'
        );

        $this->addFieldHasOne(
            'property',
            Lang::get('ecommerce::models.property.singular'),
            self::$FIELD_REQUIRED,
            'name'
        );

        $this->addFieldText('value', Lang::get('ecommerce::fields.value'), self::$FIELD_OPTIONAL);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.product'));
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.property'));
    }
}

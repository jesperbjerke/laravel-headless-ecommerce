<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use Spatie\Translatable\HasTranslations;

class ShippingMethod extends BreadModel
{
    use SoftDeletes;
    use HasTranslations;

    protected $casts = [
        'meta' => 'array'
    ];

    public array $translatable = [
        'name',
        'description'
    ];

    protected function define(): void
    {
        $this->addFieldText('name', Lang::get('ecommerce::fields.name'), self::$FIELD_REQUIRED);
        $this->addFieldTextArea('description', Lang::get('ecommerce::fields.description'), self::$FIELD_OPTIONAL);
        $this->addFieldHasMany(
            'prices',
            Lang::get('ecommerce::models.price.plural'),
            self::$FIELD_OPTIONAL
        );
    }

    public function allowedApiRelations(): array
    {
        return [
            'prices'
        ];
    }

    public function allowRelationChanges($relationName = null): bool
    {
        return ($relationName === 'prices');
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(config('ecommerce.models.price'), 'priceable');
    }
}

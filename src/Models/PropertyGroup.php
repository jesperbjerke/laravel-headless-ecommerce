<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use Spatie\Translatable\HasTranslations;

class PropertyGroup extends BreadModel
{
    use SoftDeletes;
    use HasTranslations;

    public array $translatable = [
        'name'
    ];

    protected function define(): void
    {
        $this->addFieldText('name', Lang::get('ecommerce::fields.name'), self::$FIELD_REQUIRED);

        $this->addFieldHasMany(
            'properties',
            Lang::get('ecommerce::models.property.plural'),
            self::$FIELD_OPTIONAL
        );
    }

    public function allowedApiRelations(): array
    {
        return [
            'properties'
        ];
    }

    public function allowRelationChanges($relationName = null): bool
    {
        return ($relationName === 'properties');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(config('ecommerce.models.property'));
    }
}

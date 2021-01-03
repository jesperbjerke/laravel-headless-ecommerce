<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use Spatie\Translatable\HasTranslations;

class Property extends BreadModel
{
    use SoftDeletes;
    use HasTranslations;

    public array $translatable = [
        'name'
    ];

    protected function define(): void
    {
        $this->addFieldText('name', Lang::get('ecommerce::fields.name'), self::$FIELD_REQUIRED);

        $this->addFieldHasOne(
            'propertyGroup',
            Lang::get('ecommerce::models.property_group.singular'),
            self::$FIELD_OPTIONAL,
            'name'
        );
    }

    public function propertyGroup(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.property_group'));
    }

    public function propertyValues(): HasMany
    {
        return $this->hasMany(config('ecommerce.models.property_value'));
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(config('ecommerce.models.product'))
                    ->using(config('ecommerce.models.property_value'))
                    ->withPivot('value');
    }
}

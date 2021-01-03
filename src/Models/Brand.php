<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Brand extends BreadModel
{
    use SoftDeletes;
    use HasSlug;

    protected function define(): void
    {
        $this->addFieldText('name', Lang::get('ecommerce::fields.name'), self::$FIELD_REQUIRED);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
                          ->generateSlugsFrom('name')
                          ->saveSlugsTo('slug')
                          ->preventOverwrite();
    }

    public function products(): HasMany
    {
        return $this->hasMany(config('ecommerce.models.product'));
    }
}

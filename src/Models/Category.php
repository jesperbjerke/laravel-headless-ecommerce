<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Category extends BreadModel
{
    use SoftDeletes;
    use HasTranslations;
    use HasTranslatableSlug;
    use NodeTrait;

    public array $translatable = [
        'name',
        'slug'
    ];

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

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(config('ecommerce.models.product'))
                    ->using(config('ecommerce.models.category_product'));
    }
}

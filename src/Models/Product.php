<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Bjerke\Ecommerce\Models\Traits\UsesFiles;
use Bjerke\Ecommerce\Models\Traits\UsesImages;
use Bjerke\Ecommerce\Enums\ProductStatus;
use Bjerke\Ecommerce\Enums\ProductType;
use Bjerke\Ecommerce\Helpers\SearchHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Product extends BreadModel implements HasMedia
{
    use SoftDeletes;
    use HasTranslations;
    use HasTranslatableSlug;
    use InteractsWithMedia;
    use Searchable;
    use UsesFiles;
    use UsesImages {
        UsesImages::registerMediaConversions as imagesMediaConversions;
    }

    protected $hidden = [
        'media'
    ];

    protected $casts = [
        'description' => 'array',
        'meta' => 'array'
    ];

    public array $translatable = [
        'name',
        'slug',
        'excerpt',
        'description',
        'keywords'
    ];

    protected function define(): void
    {
        $this->addFieldText('sku', Lang::get('ecommerce::fields.sku'), self::$FIELD_REQUIRED, [
            'validation' => 'unique:products,sku' . (($this->exists) ? (',' . $this->id) : '')
        ]);
        $this->addFieldText('name', Lang::get('ecommerce::fields.name'), self::$FIELD_REQUIRED);
        $this->addFieldTextArea('excerpt', Lang::get('ecommerce::fields.excerpt'), self::$FIELD_OPTIONAL);
        $this->addFieldWysiwyg('description', Lang::get('ecommerce::fields.excerpt'), self::$FIELD_OPTIONAL);
        $this->addFieldText('keywords', Lang::get('ecommerce::fields.keywords'), self::$FIELD_OPTIONAL, [
            'description' => Lang::get('ecommerce::fields.descriptions.keywords')
        ]);

        $this->addFieldSelect(
            'status',
            Lang::get('ecommerce::fields.status'),
            self::$FIELD_REQUIRED,
            ProductStatus::getTranslated()
        );

        $this->addFieldSelect(
            'type',
            Lang::get('ecommerce::fields.type'),
            self::$FIELD_REQUIRED,
            ProductType::getTranslated()
        );

        $this->addFieldImageUpload(
            'main_images',
            Lang::get('ecommerce::fields.main_images'),
            true,
            self::$FIELD_OPTIONAL,
            [
                'description' => Lang::get('ecommerce::fields.descriptions.main_images')
            ]
        );

        $this->addFieldImageUpload(
            'images',
            Lang::get('ecommerce::fields.images'),
            true,
            self::$FIELD_OPTIONAL
        );

        $this->addFieldHasOne(
            'brand',
            Lang::get('ecommerce::models.brand.singular'),
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
            'categories',
            Lang::get('ecommerce::models.category.plural'),
            self::$FIELD_OPTIONAL,
            'name'
        );

        $this->addFieldHasMany(
            'stores',
            Lang::get('ecommerce::models.variation.plural'),
            self::$FIELD_OPTIONAL
        );

        $this->addFieldHasMany(
            'prices',
            Lang::get('ecommerce::models.price.plural'),
            self::$FIELD_OPTIONAL
        );

        $this->addFieldHasMany(
            'stock',
            Lang::get('ecommerce::models.stock.plural'),
            self::$FIELD_OPTIONAL
        );

        $this->addFieldHasMany(
            'propertyValues',
            Lang::get('ecommerce::models.property_value.plural'),
            self::$FIELD_OPTIONAL
        );

        $this->addFieldHasMany(
            'variations',
            Lang::get('ecommerce::models.variation.plural'),
            self::$FIELD_OPTIONAL
        );
    }

    public function allowedApiRelations(): array
    {
        return [
            'brand',
            'categories',
            'propertyValues',
            'propertyValues.property',
            'properties',
            'properties.propertyGroup',
            'prices',
            'activeDeals',
            'stocks',
            'stores',
            'variations',
            'mainProduct',
            'variations.property',
            'variations.propertyValue',
            'variations.variantProduct'
        ];
    }

    public function allowedApiAppends(): array
    {
        return [
            'main_images',
            'images',
            'files'
        ];
    }

    public function allowRelationChanges($relationName = null): bool
    {
        return (in_array($relationName, [
            'brand',
            'categories',
            'stores',
            'propertyValues',
            'variations'
        ], true));
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
                          ->generateSlugsFrom('name')
                          ->saveSlugsTo('slug');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.brand'));
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(config('ecommerce.models.store'))
                    ->using(config('ecommerce.models.product_store'));
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(config('ecommerce.models.category'))
                    ->using(config('ecommerce.models.category_product'));
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(config('ecommerce.models.price'), 'priceable');
    }

    public function deals(): BelongsToMany
    {
        return $this->belongsToMany(config('ecommerce.models.deal'));
    }

    public function activeDeals(): BelongsToMany
    {
        $now = Carbon::now();
        return $this->belongsToMany(config('ecommerce.models.deal'))
                    ->where('activates_at', '>=', $now)
                    ->where('expires_at', '<=', $now);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(config('ecommerce.models.stock'));
    }

    public function propertyValues(): HasMany
    {
        return $this->hasMany(config('ecommerce.models.property_value'));
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(config('ecommerce.models.property'))
                    ->using(config('ecommerce.models.property_value'))
                    ->withPivot('value');
    }

    public function variations(): HasMany
    {
        return $this->hasMany(config('ecommerce.models.variation'), 'main_product_id');
    }

    public function mainProduct(): HasOneThrough
    {
        return $this->hasOneThrough(
            config('ecommerce.models.product'),
            config('ecommerce.models.variation'),
            'variant_product_id',
            'id',
            'id',
            'main_product_id'
        );
    }

    public function getMainImagesAttribute(): MediaCollection
    {
        return $this->transformImagesResponse('main_images');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->imagesMediaConversions($media, ['images', 'main_images']);
    }

    public function shouldBeSearchable(): bool
    {
        return config('ecommerce.use_scout') &&
               $this->status === ProductStatus::ACTIVE &&
               $this->type !== ProductType::VARIANT;
    }

    public function toSearchableArray(): array
    {
        $searchableData = [
            'sku' => $this->sku,
            'type' => $this->type,
            'brand_id' => $this->brand_id,
            'stores' => $this->stores()->pluck('id')->toArray()
        ];

        $translatableFields = [
            'name',
            'excerpt',
            'keywords'
        ];

        $mappedTranslations = SearchHelper::transformTranslatables(array_filter(
            $this->getTranslations(),
            static fn($key) => in_array($key, $translatableFields, true),
            ARRAY_FILTER_USE_KEY
        ));

        foreach ($mappedTranslations as $translations) {
            $searchableData += $translations;
        }

        $categories = collect([]);
        $this->categories()->with('ancestors')->get()->each(function (Category $category) use ($categories) {
            $categories->push($category);
            $categories->merge($category->ancestors);
        });

        $groupedCategories = [];
        $categories->each(function (Category $category) use ($groupedCategories) {
            $name = $category->getTranslations('name');
            foreach ($name as $locale => $translation) {
                $index = 'categories_' . $locale;
                if (!isset($groupedCategories[$index])) {
                    $groupedCategories[$index] = [];
                }

                $groupedCategories[$index][] = $translation;
            }
        });

        $searchableData += $groupedCategories;

        if ($this->brand) {
            $searchableData['brand_name'] = $this->brand->name;
        }

        if ($this->main_images->isNotEmpty()) {
            $searchableData['images'] = [];
            $this->main_images->each(function ($image) use (&$searchableData) {
                if (isset($image['sizes'][config('ecommerce.media.images.search_size')])) {
                    $searchableData['images'][] = $image['sizes'][config('ecommerce.media.images.search_size')];
                }
            });
        }

        return $searchableData;
    }

    public function createVariation(
        string $sku,
        int $propertyId,
        $propertyValue,
        array $overrides = []
    ): Product {
        if (!$this->exists) {
            throw new ModelNotFoundException();
        }

        $variantProduct = $this->replicate();
        $variantProduct->sku = $sku;
        $variantProduct->status = ProductStatus::DRAFT;
        $variantProduct->type = ProductType::VARIANT;

        $variantProduct->fill($overrides);
        $variantProduct->save();

        $relationsToSync = [
            'stores',
            'categories',
        ];

        foreach ($relationsToSync as $relation) {
            $variantProduct->{$relation}()->syncWithoutDetaching($this->{$relation}()->pluck('id'));
        }

        $this->prices->each(fn(Price $price) => $variantProduct->prices()->save($price->replicate()));

        $properties = $this->propertyValues()->where('property_id', '!=', $propertyId)->get();
        $properties->each(fn(Property $property) => $variantProduct->propertyValues()->save($property->replicate()));

        $variantPropertyValue = $variantProduct->propertyValues()->create([
            'property_id' => $propertyId,
            'value' => $propertyValue
        ]);

        $media = $this->media()->get();
        $media->each(fn(Media $media) => $media->copy($variantProduct, $media->collection_name, $media->disk));

        $variationModel = config('ecommerce.models.variation');
        $variation = new $variationModel();
        $variation->property_id = $propertyId;
        $variation->property_value_id = $variantPropertyValue->id;
        $variation->main_product_id = $this->id;
        $variation->variant_product_id = $variantProduct->id;
        $variation->save();

        return $variantProduct;
    }
}

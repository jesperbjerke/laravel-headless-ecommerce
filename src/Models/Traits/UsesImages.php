<?php

namespace Bjerke\Ecommerce\Models\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait UsesImages
{
    public function transformImagesResponse($collectionName = 'images'): MediaCollection
    {
        return $this->getMedia($collectionName)->transform(
            static fn (Media $image) => [
                'id' => $image->id,
                'name' => $image->name,
                'file_name' => $image->file_name,
                'mime_type' => $image->mime_type,
                'url' => $image->getFullUrl(),
                'sizes' => array_map(
                    static function ($config, $size) use ($image) {
                        return $image->getFullUrl($size);
                    },
                    config('ecommerce.media.images.sizes'),
                    array_keys(config('ecommerce.media.images.sizes'))
                )
            ]
        );
    }

    public function getImagesAttribute(): MediaCollection
    {
        return $this->transformImagesResponse('images');
    }

    public function registerMediaConversions(Media $media = null, array $collectionNames = ['images']): void
    {
        foreach (config('ecommerce.media.images.sizes') as $size => $config) {
            $this->addMediaConversion($size)
                 ->fit($config['fit'], $config['width'], $config['height'])
                 ->performOnCollections($collectionNames);
        }
    }
}

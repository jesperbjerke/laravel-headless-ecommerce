<?php

namespace Bjerke\Ecommerce\Models\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait UsesFiles
{
    public function getFilesAttribute(): \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection
    {
        return $this->getMedia('files')->transform(
            static fn (Media $file) => [
                'id' => $file->id,
                'name' => $file->name,
                'file_name' => $file->file_name,
                'mime_type' => $file->mime_type,
                'url' => $file->getFullUrl()
            ]
        );
    }
}

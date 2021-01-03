<?php

namespace Bjerke\Ecommerce\Helpers;

use Illuminate\Http\Request;

class MediaEagerLoading
{
    /**
     * Returns if it should eager load media on the request if it appends a media property
     *
     * @param Request $request
     * @param array  $mediaProperties
     *
     * @return bool
     */
    public static function shouldEagerLoad(
        Request $request,
        array $mediaProperties = ['images', 'main_images', 'files']
    ): bool {
        $eagerLoadMedia = false;
        if (($appends = $request->get('appends')) !== null) {
            if (is_string($appends)) {
                $appends = explode(',', $appends);
            }

            foreach ($mediaProperties as $mediaProperty) {
                if (in_array($mediaProperty, $appends, true)) {
                    $eagerLoadMedia = true;
                    break;
                }
            }
        }

        return $eagerLoadMedia;
    }
}

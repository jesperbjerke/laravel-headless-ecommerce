<?php

namespace Bjerke\Ecommerce\Helpers;

class SearchHelper
{
    public static function transformTranslatables(array $translatables): array
    {
        return array_map(static function ($translations, $key) {
            $result = [];
            foreach ($translations as $locale => $translation) {
                $result[$key . '_' . $locale] = $translation;
            }
            return $result;
        }, $translatables, array_keys($translatables));
    }
}

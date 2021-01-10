<?php

namespace Bjerke\Ecommerce\Enums;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

abstract class BaseEnum extends \Bjerke\Enum\BaseEnum
{
    public static function getTranslated()
    {
        return Lang::get('ecommerce::enums.' . Str::snake(class_basename(get_called_class())));
    }
}

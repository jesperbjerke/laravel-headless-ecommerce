<?php

namespace Bjerke\Ecommerce\Exceptions;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

trait TranslatableException
{
    public function getTranslatedMessage()
    {
        return Lang::get('ecommerce::errors.' . Str::snake(class_basename($this)));
    }
}

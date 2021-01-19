<?php

namespace Bjerke\Ecommerce\Facades;

use Illuminate\Support\Facades\Facade;

class PriceHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'price.helper';
    }
}

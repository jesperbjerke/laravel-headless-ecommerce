<?php

namespace Bjerke\Ecommerce\Facades;

use Illuminate\Support\Facades\Facade;

class PaymentHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'payment.helper';
    }
}

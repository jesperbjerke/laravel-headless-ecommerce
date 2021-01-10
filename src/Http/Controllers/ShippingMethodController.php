<?php

namespace Bjerke\Ecommerce\Http\Controllers;

use Bjerke\Bread\Http\Controllers\BreadController;

class ShippingMethodController extends BreadController
{
    public function __construct()
    {
        $this->modelName = config('ecommerce.models.shipping_method');
    }
}

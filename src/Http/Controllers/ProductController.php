<?php

namespace Bjerke\Ecommerce\Http\Controllers;

use Bjerke\Bread\Http\Controllers\BreadController;

class ProductController extends BreadController
{
    public function __construct()
    {
        $this->modelName = config('ecommerce.models.product');
    }

    public function updateOrCreatePrices()
    {
        // ..
    }

    public function updateOrCreatePropertyValues()
    {
        // ..
    }

    public function updateOrCreateStocks()
    {
        // ..
    }
}

<?php

namespace Bjerke\Ecommerce\Http\Controllers;

use Bjerke\Bread\Http\Controllers\BreadController;

class StoreController extends BreadController
{
    public function __construct()
    {
        $this->modelName = config('ecommerce.models.store');
    }
}

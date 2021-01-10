<?php

namespace Bjerke\Ecommerce\Http\Controllers;

use Bjerke\Bread\Http\Controllers\BreadController;

class PropertyValueController extends BreadController
{
    public function __construct()
    {
        $this->modelName = config('ecommerce.models.property_value');
    }
}

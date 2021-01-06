<?php

namespace Bjerke\Ecommerce\Observers;

use Bjerke\Ecommerce\Models\Order;
use Illuminate\Support\Facades\App;

class OrderObserver
{
    public function creating(Order $order): void
    {
        $order->locale = App::getLocale();
    }
}

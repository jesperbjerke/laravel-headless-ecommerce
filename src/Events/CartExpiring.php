<?php

namespace Bjerke\Ecommerce\Events;

use Bjerke\Ecommerce\Models\Cart;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CartExpiring
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Cart $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }
}

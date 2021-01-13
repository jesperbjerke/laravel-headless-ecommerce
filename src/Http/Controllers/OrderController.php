<?php

namespace Bjerke\Ecommerce\Http\Controllers;

use Bjerke\Bread\Http\Controllers\BreadController;
use Bjerke\Ecommerce\Models\Order;
use Illuminate\Http\Request;

class OrderController extends BreadController
{
    public function __construct()
    {
        $this->modelName = config('ecommerce.models.order');
    }

    public function createFromCart(Request $request, string $cartId): Order
    {
        $order = Order::createFromCart($cartId);
        $order->loadMissing('orderItems');
        return $order;
    }

    public function updateFromCart(Request $request, string $orderId, string $cartId): Order
    {
        $order = Order::findOrFail($orderId);
        return $order->updateFromCart($cartId)->refresh()->loadMissing('orderItems');
    }
}

<?php

namespace Bjerke\Ecommerce\Http\Controllers;

use Bjerke\Bread\Helpers\RequestParams;
use Bjerke\Bread\Http\Controllers\BreadController;
use Bjerke\Ecommerce\Enums\OrderStatus;
use Bjerke\Ecommerce\Enums\PaidStatus;
use Bjerke\Ecommerce\Facades\PaymentHelper;
use Bjerke\Ecommerce\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function updateAddresses(Request $request, string $id)
    {
        return $this->update($request, $id, [], static function (Builder $query) {
            $query->where('status', OrderStatus::DRAFT)
                  ->where('paid_status', '!=', PaidStatus::PAID);
        });
    }

    public function updateShipping(Request $request, string $id)
    {
        $order = Order::where('status', OrderStatus::DRAFT)
                      ->where('paid_status', '!=', PaidStatus::PAID)
                      ->findOrFail($id);

        $params = RequestParams::getParams($request);
        (Validator::make($params, [
            'shipping_method_id' => 'nullable|int'
        ]))->validate();

        $order->setShippingMethod($params['shipping_method_id'] ?: null);

        return $this->loadFresh($request, $order);
    }

    public function checkout(Request $request, string $id): ?array
    {
        $order = Order::where('status', OrderStatus::DRAFT)
                      ->where('paid_status', '!=', PaidStatus::PAID)
                      ->findOrFail($id);

        $order->validate();

        $params = RequestParams::getParams($request);
        return PaymentHelper::checkout($order, $params['token'] ?: null);
    }

    public function confirmPayment(Request $request)
    {
        return PaymentHelper::confirm($request);
    }

    public function cancelPayment(Request $request)
    {
        return PaymentHelper::cancel($request);
    }

    public function refund(Request $request, string $id)
    {
        $order = Order::where('paid_status', PaidStatus::PAID)
                      ->findOrFail($id);

        return PaymentHelper::refund($request, $order);
    }
}

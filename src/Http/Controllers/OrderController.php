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

    public function checkout(Request $request, string $id): ?array
    {
        $order = Order::where('status', OrderStatus::DRAFT)
                      ->where('paid_status', '!=', PaidStatus::PAID)
                      ->findOrFail($id);

        $params = RequestParams::getParams($request);

        $authorizeResponse = PaymentHelper::checkout($order, $params['token'] ?: null);

        if ($authorizeResponse->isRedirect()) {
            return [
                'redirect_method' => $authorizeResponse->getRedirectMethod(),
                'redirect_url' => $authorizeResponse->getRedirectUrl(),
                'redirect_data' => $authorizeResponse->getRedirectData()
            ];
        }
    }

    public function confirmPayment(Request $request)
    {
        return PaymentHelper::initializeGateway()->capture($request->post())->send();
    }

    public function cancelPayment(Request $request)
    {

    }
}

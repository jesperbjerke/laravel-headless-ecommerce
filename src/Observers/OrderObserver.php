<?php

namespace Bjerke\Ecommerce\Observers;

use Bjerke\Ecommerce\Enums\OrderLogType;
use Bjerke\Ecommerce\Enums\OrderStatus;
use Bjerke\Ecommerce\Enums\PaidStatus;
use Bjerke\Ecommerce\Models\Order;
use Bjerke\Ecommerce\Models\OrderItem;
use Bjerke\Ecommerce\Models\OrderLog;
use Illuminate\Support\Facades\App;

class OrderObserver
{
    public function creating(Order $order): void
    {
        if (!$order->locale) {
            $order->locale = App::getLocale();
        }

        if (!$order->status) {
            $order->status = OrderStatus::DRAFT;
        }

        if (!$order->paid_status) {
            $order->paid_status = PaidStatus::UNPAID;
        }
    }

    public function created(Order $order): void
    {
        OrderLog::create([
            'order_id' => $order->order_id,
            'type' => OrderLogType::CREATED,
            'meta' => $order->attributesToArray()
        ]);
    }

    public function updated(Order $order): void
    {
        OrderLog::create([
            'order_id' => $order->order_id,
            'type' => OrderLogType::UPDATED,
            'meta' => $order->getChanges()
        ]);

        if (
            $order->status === OrderStatus::PENDING &&
            $order->getOriginal('status') === OrderStatus::DRAFT
        ) {
            $order->orderItems->each(fn (OrderItem $orderItem) => $orderItem->reserveStock());
        }

        if (
            ($order->status === OrderStatus::CANCELLED) ||
            ($order->status === OrderStatus::DRAFT && $order->getOriginal('status') === OrderStatus::PENDING)
        ) {
            $order->orderItems->each(fn (OrderItem $orderItem) => $orderItem->releaseReservedStock());
        }

        if (
            $order->status === OrderStatus::SHIPPED &&
            in_array(
                $order->getOriginal('status'),
                [OrderStatus::PENDING, OrderStatus::CONFIRMED, OrderStatus::PROCESSING],
                true
            )
        ) {
            $order->orderItems->each(fn (OrderItem $orderItem) => $orderItem->confirmReservedStock());
        }
    }
}

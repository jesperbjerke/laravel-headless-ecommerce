<?php

namespace Bjerke\Ecommerce\Observers;

use Bjerke\Ecommerce\Enums\OrderLogType;
use Bjerke\Ecommerce\Models\OrderItem;
use Bjerke\Ecommerce\Models\OrderLog;

class OrderItemObserver
{
    public function created(OrderItem $orderItem): void
    {
        OrderLog::create([
            'order_id' => $orderItem->order_id,
            'type' => OrderLogType::ITEM_ADDED,
            'meta' => $orderItem->attributesToArray()
        ]);
    }

    public function updated(OrderItem $orderItem): void
    {
        OrderLog::create([
            'order_id' => $orderItem->order_id,
            'type' => OrderLogType::ITEM_UPDATED,
            'meta' => $orderItem->attributesToArray()
        ]);
    }

    public function deleted(OrderItem $orderItem): void
    {
        OrderLog::create([
            'order_id' => $orderItem->order_id,
            'type' => OrderLogType::ITEM_UPDATED,
            'meta' => $orderItem->attributesToArray()
        ]);
    }
}

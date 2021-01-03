<?php

namespace Bjerke\Ecommerce\Observers;

use Bjerke\Ecommerce\Events\LowStock;
use Bjerke\Ecommerce\Models\Stock;

class StockObserver
{
    public function updated(Stock $stock): void
    {
        if ($stock->low_stock_threshold && $stock->current_quantity <= $stock->low_stock_threshold) {
            LowStock::dispatch($stock);
        }
    }
}

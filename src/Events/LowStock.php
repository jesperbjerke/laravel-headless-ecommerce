<?php

namespace Bjerke\Ecommerce\Events;

use Bjerke\Ecommerce\Models\Stock;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStock
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Stock $stock;

    public function __construct(Stock $stock)
    {
        $this->stock = $stock;
    }
}

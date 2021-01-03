<?php

namespace Bjerke\Ecommerce\Observers;

use Bjerke\Ecommerce\Jobs\SyncDeals;
use Bjerke\Ecommerce\Models\Product;

class ProductObserver
{
    public function saved(Product $product): void
    {
        /* @var $syncDealsJob SyncDeals */
        $syncDealsJob = config('ecommerce.jobs.sync_deals');
        $syncDealsJob::dispatch($product);
    }
}

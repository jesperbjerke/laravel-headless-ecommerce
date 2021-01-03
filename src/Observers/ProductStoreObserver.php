<?php

namespace Bjerke\Ecommerce\Observers;

use Bjerke\Ecommerce\Jobs\SyncDeals;
use Bjerke\Ecommerce\Models\ProductStore;

class ProductStoreObserver
{
    public function saved(ProductStore $productStore): void
    {
        /* @var $syncDealsJob SyncDeals */
        $syncDealsJob = config('ecommerce.jobs.sync_deals');
        $syncDealsJob::dispatch($productStore->product);
    }

    public function deleted(ProductStore $productStore): void
    {
        /* @var $syncDealsJob SyncDeals */
        $syncDealsJob = config('ecommerce.jobs.sync_deals');
        $syncDealsJob::dispatch($productStore->product);
    }
}

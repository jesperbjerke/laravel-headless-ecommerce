<?php

namespace Bjerke\Ecommerce\Observers;

use Bjerke\Ecommerce\Jobs\SyncDeals;
use Bjerke\Ecommerce\Models\CategoryProduct;

class CategoryProductObserver
{
    public function saved(CategoryProduct $categoryProduct): void
    {
        /* @var $syncDealsJob SyncDeals */
        $syncDealsJob = config('ecommerce.jobs.sync_deals');
        $syncDealsJob::dispatch($categoryProduct->product, $categoryProduct->category);
    }

    public function deleted(CategoryProduct $categoryProduct): void
    {
        /* @var $syncDealsJob SyncDeals */
        $syncDealsJob = config('ecommerce.jobs.sync_deals');
        $syncDealsJob::dispatch($categoryProduct->product, $categoryProduct->category);
    }
}

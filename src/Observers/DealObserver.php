<?php

namespace Bjerke\Ecommerce\Observers;

use Bjerke\Ecommerce\Jobs\SyncDealProducts;
use Bjerke\Ecommerce\Models\Deal;

class DealObserver
{
    public function saved(Deal $deal): void
    {
        /* @var $syncDealProductsJob SyncDealProducts */
        $syncDealProductsJob = config('ecommerce.jobs.sync_deal_products');
        $syncDealProductsJob::dispatch($deal);
    }
}

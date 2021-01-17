<?php

namespace Bjerke\Ecommerce\Observers;

use Bjerke\Ecommerce\Jobs\SyncPrices;
use Bjerke\Ecommerce\Models\Price;
use Bjerke\Ecommerce\Models\Product;
use Bjerke\Ecommerce\Models\ShippingMethod;

class PriceObserver
{
    public function saved(Price $price): void
    {
        if (
            $price->currency === config('ecommerce.currencies.default') &&
            config('ecommerce.currencies.auto_conversion')
        ) {
            /* @var $syncPricesJob SyncPrices */
            $syncPricesJob = config('ecommerce.jobs.sync_prices');

            if ($price->priceable instanceof Product) {
                $syncPricesJob::dispatch($price->priceable);
            } elseif ($price->priceable instanceof ShippingMethod) {
                $syncPricesJob::dispatch(null, $price->priceable);
            }
        }
    }
}

<?php

namespace Bjerke\Ecommerce\Observers;

use Bjerke\Ecommerce\Models\Variation;

class VariationObserver
{
    public function creating(Variation $variation): void
    {
        if (!$variation->sync_options || empty($variation->sync_options)) {
            $variation->sync_options = Variation::getDefaultSyncOptions();
        }
    }
}

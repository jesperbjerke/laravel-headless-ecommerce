<?php

namespace Bjerke\Ecommerce\Jobs;

use Bjerke\Ecommerce\Models\Product;
use Bjerke\Ecommerce\Models\Variation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncVariations implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Product $product;
    public bool $recursive = true;

    public function __construct(Product $product, bool $recursive = true)
    {
        $this->product = $product;
        $this->recursive = $recursive;
    }

    public function uniqueId()
    {
        return 'syncVariations.' . $this->product->id;
    }

    public function handle()
    {
        $variations = $this->product->variations()->with('variantProduct')->get();

        /* @var $syncVariantProductJob SyncVariantProduct */
        $syncVariantProductJob = config('ecommerce.jobs.sync_variant_product');

        $variations->each(fn(Variation $variation) => $syncVariantProductJob::dispatch($variation, $this->product));

        $this->product->refresh();

        if ($this->recursive) {
            /* @var $syncVariationsJob SyncVariations */
            $syncVariationsJob = config('ecommerce.jobs.sync_variations');
            $variations->each(
                fn(Variation $variation) => $syncVariationsJob::dispatch($variation->variantProduct, true)
            );
        }
    }
}

<?php

namespace Bjerke\Ecommerce\Jobs;

use Bjerke\Ecommerce\Models\Category;
use Bjerke\Ecommerce\Models\Deal;
use Bjerke\Ecommerce\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncDeals implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public ?Category $category;
    public ?Product $product;

    public function __construct(Product $product = null, Category $category = null)
    {
        $this->category = $category;
        $this->product = $product;
    }

    public function handle()
    {
        $dealQuery = Deal::query();

        $categories = collect([]);
        if ($this->category) {
            $categories->push($this->category);
            $categories->merge($this->category->ancestors);
        }

        if ($this->product) {
            $this->product->load('categories.ancestors');
            $this->product->categories->each(function (Category $category) use ($categories) {
                $categories->push($category);
                $categories->merge($category->ancestors);
            });

            $dealQuery->orWhereHas('applicableProducts', function (Builder $query) {
                $query->where('id', $this->product->id);
            });

            $dealQuery->orWhereHas('products', function (Builder $query) {
                $query->where('id', $this->product->id);
            });

            $dealQuery->orWhereHas('brands', function (Builder $query) {
                $query->where('id', $this->product->brand_id);
            });
        }

        if ($categories->isNotEmpty()) {
            $dealQuery->orWhereHas('categories', function (Builder $query) use ($categories) {
                $query->whereIn('id', $categories->pluck('id'));
            });
        }

        $dealQuery->chunkById(50, function (Collection $deals) {
            /* @var $syncDealProductsJob SyncDealProducts */
            $syncDealProductsJob = config('ecommerce.jobs.sync_deal_products');
            $deals->each(fn (Deal $deal) => $syncDealProductsJob::dispatch($deal));
        });
    }
}

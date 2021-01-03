<?php

namespace Bjerke\Ecommerce\Jobs;

use Bjerke\Ecommerce\Models\Category;
use Bjerke\Ecommerce\Models\Deal;
use Bjerke\Ecommerce\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncDealProducts implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Deal $deal;

    public function __construct(Deal $deal)
    {
        $this->deal = $deal;
    }

    public function uniqueId()
    {
        return 'syncDealProducts.' . $this->deal->id;
    }

    public function handle()
    {
        $this->deal->load([
            'products' => function (\Illuminate\Database\Query\Builder $query) {
                $query->select('id');
            },
            'categories.descendants',
            'brands' => function (\Illuminate\Database\Query\Builder $query) {
                $query->select('id');
            }
        ]);

        $categories = collect([]);
        $this->deal->categories->each(function (Category $category) use ($categories) {
            $categories->push($category);
            $categories->merge($category->descendants);
        });

        $productQuery = Product::query();
        if ($this->deal->store_id) {
            $productQuery->where('store_id', $this->deal->store_id);
        }

        $products = $productQuery->where(function (Builder $query) use ($categories) {
            if ($categories->isNotEmpty()) {
                $query->whereHas(
                    'categories',
                    function (\Illuminate\Database\Query\Builder $query) use ($categories) {
                        $query->whereIn('id', $categories->pluck('id'));
                    }
                );
            }

            if ($this->deal->products->isNotEmpty()) {
                $query->orWhereIn('id', $this->deal->products->pluck('id'));
            }

            if ($this->deal->brands->isNotEmpty()) {
                $query->orWhereIn('brand_id', $this->deal->brands->pluck('id'));
            }
        })->pluck('id');

        $this->deal->applicableProducts()->sync($products);
    }
}

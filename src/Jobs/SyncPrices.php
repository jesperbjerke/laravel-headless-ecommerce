<?php

namespace Bjerke\Ecommerce\Jobs;

use Bjerke\Ecommerce\Helpers\PriceHelper;
use Bjerke\Ecommerce\Models\Price;
use Bjerke\Ecommerce\Models\Product;
use Bjerke\Ecommerce\Models\ShippingMethod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Money\Currency;
use Money\Money;

class SyncPrices implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public ?Product $product;
    public ?ShippingMethod $shippingMethod;

    public function __construct(Product $product = null, ShippingMethod $shippingMethod = null)
    {
        $this->product = $product;
        $this->shippingMethod = $shippingMethod;
    }

    public function handle()
    {
        $currenciesToConvert = array_filter(
            array_values(config('ecommerce.currencies.available')),
            fn ($currency) => $currency !== config('ecommerce.currencies.default')
        );

        if (empty($currenciesToConvert)) {
            return;
        }

        $priceQuery = Price::query()->where('currency', config('ecommerce.currencies.default'));

        if ($this->product || $this->shippingMethod) {
            $morphs = [];

            if ($this->product) {
                $morphs[] = Product::class;
            }

            if ($this->shippingMethod) {
                $morphs[] = ShippingMethod::class;
            }

            $priceQuery->whereHasMorph(
                'priceable',
                $morphs,
                function (Builder $query, $type) {
                    $query->where('id', ($type === Product::class) ? $this->product->id : $this->shippingMethod->id);
                }
            );
        }

        $priceQuery->chunkById(100, function (Collection $prices) use ($currenciesToConvert) {
            $prices->each(fn (Price $price) => $this->createOrUpdatePrices($price, $currenciesToConvert));
        });
    }

    private function createOrUpdatePrices(Price $basePrice, array $currenciesToConvert)
    {
        Price::$defineOnConstruct = true;
        foreach ($currenciesToConvert as $currency) {
            Price::updateOrCreate([
                'priceable_type' => $basePrice->priceable_type,
                'priceable_id' => $basePrice->priceable_id,
                'store_id' => $basePrice->store_id,
                'currency' => $currency
            ], [
                'value' => PriceHelper::getConvertedValue(
                    new Money($basePrice->value, new Currency($basePrice->currency)),
                    $currency
                ),
                'discounted_value' => ($basePrice->discounted_value) ? PriceHelper::getConvertedValue(
                    new Money($basePrice->discounted_value, new Currency($basePrice->currency)),
                    $currency
                ) : 0,
                'vat_percentage' => $basePrice->vat_percentage
            ]);
        }
        Price::$defineOnConstruct = false;
    }
}

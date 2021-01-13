<?php

namespace Bjerke\Ecommerce\Jobs;

use Bjerke\Ecommerce\Models\Price;
use Bjerke\Ecommerce\Models\Product;
use Bjerke\Ecommerce\Models\Property;
use Bjerke\Ecommerce\Models\Variation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncVariantProduct implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Variation $variation;
    public Product $product;

    public function __construct(Variation $variation, Product $product)
    {
        $this->variation = $variation;
        $this->product = $product;
    }

    public function uniqueId()
    {
        return 'syncVariantProduct.' . $this->variation->id;
    }

    public function handle()
    {
        $attributes = $this->product->getAttributes();

        if (
            isset($this->variation->sync_options['attributes']) &&
            $this->variation->sync_options['attributes']
        ) {
            foreach ($attributes as $attributeName => $attributeValue) {
                $this->variation->variantProduct->{$attributeName} = $attributeValue;
            }

            $this->variation->variantProduct->save();
        }

        if (
            isset($this->variation->sync_options['relations']) &&
            is_array($this->variation->sync_options['relations'])
        ) {
            foreach ($this->variation->sync_options['relations'] as $relation) {
                $this->variation->variantProduct->{$relation}()->sync($this->product->{$relation}()->pluck('id'));
            }
        }

        if (
            isset($this->variation->sync_options['prices']) &&
            $this->variation->sync_options['prices']
        ) {
            $this->variation->variantProduct->prices()->delete();
            $this->product->prices->each(
                fn (Price $price) => $this->variation->variantProduct->prices()->save($price->replicate())
            );
        }

        if (
            isset($this->variation->sync_options['property_values']) &&
            $this->variation->sync_options['property_values']
        ) {
            $this->variation->variantProduct->propertyValues()
                                            ->where('property_id', '!=', $this->variation->property_id)
                                            ->delete();

            $properties = $this->product->propertyValues()
                                        ->where('property_id', '!=', $this->variation->property_id)
                                        ->get();
            $properties->each(
                fn (Property $property) =>
                $this->variation->variantProduct->propertyValues()->save($property->replicate())
            );
        }

        $this->variation->save();
    }
}

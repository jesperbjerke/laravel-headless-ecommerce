<?php

namespace Bjerke\Ecommerce\Http\Controllers;

use Bjerke\Bread\Helpers\RequestParams;
use Bjerke\Bread\Http\Controllers\BreadController;
use Bjerke\Ecommerce\Models\Price;
use Bjerke\Ecommerce\Models\Product;
use Bjerke\Ecommerce\Models\PropertyValue;
use Bjerke\Ecommerce\Models\Stock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends BreadController
{
    public function __construct()
    {
        $this->modelName = config('ecommerce.models.product');
    }

    public function create(Request $request, $with = [], $manualAttributes = [], $beforeSave = null)
    {
        /* @var $product Product */
        $product = parent::create($request, $with, $manualAttributes, $beforeSave);
        return $this->updateOrCreateConvenienceRelations($request, $product, $with);
    }

    public function update(Request $request, $id, $with = [], $applyQuery = null, $beforeSave = null)
    {
        /* @var $product Product */
        $product = parent::update($request, $id, $with, $applyQuery, $beforeSave);
        return $this->updateOrCreateConvenienceRelations($request, $product, $with);
    }

    protected function updateOrCreateConvenienceRelations(Request $request, Product $product, array $with): Model
    {
        $attributes = RequestParams::getParams($request);

        $this->updateOrCreatePrices($product, $attributes);
        $this->updateOrCreatePropertyValues($product, $attributes);
        $this->updateOrCreateStocks($product, $attributes);

        return $this->loadFresh($request, $product, $with);
    }

    protected function updateOrCreatePrices(Product $product, array $attributes): void
    {
        if (
            !isset($attributes['prices']) ||
            !is_array($attributes['prices']) ||
            Auth::user()->cannot('manage-prices')
        ) {
            return;
        }

        Price::$defineOnConstruct = true;
        $priceModel = new Price();
        foreach ($attributes['prices'] as $priceData) {
            if (isset($priceData['id'])) {
                $price = $product->prices()->find($priceData['id']);
                if (!$price) {
                    continue;
                }
                $price->update($this->filterFillables($priceModel, $priceData));
            } else {
                $product->prices()->create($this->filterFillables($priceModel, $priceData));
            }
        }
        Price::$defineOnConstruct = false;
    }

    protected function updateOrCreatePropertyValues(Product $product, array $attributes): void
    {
        if (
            !isset($attributes['property_values']) ||
            !is_array($attributes['property_values']) ||
            Auth::user()->cannot('manage-properties')
        ) {
            return;
        }

        PropertyValue::$defineOnConstruct = true;
        foreach ($attributes['property_values'] as $data) {
            $product->propertyValues()->updateOrCreate(
                ['property_id' => $data['property_id']],
                ['value' => $data['value']]
            );
        }
        PropertyValue::$defineOnConstruct = false;
    }

    protected function updateOrCreateStocks(Product $product, array $attributes): void
    {
        if (
            !isset($attributes['stocks']) ||
            !is_array($attributes['stocks']) ||
            Auth::user()->cannot('manage-stocks')
        ) {
            return;
        }

        Stock::$defineOnConstruct = true;
        $stockModel = new Stock();
        foreach ($attributes['stocks'] as $stockData) {
            if (isset($stockData['id'])) {
                $stock = $product->stocks()->find($stockData['id']);
                if (!$stock) {
                    continue;
                }
                $stock->update($this->filterFillables($stockModel, $stockData));
            } else {
                $product->stocks()->create($this->filterFillables($stockModel, $stockData));
            }
        }
        Stock::$defineOnConstruct = false;
    }

    protected function filterFillables(Model $model, array $attributes): array
    {
        $fillable = $model->getFillable();
        return array_filter($attributes, static function ($key) use ($fillable) {
            return in_array($key, $fillable, true);
        }, ARRAY_FILTER_USE_KEY);
    }
}

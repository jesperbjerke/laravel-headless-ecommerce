<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Bjerke\Ecommerce\Exceptions\CorruptCartPricing;
use Bjerke\Ecommerce\Exceptions\InvalidCartItemQuantity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends BreadModel
{
    protected $casts = [
        'meta' => 'array'
    ];

    protected $touches = [
        'cart'
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.cart'));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(config('ecommerce.models.product'));
    }

    public function getTotalsAttribute(): array
    {
        return $this->calculateTotals($this->cart->currency, $this->cart->store_id);
    }

    public function calculateTotals(string $currency, int $storeId = null): array
    {
        $this->loadMissing(['product.activeDeals', 'product.prices']);

        /* @var $price Price|null */
        $price = $this->product->prices
            ->where('currency', $currency)
            ->where('store_id', $storeId)
            ->first();

        if (!$price) {
            throw new CorruptCartPricing();
        }

        return $price->calculateTotals($this->quantity);
    }

    public function validateContents(string $currency, int $storeId = null)
    {
        $this->loadMissing(['product.stocks', 'product.prices']);

        /* @var $stock Stock|null */
        $stock = $this->product->stocks
            ->where('store_id', $storeId)
            ->first();

        $availableStock = ($stock) ? ($stock->current_quantity - $stock->outgoing_quantity) : 0;
        if ($availableStock < $this->quantity) {
            throw new InvalidCartItemQuantity();
        }

        /* @var $price Price|null */
        $price = $this->product->prices
            ->where('currency', $currency)
            ->where('store_id', $storeId)
            ->first();

        if (!$price) {
            throw new CorruptCartPricing();
        }
    }
}

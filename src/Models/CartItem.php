<?php

namespace Bjerke\Ecommerce\Models;

use Bjerke\Bread\Models\BreadModel;
use Bjerke\Ecommerce\Exceptions\CorruptCartPricing;
use Bjerke\Ecommerce\Exceptions\InvalidCartItemProduct;
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

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity'
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

    public function getActivePrice(string $currency, int $storeId = null): Price
    {
        $this->loadMissing(['product.prices']);

        if (!$this->product) {
            throw new InvalidCartItemProduct();
        }

        /* @var $price Price|null */
        $price = $this->product->prices
            ->where('currency', $currency)
            ->where('store_id', $storeId)
            ->first();

        if (!$price) {
            throw new CorruptCartPricing();
        }

        return $price;
    }

    public function calculateTotals(string $currency, int $storeId = null): array
    {
        $this->loadMissing(['product.activeDeals']);
        return $this->getActivePrice($currency, $storeId)->calculateTotals($this->quantity);
    }

    public function validateContents(string $currency, int $storeId = null): bool
    {
        $this->loadMissing(['product.stocks', 'product.prices']);

        if (!$this->product) {
            throw new InvalidCartItemProduct();
        }

        self::validateStock($this->product, $this->quantity, $storeId);
        self::validatePricing($this->product, $currency, $storeId);

        return true;
    }

    public static function validateStock(Product $product, int $quantity, int $storeId = null): bool
    {
        /* @var $stock Stock|null */
        $stock = $product->stocks
            ->where('store_id', $storeId)
            ->first();

        $availableStock = ($stock) ? ($stock->current_quantity - $stock->outgoing_quantity) : 0;
        if ($availableStock < $quantity) {
            throw new InvalidCartItemQuantity();
        }

        return true;
    }

    public static function validatePricing(Product $product, string $currency, int $storeId = null): bool
    {
        /* @var $price Price|null */
        $price = $product->prices
            ->where('currency', $currency)
            ->where('store_id', $storeId)
            ->first();

        if (!$price) {
            throw new CorruptCartPricing();
        }

        return true;
    }
}

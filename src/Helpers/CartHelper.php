<?php

namespace Bjerke\Ecommerce\Helpers;

use Bjerke\Ecommerce\Models\CartItem;
use Bjerke\Ecommerce\Models\Stock;

class CartHelper
{
    public static function validateCartItem(CartItem $cartItem, int $storeId = null)
    {
        $cartItem->loadMissing('product.stocks');

        /* @var $stock Stock|null */
        $stock = $cartItem->product->stocks
            ->where('store_id', $storeId)
            ->first();

        $availableStock = ($stock) ? ($stock->current_quantity - $stock->outgoing_quantity) : 0;
        if ($availableStock >= $cartItem->quantity) {

        }
    }
}

<?php

namespace Bjerke\Ecommerce\Http\Controllers;

use Bjerke\Bread\Helpers\RequestParams;
use Bjerke\Bread\Http\Controllers\BreadController;
use Bjerke\Ecommerce\Models\Cart;
use Bjerke\Ecommerce\Models\CartItem;
use Bjerke\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CartController extends BreadController
{
    public function __construct()
    {
        $this->modelName = config('ecommerce.models.cart');
    }

    public function view(Request $request, $id, $applyQuery = null)
    {
        Cart::mergeAppends(['totals']);
        CartItem::mergeAppends(['totals']);

        /* @var $cart Cart */
        $cart = parent::view($request, $id, $applyQuery);
        $cart->validateCart();

        return $cart;
    }

    public function create(Request $request, $with = [], $manualAttributes = [], $beforeSave = null)
    {
        Cart::mergeAppends(['totals']);
        return parent::create($request, $with, $manualAttributes, $beforeSave);
    }

    public function addCartItem(Request $request, int $id)
    {
        Cart::mergeAppends(['totals']);
        CartItem::mergeAppends(['totals']);

        $cart = Cart::with('cartItems.products.stocks')->findOrFail($id);

        $validatedData = (Validator::make(RequestParams::getParams($request), [
            'product_id' => [
                'required',
                'int'
            ],
            'quantity' => [
                'required',
                'int'
            ]
        ]))->validate();

        /* @var $existingItem CartItem|null */
        $existingItem = $cart->cartItems->firstWhere('product_id', $validatedData['product_id']);
        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $validatedData['quantity'];
            CartItem::validateStock($existingItem->product, $newQuantity);

            $existingItem->quantity = $newQuantity;
            $existingItem->save();
        } else {
            $productQuery = Product::query();

            $productQuery->where(function (Builder $query) use ($cart) {
                $query->whereDoesntHave('stores');

                if ($cart->store_id) {
                    $query->orWhereHas(
                        'stores',
                        function (Builder $query) use ($cart) {
                            $query->where('id', $cart->store_id);
                        }
                    );
                }
            });

            $productQuery->whereHas('prices', function (Builder $query) use ($cart) {
                $query->where('currency', $cart->currency);
                $query->where(function (Builder $query) use ($cart) {
                    $query->whereNull('store_id');

                    if ($cart->store_id) {
                        $query->orWhere('store_id', $cart->store_id);
                    }
                });
            });

            /* @var $product Product|null */
            $product = $productQuery->with('stocks')->first();
            if (!$product) {
                throw ValidationException::withMessages([
                    'product_id' => [Lang::get('ecommerce::errors.product_invalid')]
                ]);
            }

            CartItem::validateStock($product, $validatedData['quantity']);

            $cart->cartItems()->create([
                'product_id' => $validatedData['product_id'],
                'quantity' => $validatedData['quantity']
            ]);
        }

        return $cart->refresh();
    }
}

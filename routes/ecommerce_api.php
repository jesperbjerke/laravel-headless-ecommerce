<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ecommerce API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the ServiceProvider in the Laravel Headless
| Ecommerce package. All routes are grouped with the configuration
| "ecommerce.routing.root"
|
| Controllers used are defined in that same configuration file.
|
*/

/**
 * Products
 */
Route::group(['prefix' => 'products'], static function () {
    Route::get('definition', [config('ecommerce.controllers.product'), 'definition']);

    Route::get('{id}', [config('ecommerce.controllers.product'), 'view']);
    Route::get('', [config('ecommerce.controllers.product'), 'index']);

    Route::group([
        'middleware' => [
            config('ecommerce.routing.auth.middleware'),
            'can:manage-products'
        ]
    ], static function () {
        Route::post('', [config('ecommerce.controllers.product'), 'create']);
        Route::patch('{id}', [config('ecommerce.controllers.product'), 'update']);
        Route::delete('{id}', [config('ecommerce.controllers.product'), 'delete']);
        Route::delete('{id}/detach/{relatedModel}', [config('ecommerce.controllers.product'), 'detach']);
        Route::put('{id}/attach/{relatedModel}', [config('ecommerce.controllers.product'), 'attach']);
    });
});

/**
 * Brands
 */
Route::group(['prefix' => 'brands'], static function () {
    Route::get('definition', [config('ecommerce.controllers.brand'), 'definition']);

    Route::get('', [config('ecommerce.controllers.brand'), 'index']);
    Route::get('{id}', [config('ecommerce.controllers.brand'), 'view']);

    Route::group([
        'middleware' => [
            config('ecommerce.routing.auth.middleware'),
            'can:manage-brands'
        ]
    ], static function () {
        Route::post('', [config('ecommerce.controllers.brand'), 'create']);
        Route::patch('{id}', [config('ecommerce.controllers.brand'), 'update']);
        Route::delete('{id}', [config('ecommerce.controllers.brand'), 'delete']);
        Route::delete('{id}/detach/{relatedModel}', [config('ecommerce.controllers.brand'), 'detach']);
        Route::put('{id}/attach/{relatedModel}', [config('ecommerce.controllers.brand'), 'attach']);
    });
});

/**
 * Categories
 */
Route::group(['prefix' => 'categories'], static function () {
    Route::get('definition', [config('ecommerce.controllers.category'), 'definition']);

    Route::get('', [config('ecommerce.controllers.category'), 'index']);
    Route::get('{id}', [config('ecommerce.controllers.category'), 'view']);

    Route::group([
        'middleware' => [
            config('ecommerce.routing.auth.middleware'),
            'can:manage-categories'
        ]
    ], static function () {
        Route::post('', [config('ecommerce.controllers.category'), 'create']);
        Route::patch('{id}', [config('ecommerce.controllers.category'), 'update']);
        Route::delete('{id}', [config('ecommerce.controllers.category'), 'delete']);
        Route::delete('{id}/detach/{relatedModel}', [config('ecommerce.controllers.category'), 'detach']);
        Route::put('{id}/attach/{relatedModel}', [config('ecommerce.controllers.category'), 'attach']);
    });
});

/**
 * Deals
 */
Route::group(['prefix' => 'deals'], static function () {
    Route::group([
        'middleware' => [
            config('ecommerce.routing.auth.middleware'),
            'can:manage-deals'
        ]
    ], static function () {
        Route::get('definition', [config('ecommerce.controllers.deal'), 'definition']);

        Route::get('', [config('ecommerce.controllers.deal'), 'index']);
        Route::get('{id}', [config('ecommerce.controllers.deal'), 'view']);
        Route::post('', [config('ecommerce.controllers.deal'), 'create']);
        Route::patch('{id}', [config('ecommerce.controllers.deal'), 'update']);
        Route::delete('{id}', [config('ecommerce.controllers.deal'), 'delete']);
        Route::delete('{id}/detach/{relatedModel}', [config('ecommerce.controllers.deal'), 'detach']);
        Route::put('{id}/attach/{relatedModel}', [config('ecommerce.controllers.deal'), 'attach']);
    });
});

/**
 * Orders
 */
Route::group(['prefix' => 'orders'], static function () {
    Route::get('definition', [config('ecommerce.controllers.order'), 'definition']);

    Route::get('{id}', [config('ecommerce.controllers.order'), 'view']);
    Route::post('from-cart/{cartId}', [config('ecommerce.controllers.order'), 'createFromCart']);
    Route::patch('{orderId}/from-cart/{cartId}', [config('ecommerce.controllers.order'), 'updateFromCart']);

    Route::post('{id}/checkout', [config('ecommerce.controllers.order'), 'checkout']);
    Route::patch('{id}/addresses', [config('ecommerce.controllers.order'), 'updateAddresses']);
    Route::patch('{id}/shipping', [config('ecommerce.controllers.order'), 'updateShipping']);
    Route::patch('{id}/confirm', [config('ecommerce.controllers.order'), 'confirmPayment']);
    Route::patch('{id}/cancel', [config('ecommerce.controllers.order'), 'cancelPayment']);

    Route::group([
        'middleware' => [
            config('ecommerce.routing.auth.middleware'),
            'can:manage-orders'
        ]
    ], static function () {
        Route::get('', [config('ecommerce.controllers.order'), 'index']);
        Route::post('', [config('ecommerce.controllers.order'), 'create']);
        Route::patch('{id}', [config('ecommerce.controllers.order'), 'update']);
        Route::delete('{id}', [config('ecommerce.controllers.order'), 'delete']);
        Route::delete('{id}/detach/{relatedModel}', [config('ecommerce.controllers.order'), 'detach']);
        Route::put('{id}/attach/{relatedModel}', [config('ecommerce.controllers.order'), 'attach']);

        Route::post('{id}/refund', [config('ecommerce.controllers.order'), 'refund']);
    });
});

/**
 * Prices
 */
Route::group(['prefix' => 'prices'], static function () {
    Route::group([
        'middleware' => [
            config('ecommerce.routing.auth.middleware'),
            'can:manage-prices'
        ]
    ], static function () {
        Route::get('definition', [config('ecommerce.controllers.price'), 'definition']);

        Route::get('{id}', [config('ecommerce.controllers.price'), 'view']);
        Route::patch('{id}', [config('ecommerce.controllers.price'), 'update']);
        Route::get('', [config('ecommerce.controllers.price'), 'index']);
        Route::post('', [config('ecommerce.controllers.price'), 'create']);
        Route::patch('{id}', [config('ecommerce.controllers.price'), 'update']);
        Route::delete('{id}', [config('ecommerce.controllers.price'), 'delete']);
        Route::delete('{id}/detach/{relatedModel}', [config('ecommerce.controllers.price'), 'detach']);
        Route::put('{id}/attach/{relatedModel}', [config('ecommerce.controllers.price'), 'attach']);
    });
});

/**
 * Carts
 */
Route::group(['prefix' => 'carts'], static function () {
    Route::post('', [config('ecommerce.controllers.cart'), 'create']);
    Route::get('{id}', [config('ecommerce.controllers.cart'), 'view']);
    Route::post('{id}/items', [config('ecommerce.controllers.cart'), 'addCartItem']);
    Route::patch('{id}/items/{itemId}', [config('ecommerce.controllers.cart'), 'updateCartItem']);
    Route::delete('{id}/items/{itemId}', [config('ecommerce.controllers.cart'), 'deleteCartItem']);
});

/**
 * Property groups
 */
Route::group(['prefix' => 'property-groups'], static function () {
    Route::group([
        'middleware' => [
            config('ecommerce.routing.auth.middleware'),
            'can:manage-properties'
        ]
    ], static function () {
        Route::get('definition', [config('ecommerce.controllers.property_group'), 'definition']);

        Route::get('{id}', [config('ecommerce.controllers.property_group'), 'view']);
        Route::patch('{id}', [config('ecommerce.controllers.property_group'), 'update']);
        Route::get('', [config('ecommerce.controllers.property_group'), 'index']);
        Route::post('', [config('ecommerce.controllers.property_group'), 'create']);
        Route::patch('{id}', [config('ecommerce.controllers.property_group'), 'update']);
        Route::delete('{id}', [config('ecommerce.controllers.property_group'), 'delete']);
        Route::delete('{id}/detach/{relatedModel}', [config('ecommerce.controllers.property_group'), 'detach']);
        Route::put('{id}/attach/{relatedModel}', [config('ecommerce.controllers.property_group'), 'attach']);
    });
});

/**
 * Properties
 */
Route::group(['prefix' => 'properties'], static function () {
    Route::group([
    'middleware' => [
            config('ecommerce.routing.auth.middleware'),
            'can:manage-properties'
        ]
    ], static function () {
        Route::get('definition', [config('ecommerce.controllers.property'), 'definition']);

        Route::get('{id}', [config('ecommerce.controllers.property'), 'view']);
        Route::patch('{id}', [config('ecommerce.controllers.property'), 'update']);
        Route::get('', [config('ecommerce.controllers.property'), 'index']);
        Route::post('', [config('ecommerce.controllers.property'), 'create']);
        Route::patch('{id}', [config('ecommerce.controllers.property'), 'update']);
        Route::delete('{id}', [config('ecommerce.controllers.property'), 'delete']);
        Route::delete('{id}/detach/{relatedModel}', [config('ecommerce.controllers.property'), 'detach']);
        Route::put('{id}/attach/{relatedModel}', [config('ecommerce.controllers.property'), 'attach']);
    });
});

/**
 * Property values
 */
Route::group(['prefix' => 'property-values'], static function () {
    Route::group([
    'middleware' => [
            config('ecommerce.routing.auth.middleware'),
            'can:manage-properties'
        ]
    ], static function () {
        Route::get('definition', [config('ecommerce.controllers.property_value'), 'definition']);

        Route::get('{id}', [config('ecommerce.controllers.property_value'), 'view']);
        Route::patch('{id}', [config('ecommerce.controllers.property_value'), 'update']);
        Route::get('', [config('ecommerce.controllers.property_value'), 'index']);
        Route::post('', [config('ecommerce.controllers.property_value'), 'create']);
        Route::patch('{id}', [config('ecommerce.controllers.property_value'), 'update']);
        Route::delete('{id}', [config('ecommerce.controllers.property_value'), 'delete']);
    });
});

/**
 * Shipping methods
 */
Route::group(['prefix' => 'shipping-methods'], static function () {
    Route::group([
        'middleware' => [
            config('ecommerce.routing.auth.middleware'),
            'can:manage-shipping-methods'
        ]
    ], static function () {
        Route::get('definition', [config('ecommerce.controllers.shipping_method'), 'definition']);

        Route::get('{id}', [config('ecommerce.controllers.shipping_method'), 'view']);
        Route::patch('{id}', [config('ecommerce.controllers.shipping_method'), 'update']);
        Route::get('', [config('ecommerce.controllers.shipping_method'), 'index']);
        Route::post('', [config('ecommerce.controllers.shipping_method'), 'create']);
        Route::patch('{id}', [config('ecommerce.controllers.shipping_method'), 'update']);
        Route::delete('{id}', [config('ecommerce.controllers.shipping_method'), 'delete']);
        Route::delete('{id}/detach/{relatedModel}', [config('ecommerce.controllers.shipping_method'), 'detach']);
        Route::put('{id}/attach/{relatedModel}', [config('ecommerce.controllers.shipping_method'), 'attach']);
    });
});

/**
 * Stocks
 */
Route::group(['prefix' => 'stocks'], static function () {
    Route::group([
        'middleware' => [
            config('ecommerce.routing.auth.middleware'),
            'can:manage-stocks'
        ]
    ], static function () {
        Route::get('definition', [config('ecommerce.controllers.stock'), 'definition']);

        Route::get('{id}', [config('ecommerce.controllers.stock'), 'view']);
        Route::patch('{id}', [config('ecommerce.controllers.stock'), 'update']);
        Route::get('', [config('ecommerce.controllers.stock'), 'index']);
        Route::post('', [config('ecommerce.controllers.stock'), 'create']);
        Route::patch('{id}', [config('ecommerce.controllers.stock'), 'update']);
        Route::delete('{id}', [config('ecommerce.controllers.stock'), 'delete']);
        Route::delete('{id}/detach/{relatedModel}', [config('ecommerce.controllers.stock'), 'detach']);
        Route::put('{id}/attach/{relatedModel}', [config('ecommerce.controllers.stock'), 'attach']);
    });
});

/**
 * Stores
 */
Route::group(['prefix' => 'stores'], static function () {
    Route::get('', [config('ecommerce.controllers.store'), 'index']);

    Route::group([
        'middleware' => [
            config('ecommerce.routing.auth.middleware'),
            'can:manage-stores'
        ]
    ], static function () {
        Route::get('definition', [config('ecommerce.controllers.store'), 'definition']);

        Route::get('{id}', [config('ecommerce.controllers.store'), 'view']);
        Route::patch('{id}', [config('ecommerce.controllers.store'), 'update']);
        Route::post('', [config('ecommerce.controllers.store'), 'create']);
        Route::patch('{id}', [config('ecommerce.controllers.store'), 'update']);
        Route::delete('{id}', [config('ecommerce.controllers.store'), 'delete']);
        Route::delete('{id}/detach/{relatedModel}', [config('ecommerce.controllers.store'), 'detach']);
        Route::put('{id}/attach/{relatedModel}', [config('ecommerce.controllers.store'), 'attach']);
    });
});

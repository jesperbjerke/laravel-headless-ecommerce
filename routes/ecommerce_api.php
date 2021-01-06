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
    Route::get('', [config('ecommerce.controllers.product'), 'index']);
});

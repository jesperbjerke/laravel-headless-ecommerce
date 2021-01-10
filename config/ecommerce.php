<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Holds what model classes to use, for easy extendability
    |--------------------------------------------------------------------------
    |
    | Override these values with your own classes and extending the originals
    |
    */
    'models' => [
        'brand' => Bjerke\Ecommerce\Models\Brand::class,
        'product' => Bjerke\Ecommerce\Models\Product::class,
        'product_store' => Bjerke\Ecommerce\Models\ProductStore::class,
        'category' => Bjerke\Ecommerce\Models\Category::class,
        'category_product' => Bjerke\Ecommerce\Models\CategoryProduct::class,
        'property' => Bjerke\Ecommerce\Models\Property::class,
        'property_group' => Bjerke\Ecommerce\Models\PropertyGroup::class,
        'property_value' => Bjerke\Ecommerce\Models\PropertyValue::class,
        'variation' => Bjerke\Ecommerce\Models\Variation::class,
        'store' => Bjerke\Ecommerce\Models\Store::class,
        'price' => Bjerke\Ecommerce\Models\Price::class,
        'stock' => Bjerke\Ecommerce\Models\Stock::class,
        'cart' => Bjerke\Ecommerce\Models\Cart::class,
        'cart_item' => Bjerke\Ecommerce\Models\CartItem::class,
        'deal' => Bjerke\Ecommerce\Models\Deal::class,
        'order' => Bjerke\Ecommerce\Models\Order::class,
        'order_item' => Bjerke\Ecommerce\Models\OrderItem::class,
        'shipping_method' => Bjerke\Ecommerce\Models\ShippingMethod::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Holds what job classes to use, for easy extendability
    |--------------------------------------------------------------------------
    |
    | Override these values with your own classes and extending the originals
    |
    */
    'jobs' => [
        'sync_deals' => Bjerke\Ecommerce\Jobs\SyncDeals::class,
        'sync_deal_products' => Bjerke\Ecommerce\Jobs\SyncDealProducts::class,
        'sync_variations' => Bjerke\Ecommerce\Jobs\SyncVariations::class,
        'sync_variant_product' => Bjerke\Ecommerce\Jobs\SyncVariantProduct::class,
    ],

    'media' => [
        'images' => [
            /*
            |--------------------------------------------------------------------------
            | Search size
            |--------------------------------------------------------------------------
            |
            | Define the size that should be pushed to Laravel Scout
            | to be available in the search result
            |
            */
            'search_size' => 'thumbnail',

            /*
            |--------------------------------------------------------------------------
            | Image sizes
            |--------------------------------------------------------------------------
            |
            | Define the sizes that should be generated when
            | uploading images to products
            |
            */
            'sizes' => [
                'thumbnail' => [
                    'width' => 150,
                    'height' => 150,
                    'fit' => Spatie\Image\Manipulations::FIT_CROP
                ],
                'small' => [
                    'width' => 500,
                    'height' => 750,
                    'fit' => Spatie\Image\Manipulations::FIT_CONTAIN
                ],
                'medium' => [
                    'width' => 1000,
                    'height' => 1500,
                    'fit' => Spatie\Image\Manipulations::FIT_CONTAIN
                ]
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable pushing data to Laravel Scout
    |--------------------------------------------------------------------------
    |
    | This will toggle if product data should be pushed to Laravel Scout
    |
    */
    'use_scout' => env('ECOMMERCE_USE_SCOUT', true),

    'currencies' => [
        /*
        |--------------------------------------------------------------------------
        | Default currency
        |--------------------------------------------------------------------------
        |
        | Define supported currencies, which currency that is the one
        | used to define prices and if the package should automatically calculate
        | prices from the default currency to the other supported currencies
        |
        */
        'default' => env('DEFAULT_CURRENCY', 'SEK'),

        /*
        |--------------------------------------------------------------------------
        | Available currencies
        |--------------------------------------------------------------------------
        |
        | Define array of supported currencies as currency => display label
        |
        */
        'available' => [
            env('DEFAULT_CURRENCY', 'SEK') => env('DEFAULT_CURRENCY', 'SEK')
        ],

        /*
        |--------------------------------------------------------------------------
        | Automatic currency conversion
        |--------------------------------------------------------------------------
        |
        | Define if the package should automatically calculate
        | prices from the default currency to the other supported currencies
        |
        */
        'auto_conversion' => env('USE_AUTO_CURRENCY_CONVERSION', true)
    ],

    'pricing' => [
        /*
        |--------------------------------------------------------------------------
        | Allow deals on discounted products
        |--------------------------------------------------------------------------
        |
        | Toggles the option to allow deals being applied to the total discounted
        | price on already discounted products or not
        |
        */
        'allow_deals_on_discounts' => env('ALLOW_DEALS_ON_DISCOUNTS', false)
    ],


    'vat' => [
        /*
        |--------------------------------------------------------------------------
        | Default VAT percentage
        |--------------------------------------------------------------------------
        |
        | The default vat percentage is used if the vat_percentage value is not
        | defined on the specific price
        |
        */
        'default' => env('DEFAULT_VAT_PERCENTAGE', 25)
    ],


    'cart' => [
        /*
        |--------------------------------------------------------------------------
        | Cart time to live
        |--------------------------------------------------------------------------
        |
        | Specify the length of time (in minutes) that the cart will be valid for
        | after last update. Before it will be discarded
        | Defaults to 4 hours.
        |
        */
        'ttl' => env('CART_TTL', 240),

        /*
        |--------------------------------------------------------------------------
        | Trigger expiring cart event after
        |--------------------------------------------------------------------------
        |
        | Specify the length of time (in minutes) since last update until an "CartExpiring" event
        | will be triggered.
        | Defaults to 2 hours.
        |
        */
        'trigger_expiring_event_after' => env('CART_TRIGGER_EXPIRING_EVENT_AFTER', 60)
    ],

    /*
    |--------------------------------------------------------------------------
    | Holds what controller classes to use, for easy extendability
    |--------------------------------------------------------------------------
    |
    | Override these values with your own classes and extending the originals
    |
    */
    'controllers' => [
        'product' => Bjerke\Ecommerce\Http\Controllers\ProductController::class,
        'brand' => Bjerke\Ecommerce\Http\Controllers\BrandController::class,
        'cart' => Bjerke\Ecommerce\Http\Controllers\CartController::class,
        'category' => Bjerke\Ecommerce\Http\Controllers\CategoryController::class,
        'deal' => Bjerke\Ecommerce\Http\Controllers\DealController::class,
        'order' => Bjerke\Ecommerce\Http\Controllers\OrderController::class,
        'price' => Bjerke\Ecommerce\Http\Controllers\PriceController::class,
        'property' => Bjerke\Ecommerce\Http\Controllers\PropertyController::class,
        'property_group' => Bjerke\Ecommerce\Http\Controllers\PropertyGroupController::class,
        'property_value' => Bjerke\Ecommerce\Http\Controllers\PropertyValueController::class,
        'shipping_method' => Bjerke\Ecommerce\Http\Controllers\ShippingMethodController::class,
        'stock' => Bjerke\Ecommerce\Http\Controllers\StockController::class,
        'store' => Bjerke\Ecommerce\Http\Controllers\StoreController::class,
    ],


    'routing' => [
        /*
        |--------------------------------------------------------------------------
        | Root routing options
        |--------------------------------------------------------------------------
        |
        | Define what options should be passed to the global route group
        | encompassing ALL ecommerce routes
        |
        */
        'root' => [
            'prefix' => 'api',
            'middleware' => ['api']
        ],

        /*
        |--------------------------------------------------------------------------
        | Authentication routing options
        |--------------------------------------------------------------------------
        |
        | Defined the auth middleware to use when requiring
        | a logged in user to access
        |
        */
        'auth' => [
            'middleware' => 'auth:api'
        ]
    ]

];

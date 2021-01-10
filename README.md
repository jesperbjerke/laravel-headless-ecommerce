# Laravel Headless Ecommerce

A headless ecommerce package for Laravel.

### Installation:

```shell script
composer require bjerke/laravel-headless-ecommerce
```

Publish and run migrations:
```shell script
php artisan vendor:publish --tag="ecommerce.migrations"
php artisan migrate
```

If you want to change the default configuration:
```shell script
php artisan vendor:publish --tag="ecommerce.config"
```

If you want to change the included lang files:
```shell script
php artisan vendor:publish --tag="ecommerce.lang"
```

### Models
Models can be overridden with your own versions. Just extend the original ones and configure your model class in the config file.

### Product
    - Product types
    - Laravel Scout
        - Searching, default driver, disabling (driver = null)
    - Laravel Media Library
        - Images
        - Files
    - Laravel Translatable
    - Laravel Sluggable

### Variations
    - Variation syncing

### Prices
    - MoneyPHP
    - VAT

### Deals

### Stock
    - Multiple stock types / stores (web, shops etc)
    - Current stock / Incoming stock / Outgoing stock
    - Low stock event

### Categories
    - Laravel Nested Set

### Properties

### Stores

### Brands

### Cart
    - Optional schedule to keep track of expired carts
    - Optional schedule to remove expired carts

### Orders

### Shipping

### Payments
    - Omnipay

### Translations
    - Laravel Translatable

### Commands
    - ecommerce:clean-abandoned-carts
    - ecommerce:check-expiring-carts

### Events
    - CartExpiring
        - Keep in mind that these can be triggered multiple times for the same cart. If you are sending a notification, you probably need to check if you've already sent this notification so you don't spam your customers.
    - LowStock
        - Keep in mind that these can be triggered multiple times for the same cart. You probable need to keep track if you have already acted on this event before.

### Routes
    - publish to host your own
    - configurations
    - overriding controllers
    - Authentication & Authorization
        - Default authentication middleware to require logged in user is "auth:api". You can configure this in the config file.
        - Is using the "can" middleware, to prohibit unautorized access to non-public endpoints. You can use packages like Bouncer or Spatie Laravel Permission to handle abilities, or define your own Gates & Policies.
        - Abilities:
            - manage-products

```shell script
php artisan vendor:publish --tag="ecommerce.routes"
```


### Customers / Users
This package does not provide any logic for handling customers or logged in users. This is up to the implementing application to add.
As an example, you might want to add a user_id column to the orders table to be able to link it to a user account.
Just change the migration after publishing and then extend the appropriate models/observers/controllers to handle this new column.

### Extending / Overriding

### Disabling scout
You can disable the automatic Scout integration by defining the following in your .env file
```dotenv
ECOMMERCE_USE_SCOUT=false
SCOUT_DRIVER=null
```

### Requirements
- PHP 7.4+
- Taggable cache store (like redis, memchached etc)
- SQL Database that supports JSON column type
- Intl extension
- A queue worker running

### Packages used
- Omnipay
- Laravel Scout
- MoneyPHP
- Laravel BREAD
- Laravel API Query Builder
- Spatie Laravel Media library
- Spatie Laravel Sluggable
- Spatie Laravel Translatable
- Laravel NestedSet

### On the roadmap
- Deals
    - Discount code
    - Gift cards
    - Bundles (?)
- More product types:
    - Digital content
    - Subscription
    - Preorders
    - Configurable products
- More media types
    - Video (?)
- On demand stock
- Optionally reserve stock upon adding to cart
- Wishlists
- Favorites
- Reviews
- Prisjakt/Pricerunner integration - Separate package
- Product upsell, cross-sell
- Suppliers
    - Procurements
- PoS features (?)
- Facebook / Instagram shopping (?) - Separate package
- Google Shopping (?) - Separate package
- Webhooks (?)
- Statistics (?)
- Import / Export products (?)

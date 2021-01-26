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

### Extending functionality of models
All models used by this package can be overridden with your own versions. Just extend the original ones and configure your model class in the config file.

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
    - Optinally sync prices against exchange rate by running the SynPrices job on schedule (otherwise it will only update if the base price changes)

### Deals

### Stock
    - Multiple stock types / stores (web, shops etc)
    - Current stock / Incoming stock / Outgoing stock
    - Low stock event
    - Optional schedule to remove old stock logs

### Categories
    - Laravel Nested Set

### Properties

### Stores

### Brands

### Cart
    - Optional schedule to keep track of expired carts
    - Optional schedule to remove expired carts

### Orders

### Order logs
    - Optional schedule to remove old order logs
    - Optional schedule to remove old payment logs

### Shipping

### Payments
    - Omnipay

### Translations
    - Laravel Translatable

### Commands

These are commands that you can optionally add to your schedule to have some things run automatically.

- `ecommerce:clean-abandoned-carts` - Checks for expired carts and deletes them (suggested to run daily)
- `ecommerce:check-expiring-carts` - Triggers expiring cart event (suggested to run daily)
- `ecommerce:clean-order-logs` - Deletes order logs older than the config `ecommerce.orders.log_ttl` (suggested to run daily)
- `ecommerce:clean-stock-logs` - Deletes stock logs older than the config `ecommerce.stock.log_ttl` (suggested to run daily)
- `ecommerce:clean-payment-logs` - Deletes payment logs older than the config `ecommerce.payment.log_ttl` (suggested to run daily)

### Events

- CartExpiring
    - This event will fire when a cart is about to expire (if a schedule for `ecommerce:check-expiring-carts` is setup). This can be useful for sending notifications to the customer to come back and finish their purchase.
    - Keep in mind that these can be triggered multiple times for the same cart. If you are sending a notification, you probably need to check if you've already sent this notification so you don't spam your customers.
- LowStock
    - This will fire when the low stock threshold is reached after updating stock.
    - Keep in mind that these can be triggered multiple times for the same cart. You probable need to keep track if you have already acted on this event before.

### Routes
To see all registered routes run:
```shell script
php artisan route:list
```

You can override routes in two different ways. First way is to write your own controller, extending the original one, then configure that controller in the config file (similar to the config for models).
The second way is to publish the route file.
```shell script
php artisan vendor:publish --tag="ecommerce.routes"
```
This will completely decouple the routefile within the package. Now you are free to change whatever you like. Keep in mind though that possible future updates to the route file will have to be migrated manually to your custom file.

There are also some configuration options regarding routing, such as prefix to use, default global middleware and which authentication middleware to use. You can look more closely in the config file of this package so see what you can change.

### Authentication & Authorization
Default authentication middleware to require logged in user is `auth:api`. You can configure this in the config file.

We are also using the `can` middleware, to prohibit unauthorized access to non-public endpoints. You can use packages like Bouncer or Spatie Laravel Permission to handle abilities, or define your own Gates & Policies.

#### Abilities used:
- manage-products
- manage-brands
- manage-categories
- manage-deals
- manage-prices
- manage-orders
- manage-prices
- manage-properties
- manage-shipping-methods
- manage-stocks
- manage-stores

### Customers / Users
This package does not provide any logic for handling customers or logged in users. This is up to the implementing application to add.
As an example, you might want to add a user_id column to the orders table to be able to link it to a user account.
Just change the migration after publishing and then extend the appropriate models/observers/controllers to handle this new column.

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
- [Laravel BREAD](https://github.com/jesperbjerke/laravel-bread)
- [Laravel API Query Builder](https://github.com/jesperbjerke/laravel-api-query-builder)
- [Laravel Scout](https://github.com/laravel/scout)
- [MoneyPHP](https://github.com/moneyphp/money)
- [Spatie Laravel Media library](https://github.com/spatie/laravel-medialibrary)
- [Spatie Laravel Sluggable](https://github.com/spatie/laravel-sluggable)
- [Spatie Laravel Translatable](https://github.com/spatie/laravel-translatable)
- [Laravel NestedSet](https://github.com/lazychaser/laravel-nestedset)
- [Omnipay](https://github.com/thephpleague/omnipay)

### Ideas for improvement

_Points marked with (?) are unsure if relevant to this package scope_

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
- Wishlists (?)
- Favorites (?)
- Reviews/Rating (?)
- Cart
  - Optionally reserve stock upon adding to cart
- More alternatives to shipping pricing
  - Dynamic (calculated based on ordered products)
  - Tiers?
  - Free shipping above X order value?
- Product upsell, cross-sell
- Suppliers
    - Procurements
- PoS features (?)
- Prisjakt/Pricerunner integration - Separate package
- Facebook / Instagram shopping (?) - Separate package
- Google Shopping (?) - Separate package
- Webhooks (?)
- Statistics (?)
- Import / Export products (?)

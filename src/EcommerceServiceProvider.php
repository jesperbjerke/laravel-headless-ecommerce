<?php

namespace Bjerke\Ecommerce;

use Bjerke\Ecommerce\Console\Commands\CheckExpiringCarts;
use Bjerke\Ecommerce\Console\Commands\CleanAbandonedCarts;
use Bjerke\Ecommerce\Console\Commands\CleanOrderLogs;
use Bjerke\Ecommerce\Console\Commands\CleanPaymentLogs;
use Bjerke\Ecommerce\Helpers\PaymentHelper;
use Bjerke\Ecommerce\Helpers\PriceHelper;
use Bjerke\Ecommerce\Observers\CategoryProductObserver;
use Bjerke\Ecommerce\Observers\OrderItemObserver;
use Bjerke\Ecommerce\Observers\OrderObserver;
use Bjerke\Ecommerce\Observers\PriceObserver;
use Bjerke\Ecommerce\Observers\ProductObserver;
use Bjerke\Ecommerce\Observers\StockObserver;
use Bjerke\Ecommerce\Observers\VariationObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EcommerceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'ecommerce');

        $this->registerObservers();
        $this->registerRoutes();

        Relation::morphMap(config('ecommerce.models'));
    }

    private function bootForConsole(): void
    {
        $this->publishMigrations();

        $this->publishes([__DIR__ . '/../config/ecommerce.php' => config_path('ecommerce.php'),], 'ecommerce.config');
        $this->publishes([__DIR__ . '/../resources/lang' => resource_path('lang/vendor/ecommerce'),], 'ecommerce.lang');

        $this->commands([
            CheckExpiringCarts::class,
            CleanAbandonedCarts::class,
            CleanOrderLogs::class,
            CleanPaymentLogs::class
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ecommerce.php', 'ecommerce');

        $this->app->bind('price.helper', fn ($app) => new PriceHelper());
        $this->app->bind('payment.helper', fn ($app) => new PaymentHelper());
    }

    private function registerObservers(): void
    {
        $variationModel = config('ecommerce.models.variation');
        $variationModel::observe(VariationObserver::class);

        $stockModel = config('ecommerce.models.stock');
        $stockModel::observe(StockObserver::class);

        $categoryProductModel = config('ecommerce.models.category_product');
        $categoryProductModel::observe(CategoryProductObserver::class);

        $productModel = config('ecommerce.models.product');
        $productModel::observe(ProductObserver::class);

        $orderModel = config('ecommerce.models.order');
        $orderModel::observe(OrderObserver::class);

        $orderItemModel = config('ecommerce.models.order_item');
        $orderItemModel::observe(OrderItemObserver::class);

        $orderItemModel = config('ecommerce.models.order_item');
        $orderItemModel::observe(OrderItemObserver::class);

        $priceModel = config('ecommerce.models.price');
        $priceModel::observe(PriceObserver::class);
    }

    private function registerRoutes()
    {
        $appRoutePath = base_path('routes');
        $routeFile = 'ecommerce_api.php';

        $this->publishes([
            __DIR__ . '/../routes/' . $routeFile => $appRoutePath,
        ], 'ecommerce.routes');

        // If application has published its own route file, do not load the one in the package
        if (file_exists($appRoutePath . '/' . $routeFile)) {
            return;
        }

        Route::group(config('ecommerce.routing.root'), function () use ($routeFile) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/' . $routeFile);
        });
    }

    private function publishMigrations(): void
    {
        $baseMigrationPath = __DIR__ . '/../database/migrations/';
        $basePublishFilename = 'migrations/' . date('Y_m_d_Hi');
        $publishGroup = 'ecommerce.migrations';

        if (!class_exists('CreateBrandsTable')) {
            $this->publishes([
                 $baseMigrationPath . 'create_brands_table.php.stub' => database_path(
                     $basePublishFilename . '00_create_brands_table.php'
                 )
            ], $publishGroup);
        }

        if (!class_exists('CreateProductsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_products_table.php.stub' => database_path(
                    $basePublishFilename . '01_create_products_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateCategoriesTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_categories_table.php.stub' => database_path(
                    $basePublishFilename . '02_create_categories_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateCategoryProductTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_category_product_table.php.stub' => database_path(
                    $basePublishFilename . '03_create_category_product_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreatePropertyGroupsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_property_groups_table.php.stub' => database_path(
                    $basePublishFilename . '04_create_property_groups_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreatePropertiesTable')) {
            $this->publishes([
                 $baseMigrationPath . 'create_properties_table.php.stub' => database_path(
                     $basePublishFilename . '05_create_properties_table.php'
                 )
            ], $publishGroup);
        }

        if (!class_exists('CreatePropertyValuesTable')) {
            $this->publishes([
                 $baseMigrationPath . 'create_property_values_table.php.stub' => database_path(
                     $basePublishFilename . '06_create_property_values_table.php'
                 )
            ], $publishGroup);
        }

        if (!class_exists('CreateVariationsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_variations_table.php.stub' => database_path(
                    $basePublishFilename . '07_create_variations_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateStoresTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_stores_table.php.stub' => database_path(
                    $basePublishFilename . '08_create_stores_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateProductStoreTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_product_store_table.php.stub' => database_path(
                    $basePublishFilename . '09_create_product_store_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreatePricesTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_prices_table.php.stub' => database_path(
                    $basePublishFilename . '10_create_prices_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateStocksTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_stocks_table.php.stub' => database_path(
                    $basePublishFilename . '11_create_stocks_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateStockLogsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_stock_logs_table.php.stub' => database_path(
                    $basePublishFilename . '12_create_stock_logs_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateCartsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_carts_table.php.stub' => database_path(
                    $basePublishFilename . '13_create_carts_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateCartItemsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_cart_items_table.php.stub' => database_path(
                    $basePublishFilename . '14_create_cart_items_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateDealsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_deals_table.php.stub' => database_path(
                    $basePublishFilename . '15_create_deals_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateDealablesTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_dealables_table.php.stub' => database_path(
                    $basePublishFilename . '16_create_dealables_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateDealProductTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_deal_product_table.php.stub' => database_path(
                    $basePublishFilename . '17_create_deal_product_table.php'
                )
            ], $publishGroup);
        }


        if (!class_exists('CreateShippingMethodsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_shipping_methods_table.php.stub' => database_path(
                    $basePublishFilename . '18_create_shipping_methods_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateOrdersTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_orders_table.php.stub' => database_path(
                    $basePublishFilename . '19_create_orders_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateOrderItemsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_order_items_table.php.stub' => database_path(
                    $basePublishFilename . '20_create_order_items_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateOrderLogsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_order_logs_table.php.stub' => database_path(
                    $basePublishFilename . '21_create_order_logs_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreatePaymentsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_payments_table.php.stub' => database_path(
                    $basePublishFilename . '22_create_payments_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreatePaymentLogsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_payment_logs_table.php.stub' => database_path(
                    $basePublishFilename . '23_create_payment_logs_table.php'
                )
            ], $publishGroup);
        }
    }
}

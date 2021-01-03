<?php

namespace Bjerke\Ecommerce;

use Bjerke\Ecommerce\Observers\CategoryProductObserver;
use Bjerke\Ecommerce\Observers\ProductObserver;
use Bjerke\Ecommerce\Observers\StockObserver;
use Bjerke\Ecommerce\Observers\VariationObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class EcommerceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishMigrations();

        $this->publishes([
            __DIR__ . '/../config/ecommerce.php' => config_path('ecommerce.php'),
        ], 'ecommerce.config');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'ecommerce');
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/ecommerce'),
        ], 'ecommerce.lang');

        $this->registerObservers();

        Relation::morphMap(config('ecommerce.models'));
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ecommerce.php', 'ecommerce');
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

        if (!class_exists('CreateCartsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_carts_table.php.stub' => database_path(
                    $basePublishFilename . '12_create_carts_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateCartItemsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_carts_items_table.php.stub' => database_path(
                    $basePublishFilename . '13_create_carts_items_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateDealsTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_deals_table.php.stub' => database_path(
                    $basePublishFilename . '14_create_deals_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateDealablesTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_dealables_table.php.stub' => database_path(
                    $basePublishFilename . '15_create_dealables_table.php'
                )
            ], $publishGroup);
        }

        if (!class_exists('CreateDealProductTable')) {
            $this->publishes([
                $baseMigrationPath . 'create_deal_product_table.php.stub' => database_path(
                    $basePublishFilename . '16_create_deal_product_table.php'
                )
            ], $publishGroup);
        }
    }
}

<?php

use Bjerke\Ecommerce\Enums\DealDiscountType;
use Bjerke\Ecommerce\Enums\ProductStatus;
use Bjerke\Ecommerce\Enums\ProductType;

return [
    'product_status' => [
        ProductStatus::DRAFT => 'Draft',
        ProductStatus::INACTIVE => 'Inactive',
        ProductStatus::ACTIVE => 'Active',
        ProductStatus::RETIRED => 'Retired',
        ProductStatus::UNLISTED => 'Unlisted'
    ],
    'product_type' => [
        ProductType::REGULAR => 'Regular',
        ProductType::DIGITAL_CONTENT => 'Digital content',
        ProductType::VARIANT => 'Variant'
    ],
    'deal_discount_type' => [
        DealDiscountType::PERCENTAGE => 'Percentage',
        DealDiscountType::FIXED => 'Fixed value'
    ]
];

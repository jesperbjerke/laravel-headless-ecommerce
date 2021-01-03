<?php

use Bjerke\Ecommerce\Enums\DealDiscountType;
use Bjerke\Ecommerce\Enums\ProductStatus;
use Bjerke\Ecommerce\Enums\ProductType;

return [
    'product_status' => [
        ProductStatus::DRAFT => 'Utkast',
        ProductStatus::INACTIVE => 'Inaktiv',
        ProductStatus::ACTIVE => 'Aktiv',
        ProductStatus::RETIRED => 'Utgått',
        ProductStatus::UNLISTED => 'Olistad'
    ],
    'product_type' => [
        ProductType::REGULAR => 'Normal',
        ProductType::DIGITAL_CONTENT => 'Digitalt innehåll',
        ProductType::VARIANT => 'Variant'
    ],
    'deal_discount_type' => [
        DealDiscountType::PERCENTAGE => 'Procentuell',
        DealDiscountType::FIXED => 'Fast värde'
    ]
];

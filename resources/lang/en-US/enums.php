<?php

use Bjerke\Ecommerce\Enums\DealDiscountType;
use Bjerke\Ecommerce\Enums\OrderStatus;
use Bjerke\Ecommerce\Enums\PaymentStatus;
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
    ],
    'order_status' => [
        OrderStatus::DRAFT => 'Not sent in',
        OrderStatus::PENDING => 'Pending',
        OrderStatus::CONFIRMED => 'Confirmed',
        OrderStatus::PROCESSING => 'Processing',
        OrderStatus::SHIPPED => 'Shipped',
        OrderStatus::CANCELLED => 'Cancelled'
    ],
    'payment_status' => [
        PaymentStatus::PENDING => 'Pending',
        PaymentStatus::PROCESSING => 'Processing',
        PaymentStatus::PAID => 'Paid',
        PaymentStatus::FAILED => 'Failed',
        PaymentStatus::PARTIALLY_REFUNDED => 'Partially refunded',
        PaymentStatus::REFUNDED => 'Refunded'
    ]
];

<?php

use Bjerke\Ecommerce\Enums\DealDiscountType;
use Bjerke\Ecommerce\Enums\OrderLogType;
use Bjerke\Ecommerce\Enums\OrderStatus;
use Bjerke\Ecommerce\Enums\PaidStatus;
use Bjerke\Ecommerce\Enums\PaymentLogType;
use Bjerke\Ecommerce\Enums\PaymentStatus;
use Bjerke\Ecommerce\Enums\ProductStatus;
use Bjerke\Ecommerce\Enums\ProductType;
use Bjerke\Ecommerce\Enums\StockLogTrigger;
use Bjerke\Ecommerce\Enums\StockLogType;

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
    ],
    'paid_status' => [
        PaidStatus::UNPAID => 'Unpaid',
        PaidStatus::PAID => 'Paid',
    ],
    'payment_log_type' => [
        PaymentLogType::CREATED => 'Created',
        PaymentLogType::FAILED => 'Failed',
        PaymentLogType::COMPLETED => 'Completed',
        PaymentLogType::REFUND_CREATED => 'Refund created',
        PaymentLogType::REFUND_FAILED => 'Refund failed',
        PaymentLogType::REFUND_COMPLETED => 'Refund completed'
    ],
    'order_log_type' => [
        OrderLogType::CREATED => 'Created',
        OrderLogType::UPDATED => 'Updated',
        OrderLogType::ITEM_ADDED => 'Order item added',
        OrderLogType::ITEM_REMOVED => 'Order item removed',
        OrderLogType::ITEM_UPDATED => 'Order item updated'
    ],
    'stock_log_type' => [
        StockLogType::RESERVED => 'Stock reserved',
        StockLogType::RELEASED => 'Reserved stock released',
        StockLogType::CONFIRMED => 'Reserved stock confirmed',
        StockLogType::RETURNED => 'Stock returned'
    ],
    'stock_log_trigger' => [
        StockLogTrigger::ORDER_ITEM => 'Order item'
    ]
];

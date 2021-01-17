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
    ],
    'order_status' => [
        OrderStatus::DRAFT => 'Ej inskickad',
        OrderStatus::PENDING => 'Väntande',
        OrderStatus::CONFIRMED => 'Bekräftad',
        OrderStatus::PROCESSING => 'Behandlas',
        OrderStatus::SHIPPED => 'Skickad',
        OrderStatus::CANCELLED => 'Avbruten'
    ],
    'payment_status' => [
        PaymentStatus::PENDING => 'Väntande',
        PaymentStatus::PROCESSING => 'Behandlas',
        PaymentStatus::PAID => 'Betalad',
        PaymentStatus::FAILED => 'Kunde inte betalas',
        PaymentStatus::PARTIALLY_REFUNDED => 'Delvis återbetald',
        PaymentStatus::REFUNDED => 'Återbetald'
    ],
    'paid_status' => [
        PaidStatus::UNPAID => 'Obetald',
        PaidStatus::PARTIALLY_PAID => 'Delvis betald',
        PaidStatus::PAID => 'Betald',
    ],
    'payment_log_type' => [
        PaymentLogType::CREATED => 'Skapad',
        PaymentLogType::FAILED => 'Fel',
        PaymentLogType::COMPLETED => 'Slutförd',
        PaymentLogType::REFUND_CREATED => 'Återbetalning skapad',
        PaymentLogType::REFUND_FAILED => 'Återbetalning kunde inte genomföras',
        PaymentLogType::REFUND_COMPLETED => 'Återbetalning slutförd'
    ],
    'order_log_type' => [
        OrderLogType::CREATED => 'Skapad',
        OrderLogType::UPDATED => 'Uppdaterad',
        OrderLogType::ITEM_ADDED => 'Orderrad tillagd',
        OrderLogType::ITEM_REMOVED => 'Orderrad borttagen',
        OrderLogType::ITEM_UPDATED => 'Orderrad uppdaterad'
    ],
    'stock_log_type' => [
        StockLogType::RESERVED => 'Lager reserverat',
        StockLogType::RELEASED => 'Reserverat lager släppt',
        StockLogType::CONFIRMED => 'Reserverat lager bekräftat',
        StockLogType::RETURNED => 'Lager återfört'
    ],
    'stock_log_trigger' => [
        StockLogTrigger::ORDER_ITEM => 'Orderrad'
    ]
];

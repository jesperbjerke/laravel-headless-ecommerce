<?php

use Bjerke\Ecommerce\Enums\DealDiscountType;
use Bjerke\Ecommerce\Enums\OrderStatus;
use Bjerke\Ecommerce\Enums\PaymentStatus;
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
    ]
];

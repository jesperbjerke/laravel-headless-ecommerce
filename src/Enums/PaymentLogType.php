<?php

namespace Bjerke\Ecommerce\Enums;

class PaymentLogType extends BaseEnum
{
    public const CREATED = 10;
    public const FAILED = 20;
    public const CANCELLED = 30;
    public const COMPLETED = 40;
    public const REFUND_CREATED = 50;
    public const REFUND_FAILED = 60;
    public const REFUND_COMPLETED = 70;
}

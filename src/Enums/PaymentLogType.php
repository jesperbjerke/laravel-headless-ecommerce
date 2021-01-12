<?php

namespace Bjerke\Ecommerce\Enums;

class PaymentLogType extends BaseEnum
{
    public const CREATED = 10;
    public const FAILED = 20;
    public const COMPLETED = 30;
    public const REFUND_CREATED = 40;
    public const REFUND_FAILED = 50;
    public const REFUND_COMPLETED = 60;
}

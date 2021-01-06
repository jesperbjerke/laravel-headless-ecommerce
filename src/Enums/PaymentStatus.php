<?php

namespace Bjerke\Ecommerce\Enums;

use Bjerke\Enum\BaseEnum;

class PaymentStatus extends BaseEnum
{
    public const PENDING = 10;
    public const PROCESSING = 20;
    public const PAID = 30;
    public const FAILED = 40;
    public const PARTIALLY_REFUNDED = 50;
    public const REFUNDED = 60;
}

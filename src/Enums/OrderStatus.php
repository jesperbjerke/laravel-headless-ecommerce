<?php

namespace Bjerke\Ecommerce\Enums;

use Bjerke\Enum\BaseEnum;

class OrderStatus extends BaseEnum
{
    public const DRAFT = 10;
    public const PENDING = 20;
    public const CONFIRMED = 30;
    public const PROCESSING = 40;
    public const SHIPPED = 50;
    public const CANCELLED = 60;
}

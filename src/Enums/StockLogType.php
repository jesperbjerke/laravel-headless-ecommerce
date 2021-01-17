<?php

namespace Bjerke\Ecommerce\Enums;

class StockLogType extends BaseEnum
{
    public const RESERVED = 10;
    public const RELEASED = 20;
    public const CONFIRMED = 30;
    public const RETURNED = 40;
}

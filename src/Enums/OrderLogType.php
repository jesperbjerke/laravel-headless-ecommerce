<?php

namespace Bjerke\Ecommerce\Enums;

class OrderLogType extends BaseEnum
{
    public const CREATED = 10;
    public const UPDATED = 20;
    public const ITEM_ADDED = 30;
    public const ITEM_REMOVED = 40;
    public const ITEM_UPDATED = 50;
    public const STOCK_RESERVED = 60;
    public const STOCK_RELEASED = 70;
    public const STOCK_CONFIRMED = 80;
    public const STOCK_RETURNED = 90;
}

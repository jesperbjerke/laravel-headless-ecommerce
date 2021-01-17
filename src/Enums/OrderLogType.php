<?php

namespace Bjerke\Ecommerce\Enums;

class OrderLogType extends BaseEnum
{
    public const CREATED = 10;
    public const UPDATED = 20;
    public const ITEM_ADDED = 30;
    public const ITEM_REMOVED = 40;
    public const ITEM_UPDATED = 50;
}

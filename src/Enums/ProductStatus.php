<?php

namespace Bjerke\Ecommerce\Enums;

use Bjerke\Enum\BaseEnum;

class ProductStatus extends BaseEnum
{
    public const DRAFT = 10;
    public const INACTIVE = 20;
    public const ACTIVE = 30;
    public const RETIRED = 40;
    public const UNLISTED = 50;
}

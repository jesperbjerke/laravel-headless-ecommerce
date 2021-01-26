<?php

namespace Bjerke\Ecommerce\Exceptions;

use Exception;
use Throwable;

class MethodNotImplemented extends Exception
{
    use TranslatableException;

    public function __construct(
        $message = null,
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            $message ?: $this->getTranslatedMessage(),
            $code,
            $previous
        );
    }
}

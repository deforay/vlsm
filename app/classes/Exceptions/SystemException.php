<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class SystemException extends Exception
{
    public function __construct($message = "", $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString(): string
    {
        return __CLASS__ ?? __FILE__ . ": [$this->code]: $this->message" . PHP_EOL;
    }
}

<?php

namespace Exler\Whois\Exceptions;

use Exception;
use Throwable;

class ConnectionException extends Exception
{
    // Message is not optional
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

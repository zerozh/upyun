<?php
namespace Upyun\Exceptions;

class ServiceUnavailableException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, 503, $previous);
    }
}

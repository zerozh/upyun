<?php
namespace Upyun\Exceptions;

class UnknownException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}

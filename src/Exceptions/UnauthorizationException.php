<?php
namespace Upyun\Exceptions;

class UnauthorizationException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}

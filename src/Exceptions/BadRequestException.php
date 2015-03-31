<?php
namespace Upyun\Exceptions;

class BadRequestException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, 400, $previous);
    }
}

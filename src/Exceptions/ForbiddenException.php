<?php
namespace Upyun\Exceptions;

class ForbiddenException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}

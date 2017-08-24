<?php
namespace Exception;


class BadRequestException extends \Exception
{
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, 400);
    }
}
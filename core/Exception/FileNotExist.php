<?php
namespace Exception;


class FileNotExist extends \Exception
{
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, 500);
    }
}
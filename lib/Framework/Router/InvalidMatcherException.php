<?php
namespace Framework\Router;

use Framework\Exception;

class InvalidMatcherException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
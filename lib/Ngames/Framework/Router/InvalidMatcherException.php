<?php

namespace Ngames\Framework\Router;

use Ngames\Framework\Exception;

class InvalidMatcherException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}

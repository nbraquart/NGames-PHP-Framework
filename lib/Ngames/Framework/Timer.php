<?php

namespace Ngames\Framework;

class Timer
{
    public $time;

    public function __construct($time = null)
    {
        $this->time = $time == null ? self::now()->time : $time;
    }

    public function getTime()
    {
        return $this->time;
    }

    public static function now()
    {
        return new self(microtime(true));
    }
}

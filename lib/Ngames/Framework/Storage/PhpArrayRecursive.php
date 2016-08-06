<?php

namespace Ngames\Framework\Storage;

class PhpArrayRecursive extends \Ngames\Framework\Storage\PhpArray implements StorageInterface
{
    public function __construct(array $array = [])
    {
        foreach ($array as $key => $value) {
            if (is_scalar($value)) {
                $this->set($key, $value);
            } elseif (is_array($value)) {
                $this->storage[$key] = new self($value);
            } else {
                throw new \Ngames\Framework\Exception('Invalid value for Array storage');
            }
        }
    }
}

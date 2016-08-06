<?php

namespace Ngames\Framework\Storage;

class PhpArray extends AbstractStorage implements StorageInterface
{
    protected $storage;

    public function __construct(array $array = [])
    {
        $this->storage = $array;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->storage);
    }

    public function set($name, $value)
    {
        $this->storage[$name] = $value;
    }

    public function get($name, $default = null)
    {
        return $this->has($name) ? $this->storage[$name] : $default;
    }

    public function reset()
    {
        $this->storage = [];
    }

    public function clear($name)
    {
        if ($this->has($name)) {
            unset($this->storage[$name]);
        }
    }
}

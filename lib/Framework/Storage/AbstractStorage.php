<?php
namespace Framework\Storage;

abstract class AbstractStorage implements StorageInterface, \ArrayAccess
{

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->clear($offset);
    }

    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->get($name);
        }
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }
}
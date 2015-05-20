<?php
namespace Framework\Storage;

abstract class AbstractStorage implements StorageInterface, \ArrayAccess
{
    // ArrayAccess methods
    public function offsetExists ($offset)
    {
        return $this->has($offset);
    }
    public function offsetGet ($offset)
    {
        return $this->get($offset);
    }
    public function offsetSet ($offset, $value)
    {
        return $this->set($offset, $value);
    }
    public function offsetUnset ($offset)
    {
        return $this->clear($offset);
    }
    
    // Class access
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
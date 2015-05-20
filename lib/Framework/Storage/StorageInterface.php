<?php
namespace Framework\Storage;

interface StorageInterface
{
    /**
     * Return true if the storage contains a value for the specified name
     * 
     * @param string $name
     * @return boolean
     */
    public function has($name);
    
    /**
     * Sets the value for the name
     * 
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value);
    
    /**
     * Return the value for the name
     * 
     * @param string $name
     * @param mixed $default
     * @return StorageInterface
     */
    public function get($name, $default = '');

    /**
     * Reset the storage. After this method call, no more value are stored
     */
    public function reset();
    
    /**
     * Clear the value for the field by name
     * 
     * @param string $name
     */
    public function clear($name);
}
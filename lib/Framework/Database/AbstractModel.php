<?php
namespace Framework\Database;

/**
 * Abstract class for models classes. Provide utility methods like from and to array and get finder instance.
 */
abstract class AbstractModel
{
    /**
     * Stores metadata for model classes (cache). Keys are class names (as returned by get_class()), values are arrays.
     * Arrays have three keys:
     * - reference_properties: associate a reference to the class it references (user => \Model\User)
     * - properties_mapping: associate the underscore variable name to the camelcase one (read_date => readDate)
     * - primary_key_properties: list of properties that are part of the primary key
     * 
     * @var array
     */
    protected static $metadata = array();
    
    /**
     * The autoload namespace for annotations. Could be a const, but PHP consts are always public.
     * @var string
     */
    protected static $autoloadNamespace = '\Framework\Database\Annotations';
    
    /**
     * Boolean storing whether annotations autoload namespace was already registered (for as long as the object is in memory)
     * @var boolean
     */
    protected static $autoloadNamespaceRegistered = false;
    
    /**
     * Return the finder instance able to query the database and return instances of current class
     * @return \Framework\Database\Finder:
     */
    public static function getFinder()
    {
        return new \Framework\Database\Finder(get_called_class());
    }
    
    /**
     * Sets values of current class from an array
     * @param array $array
     * @return \Framework\Database\AbstractModel
     */
    public function fromArray(array $array)
    {
        $metadata = $this->getClassMetadata(get_class($this));
        
        // Store properties for reference. Keys are the property referencing another class, values are arrays of properties for this class
        $referencesProperties = array();
        // Store properties for current class
        $properties = array();
    
        // Dispatch properties by destination class (current or one of its references)
        foreach ($array as $property => $value) {
            $isReference = false;
    
            // For all properties that are reference to another class
            if (array_key_exists('reference_properties', $metadata)) {
                foreach (array_keys($metadata['reference_properties']) as $referenceProperty) {
                    // If current property starts with the reference property name, then it's a value for referenced class
                    if (strpos($property, $referenceProperty . '_') === 0) {
                        $localPropertyName = str_replace($referenceProperty . '_', '', $property);
                        $referencesProperties[$referenceProperty][$localPropertyName] = $value;
                        $isReference = true;
                        break;
                    }
                }
            }
    
            // Property was not found as a reference, then it's a value for current class
            if (! $isReference && array_key_exists($property, $metadata['properties_mapping'])) {
                $properties[$property] = $value;
            }
        }
    
        // Set my properties
        foreach ($properties as $property => $value) {
            $this->{$metadata['properties_mapping'][$property]} = $value;
        }
    
        // Set references properties
        foreach ($referencesProperties as $referenceProperty => $referenceProperties) {
            $referenceInstance = new $metadata['reference_properties'][$referenceProperty];
            $referenceInstance->fromArray($referenceProperties);
            $this->{$metadata['properties_mapping'][$referenceProperty]} = $referenceInstance;
        }
    
        return $this;
    }
    
    /**
     * Return the metadata for the provided class
     * 
     * @param string $className
     * @return array
     */
    protected function getClassMetadata($className)
    {
        if (! array_key_exists($className, self::$metadata)) {
            // Get the reflection class and class name
            $reflectionClass = new \ReflectionClass($className);
        
            // Initialize a reader (APC if available, or in memory array cache)
            $reader = $this->getAnnotationsReader();
        
            // For each property
            $properties = $reflectionClass->getProperties();
            
            // Initialize metadata
            $metadata = array();
            
            foreach ($properties as $property) {
                // We only want non static properties defined by the sub-class
                if (! $property->isStatic() && $property->getDeclaringClass()->getName() == $className) {
                    $propertyName = $property->getName();
                    $propertyNameUnderscore = \Framework\Utility\Inflector::underscore($propertyName);
                    $idAnnotation = $reader->getPropertyAnnotation($property, '\Framework\Database\Annotations\Id');
                    $referenceAnnotation = $reader->getPropertyAnnotation($property, '\Framework\Database\Annotations\Reference');
        
                    // Add to the list
                    $metadata['properties_mapping'][$propertyNameUnderscore] = $propertyName;
                    if ($idAnnotation !== null) {
                        $metadata['primary_key_properties'][] = $propertyNameUnderscore;
                    }
                    if ($referenceAnnotation != null) {
                        $metadata['reference_properties'][$propertyNameUnderscore] = $referenceAnnotation->targetClass;
                    }
                }
            }
            
            self::$metadata[$className] = $metadata;
        }
        
        return self::$metadata[$className];
    }
    
    /**
     * Initialize a reader (APC cache reader if available, or in memory array cache)
     *
     * @return \Doctrine\Common\Annotations\Reader
     */
    protected function getAnnotationsReader()
    {
        if (! self::$autoloadNamespaceRegistered) {
            \Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(self::$autoloadNamespace, ROOT_DIR . '/src');
            self::$autoloadNamespaceRegistered = true;
        }
    
        $reader = new \Doctrine\Common\Annotations\CachedReader(
            new \Doctrine\Common\Annotations\AnnotationReader(),
            function_exists('apc_fetch') ? new \Doctrine\Common\Cache\ApcCache() : new \Doctrine\Common\Cache\ArrayCache(),
            $debug = true
        );
    
        return $reader;
    }
}
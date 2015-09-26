<?php
namespace Framework\Database;

/**
 * The finder class is a higher level API to access the database than the Connection one.
 * Methods will return/save instances of model classes rather than just arrays.
 *
 * SQL queries params have to be built by hand. If the queried model contains references, then the query may contain values for the reference.
 * The property name for the reference ust be a prefix to the reference property.
 * Example:
 * This query: SELECT m.id, u.id as user_id FROM messages m JOIN users u ON m.user_id = u.id
 * could lead to this model: [Message]{ id: '123', user: [User]{ id: '456' } }
 */
class Finder
{

    protected $className = null;

    /**
     * Return a new finder for the provided class
     * 
     * @param string $className            
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * Return a instance of the targeted class
     * 
     * @param string $query            
     * @param array $params            
     * @return \Framework\Database\AbstractModel
     */
    public function queryOne($query, array $params = array())
    {
        $result = \Framework\Database\Connection::queryOne($query, $params);
        
        if ($result !== false) {
            $result = $this->createInstance()->fromArray($result);
        }
        
        return $result;
    }

    /**
     * Return a list of targeted class instances
     * 
     * @param string $query            
     * @param array $params            
     * @return array[\Framework\Database\AbstractModel]
     */
    public function query($query, array $params = array())
    {
        $result = \Framework\Database\Connection::query($query, $params);
        
        if ($result !== false) {
            $objectList = array();
            
            foreach ($result as $array) {
                $objectList[] = $this->createInstance()->fromArray($array);
            }
            
            $result = $objectList;
        }
        
        return $result;
    }

    /**
     * Return a new instance of targeted class
     * 
     * @return \Framework\Database\AbstractModel
     */
    private function createInstance()
    {
        return new $this->className();
    }
}
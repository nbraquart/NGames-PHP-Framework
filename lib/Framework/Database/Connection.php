<?php
namespace Framework\Database;

/**
 * This class handles the connection to the database.
 *
 * @author Nicolas Braquart <nicolas.braquart@gmail.com>
 */
class Connection
{

    protected static $queries = array();

    /**
     * Returns the instance of the connection.
     * If no instance has already been retrieved,
     * then it starts by establishing the connection.
     * Note that PDO instance will raise an exception on errors.
     *
     * @return \PDO
     */
    public static function getConnection()
    {
        $configuration = \Framework\Application::getInstance()->getConfiguration();
        
        if (! self::$connection) {
            $dsn = sprintf('mysql:host=%s;dbname=%s', $configuration->database->host, $configuration->database->name);
            
            self::$connection = new \PDO($dsn, $configuration->database->username, $configuration->database->password, array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ));
        }
        
        return self::$connection;
    }

    /**
     *
     * @var \PDO
     */
    protected static $connection = null;

    /**
     * Query the database and return the data.
     * NB: all data are string, if native type is needed:
     * http://stackoverflow.com/questions/2430640/g
     *
     * @param string $query            
     * @param array $params            
     * @return array|boolean The result of the query
     */
    public static function query($query, array $params = array())
    {
        $statement = self::getConnection()->prepare($query);
        $result = false;
        $start = microtime(true);
        
        try {
            if ($statement && $statement->execute($params)) {
                $result = array();
                self::logQuery($query, microtime(true) - $start);
                
                while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                    $result[] = $row;
                }
            }
        } catch (\PDOException $e) {
            throw new \Framework\Exception('Caught PDO exception', 0, $e);
        }
        
        return $result;
    }

    /**
     * Execute a modifying query on the database (INSERT, UPDATE or DELETE).
     * If needed, after rowCount(): while ($statement->fetch(\PDO::FETCH_ASSOC)) {}
     *
     * @param string $query            
     * @param array $params            
     * @return int The number of rows impacted
     */
    public static function exec($query, array $params = array())
    {
        $statement = self::getConnection()->prepare($query);
        $result = false;
        $start = microtime(true);
        
        try {
            if ($statement && $statement->execute($params)) {
                self::logQuery($query, microtime(true) - $start);
                $result = $statement->rowCount();
            }
        } catch (\PDOException $e) {
            throw new \Framework\Exception('Caught PDO exception', 0, $e);
        }
        
        return $result;
    }

    /**
     * Count the number of rows matching the query.
     *
     * @param string $query            
     * @param array $params            
     * @return int
     */
    public static function count($query, array $params = array())
    {
        $statement = self::getConnection()->prepare($query);
        $result = false;
        $start = microtime(true);
        
        try {
            if ($statement && $statement->execute($params)) {
                self::logQuery($query, microtime(true) - $start);
                $result = $statement->rowCount();
            }
        } catch (\PDOException $e) {
            throw new \Framework\Exception('Caught PDO exception', 0, $e);
        }
        
        return $result;
    }

    /**
     * Helper method querying the database for a single row.
     *
     * @param string $query            
     * @param array $params            
     * @return array|boolean
     */
    public static function queryOne($query, array $params = array())
    {
        $result = self::query($query, $params);
        
        return is_array($result) && count($result) > 0 ? $result[0] : false;
    }

    /**
     * Inserts data in the database.
     *
     * @param string $tableName            
     * @param array $data            
     * @return boolean|number
     */
    public static function insert($tableName, array $data)
    {
        $keys = array_keys($data);
        $placeholders = array_map(function ($v) {
            return ':' . $v;
        }, $keys);
        $query = 'INSERT INTO `' . $tableName . '` (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $placeholders) . ')';
        
        if (! self::exec($query, $data)) {
            return false;
        }
        
        return (int) self::getConnection()->lastInsertId();
    }

    /**
     * Returns an element by its primary key.
     *
     * @param string $tableName            
     * @param int $id            
     * @return array|boolean
     */
    public static function findOneById($tableName, $id)
    {
        $query = 'SELECT * FROM `' . $tableName . '` WHERE id=?';
        
        return self::queryOne($query, array(
            (int) $id
        ));
    }

    /**
     * Return the last error that occured
     *
     * @return array
     */
    public static function getLastError()
    {
        return self::getConnection()->errorInfo();
    }

    /**
     * Return the number of queries run on the database
     */
    public static function getQueryCounter()
    {
        return count(self::$queries);
    }

    /**
     * Return all executed queries with their text and execution time
     * 
     * @return array
     */
    public static function getQueries()
    {
        return self::$queries;
    }

    /**
     * Logs the query and its execution time
     * 
     * @param string $queryString            
     * @param float $duration            
     */
    protected static function logQuery($queryString, $duration)
    {
        // Keep only microsecodns (no nano)
        $duration = round($duration, 6) * 1000;
        
        // Log and record the SQL query
        \Framework\Logger::logDebug('SQL query: [' . $duration . ' ms] ' . $queryString);
        self::$queries[] = array(
            'sql' => $queryString,
            'duration' => $duration
        );
    }
}
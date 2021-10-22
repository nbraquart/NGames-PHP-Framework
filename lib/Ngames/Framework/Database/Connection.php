<?php
/*
 * Copyright (c) 2014-2016 Nicolas Braquart
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Ngames\Framework\Database;

/**
 * This class handles the connection to the database.
 *
 * @author Nicolas Braquart <nicolas.braquart+ngames@gmail.com>
 */
class Connection
{
    protected static $queries = [];

    public const PDO_EXCEPTION_MESSAGE = 'Caught PDO exception';

    /**
     *
     * @var \PDO
     */
    protected static $connection = null;

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
        if (!self::$connection) {
            $configuration = \Ngames\Framework\Application::getInstance()->getConfiguration();
            $dsn = sprintf('mysql:host=%s;dbname=%s', $configuration->database->host, $configuration->database->name);

            self::$connection = new \PDO($dsn, $configuration->database->username, $configuration->database->password, [
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]);
        }

        return self::$connection;
    }

    /**
     * Query the database and return the data.
     * NB: all data are string, if native type is needed:
     * http://stackoverflow.com/questions/2430640/g.
     *
     * @param string $query
     * @param array $params
     *
     * @return array|boolean The result of the query
     */
    public static function query($query, array $params = [])
    {
        try {
            $statement = self::getConnection()->prepare($query);
            $result = false;
            $start = microtime(true);

            if ($statement && $statement->execute($params)) {
                $result = [];
                self::logQuery($query, microtime(true) - $start);

                while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                    $result[] = $row;
                }
            }
        } catch (\PDOException $e) {
            throw new \Ngames\Framework\Exception(self::PDO_EXCEPTION_MESSAGE, 0, $e);
        }

        return $result;
    }

    /**
     * Execute a modifying query on the database (INSERT, UPDATE or DELETE).
     * If needed, after rowCount(): while ($statement->fetch(\PDO::FETCH_ASSOC)) {}.
     *
     * @param string $query
     * @param array $params
     *
     * @return int|boolean The number of rows impacted
     */
    public static function exec($query, array $params = [])
    {
        try {
            $statement = self::getConnection()->prepare($query);
            $result = false;
            $start = microtime(true);

            if ($statement && $statement->execute($params)) {
                self::logQuery($query, microtime(true) - $start);
                $result = $statement->rowCount();
            }
        } catch (\PDOException $e) {
            throw new \Ngames\Framework\Exception(self::PDO_EXCEPTION_MESSAGE, 0, $e);
        }

        return $result;
    }

    /**
     * Count the number of rows matching the query.
     *
     * @param string $query
     * @param array $params
     *
     * @return int|boolean
     */
    public static function count($query, array $params = [])
    {
        return self::exec($query, $params);
    }

    /**
     * Helper method querying the database for a single row.
     *
     * @param string $query
     * @param array $params
     *
     * @return array|bool
     */
    public static function queryOne($query, array $params = [])
    {
        $result = self::query($query, $params);

        return is_array($result) && !empty($result) ? $result[0] : false;
    }

    /**
     * Inserts data in the database.
     *
     * @param string $tableName
     * @param array $data
     *
     * @return bool|number
     */
    public static function insert($tableName, array $data)
    {
        $keys = array_keys($data);
        $placeholders = array_map(function ($v) {
            return ':' . $v;
        }, $keys);
        $query = 'INSERT INTO `' . $tableName . '` (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $placeholders) . ')';

        if (self::exec($query, $data) === false) {
            return false;
        }

        return (int) self::getConnection()->lastInsertId();
    }

    /**
     * Returns an element by its primary key.
     *
     * @param string $tableName
     * @param int $id
     *
     * @return array|bool
     */
    public static function findOneById($tableName, $id)
    {
        $query = 'SELECT * FROM `' . $tableName . '` WHERE id=?';

        return self::queryOne($query, [
            (int) $id
        ]);
    }

    /**
     * Return the last error that occured.
     *
     * @return array
     */
    public static function getLastError()
    {
        return self::getConnection()->errorInfo();
    }

    /**
     * Return the number of queries run on the database.
     */
    public static function getQueryCounter()
    {
        return count(self::$queries);
    }

    /**
     * Return all executed queries with their text and execution time.
     *
     * @return array
     */
    public static function getQueries()
    {
        return self::$queries;
    }

    /**
     * Logs the query and its execution time.
     *
     * @param string $queryString
     * @param float $duration
     */
    protected static function logQuery($queryString, $duration)
    {
        // Keep only microsecodns (no nano)
        $duration = round($duration, 6) * 1000;

        // Log and record the SQL query
        \Ngames\Framework\Logger::logDebug('SQL query: [' . $duration . ' ms] ' . $queryString);
        self::$queries[] = [
            'sql' => $queryString,
            'duration' => $duration
        ];
    }
}

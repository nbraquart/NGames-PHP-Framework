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
namespace Ngames\Framework\Tests\Database;

use Ngames\Framework\Database\Connection;

/**
 * Common test case class for all tests involving a database
 * @author Nicolas Braquart <nicolas.braquart+ngames@gmail.com>
 */
class DbTestCase extends \PHPUnit\DbUnit\TestCase
{
    /**
     * @beforeClass
     */
    public static function beforeClass()
    {
        DummyConnection::getConnection()->exec('CREATE TABLE book(id INT, title VARCHAR(100), description VARCHAR(100), author_id INT)');
        DummyConnection::getConnection()->exec('CREATE TABLE author(id INT, last_name VARCHAR(100), first_name VARCHAR(100))');
    }

    /**
     * @afterClass
     */
    public static function afterClass()
    {
        DummyConnection::getConnection()->exec('DROP TABLE book');
        DummyConnection::getConnection()->exec('DROP TABLE author');
    }

    /**
     *
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $pdo = DummyConnection::getConnection();
        return $this->createDefaultDBConnection($pdo, ':memory:');
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_ArrayDataSet
     */
    public function getDataSet()
    {
        return $this->createArrayDataSet(array(
            'book' => array(
                array('id' => 1, 'title' => 'Book 1', 'description' => 'Description 1', 'author_id' => 1),
                array('id' => 2, 'title' => 'Book 2', 'description' => 'Description 2', 'author_id' => 1),
                array('id' => 3, 'title' => 'Book 3', 'description' => 'Description 3', 'author_id' => 2)
            ),
            'author' => array(
                array('id' => 1, 'last_name' => 'Last Name 1', 'first_name' => 'First Name 1'),
                array('id' => 2, 'last_name' => 'Last Name 2', 'first_name' => 'First Name 2')
            )
        ));
    }
}

/**
 * This class allows to override the PDO object within the Connection database.
 *
 * @author Nicolas Braquart <nicolas.braquart+ngames@gmail.com>
 */
class DummyConnection extends Connection
{

    public static function getConnection()
    {
        if (!parent::$connection) {
            parent::$connection = new \PDO('sqlite::memory:');
        }

        return parent::$connection;
    }
}
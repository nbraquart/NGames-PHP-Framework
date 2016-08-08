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
namespace Ngames\Framework\Tests;

use Ngames\Framework\Request;
use Ngames\Framework\Storage\PhpSession;
use Ngames\Framework\Exception;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testGetSession()
    {
        // Init a session
        session_start();
        $_SESSION['session_key1'] = 'session_val1';

        $request = $this->getRequest();
        $this->assertInstanceOf(PhpSession::class, $request->getSession());
        $this->assertEquals('session_val1', $request->getSession()->get('session_key1'));
        $this->assertEquals(null, $request->getSession()->get('session_key2'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetMethod()
    {
        $this->assertEquals('GET', $this->getRequest()->getMethod());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetGetParameter()
    {
        $request = $this->getRequest();
        $this->assertEquals('get_val1', $request->getGetParameter('get_key1'));
        $this->assertNull($request->getGetParameter('get_key2'));
        $this->assertEquals('default', $request->getGetParameter('get_key2', 'default'));
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testGetPostParameter()
    {
        $request = $this->getRequest();
        $this->assertEquals('post_val1', $request->getPostParameter('post_key1'));
        $this->assertNull($request->getPostParameter('post_key2'));
        $this->assertEquals('default', $request->getPostParameter('post_key2', 'default'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetCookie()
    {
        $request = $this->getRequest();
        $this->assertEquals('cookie_val1', $request->getCookie('cookie_key1'));
        $this->assertNull($request->getCookie('cookie_key2'));
        $this->assertEquals('default', $request->getCookie('cookie_key2', 'default'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testMethod()
    {
        $this->assertTrue($this->getRequest('GET')->isGet());
        $this->assertTrue($this->getRequest('POST')->isPost());
        $this->assertTrue($this->getRequest('PUT')->isPut());
        $this->assertTrue($this->getRequest('DELETE')->isDelete());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetHeader()
    {
        $request = $this->getRequest();
        $this->assertEquals('requested-with-val', $request->getHeader('X-Requested-With'));
        $this->assertNull($request->getHeader('not-set'));
        $this->assertEquals('default', $request->getHeader('not-set', 'default'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetFile()
    {
        $request = $this->getRequest();
        $this->assertInternalType('array', $request->getFile('file'));
        $this->assertNull($request->getFile('not-set'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testIsCli()
    {
        $this->assertTrue((new Request())->isCli());
        $this->assertFalse($this->getRequest()->isCli());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetRequestUri()
    {
        $request = $this->getRequest('GET', '/test/test2/TEST-3?key=val');
        $this->assertEquals('/test/test2/test-3', $request->getRequestUri());
        $request->setRequestUri('/test2');
        $this->assertEquals('/test2', $request->getRequestUri());

        $request = $this->getRequest('GET', '/');
        $this->assertEquals('/', $request->getRequestUri());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetRequestUri_errorInvalid()
    {
        $this->setExpectedException(Exception::class, 'Invalid requested URI');
        $request = $this->getRequest('GET', 'é');
    }
    
    /**
     * @runInSeparateProcess
     */
    public function testGetRemoteAddress()
    {
        $request = new Request([], [], [], array(
            'REMOTE_ADDR' => '1'
        ));
        $this->assertEquals('1', $request->getRemoteAddress());
        
        $request = new Request([], [], [], array(
            'REMOTE_ADDR' => '1',
            'HTTP_CLIENT_IP' => '2'
        ));
        $this->assertEquals('2', $request->getRemoteAddress());
        
        $request = new Request([], [], [], array(
            'REMOTE_ADDR' => '1',
            'HTTP_CLIENT_IP' => '2',
            'HTTP_X_FORWARDED_FOR' => '3'
        ));
        $this->assertEquals('3', $request->getRemoteAddress());
        
        $this->assertNull((new Request())->getRemoteAddress());
    }
    
    /**
     * @return Request
     */
    private function getRequest($method = 'GET', $uri = '/test')
    {
        $request = $this->getMockBuilder(Request::class)->setMethods(['isCli'])->setConstructorArgs(array(
            array('get_key1' => 'get_val1'),
            array('post_key1' => 'post_val1'),
            array('cookie_key1' => 'cookie_val1'),
            array('REQUEST_METHOD' => $method, 'REQUEST_URI' => $uri, 'HTTP_X_REQUESTED_WITH' => 'requested-with-val'),
            array('file' => array())
        ))->getMock();
        $request->method('isCli')->willReturn(false);
        
        return $request;
    }
}

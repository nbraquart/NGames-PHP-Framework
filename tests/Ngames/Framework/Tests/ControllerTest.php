<?php
/*
 * Copyright (c) 2014-2021 NGames
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

use Controller\Application\DummyController;
use Ngames\Framework\Request;
use Ngames\Framework\Router\Route;
use Ngames\Framework\Controller;
use Ngames\Framework\Application;

require_once 'DummyController.php';

class ControllerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @before
     * @after
     */
    public function deleteApplicationInstance()
    {
        // Reset the instance
        $reflection = new \ReflectionClass(Application::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        $instance->setAccessible(false);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSetRequest()
    {
        // Simply expect that no exception or error happens
        $controller = new DummyController();
        $controller->setRequest(new Request());
        $this->assertObjectHasAttribute('request', $controller);
    }

    public function testOk()
    {
        $controller = new DummyController();
        $response = $controller->okAction();
        ob_start();
        $response->send();
        $this->assertEquals('ok', ob_get_contents());
        $this->assertEquals(200, http_response_code());
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
    public function testRedirect()
    {
        $controller = new DummyController();
        $response = $controller->redirectAction();
        ob_start();
        $response->send();
        $this->assertEmpty(ob_get_contents());
        $this->assertEquals(301, http_response_code());
        $this->assertContains('Location: url', \xdebug_get_headers());
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
    public function testNotFound()
    {
        $controller = new DummyController();
        $response = $controller->notFoundAction();
        ob_start();
        $response->send();
        $this->assertEquals('not_found', ob_get_contents());
        $this->assertEquals(404, http_response_code());
        $this->assertContains('Content-Type: text/plain; charset=utf-8', \xdebug_get_headers());
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
    public function testBadRequest()
    {
        $controller = new DummyController();
        $response = $controller->badRequestAction();
        ob_start();
        $response->send();
        $this->assertEquals('bad_request', ob_get_contents());
        $this->assertEquals(400, http_response_code());
        $this->assertContains('Content-Type: text/plain; charset=utf-8', \xdebug_get_headers());
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
    public function testInternalError()
    {
        $controller = new DummyController();
        $response = $controller->internalErrorAction();
        ob_start();
        $response->send();
        $this->assertEquals('internal_error', ob_get_contents());
        $this->assertEquals(500, http_response_code());
        $this->assertContains('Content-Type: text/plain; charset=utf-8', \xdebug_get_headers());
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
    public function testUnauthorized()
    {
        $controller = new DummyController();
        $response = $controller->unauthorizedAction();
        ob_start();
        $response->send();
        $this->assertEquals('unauthorized', ob_get_contents());
        $this->assertEquals(401, http_response_code());
        $this->assertContains('Content-Type: text/plain; charset=utf-8', \xdebug_get_headers());
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
    public function testJson()
    {
        $controller = new DummyController();
        $response = $controller->jsonAction();
        ob_start();
        $response->send();
        $this->assertEquals("{\n    \"key\": \"value\"\n}", ob_get_contents());
        $this->assertEquals(200, http_response_code());
        $this->assertContains('Content-Type: application/json; charset=utf-8', \xdebug_get_headers());
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
    public function testExecute()
    {
        $route = new Route('application', 'dummy', 'index');
        $request = new Request();
        ob_start();
        Controller::execute($route, $request)->send();
        $this->assertEquals('index', ob_get_contents());
        $this->assertEquals(200, http_response_code());
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
    public function testForward()
    {
        $route = new Route('application', 'dummy', 'forward');
        $request = new Request();
        ob_start();
        Controller::execute($route, $request)->send();
        $this->assertEquals('forward_after', ob_get_contents());
        $this->assertEquals(200, http_response_code());
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
    public function testExecute_errorMethodNotFound()
    {
        // Application instance needed for the configuration
        Application::initialize(ROOT_DIR . '/tests/data/Application/config.ini');
        $route = new Route('application', 'dummy', 'does_not_exist');
        $request = new Request();
        ob_start();
        Controller::execute($route, $request)->send();
        $this->assertEquals('File not found.', ob_get_contents());
        $this->assertEquals(404, http_response_code());
        ob_end_clean();
    }
}

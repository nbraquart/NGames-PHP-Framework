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

namespace Ngames\Framework\Tests\Router;

use Ngames\Framework\Router\Matcher;
use Ngames\Framework\Router\Router;

class RouterTest extends \PHPUnit\Framework\TestCase
{
    public function testGetRoute_noRouteDefined()
    {
        $router = new Router();

        // No route defined
        $route = $router->getRoute('/');
        $this->assertNull($route);
    }

    public function testGetRoute_match()
    {
        $router = new Router();
        $router->addMatcher(new Matcher('/', 'default-module', 'default-controller', 'default-action'));
        $route = $router->getRoute('/');
        $this->assertEquals('default-module', $route->getModuleName());
        $this->assertEquals('default-controller', $route->getControllerName());
        $this->assertEquals('default-action', $route->getActionName());
    }

    public function testGetRoute_noMatch()
    {
        $router = new Router();
        $router->addMatcher(new Matcher('/test', 'default-module', 'default-controller', 'default-action'));
        $route = $router->getRoute('/test1');
        $this->assertNull($route);
    }

    public function testGetRoute_routeOrder()
    {
        $router = new Router();
        $router->addMatcher(new Matcher('/test', 'test1-module', 'test1-controller', 'test1-action'));
        $router->addMatcher(new Matcher('/test', 'test2-module', 'test2-controller', 'test2-action'));
        $route = $router->getRoute('/test');
        $this->assertEquals('test1-module', $route->getModuleName());
        $this->assertEquals('test1-controller', $route->getControllerName());
        $this->assertEquals('test1-action', $route->getActionName());
    }
}

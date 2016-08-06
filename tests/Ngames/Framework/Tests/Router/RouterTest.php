<?php

namespace Framework\Tests;

use Ngames\Framework\Router\Matcher;
use Ngames\Framework\Router\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $router = new Router();
    }

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

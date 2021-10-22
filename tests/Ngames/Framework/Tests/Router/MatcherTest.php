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

namespace Ngames\Framework\Tests\Router;

use Ngames\Framework\Router\Matcher;

class MatcherTest extends \PHPUnit\Framework\TestCase
{
    // Initialization error cases
    public function testInvalidInitialization_missingModuleKeyAndValue()
    {
        $this->expectException('\Ngames\Framework\Router\InvalidMatcherException');
        $this->expectExceptionMessage('Missing module key or module value, or provided both');
        new Matcher('/:controller/:action');
    }

    public function testInvalidInitialization_moduleKeyAndValue()
    {
        $this->expectException('\Ngames\Framework\Router\InvalidMatcherException');
        $this->expectExceptionMessage('Missing module key or module value, or provided both');
        new Matcher('/:module', 'module');
    }

    public function testInvalidInitialization_missingControllerKeyAndValue()
    {
        $this->expectException('\Ngames\Framework\Router\InvalidMatcherException');
        $this->expectExceptionMessage('Missing controller key or controller value, or provided both');
        new Matcher('/:module/:action');
    }

    public function testInvalidInitialization_controllerKeyAndValue()
    {
        $this->expectException('\Ngames\Framework\Router\InvalidMatcherException');
        $this->expectExceptionMessage('Missing controller key or controller value, or provided both');
        new Matcher('/:controller', 'module', 'controller');
    }

    public function testInvalidInitialization_missingActionKeyAndValue()
    {
        $this->expectException('\Ngames\Framework\Router\InvalidMatcherException');
        $this->expectExceptionMessage('Missing action key or action value, or provided both');
        new Matcher('/:module/:controller');
    }

    public function testInvalidInitialization_actionKeyAndValue()
    {
        $this->expectException('\Ngames\Framework\Router\InvalidMatcherException');
        $this->expectExceptionMessage('Missing action key or action value, or provided both');
        new Matcher('/:action', 'module', 'controller', 'action');
    }

    // No match cases
    public function testNoMatch()
    {
        $matcher1 = new Matcher('/test', 'module1', 'controller1', 'action1');
        $this->assertNull($matcher1->match('/test1'));

        $matcher2 = new Matcher('/test/test', 'module2', 'controller2', 'action2');
        $this->assertNull($matcher1->match('/test/test1'));

        $matcher2 = new Matcher('/:module/:controller/:action');
        $this->assertNull($matcher1->match('/module/controller/action/a'));
    }

    // Match cases
    public function testMatch_onlyDefault()
    {
        $matcher1 = new Matcher('/test', 'module1', 'controller1', 'action1');
        $result1 = $matcher1->match('/test');
        $this->assertNotNull($result1);
        $this->assertEquals('module1', $result1->getModuleName());
        $this->assertEquals('controller1', $result1->getControllerName());
        $this->assertEquals('action1', $result1->getActionName());
    }

    public function testMatch_matchModule()
    {
        $matcher = new Matcher('/:module', null, 'controller', 'action');
        $result = $matcher->match('/module-match');
        $this->assertNotNull($result);
        $this->assertEquals('module-match', $result->getModuleName());
        $this->assertEquals('controller', $result->getControllerName());
        $this->assertEquals('action', $result->getActionName());
    }

    public function testMatch_matchController()
    {
        $matcher = new Matcher('/:controller', 'module', null, 'action');
        $result = $matcher->match('/controller-match');
        $this->assertNotNull($result);
        $this->assertEquals('module', $result->getModuleName());
        $this->assertEquals('controller-match', $result->getControllerName());
        $this->assertEquals('action', $result->getActionName());
    }

    public function testMatch_matchAction()
    {
        $matcher = new Matcher('/:action', 'module', 'controller', null);
        $result = $matcher->match('/action-match');
        $this->assertNotNull($result);
        $this->assertEquals('module', $result->getModuleName());
        $this->assertEquals('controller', $result->getControllerName());
        $this->assertEquals('action-match', $result->getActionName());
    }
}

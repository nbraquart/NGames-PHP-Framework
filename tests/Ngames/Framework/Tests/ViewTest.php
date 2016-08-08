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

use Ngames\Framework\View;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSetVariable()
    {
        $view = new View();
        $this->assertEquals('value', $view->test = 'value');
        $this->assertEquals('value', $view->test);
    }

    public function testGetVariable_errorNotSet()
    {
        $this->setExpectedException('\Ngames\Framework\Exception', 'Tried to access non existing variable test');
        $view = new View();
        $view->test;
    }

    public function testGetVariable_errorProtected()
    {
        $this->setExpectedException('\Ngames\Framework\Exception', 'Tried to access reserved variable __STYLESHEETS__');
        $view = new View();
        $view->__STYLESHEETS__;
    }
    
    public function testSetVariable_errorProtected()
    {
        $this->setExpectedException('\Ngames\Framework\Exception', 'Cannot used reserved variable __STYLESHEETS__');
        $view = new View();
        $view->__STYLESHEETS__ = 'value';
    }

    public function testUnsetVariable()
    {
        $view = new View();
        $view->test = 'value';
        unset($view->test);
    }
}

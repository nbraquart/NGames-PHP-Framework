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
namespace Ngames\Framework;

use Ngames\Framework\Storage\PhpSession;

/**
 * Stores all informations relative to the current request being processed.
 * This is initialized by the application, and given to the controller.
 * 
 * @author Nicolas Braquart <nicolas.braquart+ngames@gmail.com>
 */
class Request
{

    const HTTP_METHOD_OPTIONS = 'OPTIONS';

    const HTTP_METHOD_GET = 'GET';

    const HTTP_METHOD_HEAD = 'HEAD';

    const HTTP_METHOD_POST = 'POST';

    const HTTP_METHOD_PUT = 'PUT';

    const HTTP_METHOD_DELETE = 'DELETE';

    const HTTP_METHOD_TRACE = 'TRACE';

    const HTTP_METHOD_CONNECT = 'CONNECT';

    /**
     * The request method.
     * Can be only one of the constants HTTP_METHOD*.
     *
     * @var string
     */
    protected $method;

    /**
     * The requested URI.
     * Does not contain protocol, hostname nor query string.
     *
     * @var string
     */
    protected $requestUri;

    /**
     * URL Parameters of the request.
     *
     * @var array
     */
    protected $getParameters;

    /**
     * POST parameters (form data).
     *
     * @var array
     */
    protected $postParameters;

    /**
     * Cookies from the request (at request start, ie changes during request processing are not reflected here).
     *
     * @var array
     */
    protected $cookies;

    /**
     * Server variables.
     *
     * @var array
     */
    protected $server;

    /**
     * Session variables at request start (as for cookies, changes during request processing are not reflected here).
     *
     * @var PhpSession
     */
    protected $session;

    /**
     * Files sent in current request.
     *
     * @var array
     */
    protected $files;

    /**
     * Create a new request object from the request context
     *
     * @param String $method            
     * @param String $requestUri            
     * @param array $getParameters            
     * @param array $postParameters            
     * @param array $cookies            
     * @param PhpSession $session            
     * @param array $server            
     * @param array $files            
     */
    private function __construct($method, $requestUri, $getParameters, $postParameters, $cookies, PhpSession $session, $server, $files)
    {
        $this->method = $method;
        $this->requestUri = $requestUri;
        $this->postParameters = $postParameters;
        $this->getParameters = $getParameters;
        $this->cookies = $cookies;
        $this->session = $session;
        $this->server = $server;
        $this->files = $files;
    }

    /**
     * Create a new request from the global variables.
     *
     * @return Request
     */
    public static function createRequestFromGlobals()
    {
        // Get the URI (only if not CLI)
        if (PHP_SAPI != 'cli') {
            $uri = explode('?', $_SERVER['REQUEST_URI'])[0];
            $requestMethod = $_SERVER['REQUEST_METHOD'];
        } else {
            $uri = null;
            $requestMethod = null;
        }
        
        // Build and return the request
        $request = new \Ngames\Framework\Request($requestMethod, $uri, $_GET, $_POST, $_COOKIE, PhpSession::getInstance(), $_SERVER, $_FILES);
        
        return $request;
    }

    /**
     *
     * @return PhpSession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     *
     * @return boolean
     */
    public function isPost()
    {
        return $this->method == self::HTTP_METHOD_POST;
    }

    /**
     *
     * @return boolean
     */
    public function isGet()
    {
        return $this->method == self::HTTP_METHOD_GET;
    }

    /**
     *
     * @return boolean
     */
    public function isDelete()
    {
        return $this->method == self::HTTP_METHOD_DELETE;
    }

    /**
     *
     * @return boolean
     */
    public function isPut()
    {
        return $this->method == self::HTTP_METHOD_PUT;
    }

    /**
     *
     * @return string
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * Set the request URI.
     * Useful to change it in forward use-case.
     *
     * @param String $uri            
     * @return Request
     */
    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
        
        return $this;
    }

    /**
     * Return the client IP.
     * Tries to return the most relevant value.
     *
     * @return string|null
     */
    public function getRemoteAddress()
    {
        if (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            $ip = $this->server['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($this->server['HTTP_CLIENT_IP'])) {
            $ip = $this->server['HTTP_CLIENT_IP'];
        } elseif (!empty($this->server['REMOTE_ADDR'])) {
            $ip = $this->server['REMOTE_ADDR'];
        } else {
            $ip = null;
        }
        
        return $ip === null ? null : str_replace('::ffff:', null, $ip);
    }
}

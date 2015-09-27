<?php
namespace Framework;

use Framework\Storage\PhpSession;

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
     * Can be only one of the constants HTTP_METHOD*
     *
     * @var string
     */
    protected $method;

    /**
     * The requested URI.
     * Does not contain protocol, hostname nor query string
     *
     * @var unknown
     */
    protected $requestUri;

    /**
     * URL Parameters of the request
     *
     * @var array
     */
    protected $getParameters;

    /**
     * POST parameters (form data)
     *
     * @var array
     */
    protected $postParameters;

    /**
     * Cookies from the request (at request start, ie changes during request processing are not reflected here)
     *
     * @var array
     */
    protected $cookies;

    /**
     * Server variables
     *
     * @var array
     */
    protected $server;

    /**
     * Session variables at request start (as for cookies, changes during request processing are not reflected here)
     *
     * @var PhpSession
     */
    protected $session;

    /**
     * Files sent in current request
     *
     * @var array
     */
    protected $files;

    public function __construct($method, $requestUri, $getParameters, $postParameters, $cookies, PhpSession $session, $server, $files)
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
        $request = new \Framework\Request($requestMethod, $uri, $_GET, $_POST, $_COOKIE, PhpSession::getInstance(), $_SERVER, $_FILES);
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

    public function getMethod()
    {
        return $this->method;
    }

    public function isPost()
    {
        return $this->method == self::HTTP_METHOD_POST;
    }

    public function isGet()
    {
        return $this->method == self::HTTP_METHOD_GET;
    }

    public function isDelete()
    {
        return $this->method == self::HTTP_METHOD_DELETE;
    }

    public function isPut()
    {
        return $this->method == self::HTTP_METHOD_PUT;
    }

    public function getRequestUri()
    {
        return $this->requestUri;
    }

    public function setRequestUri($uri)
    {
        $this->requestUri = $uri;
        return $this;
    }

    public function getRemoteAddress()
    {
        if (! empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            $ip = $this->server['HTTP_X_FORWARDED_FOR'];
        } elseif (! empty($this->server['HTTP_CLIENT_IP'])) {
            $ip = $this->server['HTTP_CLIENT_IP'];
        } elseif (! empty($this->server['REMOTE_ADDR'])) {
            $ip = $this->server['REMOTE_ADDR'];
        } else {
            $ip = null;
        }
        
        return str_replace('::ffff:', null, $ip);
    }
}
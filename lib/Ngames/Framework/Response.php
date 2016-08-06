<?php

namespace Ngames\Framework;

class Response
{
    const HTTP_STATUS_OK = 200;

    const HTTP_STATUS_CREATED = 201;

    const HTTP_STATUS_MOVED_PERMANENTLY = 301;

    const HTTP_STATUS_FOUND = 302;

    const HTTP_STATUS_NOT_MODIFIED = 304;

    const HTTP_STATUS_BAD_REQUEST = 400;

    const HTTP_STATUS_UNAUTHORIZED = 401;

    const HTTP_STATUS_FORBIDDEN = 403;

    const HTTP_STATUS_NOT_FOUND = 404;

    const HTTP_STATUS_INTERNAL_SERVER_ERROR = 500;

    const HTTP_STATUS_NOT_IMPLEMENTED = 501;

    protected $statusCode;

    protected $headers;

    protected $content;

    public function __construct()
    {
        $this->headers = [];
        $this->content = null;
        $this->statusCode = self::HTTP_STATUS_OK;
    }

    public function send()
    {
        // Send headers
        foreach ($this->headers as $name => $value) {
            header($name.': '.$value);
        }

        // Set response code
        http_response_code($this->statusCode);

        // Send the content
        echo $this->content;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public static function createOkResponse($content = null)
    {
        $response = new self();
        $response->setContent($content);
        $response->setStatusCode(self::HTTP_STATUS_OK);

        return $response;
    }

    public static function createInternalErrorResponse($message = null)
    {
        $response = new self();
        $response->setHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->setContent($message != null ? $message : 'Internal server error.');
        $response->setStatusCode(self::HTTP_STATUS_INTERNAL_SERVER_ERROR);

        return $response;
    }

    public static function createNotFoundResponse($message = null)
    {
        $response = new self();
        $response->setHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->setContent($message != null ? $message : 'File not found.');
        $response->setStatusCode(self::HTTP_STATUS_NOT_FOUND);

        return $response;
    }

    public static function createBadRequestResponse($message = null)
    {
        $response = new self();
        $response->setHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->setContent($message != null ? $message : 'Bad request.');
        $response->setStatusCode(self::HTTP_STATUS_BAD_REQUEST);

        return $response;
    }

    public static function createRedirectResponse($url)
    {
        $response = new self();
        $response->setStatusCode(self::HTTP_STATUS_MOVED_PERMANENTLY);
        $response->setHeader('Location', $url);

        return $response;
    }
}

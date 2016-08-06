<?php

namespace Ngames\Framework;

use Ngames\Framework\Storage\IniFile;

class Application
{
    protected static $instance = null;

    protected $autoloader = null;

    protected $router = null;

    protected $configuration = null;

    protected $timer = null;

    public static function initialize($configurationFile)
    {
        // First check an instance does not already exists
        if (self::$instance != null) {
            require_once __DIR__.'/Exception.php';
            throw new Exception('The application has already been initialized');
        }

        return self::$instance = new self($configurationFile);
    }

    public static function getInstance()
    {
        // Ensure instance exists
        if (self::$instance == null) {
            require_once __DIR__.'/Exception.php';
            throw new Exception('The application has not been initialized');
        }

        return self::$instance;
    }

    private function __construct($configurationFile)
    {
        // Register autoload
        require_once __DIR__.'/Autoloader.php';
        $this->autoloader = new Autoloader();
        $this->autoloader->register();

        // Initialize the router
        $this->router = new \Ngames\Framework\Router\Router();

        // Initialize the timer
        $this->timer = new \Ngames\Framework\Timer();

        // Parse the configuration
        $this->configuration = new IniFile($configurationFile);

        // Intialize the logging facility if needed
        if ($this->configuration->has('log')) {
            $destination = $this->configuration->log->destination;
            $constantName = '\Ngames\Framework\Logger::LEVEL_'.strtoupper($this->configuration->log->level);

            // Initialize the logger if possible
            if (defined($constantName)) {
                $level = constant($constantName);
                \Ngames\Framework\Logger::initialize($destination, $level);
            }
        }
    }

    /**
     * @return \Ngames\Framework\Storage\IniFile
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return \Ngames\Framework\Autoloader
     */
    public function getAutoloader()
    {
        return $this->autoloader;
    }

    /**
     * @return \Ngames\Framework\Timer
     */
    public function getTimer()
    {
        return $this->timer;
    }

    /**
     * @return \Ngames\Framework\Router\Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    public function isDebug()
    {
        return $this->configuration->debug == '1' || $this->configuration->debug == 'true';
    }

    public function run()
    {
        try {
            // Execute the module/controller/action
            $request = \Ngames\Framework\Request::createRequestFromGlobals();
            $route = $this->router->getRoute($request->getRequestUri());
            $response = null;

            if ($route == null) {
                $response = Response::createNotFoundResponse($this->isDebug() ? 'No route matched the requested URI' : null);
            } else {
                $actionResult = Controller::execute($route, $request);

                // If not a response object (string typically), constructs it (but it's a default instance)
                if ($actionResult instanceof Response) {
                    $response = $actionResult;
                } elseif (is_string($actionResult)) {
                    $response = new Response();
                    $response->setHeader('Content-Type', 'text/html; charset=utf-8');
                    $response->setContent($actionResult);
                }
            }

            if ($response == null) {
                throw new Exception('Invalid response');
            }

            // Send the response
            $response->send();
        } catch (\Exception $e) {
            $content = "Internal server error.\n\n".\Ngames\Framework\Exception::trace($e);
            \Ngames\Framework\Logger::logError($content);

            Response::createInternalErrorResponse($this->isDebug() ? $content : null)->send();
        }
    }
}

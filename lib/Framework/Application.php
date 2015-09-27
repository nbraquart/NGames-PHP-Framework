<?php
namespace Framework;

use Framework\Storage\IniFile;
use Framework\Storage\PhpSession;

class Application
{

    protected static $instance = null;

    protected $autoloader = null;

    protected $configuration = null;

    protected $request = null;

    protected $timer = null;

    public static function initialize($configurationFile)
    {
        // First check an instance does not already exists
        if (self::$instance != null) {
            require_once __DIR__ . '/Exception.php';
            throw new Exception('The application has already been initialized');
        }
        
        return self::$instance = new self($configurationFile);
    }

    public static function getInstance()
    {
        // Ensure instance exists
        if (self::$instance == null) {
            require_once __DIR__ . '/Exception.php';
            throw new Exception('The application has not been initialized');
        }
        
        return self::$instance;
    }

    protected function __construct($configurationFile)
    {
        // Register autoload
        require_once __DIR__ . '/Autoloader.php';
        $this->autoloader = new Autoloader();
        $this->autoloader->register();
        
        // Initialize the timer
        $this->timer = new \Framework\Timer();
        
        // Parse the configuration
        $this->configuration = new IniFile($configurationFile);
        
        // Intialize the logging facility if needed
        if ($this->configuration->has('log')) {
            $destination = $this->configuration->log->destination;
            $constantName = '\Framework\Logger::LEVEL_' . strtoupper($this->configuration->log->level);
            
            // Initialize the logger if possible
            if (defined($constantName)) {
                $level = constant($constantName);
                \Framework\Logger::initialize($destination, $level);
            }
        }
        
        // Parse the request
        $this->request = \Framework\Request::createRequestFromGlobals();
    }

    /**
     *
     * @return \Framework\Storage\IniFile
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     *
     * @return \Framework\Autoloader
     */
    public function getAutoloader()
    {
        return $this->autoloader;
    }

    /**
     *
     * @return \Framework\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     *
     * @return \Framework\Timer
     */
    public function getTimer()
    {
        return $this->timer;
    }

    public function isDebug()
    {
        return $this->configuration->debug == '1' || $this->configuration->debug == 'true';
    }

    public function run()
    {
        try {
            // Execute the module/controller/action
            $actionResult = Controller::execute($this->request);
            
            // If not a response object (string typically), constructs it (but it's a default instance)
            if ($actionResult instanceof Response) {
                $response = $actionResult;
            } elseif (is_string($actionResult)) {
                $response = new Response();
                $response->setHeader('Content-Type', 'text/html; charset=utf-8');
                $response->setContent($actionResult);
            } else {
                throw new Exception('Invalid output');
            }
            
            // Send the response
            $response->send();
        } catch (\Exception $e) {
            $content = "Internal server error.\n\n" . \Framework\Exception::trace($e);
            \Framework\Logger::logError($content);
            
            Response::createInternalErrorResponse($this->isDebug() ? $content : null)->send();
        }
    }
}
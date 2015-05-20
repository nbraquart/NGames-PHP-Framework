<?php
namespace Framework;

use Framework\Exception\MalformedUrlException;

class Router
{
    // Constants for defaults
    const DEFAULT_MODULE = 'application';
    const DEFAULT_CONTROLLER = 'index';
    const DEFAULT_ACTION = 'index';
    
    protected $moduleName;
    protected $controllerName;
    protected $actionName;
    
    public function __construct($uri)
    {
        if (!is_string($uri)) {
            var_dump($uri);
            exit;
        }
        
        $uriParts = array_values(array_filter(explode('/', $uri), function ($uriPart) {
            return ! empty($uriPart);
        }));
        
        if (count($uriParts) == 0) {
            $this->moduleName = self::DEFAULT_MODULE;
            $this->controllerName = self::DEFAULT_CONTROLLER;
            $this->actionName = self::DEFAULT_ACTION;
        } elseif(count($uriParts) == 1) {
            $this->moduleName = $uriParts[0];
            $this->controllerName = self::DEFAULT_CONTROLLER;
            $this->actionName = self::DEFAULT_ACTION;
        } elseif(count($uriParts) == 2) {
            $this->moduleName = $uriParts[0];
            $this->controllerName = $uriParts[1];
            $this->actionName = self::DEFAULT_ACTION;
        } elseif(count($uriParts) == 3) {
            $this->moduleName = $uriParts[0];
            $this->controllerName = $uriParts[1];
            $this->actionName = $uriParts[2];
        } else {
            $this->moduleName = null;
            $this->controllerName = null;
            $this->actionName = null;
        }
    }
    
    /**
     * Basic implementation: /[module/[controller[/action]]]
     * @param string $uri
     * @throws Exception
     */
    protected function parseUri($uri)
    {
    }

    public function getModuleName()
    {
        return $this->moduleName;
    }
    
    public function getControllerName()
    {
        return $this->controllerName;
    }
    
    public function getActionName()
    {
        return $this->actionName;
    }
}
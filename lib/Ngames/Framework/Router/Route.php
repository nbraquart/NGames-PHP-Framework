<?php

namespace Ngames\Framework\Router;

class Route
{
    protected $moduleName;

    protected $controllerName;

    protected $actionName;

    public function __construct($moduleName, $controllerName, $actionName)
    {
        $this->moduleName = $moduleName;
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
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

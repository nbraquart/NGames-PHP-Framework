<?php
namespace Ngames\Framework\Router;

class Matcher
{

    const MODULE_KEY = ":module";

    const CONTROLLER_KEY = ":controller";

    const ACTION_KEY = ":action";

    private $pattern;

    private $moduleName;

    private $controllerName;

    private $actionName;

    /**
     * Create a new matcher that will be used to test the route eligility.
     *
     * The pattern may define the URI part where module, controller or action are read. If not, the corresponding element must have a value defined.
     * Samples pattern are:
     * /home + module=default, controller=index, action=index
     * /:controller/:action + module=default
     * Etc.
     *
     * @param String $pattern            
     * @param String $moduleName            
     * @param String $controllerName            
     * @param String $actionName            
     */
    public function __construct($pattern, $moduleName = null, $controllerName = null, $actionName = null)
    {
        $this->pattern = $pattern;
        $this->moduleName = $moduleName;
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        
        $this->check();
    }

    /**
     * Tries to match the input URI.
     * Output is null if no match, a route otherwise.
     *
     * @param String $uri            
     * @return Route
     */
    public function match($uri)
    {
        $pattern = $this->prepareForMatching($this->pattern);
        $uri = $this->prepareForMatching($uri);
        $moduleName = $this->moduleName;
        $controllerName = $this->controllerName;
        $actionName = $this->actionName;
        $countPattern = count($pattern);
        $match = true;
        
        if ($countPattern !== count($uri)) {
            $match = false;
        } else {
            for ($i = 0; $i < $countPattern; $i ++) {
                $currentPatternPart = $pattern[$i];
                $currentUriPart = $uri[$i];
                
                if ($currentPatternPart !== $currentUriPart) {
                    if ($currentPatternPart === self::MODULE_KEY) {
                        $moduleName = $currentUriPart;
                    } else 
                        if ($currentPatternPart === self::CONTROLLER_KEY) {
                            $controllerName = $currentUriPart;
                        } else 
                            if ($currentPatternPart === self::ACTION_KEY) {
                                $actionName = $currentUriPart;
                            } else {
                                $match = false;
                                break;
                            }
                }
            }
        }
        
        return $match ? new Route($moduleName, $controllerName, $actionName) : null;
    }

    private function check()
    {
        if (! ($this->moduleName !== null xor strpos($this->pattern, self::MODULE_KEY) !== false)) {
            throw new InvalidMatcherException('Missing module key or module value, or provided both');
        }
        if (! ($this->controllerName !== null xor strpos($this->pattern, self::CONTROLLER_KEY) !== false)) {
            throw new InvalidMatcherException('Missing controller key or controller value, or provided both');
        }
        if (! ($this->actionName !== null xor strpos($this->pattern, self::ACTION_KEY) !== false)) {
            throw new InvalidMatcherException('Missing action key or action value, or provided both');
        }
    }

    /**
     * Return an array containing the URI/pattern parts
     *
     * @param unknown $uri            
     */
    private function prepareForMatching($uri)
    {
        return array_values(array_filter(explode('/', $uri), function ($uriPart) {
            return ! empty($uriPart);
        }));
    }
}
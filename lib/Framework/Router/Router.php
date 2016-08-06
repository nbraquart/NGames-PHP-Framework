<?php
namespace Framework\Router;

class Router
{

    /**
     *
     * @var Matcher[]
     */
    private $matchers = array();

    /**
     * Initialize default matchers
     */
    public function __construct()
    {
    }

    /**
     * Adds a new matcher at the begining of the matcher list
     *
     * @param Matcher $matcher
     * @return Router
     */
    public function addMatcher(Matcher $matcher)
    {
        $this->matchers[] = $matcher;
        return $this;
    }

    /**
     *
     * @param String $uri            
     */
    public function getRoute($uri)
    {
        $result = null;
        
        foreach ($this->matchers as $matcher) {
            $matchedRoute = $matcher->match($uri);
            if ($matchedRoute !== null) {
                $result = $matchedRoute;
                break;
            }
        }
        
        return $result;
    }
}
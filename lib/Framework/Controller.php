<?php
namespace Framework;

use Framework\Utility\Inflector;

class Controller
{

    const CONTROLLER_NAMESPACE = 'Controller';

    const CONTROLLER_SUFFIX = 'Controller';

    const ACTION_SUFFIX = 'Action';

    /**
     *
     * @var View
     */
    protected $view;

    /**
     *
     * @var Request
     */
    protected $request;

    /**
     * Default constructor.
     * A view is created with default layout.
     */
    public function __construct()
    {
        $this->view = new View();
        $this->view->setLayout(View::DEFAULT_LAYOUT);
    }

    /**
     * Pre-execute.
     * Does nothing by default, but can be overriden by application.
     */
    protected function preExecute()
    {}

    /**
     * Post-execute.
     * Does nothing by default, but can be overriden by application.
     */
    protected function postExecute()
    {}

    /**
     * Sets the controller request.
     * Default view script is set at that time.
     *
     * @param Request $request            
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->view->setScriptFromRouter(new Router($this->request->getRequestUri()));
    }
    
    // / Status methods
    protected function ok($content = null)
    {
        return Response::createOkResponse($content);
    }

    protected function redirect($url)
    {
        return Response::createRedirectResponse($url);
    }

    protected function notFound($message = null)
    {
        return Response::createNotFoundResponse($message);
    }

    protected function badRequest($message = null)
    {
        return Response::createBadRequestResponse($message);
    }

    protected function internalError($message = null)
    {
        return Response::createInternalErrorResponse($message);
    }

    protected function forward($actionName, $controllerName = null, $moduleName = null)
    {
        // If module or controller not provided, use router to determine current ones and use it
        if ($moduleName == null || $controllerName == null) {
            $router = new Router($this->request->getRequestUri());
            
            if ($moduleName == null) {
                $moduleName = $router->getModuleName();
            }
            if ($controllerName == null) {
                $controllerName = $router->getControllerName();
            }
        }
        
        // Build a new request
        $request = clone $this->request;
        $request->setRequestUri('/' . $moduleName . '/' . $controllerName . '/' . $actionName);
        
        // Execute again for the forward
        return self::execute($request);
    }
    
    // / Util methods
    
    /**
     * Output a JSON
     *
     * @param mixed $json            
     * @param string $options            
     * @return \Framework\Response
     */
    protected function json($json, $options = JSON_PRETTY_PRINT)
    {
        $response = new Response();
        $response->setHeader('Content-Type', 'application/json; charset=utf-8');
        $response->setContent(json_encode($json, $options));
        
        return $response;
    }

    /**
     * Execute the provided request.
     *
     * @param Request $request            
     */
    public static function execute(Request $request)
    {
        // Get module, controller and action from the request using a router
        $router = new Router($request->getRequestUri());
        $moduleName = $router->getModuleName();
        $controllerName = $router->getControllerName();
        $actionName = $router->getActionName();
        
        // Build controller class name
        $controllerClassName = self::CONTROLLER_NAMESPACE . '\\';
        $controllerClassName .= ucfirst(Inflector::camelize(str_replace('-', '_', $moduleName))) . '\\';
        $controllerClassName .= ucfirst(Inflector::camelize(str_replace('-', '_', $controllerName)));
        $controllerClassName .= self::CONTROLLER_SUFFIX;
        
        // Build action method name
        $actionMethodName = Inflector::camelize(str_replace('-', '_', $actionName)) . self::ACTION_SUFFIX;
        
        // Handle not found (test if class is loadable, exists and method exists)
        if (! \Framework\Application::getInstance()->getAutoloader()->canLoadClass($controllerClassName) || ! class_exists($controllerClassName) || ! method_exists($controllerClassName, $actionMethodName)) {
            $message = 'Not found: ' . $controllerClassName . '::' . $actionMethodName . '()';
            \Framework\Logger::logWarning($message);
            
            return Response::createNotFoundResponse(\Framework\Application::getInstance()->isDebug() ? $message : null);
        }
        
        // Create the controller
        $controllerInstance = new $controllerClassName();
        $controllerInstance->setRequest($request);
        
        // Execute preExecute, action and postExecute. First not null return value is returned
        $methods = array(
            'preExecute',
            $actionMethodName,
            'postExecute'
        );
        $actionResult = null;
        
        foreach ($methods as $method) {
            $actionResult = $controllerInstance->$method();
            
            if ($actionResult !== null) {
                break;
            }
        }
        
        return $actionResult;
    }
}
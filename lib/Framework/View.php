<?php
namespace Framework;

/**
 * This class represents a view
 * 
 * @author Nicolas Braquart <nicolas.braquart@gmail.com>
 */
class View
{

    /**
     * Define the default layout when not explicitely set
     * 
     * @var string
     */
    const DEFAULT_LAYOUT = 'default';

    /**
     * Extension used for views.
     * Not changeable but could be
     * 
     * @var string
     */
    const VIEWS_EXTENSION = '.phtml';

    /**
     * Variable name storing placeholders.
     * If overriden by client application, an exception is thrown.
     * 
     * @var string
     */
    const VARIABLE_PLACEHOLDERS = '__PLACEHOLDERS__';

    /**
     * View script template to render
     * 
     * @var string
     */
    protected $script = null;

    /**
     * Directory into which templates are fetched
     * Defaults to ROOT/src/View
     * 
     * @var string
     */
    protected $directory = null;

    /**
     * A view which will be rendered with the content of the current view in $content variable
     * 
     * @var View
     */
    protected $parentView = null;

    /**
     * Store all the variables set by the user
     * 
     * @var array
     */
    protected $variables = array();

    /**
     * Stores the current placeholder for the two-phase placeholder definition.
     *
     * @var string
     */
    protected $currentPlaceHolder = null;

    public function __construct($script = null)
    {
        $this->script = $script;
        $this->directory = ROOT_DIR . '/src/views/';
        $this->variables = array(
            self::VARIABLE_PLACEHOLDERS => array()
        );
    }

    public function __set($name, $value)
    {
        if ($name == self::VARIABLE_PLACEHOLDERS) {
            throw new \Framework\Exception('Cannot used reserved variable ' . self::VARIABLE_PLACEHOLDERS);
        }
        
        // Add to the list of user variables
        $this->variables[$name] = $value;
    }

    public function __unset($name)
    {
        unset($this->variables[$name]);
    }

    public function getScript()
    {
        return $this->script;
    }

    public function setScript($script)
    {
        $this->script = $script;
        return $this;
    }

    public function setScriptFromRouter(Router $router)
    {
        $moduleName = $router->getModuleName();
        $controllerName = $router->getControllerName();
        $actionName = $router->getActionName();
        
        $this->script = $moduleName . '/' . $controllerName . '/' . $actionName;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;
        return $this;
    }

    public function getParentView()
    {
        return $this->parentView;
    }

    public function setParentView($parentView)
    {
        $this->parentView = $parentView;
        return $this;
    }

    /**
     * This function is used for both set/get placeholder.
     * Switch relies on $value being null or not.
     * In getter mode, non-existing values are returned as empty string.
     * 
     * @param unknown $name            
     * @param string $value            
     */
    public function placeholder($name, $value = null)
    {
        if ($value != null) {
            $this->variables[self::VARIABLE_PLACEHOLDERS][$name] = $value;
        } else {
            if (array_key_exists($name, $this->variables[self::VARIABLE_PLACEHOLDERS])) {
                return $this->variables[self::VARIABLE_PLACEHOLDERS][$name];
            } else {
                return '';
            }
        }
    }

    /**
     * Starts a placeholder.
     * Always use placeHolderStop() or an exception will be thrown.
     * 
     * @param string $name            
     */
    public function startPlaceHolder($name)
    {
        if ($this->currentPlaceHolder != null) {
            throw new \Framework\Exception('Cannot start a new placeholder, previous not stopped');
        }
        
        $this->currentPlaceHolder = $name;
        ob_start();
    }

    /**
     * End the placeholder.
     * Content is returned so that it can be rendered in place if needed.
     */
    public function stopPlaceHolder()
    {
        if ($this->currentPlaceHolder == null) {
            throw new \Framework\Exception('Cannot stop a new placeholder: none started');
        }
        
        $placeHolderContent = ob_get_contents();
        ob_end_clean();
        
        $this->variables[self::VARIABLE_PLACEHOLDERS][$this->currentPlaceHolder] = $placeHolderContent;
        $this->currentPlaceHolder = null;
        
        return $placeHolderContent;
    }

    /**
     * Helper function to set layout (actually parent view), from a string.
     * It also changes the script directory.
     * 
     * @param string $layout            
     * @return \Framework\View
     */
    public function setLayout($layout)
    {
        if ($layout != null) {
            $view = new View($layout);
            $view->setDirectory($view->directory . 'layouts/');
            $this->setParentView($view);
        } else {
            $this->disableLayout();
        }
        
        return $this;
    }

    public function disableLayout()
    {
        $this->setParentView(null);
    }

    public function render($script = null)
    {
        // Override the script?
        if ($script != null) {
            $this->setScript($script);
        }
        
        // Check the script path
        $scriptFullPath = $this->directory . $this->getScript() . self::VIEWS_EXTENSION;
        if (! is_readable($scriptFullPath)) {
            throw new Exception($scriptFullPath . ' not found');
        }
        
        // Put the variables in scope
        foreach ($this->variables as $variableName => $variableValue) {
            $$variableName = $variableValue;
        }
        
        // Render
        ob_start();
        try {
            include $scriptFullPath;
            
            // Check that after script rendering, a placeholder was not being defined
            if ($this->currentPlaceHolder != null) {
                throw new \Framework\Exception('Placeholder not stopped at the end of view rendering');
            }
        } catch (\Exception $e) {
            ob_end_clean();
            throw new \Exception('Exception caught during view rendering', 0, $e);
        }
        $renderContent = ob_get_contents();
        ob_end_clean();
        
        // Call parent if needed
        if ($this->parentView != null) {
            // Save parent variables
            $parentVariables = $this->parentView->variables;
            
            // Add child view variables to parent
            $this->parentView->variables = array_merge_recursive($this->variables, $parentVariables);
            
            // Set parent view 'content' variable
            $this->parentView->content = $renderContent;
            
            // Render parent
            $renderContent = $this->parentView->render();
            
            // Restore parent user variables
            $this->parentView->variables = $parentVariables;
        }
        
        return $renderContent;
    }
}
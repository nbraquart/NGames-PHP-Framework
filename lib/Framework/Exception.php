<?php
namespace Framework;

/**
 * Basic exception class only to define the namespace at framework level (for specific catches)
 */
class Exception extends \Exception
{
    /**
     * Enhanced trace printer for exceptions. Strongly inspired by http://php.net/manual/fr/exception.gettraceasstring.php#114980
     * @param \Exception $e
     * @param array $seen
     * @return string
     */
    public static function trace($e, array $seen = array())
    {
        $starter = $seen ? 'Caused by: ' : '';
        $result = array();
        $trace  = $e->getTrace();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage() == '' ? '[no message set]' : $e->getMessage());
        $file = $e->getFile();
        $line = $e->getLine();
        
        while (true) {
            $current = "$file:$line";
            
            // Stop if already displayed
            if (in_array($current, $seen)) {
                $result[] = sprintf('    ... %d more', count($trace)+1);
                break;
            }
                
            // Add the current formatted trace element
            if (count($trace) && array_key_exists('function', $trace[0])) {
                $args = array();
                
                if (count($trace) && array_key_exists('args', $trace[0]) && is_array($trace[0]['args'])) {
                    $args = array_map(function($arg) {
                        if (is_scalar($arg) || is_array($arg)) {
                            return preg_replace('/\s+/', ' ', str_replace(array("\n"), '', var_export($arg, true)));
                        } elseif (is_object($arg)) {
                            return get_class($arg);
                        } else {
                            return gettype($arg);
                        }
                    }, $trace[0]['args']);
                }

                // Build the function with args
                $function = array_key_exists('class', $trace[0]) ? $trace[0]['class'] . '::' : '';
                $function .= $trace[0]['function'];
                $function .= '(' . implode(', ', $args) . ')';
            } else {
                $function = '(main)';
            }

            $location = str_replace(ROOT_DIR, null, $file) . ($line === null ? '' : ':' . $line);
            $result[] = sprintf('    at %s (%s)', $function, $location);
            $seen[] = $current;

            // Reached the end
            if (!count($trace)) {
                break;
            }
            
            // Get the next trace element
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }
        
        $result = join("\n", $result);
        
        // Append previous exception trace
        if ($e->getPrevious()) {
            $result  .= "\n" . self::trace($e->getPrevious(), $seen);
        }
    
        return $result;
    }
}
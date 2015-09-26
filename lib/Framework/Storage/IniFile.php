<?php
namespace Framework\Storage;

use Framework\Exception;

class IniFile extends PhpArrayRecursive implements StorageInterface
{

    public function __construct($fileName)
    {
        if (! is_readable($fileName)) {
            throw new Exception($fileName . ' is not readable');
        }
        
        $parsedFile = parse_ini_file($fileName, true);
        $processedArray = array();
        
        if ($parsedFile !== false) {
            $processedArray = $this->processParsedFile($parsedFile);
        }
        
        parent::__construct($processedArray);
    }

    public static function writeFile($fileName, $configuration)
    {
        $content = '';
        
        foreach ($configuration as $key => $value) {
            $content .= $key . '=' . $value . "\n";
        }
        
        file_put_contents($fileName, $content);
    }

    protected function processParsedFile(array $array)
    {
        $result = array();
        
        foreach ($array as $key => $value) {
            $currentResult = &$result;
            
            if (is_int($key)) {
                $currentResult = &$currentResult[$key];
            } else {
                $keyPartArray = explode('.', $key);
                
                while ($keyPart = array_shift($keyPartArray)) {
                    $currentResult = &$currentResult[$keyPart];
                }
            }
            
            if (is_array($value)) {
                $currentResult = $this->processParsedFile($value);
            } else {
                if (strpos($value, '%') == - 1) {
                    $currentResult = $value;
                } else {
                    $currentResult = preg_replace_callback('/%(.*?)%/s', function ($match) {
                        if (getenv($match[1])) {
                            return getenv($match[1]);
                        } elseif (defined($match[1])) {
                            return constant($match[1]);
                        } else {
                            return $match[0];
                        }
                    }, $value);
                }
            }
        }
        
        return $result;
    }
}
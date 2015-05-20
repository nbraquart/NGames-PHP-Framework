<?php
namespace Framework\Utility;

class FileSystem
{
    public static function unlink($path)
    {
        if (is_file($path)) {
            unlink($path);
        } elseif (is_dir($path)) {
            foreach (scandir($path) as $subPath) {
                if ($subPath != '.' && $subPath != '..') {
                    self::unlink($path . '/' . $subPath);
                }
            }
            
            rmdir($path);
        }
    }
    
    public static function fwriteStream($fp, $string)
    {
        for ($written = 0; $written < strlen($string); $written += $fwrite) {
            $fwrite = fwrite($fp, substr($string, $written));
            if ($fwrite === false) {
                return $fwrite;
            }
        }
        
        return $written;
    }
}
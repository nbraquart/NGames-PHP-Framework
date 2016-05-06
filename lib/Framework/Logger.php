<?php
namespace Framework;

class Logger
{

    const LEVEL_DEBUG = 0;

    const LEVEL_INFO = 1;

    const LEVEL_WARNING = 3;

    const LEVEL_ERROR = 4;

    public static $destination = null;

    public static $minLevel = null;

    private static $file = null;
    
    public static function initialize($destination, $minLevel)
    {
        self::$minLevel = $minLevel;
        self::setDestination($destination);
    }

    public static function setDestination($destination)
    {
        self::$destination = $destination;

        if (!self::$file = fopen(self::$destination, "a")) {
            throw new \Exception('Cannot open log file for writing');
        }
    }

    public static function logDebug($message)
    {
        self::log(self::LEVEL_DEBUG, $message, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);
    }

    public static function logError($message)
    {
        self::log(self::LEVEL_ERROR, $message, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);
    }

    public static function logWarning($message)
    {
        self::log(self::LEVEL_WARNING, $message, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);
    }

    public static function logInfo($message)
    {
        self::log(self::LEVEL_INFO, $message, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);
    }

    public static function log($level, $message, $trace = null)
    {
        if (self::$destination != null && self::$minLevel <= $level) {
            if ($trace == null) {
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
            }
            $logLine = self::assembleLogLine($level, $message, $trace);
            \Framework\Utility\FileSystem::fwriteStream(self::$file, $logLine);
        }
    }

    protected static function assembleLogLine($level, $message, $trace)
    {
        $levelString = null;
        
        switch ($level) {
            case self::LEVEL_DEBUG:
                $levelString = 'DEBUG';
                break;
            case self::LEVEL_INFO:
                $levelString = 'INFO ';
                break;
            case self::LEVEL_WARNING:
                $levelString = 'WARN ';
                break;
            case self::LEVEL_ERROR:
                $levelString = 'ERROR';
                break;
        }
        
        $time = microtime(true);
        $dateTimeString = date('Y-m-d H:i:s');
        $dateTimeString .= ',' . sprintf("%06d", ($time - floor($time)) * 1000000);
        
        $lineNumber = $trace['line'];
        $fileName = str_replace(ROOT_DIR, '', $trace['file']);
        
        return $dateTimeString . ' [' . $levelString . '] ' . $fileName . ':' . $lineNumber . ' - ' . $message . "\n";
    }
}
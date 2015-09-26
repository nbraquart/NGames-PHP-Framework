<?php
namespace Framework\Storage;

class PhpSession extends PhpArray implements StorageInterface
{

    protected $storage;

    protected static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    /**
     * Should not be public but PHP does not allow it
     */
    public function __construct()
    {
        $this->initializePhpSession();
        $this->storage = $_SESSION;
    }

    public function reset()
    {
        session_destroy();
        $_SESSION = $this->storage = array();
        $this->initializePhpSession();
    }

    public function __destruct()
    {
        $_SESSION = $this->storage;
        session_write_close();
    }

    protected function initializePhpSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}
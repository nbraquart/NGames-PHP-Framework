<?php

namespace Ngames\Framework;

use Doctrine\Common\ClassLoader;

class Autoloader
{
    protected $classLoaders = [];

    public function register()
    {
        $namespaces = [
            'Controller' => ROOT_DIR.'/src',
        ];

        foreach ($namespaces as $namespace => $includePath) {
            $classLoader = new ClassLoader($namespace, $includePath);
            $classLoader->register();
            $this->classLoaders[] = $classLoader;
        }
    }

    public function canLoadClass($className)
    {
        foreach ($this->classLoaders as $classLoader) {
            if ($classLoader->canLoadClass($className)) {
                return true;
            }
        }

        return false;
    }
}

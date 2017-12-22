# NGames Framework - A simple PHP framework
[![Build Status](https://travis-ci.org/nbraquart/ngames-framework.svg?branch=master)](https://travis-ci.org/nbraquart/ngames-framework)
[![License](https://poser.pugx.org/ngames/framework/license.png)](https://packagist.org/packages/ngames/framework)
[![Latest Stable Version](https://poser.pugx.org/ngames/framework/v/stable)](https://packagist.org/packages/ngames/framework)
[![Latest Unstable Version](https://poser.pugx.org/ngames/framework/v/unstable)](https://packagist.org/packages/ngames/framework)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nbraquart/ngames-framework/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nbraquart/ngames-framework/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/nbraquart/ngames-framework/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/nbraquart/ngames-framework/?branch=master)

## Installation
Use composer to install the application: `composer require ngames/framework`. Or you can update the `composer.json` file by adding the following line in the `require` object:

```json
{
    "require": {
        "ngames/framework": "*"
    }
}
```

## Usage
### Configuration file
You need to write a configuration file using INI format. Typically, this file is called `config.ini` and is located in the `config` folder at the root of the project. Here is an example with all supported configuration keys:

```ini
# Database configuration
database.host       = "127.0.0.1"
database.username   = "db_user"
database.password   = "db_password"
database.name       = "db_name"

# Log configuration (destination must be a file), the level is the minimum level for logging messages
log.destination     = "./logs/application.log"
log.level           = "debug"

# Whether the debug mode is enabled or not (default is false)
debug               = "true"
```

### Application initialization
In a `public` folder in the root of the project, create a file named `index.php` with the following content:

```php
<?php
defined("ROOT_DIR") || define ("ROOT_DIR", dirname(__DIR__));
chdir(ROOT_DIR);

// Initialize and run the application
require_once ROOT_DIR . '/vendor/autoload.php';
\Ngames\Framework\Application::initialize(ROOT_DIR . '/config/config.ini')->run();
```

That's all you need to startup the application.

### Controllers
Controllers are located in a `src` folder in the root of the project. They are grouped by folders representing the modules. The default module is `Application`.

TODO document

### Router
TODO document
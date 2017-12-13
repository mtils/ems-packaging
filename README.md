# A helper lib for local laravel development
This is a small helper package to help you while developing laravel applications and packages.

## Install
### 1. Add this to your composer.json:
```json
{
    "require-dev": {
        "ems/packaging" : "0.1.*"
    }
}
```
### 2. Then on Laravel in your app.php on the top:

```php

use Packaging\Environment;

$usePackaging = Environment::init();

```
It returns true if it found a configuration.

### 3. Let the environment configure your application

```php
<?php

Environment::configure($app);

```
In this step it just adds some Development ServiceProviders (if configured).

### 4. Add a develop configuration file (config/develop.php) with the following content:
```php
<?php

return [

    'providers' => [
        'Barryvdh\Debugbar\ServiceProvider'
    ]
    ,
    'package-overwrites' => [
        'psr-0' => [
            'Collection'    => "/home/youruser/development/github/mypackage/src"
        ]
    ]

];
```
This is just a sample. Under "providers" you can add any custom service providers for development. The part under "package-overwrites" is to load your custom packages from anywhere on your hdd without changing your composer.json.
Package overloading is realized with a simple autoloader which will prepended to the spl autoload stack.

If no develop.php file is found it will exist and does nothing.


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
### 2. Then on Laravel 5 add this to your bootstrap/app.php:

```php
$env = $app->detectEnvironment(function()
{
    return getenv('APP_ENV') ?: 'production';
});

$providerBootstrap = 'Illuminate\Foundation\Bootstrap\RegisterProviders';

$app->afterBootstrapping($providerBootstrap, function($app){

    if ($app->isLocal() && class_exists('Packaging\DevServiceProvider')) {
        $app->register('Packaging\DevServiceProvider');
    }

});
```
### 3. Add a develop configuration file (config/develop.php) with the following content:
```php
<?php

$basePath = '/home/michi/Dokumente/IT/Kdevelop/web-utils-libs/';

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

The DevServiceProvider will exit if the environment is not local and no config.develop configuration was found.


<?php namespace Packaging;

use Log;

use Illuminate\Support\ServiceProvider;

/**
 * This class looks for configurations in config/develop.php
 * Currently it can load custom providers and overwrite package
 * paths via an autoloader
 **/
class DevServiceProvider extends ServiceProvider
{

    /**
     * Register all overwrites
     *
     * @return void
     **/
    public function register()
    {

        if (!$this->checkLocal()) {
            return;
        }

        if (!$this->checkInstallation()) {
            return;
        }

        $this->registerPackageAutoloader();

        $this->registerDevServiceProviders();

    }

    /**
     * Register any package path overwrites to replace packages with your own
     *
     * @return void
     **/
    protected function registerPackageAutoloader()
    {
        if (!$paths = $this->app['config']['develop']['package-overwrites']['psr-0']) {
            return;
        }

        $loader = $this->app->make('Packaging\AutoLoader');
        $loader->addNamespaces($paths);
        $loader->register();

    }

    /**
     * Register any service providers only for development
     *
     * @return void
     **/
    protected function registerDevServiceProviders()
    {
        if (!$providers = $this->app['config']['develop']['providers']) {
            return;
        }

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * Checks if the environment is local
     *
     * @return bool
     **/
    protected function checkLocal()
    {
        if ($this->app->isLocal()) {
            return true;
        }
        Log::warning('DevServiceProvider should only be used in local environments. Disabling');
        return false;
    }

    /**
     * Checks if a package config exists
     *
     * @return bool
     **/
    protected function checkInstallation()
    {

        $config = $this->app['config']['develop'];

        if (is_array($config) && count($config)) {
            return true;
        }

        Log::warning('DevServiceProvider couldnt find config config/develop.php. Disabling');
        return false;

    }
}
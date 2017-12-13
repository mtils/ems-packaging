<?php
/**
 *  * Created by mtils on 13.12.17 at 05:25.
 **/

namespace Packaging;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use function realpath;
use RuntimeException;

/**
 * Class Environment
 *
 * This is a helper to set up some redirected paths in your development
 * environment. You can just create a develop.php file and redirect the
 * paths to any packages somewhere on your disk.
 * Immediately after the init of composer you should call Enviroment::init().
 * After this you can call Environment::configure($app) to load some development-only
 * ServiceProviders.
 *
 * @package Packaging
 */
class Environment
{
    /**
     * @var string
     */
    protected static $configPath = '';

    /**
     * @var array
     **/
    protected static $config;

    /**
     * @var bool
     **/
    protected static $didInit = false;

    /**
     * @var bool
     **/
    protected static $active = false;

    /**
     * Initialize the packaging environment. It returns true if the env should
     * be active.
     *
     * @param string $configPath (optional)
     *
     * @return bool
     **/
    public static function init($configPath='')
    {
        static::$configPath = $configPath;

        if (!static::isEnabled()) {
            static::$didInit = true;
            return false;
        }

        $config = static::config();

        if ($paths = static::getPsr0Paths($config)) {
            $loader = new AutoLoader();
            $loader->addNamespaces($paths);
            $loader->register();
            static::$active = true;
        }

        static::$didInit = true;

        return true;
    }

    /**
     * Configure the laravel application. (For custom ServiceProviders)
     * Returns true if this class did register custom package paths or custom
     * providers.
     *
     * @param Application $app
     *
     * @return bool
     */
    public static function configure(Application $app)
    {
        if (!static::$didInit) {
            throw new RuntimeException('Packaging Env: You have to call init() before configuring Application.');
        }

        if (!static::isEnabled()) {
            return false;
        }

        $config = static::config();

        $app->beforeBootstrapping(RegisterProviders::class, function (Application $app) use ($config) {
            foreach (static::getDevServiceProviders($config) as $provider) {
                $app->register($provider);
                static::$active = true;
            }
        });

        return static::$active;

    }

    /**
     * Check if the environment was configured by this class. At the moment this
     * means if custom package paths were assigned or custom ServiceProviders
     * were registered
     *
     * @return bool
     **/
    public static function isActive()
    {
        return static::$active;
    }

    /**
     * Check if the develop environment is enabled. This does not mean
     * it is active (configure() was called).
     *
     * If the develop.php file was found it is considered as enabled
     * unless you put a key into the config array "enable" and set
     * it to anything casted to false.
     *
     * If you want to know that it did something use self::isActive().
     *
     * @return bool
     **/
    public static function isEnabled()
    {
        if (!$config = static::config()) {
            return false;
        }

        if (!array_key_exists('enabled', $config)) {
            return true;
        }

        return (bool)$config['enabled'];
    }

    /**
     * Return the develop environment configuration
     *
     * @return array
     **/
    public static function config()
    {
        if (static::$config !== null) {
            return static::$config;
        }

        if (!$configPath = static::configPath()) {
            static::$config = [];
            return static::$config;
        }

        static::$config = include($configPath);

        return static::$config;
    }

    /**
     * Return all psr-0 custom paths from config.
     *
     * @param array $config
     *
     * @return array
     */
    protected static function getPsr0Paths(array $config)
    {
        if (!isset($config['package-overwrites'])) {
            return [];
        }

        if (!isset($config['package-overwrites']['psr-0'])) {
            return [];
        }

        return $config['package-overwrites']['psr-0'];
    }

    /**
     * Return all custom development service providers that should be loaded.
     *
     * @param array $config
     *
     * @return array
     */
    protected static function getDevServiceProviders(array $config)
    {
        return isset($config['providers']) ? $config['providers'] : [];
    }

    /**
     * Return or try to guess the develop.php file.
     *
     * @return string
     */
    protected static function configPath()
    {
        if (static::$configPath) {
            return static::$configPath;
        }

        $configPath = realpath(__DIR__.'/../../../../../config/develop.php');

        static::$configPath = $configPath ? $configPath : '';

        return static::$configPath;
    }
}

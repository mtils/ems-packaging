<?php namespace Packaging;;

/**
 * This is a simple loader. Add a directory and it will load th class. It is
 * mainly for overriding your composer entries with local ones. This autoloader
 * will prepend itself to the stack and be asked before composer is asked.
 *
 **/
class AutoLoader
{

    /**
     * @var array
     **/
    protected $ns2Dir = [];

    /**
     * @var bool
     **/
    private $registered = false;

    public function __construct(array $ns2Dir = [])
    {
        $this->ns2Dir = $ns2Dir;
    }

    /**
     * Add a namespace and directory to autoload containing classes
     *
     * @param string $namespace The Namespace (MyPackackage)
     * @param string $directory
     **/
    public function addNamespace($namespace, $directory)
    {
        $this->ns2Dir[$this->normalizeNamespace($namespace)] =
            $this->normalizeDirectory($directory);
    }

    /**
     * Add multiple namespaces by array
     *
     * @param array $namespaces
     * @return void
     **/
    public function addNamespaces(array $namespaces)
    {
        foreach ($namespaces as $namespace=>$dir) {
            $this->addNamespace($namespace, $dir);
        }
    }


    /**
     * The actual autoload method
     *
     * @param string $class
     * @return bool
     **/
    public function autoload($class)
    {
        if ($file = $this->resolveFile($class)) {
            if (file_exists($file)) {
                return include($file);
            }
        }
    }

    /**
     * A separate method for finding file of class $class. This is separated for
     * testing concerns
     *
     * @param string $class
     * @return string
     **/
    public function resolveFile($class)
    {
        $class = $this->normalizeNamespace($class);

        if (!$file = $this->findFileOfClass($class)) {
            return '';
        }

        return $file;

    }

    /**
     * Registers this autoloader and prepends it to the spl stack
     *
     * @return void
     **/
    public function register()
    {
        if ($this->registered) {
            return;
        }
        $this->registerBefore();
        $this->registered = true;
    }

    /**
     * Registeres the loader on top of the previous loaders
     *
     * @return void
     **/
    protected function registerBefore()
    {
        spl_autoload_register([$this, 'autoload'], true, true);
    }

    /**
     * PSR-0 Implementation of class loading
     *
     * @param string $class
     * @return string
     **/
    protected function findFileOfClass($class)
    {
        foreach ($this->ns2Dir as $ns=>$dir) {

            if (strpos($class, $ns) !== 0) {
                continue;
            }

            $trimmedClass = trim(substr($class, strlen($ns)),'\\');
            $nsParts = explode('\\', $ns);
            $last = $nsParts[count($nsParts)-1];
            return $this->classToFilename($trimmedClass, "$dir$last/");

        }
    }

    /**
     * Transform the class name to a filename
     *
     * @param  string $class
     * @param  string $directory
     * @return string
     */
    protected function classToFilename($class, $directory)
    {

        $matches = [];
        preg_match('/(?P<namespace>.+\\\)?(?P<class>[^\\\]+$)/', $class, $matches);
        $class     = (isset($matches['class'])) ? $matches['class'] : '';
        $namespace = (isset($matches['namespace'])) ? $matches['namespace'] : '';
        return $directory
             . str_replace('\\', '/', $namespace)
             . str_replace('_', '/', $class)
             . '.php';
    }

    /**
     * Ensures a namespace without leading or trailing slashes
     *
     * @param string $namespace
     * @return string
     **/
    protected function normalizeNamespace($namespace)
    {
        return trim($namespace, '\\');
    }

    /**
     * Ensures a directory without leading or trailing slashes
     *
     * @param string $directory
     * @return string
     **/
    protected function normalizeDirectory($directory)
    {
        $sep = DIRECTORY_SEPARATOR;
        return $sep . trim(realpath($directory),$sep) . $sep;
    }

}
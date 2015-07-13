<?php
namespace Leadtech\Boot;

use Leadtech\Boot\XmlApplicationContext;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class ApplicationContextBuilder
 * @package Leadtech\Core\DependencyInjection
 * @author  Daan Biesterbos <daan@leadtech.nl>
 * @license http://www.wtfpl.net/
 */

class Builder
{
    /** @var string */
    protected $appName = 'default';

    /** @var bool  */
    protected $useCache = false;

    /** @var  string */
    protected $cacheDir = null;

    /** @var  string */
    protected $projectDir = null;

    /** @var array  */
    protected $paths = [];

    /** @var string  */
    protected $environment = Boot::PRODUCTION;

    /** @var array  */
    protected $parameters = [];

    /** @var CompilerPassInterface[] */
    protected $compilerPasses = [];

    /**
     * @param $projectDir
     */
    public function __construct($projectDir)
    {
        if (!is_dir($projectDir)) {
            throw new \InvalidArgumentException("Cache directory `$projectDir` does not exist.");
        }

        $this->projectDir = realpath($projectDir);
    }

    /**
     * @return ContainerInterface
     */
    public function build()
    {
        $ctx = new XmlApplicationContext($this->appName);
        return $ctx
            ->setCacheDir($this->cacheDir)
            ->setDirectories($this->getRealPaths())
            ->setEnvironment($this->environment)
            ->setCompilerPasses($this->compilerPasses)
            ->bootstrap(
                $this->parameters,
                $this->useCache and $this->cacheDir
            )
        ;
    }

    /**
     * @param $name
     * @return $this
     */
    public function appName($name)
    {
        if (!ctype_alnum($name)) {
            throw new \InvalidArgumentException("The appname must be alphanumeric. Only letters and digits are allowed.");
        }
        $this->appName = $name;

        return $this;
    }

    /**
     * @param $env
     * @return $this
     */
    public function environment($env)
    {
        $this->environment = $env;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function path($path)
    {
        $this->paths[] = $path;

        return $this;
    }

    /**
     * @param array $paths
     *
     * @return $this
     */
    public function paths(array $paths)
    {
        $this->paths = array_merge($paths, $this->paths);

        return $this;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function parameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * @param string $cacheDir
     * @param bool $useCache
     *
     * @return $this
     */
    public function caching($cacheDir, $useCache = true)
    {
        if (substr($cacheDir, 0, 1) != DIRECTORY_SEPARATOR) {
            $cacheDir = $this->projectDir . DIRECTORY_SEPARATOR . $cacheDir;
        }
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir);
        }

        $this->cacheDir = $cacheDir;
        $this->useCache = $useCache;

        return $this;
    }

    /**
     * @param CompilerPassInterface $compilerPass
     * @return $this
     */
    public function afterRemoving(CompilerPassInterface $compilerPass)
    {
        $this->compilerPasses[] = [$compilerPass, PassConfig::TYPE_AFTER_REMOVING];

        return $this;
    }

    /**
     * @param CompilerPassInterface $compilerPass
     * @return $this
     */
    public function beforeRemoving(CompilerPassInterface $compilerPass)
    {
        $this->compilerPasses[] = [$compilerPass, PassConfig::TYPE_AFTER_REMOVING];

        return $this;
    }

    /**
     * @param CompilerPassInterface $compilerPass
     * @return $this
     */
    public function beforeOptimization(CompilerPassInterface $compilerPass)
    {
        $this->compilerPasses[] = [$compilerPass, PassConfig::TYPE_BEFORE_OPTIMIZATION];

        return $this;
    }

    /**
     * @param CompilerPassInterface $compilerPass
     * @return $this
     */
    public function onOptimization(CompilerPassInterface $compilerPass)
    {
        $this->compilerPasses[] = [$compilerPass, PassConfig::TYPE_OPTIMIZE];

        return $this;
    }

    /**
     * @param CompilerPassInterface $compilerPass
     * @return $this
     */
    public function onRemoving(CompilerPassInterface $compilerPass)
    {
        $this->compilerPasses[] = [$compilerPass, PassConfig::TYPE_REMOVE];

        return $this;
    }

    /**
     * @return string[]
     */
    protected function getRealPaths()
    {
        $directories = [];
        $rootDir = $this->projectDir ?: getcwd();
        foreach ($this->paths as $path) {

            // Check if this path is absolute
            if(is_dir($path)) {
                $directories[] = $path;
                continue;
            }

            // Prepend the root directory
            if (substr($path, 0, 1) !== DIRECTORY_SEPARATOR) {

                // Create full path
                $realpath = realpath($rootDir . DIRECTORY_SEPARATOR . $path);

                // Check if the realpath is valid, if so use this path.
                if(!empty($realpath)) {

                    // Path is valid!
                    $directories[] = $realpath;
                    continue;

                }

                // Path not found
                throw new \InvalidArgumentException("Unable to resolve path to directory '{$path}'.");
            }

        }

        return $directories;
    }
}

<?php

namespace Boot;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ExpressionLanguageProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ApplicationContextBuilder.
 *
 * @author  Daan Biesterbos <daan@leadtech.nl>
 * @license http://www.wtfpl.net/
 */
class Builder
{
    /** @var string */
    protected $appName = 'default';

    /** @var bool  */
    protected $cacheEnabled = false;

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

    /** @var array  */
    protected $expressionProviders = [];

    /** @var InitializerInterface[] */
    protected $initializers = [];

    /** @var EventDispatcher */
    protected $eventDispatcher = null;

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
        // Initialize
        foreach ($this->getInitializers() as $initializer) {
            $initializer->initialize($this);
        }

        // Boot application
        $ctx = new ApplicationContext($this->appName);

        return $ctx
            ->setCacheDir($this->cacheDir)
            ->setDirectories($this->getRealPaths())
            ->setEnvironment($this->environment)
            ->setCompilerPasses($this->compilerPasses)
            ->bootstrap(
                $this->parameters,
                $this->cacheEnabled and $this->cacheDir,
                $this->initializers,
                $this->expressionProviders
            )
        ;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function appName($name)
    {
        if (!ctype_alnum($name)) {
            throw new \InvalidArgumentException('The appname must be alphanumeric. Only letters and digits are allowed.');
        }
        $this->appName = $name;

        return $this;
    }

    /**
     * @param InitializerInterface $initializer
     *
     * @return $this
     */
    public function initializer(InitializerInterface $initializer)
    {
        $this->initializers[] = $initializer;

        return $this;
    }

    /**
     * @param ExpressionLanguageProvider $provider
     *
     * @return $this
     */
    public function expr(ExpressionLanguageProvider $provider)
    {
        $this->expressionProviders[] = $provider;

        return $this;
    }

    /**
     * @param $env
     *
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
    public function configDir($path)
    {
        $this->paths[] = $path;

        return $this;
    }

    /**
     * @param array $paths
     *
     * @return $this
     */
    public function configDirs(array $paths)
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
     * @param bool   $useCache
     *
     * @return $this
     */
    public function caching($cacheDir, $useCache = true)
    {
        if (substr($cacheDir, 0, 1) != DIRECTORY_SEPARATOR) {
            $cacheDir = $this->projectDir.DIRECTORY_SEPARATOR.$cacheDir;
        }
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir);
        }

        $this->cacheDir = $cacheDir;
        $this->cacheEnabled = $useCache;

        return $this;
    }

    /**
     * @param CompilerPassInterface $compilerPass
     *
     * @return $this
     */
    public function afterRemoving(CompilerPassInterface $compilerPass)
    {
        $this->compilerPasses[] = [$compilerPass, PassConfig::TYPE_AFTER_REMOVING];

        return $this;
    }

    /**
     * @param CompilerPassInterface $compilerPass
     *
     * @return $this
     */
    public function beforeRemoving(CompilerPassInterface $compilerPass)
    {
        $this->compilerPasses[] = [$compilerPass, PassConfig::TYPE_AFTER_REMOVING];

        return $this;
    }

    /**
     * @param CompilerPassInterface $compilerPass
     *
     * @return $this
     */
    public function beforeOptimization(CompilerPassInterface $compilerPass)
    {
        $this->compilerPasses[] = [$compilerPass, PassConfig::TYPE_BEFORE_OPTIMIZATION];

        return $this;
    }

    /**
     * @param CompilerPassInterface $compilerPass
     *
     * @return $this
     */
    public function onOptimization(CompilerPassInterface $compilerPass)
    {
        $this->compilerPasses[] = [$compilerPass, PassConfig::TYPE_OPTIMIZE];

        return $this;
    }

    /**
     * @param CompilerPassInterface $compilerPass
     *
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
            if (is_dir($path)) {
                $directories[] = $path;
                continue;
            }

            // Prepend the root directory
            if (substr($path, 0, 1) !== DIRECTORY_SEPARATOR) {

                // Create full path
                $realpath = realpath($rootDir.DIRECTORY_SEPARATOR.$path);

                // Check if the realpath is valid, if so use this path.
                if (!empty($realpath)) {

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

    /**
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @return string
     */
    public function getProjectDir()
    {
        return $this->projectDir;
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getExpressionProviders()
    {
        return $this->expressionProviders;
    }

    /**
     * @return InitializerInterface[]
     */
    public function getInitializers()
    {
        return $this->initializers;
    }

    /**
     * Lazy load a event dispatcher if needed.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        if ($this->eventDispatcher) {
            $this->eventDispatcher = new EventDispatcher();
        }

        return $this->eventDispatcher;
    }
}

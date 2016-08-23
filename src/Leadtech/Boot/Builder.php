<?php

namespace Boot;

use Boot\Exception\BootstrapException;
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
 */
class Builder
{
    /** @var string */
    protected $appName = 'default';

    /** @var  string */
    protected $compiledClassDir = null;

    /** @var  string */
    protected $projectDir = null;

    /** @var array  */
    protected $configDirs = [];

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

    protected $bootstrapModules = [];

    /**
     * @param $projectDir
     */
    public function __construct($projectDir)
    {
        if (!is_dir($projectDir)) {
            throw new \InvalidArgumentException(
                "Invalid project directory. The directory `$projectDir` does not exist."
            );
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

        // Create the application context
        return $this->createApplicationContext()

            // Configurations
            ->setCompiledClassDir($this->getCompiledClassDir())
            ->setDirectories($this->getRealPaths())
            ->setEnvironment($this->getEnvironment())
            ->setCompilerPasses($this->getCompilerPasses())

            // Bootstrap the application
            ->bootstrap(
                $this->getParameters(),
                $this->isOptimized(),
                $this->getInitializers(),
                $this->getExpressionProviders()
            )
        ;
    }

    /**
     * Creates instance of the application context.
     *
     * @return ApplicationContext
     */
    protected function createApplicationContext()
    {
        return new ApplicationContext($this->getAppName());
    }

    /**
     * @param string $name
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
        $this->configDirs[] = $path;

        return $this;
    }

    /**
     * @param array $paths
     *
     * @return $this
     */
    public function configDirs(array $paths)
    {
        $this->configDirs = array_merge($paths, $this->configDirs);

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
     * @param string $directory
     *
     * @return $this
     */
    public function optimize($directory)
    {
        // Determine if the given directory is relative or absolute.
        // If the path is relative do prepend the project dir.
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (!preg_match('/^[A-Z]:.*$/', $directory)) {
                $directory = $this->getProjectDir().DIRECTORY_SEPARATOR.$directory;
            }
        } elseif (substr($directory, 0, 1) != DIRECTORY_SEPARATOR) {
            $directory = $this->getProjectDir().DIRECTORY_SEPARATOR.$directory;
        }

        $dirExists = is_dir($directory);
        if (!$dirExists) {
            $dirExists = mkdir($directory, 0777, true);
        }

        if (!$dirExists) {
            new BootstrapException('Could not start the application. Could not ');
        }

        $this->compiledClassDir = $directory;

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
        foreach ($this->getConfigDirs() as $path) {

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
    public function getCompiledClassDir()
    {
        return $this->compiledClassDir;
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
    public function getConfigDirs()
    {
        return $this->configDirs;
    }

    /**
     * @return bool
     */
    public function isOptimized()
    {
        return  $this->environment === Boot::PRODUCTION && !empty($this->compiledClassDir);
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

    /**
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses()
    {
        return $this->compilerPasses;
    }
}

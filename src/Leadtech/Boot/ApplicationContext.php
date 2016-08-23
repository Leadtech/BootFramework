<?php

namespace Boot;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\ExpressionLanguageProvider;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class ApplicationContext.
 *
 * @author  Daan Biesterbos <daan@leadtech.nl>
 */
class ApplicationContext
{
    /** @var  ContainerInterface|ContainerBuilder */
    protected $serviceContainer;

    /** @var  string */
    protected $environment;

    /** @var  string */
    protected $compiledClassDir;

    /** @var array  */
    protected $directories = [];

    /** @var string  */
    protected $appName;

    /** @var array  */
    protected $compilerPasses = [];

    /** @var  string */
    protected $projectDir;

    /**
     * @param string $appName
     * @param string $environment
     */
    public function __construct($appName = 'default', $environment = Boot::PRODUCTION)
    {
        $this->appName = $appName;
        $this->environment = $environment;
    }

    /**
     * @param array                        $parameters    Parameters to register to the service container. Converted to uppercase snake case format.
     * @param bool                         $isOptimized
     * @param InitializerInterface[]       $initializers
     * @param ExpressionLanguageProvider[] $exprProviders
     *
     * @return ContainerInterface
     */
    public function bootstrap(array $parameters = null, $isOptimized = true, array $initializers = [], array $exprProviders = null)
    {
        // Get compiled context class
        $compiledClass = $this->getCompiledClassName();

        // Load the services if optimization is not enabled.
        if (!$isOptimized) {

            // Load services
            $this->loadServices($parameters, $initializers, $exprProviders);

            return $this->serviceContainer;
        }

        // Determine the path to the compiled service container
        $classPath = $this->getCompiledClassDir().DIRECTORY_SEPARATOR.$this->getCompiledClassName().'.php';

        // Create cache object
        $configCache = new ConfigCache($classPath, $this->environment != Boot::PRODUCTION);

        // Load the services if the cached version is dirty
        if (!$configCache->isFresh()) {

            // Build container
            $this->serviceContainer = new ContainerBuilder();

            // Load the services
            $this->loadServices($parameters, $initializers, $exprProviders);

            // Generates an optimized class that reflects the final state of the service container.
            $dumper = new PhpDumper($this->serviceContainer);
            $configCache->write(
                $dumper->dump(['class' => $compiledClass]),
                $this->serviceContainer->getResources()
            );
        } else {

            // Load compiled class
            require_once $classPath;
            $this->serviceContainer = new $compiledClass();

            foreach ($initializers as $initializer) {
                $initializer->bootstrap($this->serviceContainer);
            }
        }

        return $this->serviceContainer;
    }

    /**
     * @param array                        $parameters    Parameters to register to the service container. Converted to uppercase snake case format.
     * @param InitializerInterface[]       $initializers
     * @param ExpressionLanguageProvider[] $exprProviders
     */
    protected function loadServices(array $parameters = null, array $initializers = [], array $exprProviders = null)
    {
        // Build container
        $this->serviceContainer = new ContainerBuilder();

        // Expression providers
        //$this->serviceContainer->addExpressionLanguageProvider(new ExpressionLanguageProvider());
        if ($exprProviders) {
            foreach ($exprProviders as $expressionProvider) {
                $this->serviceContainer->addExpressionLanguageProvider($expressionProvider);
            }
        }

        // Bootstrap initializers
        foreach ($initializers as $initializer) {
            $initializer->bootstrap($this->serviceContainer);
        }

        // Add compiler passes
        foreach ($this->compilerPasses as list($pass, $type)) {
            if ($pass instanceof CompilerPassInterface) {
                $this->serviceContainer->addCompilerPass($pass, $type);
            }
        }

        // Set parameters. Set this parameter prior to loading the context files.
        // We should be able to override the parameters from the service container.
        if ($parameters) {
            foreach ($parameters as $name => $value) {
                $param = preg_replace('/[^a-z0-9_.:@]+/', '', $name);
                $this->serviceContainer->setParameter($param, $value);
            }
        }

        // Load configurations
        foreach ($this->directories as $directory) {
            $this->loadConfiguration($directory);
        }

        $this->serviceContainer->compile();
    }

    /**
     * Load configuration from module resource folder.
     *
     * @param string $resourceDir
     */
    public function loadConfiguration($resourceDir)
    {
        if (!empty($resourceDir)) {

            // Create xml loader
            $loader = new XmlFileLoader($this->serviceContainer, new FileLocator($resourceDir));

            // Load parameters.xml first to ensure the parameters are available
            $filepath = realpath($resourceDir).'/parameters.xml';
            if (file_exists($filepath)) {
                $loader->load('parameters.xml');
            }

            // Load configurations from current directory
            foreach (new \DirectoryIterator($resourceDir) as $file) {
                /** @var SplFileInfo $file */
                if ($file->getFilename() != 'parameters.xml') {
                    if ($file->isFile() && strtolower($file->getExtension()) == 'xml') {
                        $loader->load($file->getFilename());
                    }
                }
            }

            // Check if there are environment configurations available
            // Look for environment settings e.g. `prod`, `dev`, or `test`.
            $targetDirEnv = $resourceDir.DIRECTORY_SEPARATOR.$this->getEnvironment();
            if (is_dir($targetDirEnv)) {
                $this->loadConfiguration($targetDirEnv);
            }
        }
    }

    /**
     * @return string prod|dev|test
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param $environment
     *
     * @return $this
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getCompiledClassDir()
    {
        return $this->compiledClassDir;
    }

    /**
     * @param string $compiledClassDir
     *
     * @return $this
     */
    public function setCompiledClassDir($compiledClassDir)
    {
        $this->compiledClassDir = $compiledClassDir;

        return $this;
    }

    /**
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    /**
     * @param array $directories
     *
     * @return $this
     */
    public function setDirectories($directories)
    {
        $this->directories = $directories;

        return $this;
    }

    /**
     * @return array
     */
    public function getCompilerPasses()
    {
        return $this->compilerPasses;
    }

    /**
     * @param array $compilerPasses [compilerPass,type][]
     *
     * @return $this
     */
    public function setCompilerPasses(array $compilerPasses)
    {
        $this->compilerPasses = $compilerPasses;

        return $this;
    }

    /**
     * @return string
     */
    public function getCompiledClassName()
    {
        return strtr('Compiled{app}{env}', [
            '{app}' => $this->camelCase(ucfirst($this->appName ?: 'default')),
            '{env}' => $this->camelCase(strtolower($this->environment) ?: 'prod'),
        ]);
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @param string $word
     *
     * @return string
     */
    public function snakeCase($word)
    {
        return preg_replace_callback('/([A-Z])/', create_function('$c', 'return "_" . strtolower($c[1]);'),  lcfirst($word));
    }

    /**
     * @param string $word
     *
     * @return string
     */
    public function camelCase($word)
    {
        return preg_replace_callback('/_([a-z])/', create_function('$c', 'return strtoupper($c[1]);'), ucfirst($word));
    }
}

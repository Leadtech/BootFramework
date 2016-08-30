<?php

namespace Boot;

use Boot\Utils\StringUtils;
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
     * @param array                        $params        Parameters to register to the service container
     * @param bool                         $isOptimized   Whether application is optimized (pre-compiled/optimized obj)
     * @param InitializerInterface[]       $initializers  A number of initializers (-/ bootstrap components)
     * @param ExpressionLanguageProvider[] $providers     A number of expression language providers
     *
     * @return ContainerInterface
     */
    public function bootstrap(array $params = [], $isOptimized = true, array $initializers = [], array $providers = [])
    {
        // Load the services if optimization is not enabled.
        if (!$isOptimized) {
            $this->createServiceContainer($params, $initializers, $providers);
        } else {
            $this->getOrCreateOptimizedServiceContainer($params, $initializers, $providers);
        }

        return $this->getServiceContainer();
    }

    /**
     * @param array                        $params        Parameters to register to the service container
     * @param InitializerInterface[]       $initializers  A number of initializers (-/ bootstrap components)
     * @param ExpressionLanguageProvider[] $providers     A number of expression language providers
     *
     * @return ContainerInterface
     */
    protected function getOrCreateOptimizedServiceContainer(array $params = [], array $initializers, array $providers)
    {
        // Get compiled context class
        $compiledClass = $this->getCompiledClassName();

        // Determine path to compiled service container
        $classPath = $this->getCompiledClassDir().DIRECTORY_SEPARATOR.$compiledClass.'.php';

        // Create cache object
        $configCache = new ConfigCache($classPath, $this->environment != Boot::PRODUCTION);

        // Load the services if the cached version is dirty
        if (!$configCache->isFresh()) {

            // Create new container
            $this->createServiceContainer($params, $initializers, $providers);

            // Generates optimized class reflecting the final state of the service container.
            $dumper = new PhpDumper($this->serviceContainer);
            $configCache->write(
                $dumper->dump(['class' => $compiledClass]),
                $this->serviceContainer->getResources()
            );

        } else {

            // Load the compiled class
            require_once $classPath;

            // Create instance
            $this->serviceContainer = new $compiledClass();

            // Bootstrap initializer(s)
            foreach ($initializers as $initializer) {
                $initializer->bootstrap($this->serviceContainer);
            }

        }
    }

    /**
     * @param array                        $parameters    Parameters to register to the service container. Converted to uppercase snake case format.
     * @param InitializerInterface[]       $initializers
     * @param ExpressionLanguageProvider[] $exprProviders
     *
     * @return ContainerBuilder
     */
    protected function createServiceContainer(array $parameters = null, array $initializers = [], array $exprProviders = null)
    {
        // Build container
        $this->serviceContainer = new ContainerBuilder();

        // Load all components
        $this
            ->loadExpressionProviders($exprProviders)
            ->loadInitializers($initializers)
            ->loadCompilerPasses($this->getCompilerPasses())
            ->loadParameters($parameters)
            ->loadConfigurations($this->getDirectories())
        ;

        $this->serviceContainer->compile();

        return $this->serviceContainer;
    }

    /**
     * @param ExpressionLanguageProvider[]|null $providers
     *
     * @return $this
     */
    private function loadExpressionProviders($providers)
    {
        if (is_array($providers)) {
            // Expression providers
            //$this->serviceContainer->addExpressionLanguageProvider(new ExpressionLanguageProvider());
            foreach ($providers as $provider) {
                $this->serviceContainer->addExpressionLanguageProvider($provider);
            }
        }

        return $this;
    }

    /**
     * @param InitializerInterface[]|null $initializers
     * @return $this
     */
    private function loadInitializers($initializers)
    {
        // Load initializers
        if (is_array($initializers)) {
            foreach ($initializers as $initializer) {
                $initializer->bootstrap($this->serviceContainer);
            }
        }

        return $this;
    }

    /**
     * @param array|null $compilerPasses
     *
     * @return $this
     */
    private function loadCompilerPasses($compilerPasses)
    {
        // Load compiler passes
        if (is_array($compilerPasses)) {
            foreach ($compilerPasses as list($pass, $type)) {
                if ($pass instanceof CompilerPassInterface) {
                    $this->serviceContainer->addCompilerPass($pass, $type);
                }
            }
        }

        return $this;
    }

    /**
     * @param array|null $parameters
     *
     * @return $this
     */
    private function loadParameters($parameters)
    {
        // Note that this must be done before loading the context files.
        // It should be possible to override the parameters from the service container.
        if (is_array($parameters)) {
            foreach ($parameters as $name => $value) {
                $param = preg_replace('/[^a-z0-9_.:@]+/', '', $name);
                $this->serviceContainer->setParameter($param, $value);
            }
        }

        return $this;
    }

    /**
     * @param string[]|null $directories
     *
     * @return $this
     */
    public function loadConfigurations($directories)
    {
        // Load configurations
        if (is_array($directories)) {
            foreach ($directories as $directory) {
                if (is_dir($directory)) {
                    $this->loadConfiguration($directory);
                }
            }
        }

        return $this;
    }


    /**
     * Load configuration from module resource folder.
     *
     * @param string $resourceDir
     */
    public function loadConfiguration($resourceDir)
    {
        // Create xml loader
        $loader = new XmlFileLoader($this->serviceContainer, new FileLocator($resourceDir));

        // Load parameters.xml first to ensure the parameters are available
        $filepath = realpath($resourceDir).'/parameters.xml';
        if (file_exists($filepath)) {
            $loader->load($filepath);
        }

        // Load configurations from current directory
        foreach (new \DirectoryIterator($resourceDir) as $file) {
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
            '{app}' => StringUtils::camelCase(ucfirst($this->appName ?: 'default')),
            '{env}' => StringUtils::camelCase(strtolower($this->environment) ?: 'prod'),
        ]);
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }
}

<?php

namespace Boot\Http\Router;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ExpressionLanguageProvider;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteMatcherBuilder.
 */
class RouteMatcherBuilder
{
    const DEFAULT_CLASS_NAME = 'AppRouteMatcher';
    const CONTAINER_DEBUG_MODE = false;

    /** @var  string */
    protected $compiledClassDir = null;

    /** @var bool  */
    protected $debug = false;

    /** @var ExpressionLanguageProvider[] */
    protected $expressionLanguageProviders = [];

    /** @var  string */
    protected $className;

    /**
     * @param string $className
     */
    public function __construct($className = self::DEFAULT_CLASS_NAME)
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    protected function getCompiledFileName()
    {
        return rtrim($this->compiledClassDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$this->className.'.php';
    }

    /**
     * @param RequestContext  $requestContext
     * @param RouteCollection $routeCollection
     *
     * @return UrlMatcher
     */
    public function build(RequestContext $requestContext, RouteCollection $routeCollection)
    {
        // Just create the route matcher if optimization is disabled
        if (!$this->isOptimized()) {
            return new UrlMatcher($routeCollection, $requestContext);
        }

        $filepath = $this->getCompiledFileName();

        $className = $this->className;

        $cache = new ConfigCache($filepath, $this->debug);

        // Check if the compiled route matcher is in place
        if (!file_exists($filepath) || !$cache->isFresh()) {
            // Compile the route collection
            $this->compileRoutes($routeCollection);
        }

        // Load compiled route matcher
        require_once $filepath;

        return new $className($requestContext);
    }

    /**
     * @param RouteCollection $routeCollection
     */
    protected function compileRoutes($routeCollection)
    {
        $cache = new ConfigCache($this->getCompiledFileName(), $this->debug);

        if (!$cache->isFresh()) {
            $dumper = new PhpMatcherDumper($routeCollection);

            // Add expression language providers
            foreach ($this->expressionLanguageProviders as $provider) {
                $dumper->addExpressionLanguageProvider($provider);
            }

            // Write file
            $cache->write(
                $dumper->dump(['class' => $this->className, 'base_class' => UrlMatcher::class]),
                $dumper->getRoutes()->getResources()
            );
        }
    }

    /**
     * @param string    $directory
     * @param bool|true $doCreate
     *
     * @return $this
     */
    public function optimize($directory, $doCreate = true)
    {
        // Check if dir exists, if not either create if or throw exception.
        if (!is_dir($directory)) {
            // Check if the directory should be automatically created.
            if (!$doCreate) {
                throw new \InvalidArgumentException('Path to cache directory is invalid.');
            }
            // Create directory, if the directory was not created an exception is thrown.
            if (!mkdir($directory, 0775, true)) {
                throw new IOException('Failed to create cache directory.');
            }
        }

        // Get realpath to the target directory.
        $directory = realpath($directory);
        if (!empty($directory)) {
            $this->compiledClassDir = $directory;

            return $this;
        }

        throw new \InvalidArgumentException('Path to cache directory is invalid.');
    }

    /**
     * @return bool
     */
    public function isOptimized()
    {
        return !empty($this->compiledClassDir);
    }

    /**
     * @param ExpressionLanguageProvider $provider
     *
     * @return $this
     */
    public function addExpressionLanguageProvider(ExpressionLanguageProvider $provider)
    {
        $this->expressionLanguageProviders[] = $provider;
    }

    /**
     * @return ExpressionLanguageProvider[]
     */
    public function getExpressionLanguageProviders()
    {
        return $this->expressionLanguageProviders;
    }
}

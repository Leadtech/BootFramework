<?php

namespace Boot\Http\Router;

use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheInterface;
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
    protected $targetDir = null;

    /** @var bool  */
    protected $debug = false;

    /** @var ExpressionLanguageProvider[] */
    protected $expressionLanguageProviders = [];

    /** @var  string */
    protected $className;

    /** @var RouteCollection */
    protected $routeCollection;

    /**
     * @param string $className
     */
    public function __construct($className = self::DEFAULT_CLASS_NAME)
    {
        $this->className = $className;

        $this->routeCollection = new RouteCollection();
    }

    /**
     * @param RequestContext $requestContext
     *
     * @return UrlMatcher
     */
    public function build(RequestContext $requestContext)
    {
        // Check if the cache directory was provided.
        if ($this->targetDir) {

            // Declare variables
            $dumper = new PhpMatcherDumper($this->routeCollection);
            $cacheClass = $this->className;
            $baseClass = UrlMatcher::class;
            $expressionLanguageProviders = $this->expressionLanguageProviders;
            $cacheFactory = new ConfigCacheFactory(self::CONTAINER_DEBUG_MODE);

            $pathRouterCache = rtrim($this->targetDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$cacheClass.'.php';
            if (file_exists($pathRouterCache)) {
                unlink($pathRouterCache);
                if (file_exists($pathRouterCache.'.meta')) {
                    // in case some one deleted the cache file by hand but forgot about this one...
                    unlink($pathRouterCache.'.meta');
                }
            }

            // Cache routing
            $cache = $cacheFactory->cache($pathRouterCache,
                function (ConfigCacheInterface $cache) use ($cacheClass, $baseClass, $expressionLanguageProviders, $dumper) {

                    if (method_exists($dumper, 'addExpressionLanguageProvider')) {
                        if (empty($this->expressionLanguageProviders)) {
                            $this->expressionLanguageProviders[] = new ExpressionLanguageProvider();
                        }
                        foreach ($expressionLanguageProviders as $provider) {
                            $dumper->addExpressionLanguageProvider($provider);
                        }
                    }

                    $cache->write(
                        $dumper->dump(['class' => $cacheClass, 'base_class' => $baseClass]),
                        $dumper->getRoutes()->getResources()
                    );
                }
            );

            require_once $cache->getPath();

            return $matcher = new $cacheClass($requestContext);
        }

        throw new \RuntimeException('Target directory is not set.');
    }

    /**
     * @return string
     */
    public function getCacheFile()
    {
        return rtrim($this->targetDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$this->className.'.php';
    }

    /**
     * @param RouteCollection $routeCollection
     *
     * @return $this
     */
    public function mount(RouteCollection $routeCollection)
    {
        $this->routeCollection->addCollection($routeCollection);

        return $this;
    }

    /**
     * @param string    $targetDir
     * @param bool|true $doCreate
     *
     * @return $this
     */
    public function targetDir($targetDir, $doCreate = true)
    {
        // Check if dir exists, if not either create if or throw exception.
        if (!is_dir($targetDir)) {
            // Check if the directory should be automatically created.
            if (!$doCreate) {
                throw new \InvalidArgumentException('Path to cache directory is invalid.');
            }
            // Create directory, if the directory was not created an exception is thrown.
            if (!mkdir($targetDir, 0775, true)) {
                throw new IOException('Failed to create cache directory.');
            }
        }

        // Get realpath to the target directory.
        $targetDir = realpath($targetDir);
        if (!empty($targetDir)) {
            $this->targetDir = $targetDir;

            return $this;
        }

        throw new \InvalidArgumentException('Path to cache directory is invalid.');
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

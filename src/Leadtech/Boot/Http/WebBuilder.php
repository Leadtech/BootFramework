<?php
namespace Boot\Http;

use Boot\Boot;
use Boot\Builder;
use Boot\Http\Router\RouteOptions;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class WebBuilder
 * @package Boot\Http
 */
class WebBuilder extends Builder
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';
    const HTTP_PATCH = 'PATCH';

    /** @var array  */
    private $routeParams = array();

    /** @var array  */
    private $defaultRouteRequirements = array();

    /** @var string */
    private $baseUrl;

    /** @var  RouteCollection */
    private $routeCollection;

    /**
     * @param $projectDir
     */
    public function __construct($projectDir)
    {
        parent::__construct($projectDir);
        $this->routeCollection = new RouteCollection();
    }

    /**
     * @return ContainerInterface
     */
    public function build()
    {
        $isDebug = $this->environment !== Boot::PRODUCTION;
        $this->initializer(new ServiceInitializer('http', $isDebug));

        return parent::build();
    }


    /**
     * @param string $baseUrl
     *
     * @return $this
     */
    public function baseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }


    /**
     * @param string       $path            e.g. /employees/{employeeId}
     * @param string       $service         e.g. App\Service\EmployeeService
     * @param string       $method          e.g. findOne
     * @param RouteOptions $routeOptions
     *
     * @return WebBuilder
     */
    public function get($path, $service, $method, RouteOptions $routeOptions)
    {
        $this->addService(
            $service,
            $method,
            $this->createMethod(self::HTTP_GET, $path, $routeOptions)
        );

        return $this;
    }

    /**
     * @param string       $path            e.g. /employees/{employeeId}
     * @param string       $service         e.g. App\Service\EmployeeService
     * @param string       $method          e.g. findOne
     * @param RouteOptions $routeOptions
     *
     * @return WebBuilder
     */
    public function post($path, $service, $method, RouteOptions $routeOptions)
    {
        $this->addService(
            $service,
            $method,
            $this->createMethod(self::HTTP_POST, $path, $routeOptions)
        );

        return $this;
    }

    /**
     * @param string       $path            e.g. /employees/{employeeId}
     * @param string       $service         e.g. App\Service\EmployeeService
     * @param string       $method          e.g. findOne
     * @param RouteOptions $routeOptions
     *
     * @return WebBuilder
     */
    public function put($path, $service, $method, RouteOptions $routeOptions)
    {
        $this->addService(
            $service,
            $method,
            $this->createMethod(self::HTTP_PUT, $path, $routeOptions)
        );

        return $this;
    }

    /**
     * @param string       $path            e.g. /employees/{employeeId}
     * @param string       $service         e.g. App\Service\EmployeeService
     * @param string       $method          e.g. findOne
     * @param RouteOptions $routeOptions
     *
     * @return WebBuilder
     */
    public function delete($path, $service, $method, RouteOptions $routeOptions)
    {
        $this->addService(
            $service,
            $method,
            $this->createMethod(self::HTTP_DELETE, $path, $routeOptions)
        );

        return $this;
    }

    /**
     * @param string       $path            e.g. /employees/{employeeId}
     * @param string       $service         e.g. App\Service\EmployeeService
     * @param string       $method          e.g. findOne
     * @param RouteOptions $routeOptions
     *
     * @return WebBuilder
     */
    public function patch($path, $service, $method, RouteOptions $routeOptions)
    {
        $this->addService(
            $service,
            $method,
            $this->createMethod(self::HTTP_PATCH, $path, $routeOptions)
        );

        return $this;
    }

    /**
     * Sets global route defaults
     *
     * @param array $defaults
     *
     * @return WebBuilder
     */
    public function defaultRouteParams(array $defaults)
    {
        $this->routeParams = array_merge($this->routeParams, $defaults);

        return $this;
    }

    /**
     * Sets global route requirements
     *
     * @param array $requirements
     *
     * @return WebBuilder
     */
    public function defaultRouteRequirements(array $requirements)
    {
        $this->defaultRouteRequirements = array_merge($this->defaultRouteRequirements, $requirements);

        return $this;
    }

    /**
     * @param string       $method          e.g.  GET, POST, PUT, DELETE or PATCH
     * @param string       $path
     * @param RouteOptions $routeOptions
     *
     * @return HttpMethod
     */
    private function createMethod($method, $path, RouteOptions $routeOptions)
    {
        // Sanitize path
        $path =  '/' . ltrim($path, '/');

        // Optionally apply base url
        if (!empty($this->baseUrl)) {
            $baseUrl = trim($this->baseUrl, '/');
            if (!empty($baseUrl)) {
                $path = '/' . $baseUrl . $path;
            }
        }

        /** @var HttpMethod $route */
        $route = new HttpMethod($method, $routeOptions->getRouteName(), $path);
        $route = $route
            ->setDefaults(array_merge($this->routeParams, $routeOptions->getDefaults()))
            ->setRequirements(array_merge($this->defaultRouteRequirements, $routeOptions->getRequirements()))
        ;

        return $route;
    }

    /**
     * @param string $serviceName
     * @param string $methodName
     * @param HttpMethod $method
     *
     * @return void
     */
    private function addService($serviceName, $methodName, HttpMethod $method)
    {
        // Create symfony route
        $route = $method->createRoute()->addDefaults([
            '_serviceClass'  => $serviceName,
            '_serviceMethod' => $methodName
        ]);

        // Add to route collection
        $this->routeCollection->add($method->getName(), $route);
    }

    /**
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->routeCollection;
    }
}
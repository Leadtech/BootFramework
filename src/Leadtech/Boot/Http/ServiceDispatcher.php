<?php

namespace Boot\Http;

use Boot\Boot;
use Boot\Http\Exception\ServiceLogicException;
use Boot\Http\Exception\ServiceClassNotFoundException;
use Boot\Http\Exception\ServiceMethodNotFoundException;
use Boot\Http\Service\ServiceInterface;
use Boot\Http\Service\Validator\ServiceValidator;
use Boot\Http\Service\Validator\ServiceValidatorInterface;
use Boot\InitializerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ExpressionLanguageProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class ServerInitializer.
 */
class ServiceDispatcher implements InitializerInterface
{
    /** @var WebBuilder */
    protected $builder;

    /** @var  ContainerInterface */
    protected $serviceContainer;

    /** @var string */
    protected $environment = Boot::DEVELOPMENT;

    /** @var string  */
    protected $cacheDir = null;

    /** @var ExpressionLanguageProvider[] */
    private $expressionProviders = [];

    /** @var  EventDispatcher */
    private $eventDispatcher;

    /** @var bool  */
    private $debug = false;

    /** @var RequestContext */
    private $requestContext = null;

    /** @var bool  */
    private $cacheEnabled = false;

    /** @var  string */
    private $serviceId;

    /** @var  RouteCollection */
    private $routeCollection;

    /** @var  ServiceValidatorInterface  validates service to ensure the requested service method can be executed */
    private $serviceValidator;

    /**
     * @param string $serviceId The service name to use to register the http component
     * @param bool   $debug     Enables debug mode
     */
    public function __construct($serviceId = 'http', $debug = false)
    {
        $this->serviceId = $serviceId;
        $this->debug = $debug;
        $this->requestContext = new RequestContext();
    }

    /**
     * @param WebBuilder $builder
     */
    public function initialize($builder)
    {
        if (!$builder instanceof WebBuilder) {
            throw new \LogicException('The server initializer depends on the WebBuilder.');
        }

        $this->builder = $builder;
        $this->environment = $builder->getEnvironment();
        $this->cacheDir = $builder->getCacheDir();
        $this->cacheEnabled = $builder->isCacheEnabled();
        $this->eventDispatcher = $builder->getEventDispatcher();
        $this->expressionProviders = $builder->getExpressionProviders();
        $this->routeCollection = $builder->getRouteCollection();
    }

    /**
     * @param ContainerInterface $container
     */
    public function bootstrap(ContainerInterface $container)
    {
        // Register this instance as a service
        $container->set($this->serviceId, $this);
        $this->serviceContainer = $container;
    }

    /**
     * Dispatch service.
     *
     * @param Request $request
     */
    public function handle(Request $request = null)
    {
        // Update request context
        $request = $request ?: Request::createFromGlobals();

        // Initialize request context
        $this->getRequestContext()->fromRequest($request);

        // Resolve route
        if ($routeMatch = $this->resolve($request)) {
            // Execute service
            $this->invokeService($routeMatch['_serviceClass'], $routeMatch['_serviceMethod'], $request);
        } else {
            // Dispatch 404
            $this->dispatchNotFound($this->debug ? 'NOT FOUND' : '');
        }
    }

    /**
     * Invoke the service.
     *
     * @param ServiceInterface $serviceClass  a static reference to a service implementation
     * @param string           $serviceMethod the name of the method that we want to invoke
     * @param Request          $request
     */
    protected function invokeService($serviceClass, $serviceMethod, Request $request)
    {
        try {
            // Validate the requested service method
            $this->getServiceValidator()->validateService($serviceClass, $serviceMethod);

            // Create service
            $service = $serviceClass::createService($this->getServiceContainer());

            // Dispatch service
            $this->dispatchService($service, $serviceMethod, $request);
        } catch (ServiceMethodNotFoundException $e) {
            // Dispatch error. The method does not exist.
            $this->dispatchInternalServerError("The {$serviceMethod} method does not exist.");
        } catch (ServiceClassNotFoundException $e) {
            // Service does not exist!
            $this->dispatchInternalServerError("The service '{$e->getServiceClass()}' does not exist.", $e);
        } catch (ServiceLogicException $e) {
            // Invalid service
            $this->dispatchInternalServerError(
                'This service is not available because of technical problems. '.
                'Please let us know so we can fix this problem as soon as possible.', $e
            );
        } catch (\Exception $e) {
            // Dispatch error
            $this->dispatchInternalServerError('An unknown error occurred.', $e);
        } catch (\EngineException $e) { // @codeCoverageIgnoreStart
            // Only executed on php < 7.0 in case of a fatal error.
            $this->dispatchInternalServerError('An unknown fatal error occurred.', $e);
        } // @codeCoverageIgnoreEnd
    }

    /**
     * @param Request $request
     *
     * @return ServiceInterface
     */
    protected function resolve(Request $request)
    {
        // Create route matcher
        try {

            // Get route match
            $routeMatch = $this->createRouteMatcher()->matchRequest($request);

            if (isset($routeMatch['_serviceClass'], $routeMatch['_serviceMethod'])) {
                return $routeMatch;
            }
        } catch (ResourceNotFoundException $e) {
            // Not found!
            return false;
        }

        return false;
    }

    /**
     * @param ServiceInterface $service
     * @param $methodName
     * @param Request $request
     *
     * @return mixed
     */
    protected function dispatchService(ServiceInterface $service, $methodName, Request $request)
    {
        $resp = $service->invoke($methodName, $request);

        if ($resp instanceof Response) {
            $resp->send();

            return;
        }

        throw new \RuntimeException('Service failed to send a valid response.');
    }

    /**
     * @param $errorMessage
     * @param \Exception $e
     */
    protected function dispatchInternalServerError($errorMessage, \Exception $e = null)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error', true, 500);
        }

        if ($e && $this->debug) {
            echo strtr('Error: {error} on line {line} in file {file}.', [
                '{error}' => $e->getMessage(),
                '{line}' => $e->getLine(),
                '{file}' => $e->getFile(),
            ]);

            return;
        }

        echo $errorMessage;
    }

    /**
     * @param string $message
     */
    protected function dispatchNotFound($message = null)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 404 Not Found', true, 404);
        }

        if ($message) {
            echo $message;
        }
    }

    /**
     * @return string
     */
    protected function generateClassName()
    {
        return 'Compiled'.$this->builder->getAppName().ucfirst($this->builder->getEnvironment()).'Router';
    }

    /**
     * @return RequestContext
     */
    public function getRequestContext()
    {
        return $this->requestContext;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    /**
     * TODO Refactor, move to factory.
     *
     * @return UrlMatcher
     */
    protected function createRouteMatcher()
    {
        // Generate a class name based on the registered endpoints.
        $className = $this->generateClassName();

        $builder = new \Boot\Http\Router\RouteMatcherBuilder($className);

        $builder->targetDir($this->cacheDir);

        // We must create a route matcher i the environment is other than production or when the generated file does not exist.
        $cacheEnabled = $this->environment === Boot::PRODUCTION && $this->isCacheEnabled();
        $cacheFile = $builder->getCacheFile();
        if ($cacheEnabled && file_exists($cacheFile)) {

            // Load from cache
            require_once $cacheFile;

            $matcher = new $className($this->getRequestContext());
        } else {

            // Mount the route collection
            $builder->mount($this->routeCollection);

            // Add expression providers
            if (!empty($this->expressionProviders)) {
                foreach ($this->expressionProviders as $provider) {
                    $builder->addExpressionLanguageProvider($provider);
                }
            }

            // Generate matcher
            $matcher = $builder->build($this->getRequestContext());
        }

        return $matcher;
    }

    /**
     * @return ServiceValidatorInterface
     */
    public function getServiceValidator()
    {
        if (empty($this->serviceValidator)) {
            $this->serviceValidator = new ServiceValidator();
        }

        return $this->serviceValidator;
    }

    /**
     * @param ServiceValidatorInterface $serviceValidator
     */
    public function setServiceValidator(ServiceValidatorInterface $serviceValidator)
    {
        $this->serviceValidator = $serviceValidator;
    }
}

<?php
namespace Boot\Http;

use Boot\Boot;
use Boot\Http\Exception\InvalidServiceLogicException;
use Boot\Http\Exception\ServiceClassNotFoundException;
use Boot\Http\Exception\ServiceMethodNotFoundException;
use Boot\Http\Service\Handler\RequestHandlerInterface;
use Boot\Http\Service\ServiceInterface;
use Boot\InitializerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ExpressionLanguageProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;


/**
 * Class ServerInitializer
 *
 * @package Boot\Http
 */
class ServerInitializer implements InitializerInterface, RequestHandlerInterface
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

    /** @var  string */
    private $serviceId;

    /** @var  RouteCollection */
    private $routeCollection;

    /**
     * @param string  $serviceId     The service name to use to register the http component
     * @param boolean $debug         Enables debug mode
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
            throw new \LogicException("The server initializer depends on the WebBuilder.");
        }

        $this->builder = $builder;
        $this->environment = $builder->getEnvironment();
        $this->cacheDir = $builder->getCacheDir();
        $this->eventDispatcher = $builder->getEventDispatcher();
        $this->expressionProviders = $builder->getExpressionProviders();
        $this->routeCollection = $builder->getRouteCollection();
    }

    /**
     * @param ContainerInterface $container
     */
    public function bootstrap(ContainerInterface $container)
    {
        // Set service container
        $this->serviceContainer = $container;

        // Register this component as a service
        $this->serviceContainer->set($this->serviceId, $this);
    }

    /**
     * Dispatch service
     *
     * @param Request $request
     */
    public function handle(Request $request = null)
    {
        // Update request context
        $request = $request ?: Request::createFromGlobals();

        // Initialize request context
        $this->requestContext->fromRequest($request);

        // Resolve route
        if ($routeMatch = $this->resolve($request)) {

            // Declare class and method
            $serviceClass = $routeMatch['_serviceClass'];
            $serviceMethod = $routeMatch['_serviceMethod'];

            try {

                // Assert that this is a valid service
                $this->checkService($serviceClass, $serviceMethod);

                // Create service
                $service = $serviceClass::createService($this->serviceContainer);

                // Dispatch service
                $this->dispatchService($service, $serviceMethod, $request);

            } catch(ServiceMethodNotFoundException $e) {
                // Dispatch error. The method does not exist.
                $this->dispatchError("The service does not implement the requested method.");
            } catch(ServiceClassNotFoundException $e) {
                // Service does not exist!
                $this->dispatchError("This expected service seems to have moved or does not longer exist.", $e);
            } catch(InvalidServiceLogicException $e) {
                // Invalid service
                $this->dispatchError("This service is not available due to technical problems. Please contact support.", $e);
            } catch(\Exception $e) {
                // Dispatch error
                $this->dispatchError('An unknown error occurred.', $e);
            }

        } else {
            // Dispatch 404
            $this->dispatchNotFound($this->debug ? 'NOT FOUND' : '');
        }
    }

    /**
     * @param Request $request
     * @return ServiceInterface
     */
    protected function resolve(Request $request)
    {
        // Create route matcher
        try {

            // Get route match
            $routeMatch = $this->createRouteMatcher()->matchRequest($request);

            if(isset($routeMatch['_serviceClass'], $routeMatch['_serviceMethod'])) {
                return $routeMatch;
            }

        } catch(ResourceNotFoundException $e) {
            // Not found!
            return false;
        }

        return false;
    }

    /**
     * Validates service (prior to dispatch)
     *
     * @param string $className
     * @param string $methodName
     *
     * @throws ServiceClassNotFoundException
     * @throws ServiceMethodNotFoundException
     * @throws InvalidServiceLogicException
     *
     */
    protected function checkService($className, $methodName)
    {
        if (!method_exists($className, $methodName)) {
            throw new ServiceClassNotFoundException($className, $methodName);
        }

        // Check if the service exists and implements the ServiceInterface.
        $interfaces = (array) @class_implements($className, true);
        if (!in_array(ServiceInterface::class, $interfaces)) {
            throw new InvalidServiceLogicException(
                $className,
                $methodName,
                "The service does not implement " . ServiceInterface::class
            );
        }

        if (!method_exists($className, $methodName)) {
            throw new ServiceMethodNotFoundException(
                $className,
                $methodName,
                "The service does not implement " . ServiceInterface::class
            );
        }
    }

    /**
     * @param ServiceInterface $service
     * @param $methodName
     * @param Request $request
     * @return mixed
     */
    protected function dispatchService(ServiceInterface $service, $methodName, Request $request)
    {
        $resp = call_user_func([$service, $methodName], $request);
        if(is_scalar($resp)) {
            echo $resp;

            return;
        }

        // Create json response if a array is returned
        if (is_array($resp)) {
            $resp = new JsonResponse($resp);
        }

        if ($resp instanceof Response) {
            $resp->send();

            return;
        }

        throw new \RuntimeException("The response must be either scalar, an array or a response object.");
    }

    /**
     * @param $errorMessage
     * @param \Exception $e
     */
    protected function dispatchError($errorMessage, \Exception $e = null)
    {
        header('HTTP/1.1 500 Internal Server Error', true, 500);

        if($e && $this->debug) {

            echo strtr('Error: {error} on line {line} in file {file}.', [
                '{error}' => $e->getMessage(),
                '{line}'  => $e->getLine(),
                '{file}'  => $e->getFile()
            ]);

            return;
        }

        echo $errorMessage;
    }

    /**
     * @param $message
     */
    protected function dispatchNotFound($message = null)
    {
        header('HTTP/1.1 404 Not Found', true, 404);
        if ($message) {
            echo $message;
        }
    }

    /**
     * @return string
     */
    protected function generateClassName()
    {
        return 'Compiled' . $this->builder->getAppName() . ucfirst($this->builder->getEnvironment()) . 'Router';
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
        if ($this->environment != Boot::PRODUCTION || !file_exists($builder->getClassPath())) {

            // Mount the route collection
            $builder->mount($this->routeCollection);

            // Add expression providers
            if (!empty($this->expressionProviders)) {
                foreach($this->expressionProviders as $provider) {
                    $builder->addExpressionLanguageProvider($provider);
                }
            }


            // Generate matcher
            $matcher = $builder->build($this->requestContext);

        } else {

            // Load from cache
            require_once $builder->getClassPath();

            $matcher = new $className($this->requestContext);

        }

        return $matcher;
    }

}
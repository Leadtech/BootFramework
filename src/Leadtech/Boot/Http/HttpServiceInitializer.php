<?php

namespace Boot\Http;

use Boot\AbstractInitializer;
use Boot\Boot;
use Boot\Builder;
use Boot\Exception\IncompatibleComponentException;
use Boot\Http\Exception\ServiceLogicException;
use Boot\Http\Exception\ServiceClassNotFoundException;
use Boot\Http\Exception\ServiceMethodNotFoundException;
use Boot\Http\Router\RouteMatcherBuilder;
use Boot\Http\Service\ServiceInterface;
use Boot\Http\Service\Validator\ServiceValidator;
use Boot\Http\Service\Validator\ServiceValidatorInterface;
use Boot\InitializerInterface;
use Boot\Utils\NetworkUtils;
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
class HttpServiceInitializer extends AbstractInitializer implements InitializerInterface
{
    /** @var WebBuilder */
    protected $builder;

    /** @var  ContainerInterface */
    protected $serviceContainer;

    /** @var string */
    protected $environment = Boot::DEVELOPMENT;

    /** @var string  */
    protected $compiledClassDir = null;

    /** @var ExpressionLanguageProvider[] */
    private $expressionProviders = [];

    /** @var  EventDispatcher */
    private $eventDispatcher;

    /** @var bool  */
    private $debug = false;

    /** @var RequestContext */
    private $requestContext = null;

    /** @var bool  */
    private $optimized = false;

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
    public function __construct($serviceId, $debug = false)
    {
        $this->serviceId = $serviceId;
        $this->debug = $debug;
        $this->requestContext = new RequestContext();
    }

    /**
     * @param WebBuilder|Builder $builder
     *
     * @throws IncompatibleComponentException
     */
    public function initialize(Builder $builder)
    {
        parent::initialize($builder);

        $this->builder = $builder;
        $this->environment = $builder->getEnvironment();
        $this->compiledClassDir = $builder->getCompiledClassDir();
        $this->optimized = $builder->isOptimized();
        $this->eventDispatcher = $builder->getEventDispatcher();
        $this->expressionProviders = $builder->getExpressionProviders();
        $this->routeCollection = $builder->getRouteCollection();
    }

    /**
     * @param Builder $builder
     *
     * @return bool
     */
    public function accept(Builder $builder)
    {
        return $builder instanceof WebBuilder;
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
            if ($this->isAccessGranted($routeMatch, $request)) {
                // Execute service
                $this->invokeService($routeMatch['_serviceClass'], $routeMatch['_serviceMethod'], $request);
            } else {
                $this->dispatchForbidden($this->debug ? 'IP ADDRESS REJECTED' : null);
            }
        } else {
            // Dispatch 404
            $this->dispatchNotFound($this->debug ? 'NOT FOUND' : '');

            return;
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
     * @return array
     */
    protected function resolve(Request $request)
    {
        try {
            // Get route match
            $routeMatch = $this->getRouteMatcher()->matchRequest($request);
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
     * @param string $message
     */
    protected function dispatchForbidden($message = null)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 403 Forbidden', true, 403);
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
        return strtr('Compiled{appName}{environment}{numRoutes}Router', [
            '{appName}' => ucfirst($this->builder->getAppName()),
            '{environment}' => ucfirst($this->builder->getEnvironment()),
            '{numRoutes}' => $this->routeCollection->count(),
        ]);
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
    public function isOptimized()
    {
        return $this->optimized;
    }

    /**
     * @return UrlMatcher
     */
    protected function getRouteMatcher()
    {
        // Generate a class name based on the environment settings.
        $builder = new RouteMatcherBuilder($this->generateClassName());
        if ($this->isOptimized()) {
            $builder->optimize($this->compiledClassDir);
        }

        // Add expression language provider for 'service' and 'parameter'
        // TODO: After seeing this after a while I don't recall for what reason I added this, should test if this is
        // TODO: really needed or if symfony provides an elegant callback out of the box.
        $builder->addExpressionLanguageProvider(new ExpressionLanguageProvider());

        // Add expression providers
        if (!empty($this->expressionProviders)) {
            foreach ($this->expressionProviders as $provider) {
                $builder->addExpressionLanguageProvider($provider);
            }
        }

        $matcher = $builder->build($this->getRequestContext(), $this->routeCollection);

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

    /**
     * @param array   $routeMatch
     * @param Request $request
     *
     * @return bool
     */
    private function isAccessGranted($routeMatch, Request $request)
    {
        // Declare vars
        $clientIp = $request->getClientIp();
        $host = $request->getHost();

        // All services are public, unless specified otherwise.
        $accessGranted = true;

        // Apply ip range limitations in place (see RemoteAccessPolicy and/or RouteOptions)
        if (!empty($routeMatch['_publicIpRangesDenied']) && NetworkUtils::isPublicIpRange($clientIp)) {
            $accessGranted = false;
        } elseif (!empty($routeMatch['_privateIpRangesDenied']) && NetworkUtils::isPrivateIpRange($clientIp)) {
            $accessGranted = false;
        } elseif (!empty($routeMatch['_reservedIpRangesDenied']) && NetworkUtils::isReservedIpRange($clientIp)) {
            $accessGranted = false;
        }

        // Check blacklisted/whitelisted IP's and/or hosts
        if ($accessGranted) {
            // Verify that client is not on the blacklist
            if (isset($routeMatch['_blacklistIps']) && NetworkUtils::checkIp($clientIp, $routeMatch['_blacklistIps'])) {
                return false;
            }
            if (isset($routeMatch['_blacklistHosts']) && NetworkUtils::checkHost($host, $routeMatch['_blacklistHosts'])) {
                return false;
            }

            return true;
        } else {
            // Check the white list before denying access...
            if (isset($routeMatch['_whitelistIps']) && NetworkUtils::checkIp($clientIp, $routeMatch['_whitelistIps'])) {
                return true;
            }
            if (isset($routeMatch['_whitelistHosts']) && NetworkUtils::checkHost($host, $routeMatch['_whitelistHosts'])) {
                return true;
            }

            return false;
        }
    }
}

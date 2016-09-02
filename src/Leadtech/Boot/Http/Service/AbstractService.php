<?php

namespace Boot\Http\Service;

use Boot\Http\Router\RouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractService.
 */
abstract class AbstractService implements ServiceInterface
{
    /** @var  ContainerInterface */
    private $serviceContainer;

    /** @var  Request */
    private $request;

    /** @var  RouteMatch */
    private $routeMatch;

    /**
     * Make constructor protected. A service must be created using the createService factory method.
     *
     * @param ContainerInterface $serviceContainer
     */
    protected function __construct(ContainerInterface $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * Factory method to get a service instance.
     *
     * @param ContainerInterface $serviceContainer
     *
     * @return ServiceInterface
     */
    public static function createService(ContainerInterface $serviceContainer)
    {
        return new static($serviceContainer);
    }

    /**
     * @return ContainerInterface
     *
     * @codeCoverageIgnore   can ignore coverage, is out of scope, getter is for the concrete service implementation.
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @param RouteMatch $routeMatch
     * @param Request    $request
     *
     * @throws \Exception
     *
     * @return response
     */
    public function invoke(RouteMatch $routeMatch, Request $request)
    {
        // Set route match & request
        $this->request = $request;
        $this->routeMatch = $routeMatch;

        try {

            // Trigger before the service method is invoked
            $this->preInvoke();

            // Execute method
            $response = $this->{$routeMatch->getMethodName()}();

            // Symfony works with any of the items below,  however I prefer a strongly typed interface.
            // The invoke method must always return an instance of response.
            if ($response === null || $response === '') {
                // do nothing
            } elseif ($response instanceof Response) {
                // do nothing...
            } elseif (is_array($response)) {
                $response = new JsonResponse($response);
            } elseif ($response instanceof \JsonSerializable) {
                $response = new JsonResponse($response->jsonSerialize());
            } elseif (is_scalar($response)) {
                $response = new Response($response);
            } else {
                throw new \DomainException('Invalid response format');
            }

            // Trigger after a service method is invoked and response validness is verified
            $this->onSuccess($response);

        } catch (\Exception $e) {
            // Optional handler to send alternative error response
            $response = $this->onException($e);
            if (!$response instanceof Response) {
                // Rethrow
                throw $e;
            }
        }

        return $response;
    }

    /**
     * Called before the service method is invoked...
     *
     * @return void
     */
    protected function preInvoke()
    {
        // optionally implement in concrete service
    }

    /**
     * Called after the service method is successfully invoked...
     *
     * @param Response $response
     *
     * @return void
     */
    protected function onSuccess(Response $response)
    {
        // optionally implement in concrete service
    }

    /**
     * Called in case of an uncaught exception after invoking a service method.
     * Optionally return a response object. When null is returned the framework will send a 500 response.
     *
     * @param \Exception $e
     * @return null|Response
     */
    protected function onException(\Exception $e)
    {
        // optionally implement in concrete service
        return null;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }
}

<?php

namespace Boot\Http\Service;

use Boot\Http\Exception\ServiceMethodNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractService.
 */
abstract class AbstractService implements ServiceInterface
{
    /** @var  Request */
    protected $request;

    /** @var  ContainerInterface */
    protected $serviceContainer;

    /**
     * Make constructor protected. A service must be created using the createService factory method.
     *
     * @param ContainerInterface $serviceContainer
     */
    final public function __construct(ContainerInterface $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @param string $method
     * @param Request $request
     *
     * @return response
     */
    public function invokeMethod($method, Request $request)
    {
        // Trigger before the service method is invoked
        $this->preInvoke($method, $request);

        // Execute method
        $response = $this->$method($request);

        // Symfony works with any of the items below,  however I prefer a strongly typed interface.
        // The invoke method must always return an instance of response.
        if ($response === null || $response === "") {
            // do nothing
        } else if ($response instanceof Response) {
            // do nothing...
        } else if (is_array($response)) {
            $response = new JsonResponse($response);
        } else if ($response instanceof \JsonSerializable) {
            $response = new JsonResponse($response->jsonSerialize());
        } else if (is_scalar($response)) {
            $response = new Response($response);
        } else {
            throw new \DomainException("Invalid response format");
        }

        // Trigger after a service method is invokeed
        $this->postInvoke($method, $request, $response);

        return $response;
    }

    /**
     * Called before the service method is invoked...
     *
     * @param string  $method
     * @param Request $request
     */
    protected function preInvoke($method, Request $request)
    {
        // optionally implement in concrete class
    }

    /**
     * Called after the service method is invoked...
     *
     * @param string   $method
     * @param Request  $request
     * @param Response $response
     */
    protected function postInvoke($method, Request $request, $response = null)
    {
        // optionally implement in concrete class
    }
}

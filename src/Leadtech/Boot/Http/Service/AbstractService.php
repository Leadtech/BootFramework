<?php

namespace Boot\Http\Service;

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
    protected $serviceContainer;

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
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @param string  $method
     * @param Request $request
     *
     * @return response
     */
    public function invoke($method, Request $request)
    {
        // Trigger before the service method is invoked
        $this->preInvoke($method, $request);

        // Execute method
        $response = $this->$method($request);

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

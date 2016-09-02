<?php

namespace Boot\Tests\Assets\Http;

use Boot\Http\Router\RouteMatch;
use Boot\Http\Service\AbstractService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\CS\StdinFileInfo;

class FooService extends AbstractService
{
    /**
     * Ensures all tests fail if the required request or route match instances are missing or invalid.
     */
    protected function preInvoke()
    {
        if (!$this->getRequest() instanceof Request || !$this->getRouteMatch() instanceof RouteMatch) {
            throw new \RuntimeException("Missing dependency request or route match");
        }
    }

    /**
     * @return array
     */
    public function returnArray()
    {
        $routeParams = $this->getRouteMatch()->getRouteParams();
        return $routeParams;
    }

    /**
     * @return Response
     */
    public function returnResponseObject()
    {
        return Response::create('blaat');
    }

    /**
     * @return \stdClass
     */
    public function returnInvalidResponse()
    {
        return new \stdClass();
    }

    /**
     * @return Response|static
     */
    public function returnJsonResponseObject()
    {
        return JsonResponse::create([]);
    }

    /**
     * @return \JsonSerializable
     */
    public function returnJsonSerializable()
    {
        return new JsonSerializableImpl();
    }

    /**
     * @return string
     */
    public function returnString()
    {
        return 'foobar';
    }

    /**
     * @throws \Exception
     */
    public function throwsException()
    {
        throw new \RuntimeException("Some unexpected error");
    }
}

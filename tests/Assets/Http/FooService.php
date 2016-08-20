<?php

namespace Boot\Tests\Assets\Http;

use Boot\Http\Service\AbstractService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FooService extends AbstractService
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function returnArray(Request $request)
    {
        return [];
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function returnResponseObject(Request $request)
    {
        return Response::create('blaat');
    }

    /**
     * @param Request $request
     *
     * @return Response|static
     */
    public function returnJsonResponseObject(Request $request)
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
}

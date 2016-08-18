<?php
namespace Boot\Tests\Assets\Http;

use Boot\Http\Service\AbstractService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FooService extends AbstractService
{
    /**
     * @param ContainerInterface $serviceContainer
     * @return \Boot\Http\Service\ServiceInterface
     */
    public static function createService(ContainerInterface $serviceContainer)
    {
        // Do not use this method to set additional services or parameters!
        // Use a factory instead and use dependency injection to inject the factory or the result.
        // It is not possible to set parameters at this point because the container might be "frozen".
       // $serviceContainer->set('service123', new \stdClass());
        //$serviceContainer->setParameter('foobar.value1', 1);
        //$serviceContainer->setParameter('foobar.value2', 2);

        return parent::createService($serviceContainer);
    }

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
     * @return Response
     */
    public function returnResponseObject(Request $request)
    {
        return Response::create("blaat");
    }

    /**
     * @param Request $request
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
        return "foobar";
    }
}
<?php

namespace Boot\Http\Service;

use Boot\Http\Exception\ServiceMethodNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface ServiceInterface.
 */
interface ServiceInterface
{
    /**
     * A factory method to instantiate a service.
     *
     * It can imagine why someone would question the use of a factory
     * method instead of a constructor in this situation. I know I would :-)
     * So I will explain myself here.
     *
     * Each service must implement the ServiceInterface.
     * Other components will only know the ServiceInterface.
     * Other classes need to know two things.
     *  - How to obtain the service (and provide the expected arguments)
     *  - How to invoke a service method
     *
     * I had a dilemma. Either I would need to put a contract on the constructor. I could make it final and work
     * with the implementation. But that would break my design. Alternatively I could have added the
     * constructor to the interface. That is perfectly valid in PHP syntax-wise but that would be even worse.
     * Also, I have a problem with putting this logic in the constructor.
     * What if we would want to implement a new type of service?  Perhaps some future websocket library?
     * The requirements would be different and it would possibly not make any sense to be enforced to instantiate an
     * object.  (since all we would know is the constructor).
     *
     * @param ContainerInterface $serviceContainer
     *
     * @return ServiceInterface
     */
    public static function createService(ContainerInterface $serviceContainer);

    /**
     * Invokes the service method and implements business logic before and after the call.
     *
     * @param string  $method
     * @param Request $request
     *
     * @throws ServiceMethodNotFoundException
     */
    public function invoke($method, Request $request);
}

<?php

namespace Boot\Http\Service;

use Boot\Http\Exception\ServiceMethodNotFoundException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface ServiceInterface.
 */
interface ServiceInterface
{
    /**
     * @param string  $method
     * @param Request $request
     *
     * @throws ServiceMethodNotFoundException
     */
    public function invokeMethod($method, Request $request);
}

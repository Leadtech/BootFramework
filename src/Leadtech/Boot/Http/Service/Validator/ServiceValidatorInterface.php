<?php

namespace Boot\Http\Service\Validator;

use Boot\Http\Exception\ServiceClassNotFoundException;
use Boot\Http\Exception\ServiceLogicException;
use Boot\Http\Exception\ServiceMethodNotFoundException;

/**
 * Interface ServiceValidatorInterface.
 *
 * Validates service to ensure the requested service method can be executed.
 */
interface ServiceValidatorInterface
{
    /**
     * @param string $serviceClass
     * @param string $serviceMethod
     *
     * @throws ServiceClassNotFoundException
     * @throws ServiceLogicException
     * @throws ServiceMethodNotFoundException
     */
    public function validateService($serviceClass, $serviceMethod);
}

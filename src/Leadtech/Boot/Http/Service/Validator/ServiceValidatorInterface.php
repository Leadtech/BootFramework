<?php
namespace Boot\Http\Service\Validator;

use Boot\Http\Exception\ServiceClassNotFoundException;
use Boot\Http\Exception\ServiceLogicException;
use Boot\Http\Exception\ServiceMethodNotFoundException;

/**
 * Interface ServiceValidatorInterface
 *
 * Validates service to ensure the requested service method can be executed.
 *
 * @package Boot\Http\Service\Validator
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
     *
     * @return void
     */
    public function validateService($serviceClass, $serviceMethod);
}
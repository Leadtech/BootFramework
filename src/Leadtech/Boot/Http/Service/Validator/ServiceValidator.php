<?php
namespace Boot\Http\Service\Validator;

use Boot\Http\Exception\ServiceClassNotFoundException;
use Boot\Http\Exception\ServiceLogicException;
use Boot\Http\Exception\ServiceMethodNotFoundException;
use Boot\Http\Service\ServiceInterface;

/**
 * Class ServiceValidator
 *
 * Validates service to ensure the requested service method can be executed.
 *
 * @package Boot\Http\Service\Validator
 */
class ServiceValidator implements ServiceValidatorInterface
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
    public function validateService($serviceClass, $serviceMethod)
    {
        if (!class_exists($serviceClass)) {
            throw new ServiceClassNotFoundException($serviceClass);
        }

        // Check if the service exists and implements the ServiceInterface.
        if (!$this->isServiceImplementation($serviceClass)) {
            throw new ServiceLogicException($serviceClass, $serviceMethod,
                'The service must implement '.ServiceInterface::class
            );
        }

        if (!method_exists($serviceClass, $serviceMethod)) {
            throw new ServiceMethodNotFoundException($serviceClass, $serviceMethod);
        }
    }

    /**
     * @param string $className for example  MyService::class
     *
     * @return bool
     */
    protected function isServiceImplementation($className)
    {
        return in_array(ServiceInterface::class, (array) @class_implements($className, true));
    }

}
<?php

namespace Boot\Http\Exception;

use RuntimeException;

/**
 * Class ServiceMethodNotFoundException.
 *
 * Exception is thrown when the router returns a route pointing to an existing service and the class method is not
 * implemented.
 */
class ServiceMethodNotFoundException extends RuntimeException
{
    /** @var  string */
    protected $serviceClass;

    /** @var  string */
    protected $serviceMethod;

    /**
     * ServiceMethodNotFoundException constructor.
     *
     * @param string $className
     * @param int    $methodName
     */
    public function __construct($className, $methodName)
    {
        $message = strtr("The service {service} does not have a method called {method}.", [
            '{method}' => $methodName,
            '{service}' => $className
        ]);

        parent::__construct($message);

        $this->serviceClass = $className;
        $this->serviceMethod = $methodName;
    }
}

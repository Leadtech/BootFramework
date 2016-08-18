<?php
namespace Boot\Http\Exception;

use RuntimeException;

/**
 * Class ServiceResourceNotFoundException
 *
 * Exception is thrown when the router returns a route pointing to a non existing service or service method.
 *
 * @package Boot\Http\Exception
 * @codeCoverageIgnore
 */
class ServiceClassNotFoundException extends RuntimeException
{
    /** @var  string */
    protected $serviceClass;

    /** @var  string */
    protected $serviceMethod;

    /**
     * ServiceClassNotFoundException constructor.
     *
     * @param string  $className
     * @param int     $methodName
     * @param string  $message
     * @param int     $code
     */
    public function __construct($className, $methodName, $message = "", $code = 0)
    {
        parent::__construct($message, $code);

        $this->serviceClass = $className;
        $this->serviceMethod = $methodName;
    }
}
<?php
namespace Boot\Http\Exception;

use RuntimeException;

/**
 * Class ServiceMethodNotFoundException
 *
 * Exception is thrown when the router returns a route pointing to an existing service and the class method is not
 * implemented.
 *
 * @package Boot\Http\Exception
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
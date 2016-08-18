<?php
namespace Boot\Http\Exception;

/**
 * Class InvalidServiceLogicException
 *
 * Exception is thrown if the service implementation is invalid. For example, services MUST implement the
 * ServiceInterface. When this is not the case this exception will be thrown. This exception is always the result
 * of human error.
 *
 * @package Boot\Http\Exception
 */
class ServiceLogicException extends \LogicException
{
    /** @var  string */
    protected $serviceClass;

    /** @var  string */
    protected $serviceMethod;

    /**
     * InvalidServiceLogicException constructor.
     *
     * @param string  $className
     * @param int     $methodName
     * @param string  $message
     * @param int     $code
     */
    public function __construct($className, $methodName, $message = "", $code = 0)
    {
        if (empty($message)) {
            $message = "";
        }

        parent::__construct($message, $code);

        $this->serviceClass = $className;
        $this->serviceMethod = $methodName;
    }
}
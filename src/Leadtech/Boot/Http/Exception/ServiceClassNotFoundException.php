<?php

namespace Boot\Http\Exception;

use RuntimeException;

/**
 * Class ServiceResourceNotFoundException.
 *
 * Exception is thrown when the router returns a route pointing to a non existing service or service method.
 */
class ServiceClassNotFoundException extends RuntimeException
{
    /** @var  string */
    protected $className;

    /**
     * ServiceClassNotFoundException constructor.
     *
     * @param string $className
     * @param string $message
     * @param int    $code
     */
    public function __construct($className, $message = '', $code = 0)
    {
        parent::__construct($message, $code);

        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}

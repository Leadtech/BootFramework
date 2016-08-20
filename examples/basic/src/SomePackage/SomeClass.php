<?php

namespace SomePackage;

use Psr\Log\LoggerInterface;

class SomeClass
{
    /**
     * @var \stdClass
     */
    private $dependency;

    private $logger;

    /**
     * SomeClass constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \stdClass
     */
    public function getDependency()
    {
        return $this->dependency;
    }

    /**
     * @param \stdClass $dependency
     */
    public function setDependency($dependency)
    {
        $this->dependency = $dependency;
    }

    /**
     */
    public function doSomething()
    {
        if ($this->dependency instanceof \stdClass && $this->logger instanceof LoggerInterface) {
            echo "Verified the injected dependencies. \n";
            echo "Executing the doSomething() method.\n";
        }
    }
}

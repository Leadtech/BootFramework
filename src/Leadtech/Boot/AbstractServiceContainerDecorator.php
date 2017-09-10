<?php

namespace Boot;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ScopeInterface;

/**
 * Class AbstractServiceContainerDecorator.
 *
 * The builder class will build an instance of ContainerInterface.
 * For specific use cases, the ConsoleBuilder for example you may want to return an application instance
 * that provides a  run() method to encapsulate the logic of executing the console service.
 * To do this we can use the decorator pattern to add a run method in runtime.
 * This class is meant to make it very easy to do this.
 *
 * The overhead of this class should be negligible. Its intended use is to be executed (once?) after the bootstrap.
 *
 * So for example:
 * $app = $builder->build();
 * $app->run();
 *
 * But if you need the object for additional service lookups and if you don't want the decorator in between than
 * you can the obtain the original container instance using the getInternal() method.
 */
abstract class AbstractServiceContainerDecorator implements ContainerInterface
{
    /** @var ContainerInterface  */
    private $internal;

    /**
     * AbstractServiceContainerDecorator constructor.
     *
     * @param ContainerInterface $internal
     */
    public function __construct(ContainerInterface $internal)
    {
        $this->internal = $internal;
    }

    /**
     * @return ContainerInterface
     */
    final public function getInternal()
    {
        return $this->internal;
    }

    /**
     * @param string $id
     * @param object $service
     */
    final public function set($id, $service)
    {
        $this->internal->set($id, $service);
    }

    /**
     * @param string $id
     * @param int    $invalidBehavior
     *
     * @return object
     */
    final public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return $this->internal->get($id, $invalidBehavior);
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    final public function has($id)
    {
        return $this->internal->has($id);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    final public function getParameter($name)
    {
        return $this->internal->getParameter($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    final public function hasParameter($name)
    {
        return $this->internal->hasParameter($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    final public function setParameter($name, $value)
    {
        $this->internal->setParameter($name, $value);
    }

    /**
     * Check for whether or not a service has been initialized.
     *
     * @param string $id
     *
     * @return bool true if the service has been initialized, false otherwise
     */
    public function initialized($id)
    {
        return $this->internal->initialized($id);
    }
}

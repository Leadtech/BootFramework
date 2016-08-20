<?php

namespace Boot\Http\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractService.
 */
abstract class AbstractService implements ServiceInterface
{
    /** @var  Request */
    protected $request;

    /** @var  ContainerInterface */
    protected $serviceContainer;

    /**
     * @param ContainerInterface $serviceContainer
     *
     * @return ServiceInterface
     */
    final public static function createService(ContainerInterface $serviceContainer)
    {
        return new static($serviceContainer);
    }

    /**
     * Make constructor protected. A service must be created using the createService factory method.
     *
     * @param ContainerInterface $serviceContainer
     */
    final protected function __construct(ContainerInterface $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }
}

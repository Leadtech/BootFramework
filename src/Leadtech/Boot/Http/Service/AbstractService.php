<?php
namespace Boot\Http\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractService
 *
 * @package Boot\Http\Service
 */
abstract class AbstractService implements ServiceInterface
{
    /** @var  Request */
    protected $request;

    /** @var  ContainerInterface */
    protected $serviceContainer;

    /**
     * @param ContainerInterface $serviceContainer
     * @return ServiceInterface
     */
    public static function createService(ContainerInterface $serviceContainer)
    {
        return new static();
    }

    /**
     * Make constructor protected. A service must be created using the createService factory method.
     */
    final protected function __construct(){}

}
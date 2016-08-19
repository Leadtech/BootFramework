<?php

namespace Boot\Http\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interface ServiceInterface.
 */
interface ServiceInterface
{
    /**
     * @param ContainerInterface $serviceContainer
     *
     * @return ServiceInterface
     */
    public static function createService(ContainerInterface $serviceContainer);
}

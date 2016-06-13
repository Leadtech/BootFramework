<?php
namespace Boot\Http\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface ServiceInterface
{
    /**
     * @param ContainerInterface $serviceContainer
     * @return ServiceInterface
     */
    public static function createService(ContainerInterface $serviceContainer);
}
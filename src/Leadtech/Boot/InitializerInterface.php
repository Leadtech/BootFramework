<?php

namespace Boot;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Hook custom components into the boot bootstrap process.
 * A web or api component for example.
 *
 * Interface InitializerInterface
 */
interface InitializerInterface
{
    /**
     * @param Builder $builder
     */
    public function initialize($builder);

    /**
     * @param ContainerInterface $container
     */
    public function bootstrap(ContainerInterface $container);
}

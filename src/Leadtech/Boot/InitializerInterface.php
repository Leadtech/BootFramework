<?php

namespace Boot;

use Boot\Exception\IncompatibleComponentException;
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
     *
     * @throws IncompatibleComponentException
     */
    public function initialize(Builder $builder);

    /**
     * Check if the builder is compatible with this initializers.
     *
     * @param Builder $builder
     *
     * @return bool returns true
     */
    public function accept(Builder $builder);

    /**
     * @param ContainerInterface $container
     */
    public function bootstrap(ContainerInterface $container);
}

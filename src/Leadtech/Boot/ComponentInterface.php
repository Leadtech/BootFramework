<?php
namespace Boot;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Hook custom components into the boot bootstrap process.
 * A web or api component for example.
 *
 * Interface ComponentInterface
 * @package Boot
 */
interface ComponentInterface
{
    /**
     * @param Builder $builder
     *
     * @return void
     */
    public function initialize(Builder $builder);

    /**
     * @param ContainerInterface $container
     *
     * @return void
     */
    public function bootstrap(ContainerInterface $container);
}
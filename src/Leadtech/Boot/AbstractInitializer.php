<?php

namespace Boot;

use Boot\Exception\IncompatibleComponentException;

/**
 * Class AbstractInitializer.
 */
abstract class AbstractInitializer implements InitializerInterface
{
    /**
     * @param Builder $builder
     *
     * @throws IncompatibleComponentException
     */
    public function initialize(Builder $builder)
    {
        if (!$this->accept($builder)) {
            throw new IncompatibleComponentException($this, $builder);
        }
    }
}

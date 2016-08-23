<?php

namespace Boot;

use Boot\Exception\IncompatibleInitializerException;

/**
 * Class AbstractInitializer.
 */
abstract class AbstractInitializer implements InitializerInterface
{
    /**
     * @param Builder $builder
     *
     * @throws IncompatibleInitializerException
     */
    public function initialize(Builder $builder)
    {
        if (!$this->accept($builder)) {
            throw new IncompatibleInitializerException($this, $builder);
        }
    }
}

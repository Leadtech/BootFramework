<?php

namespace Boot\Exception;

use Boot\Builder;
use Boot\InitializerInterface;

/**
 * Class IncompatibleComponentException.
 *
 * A framework exception caused by registering an initializer that is not compatible with the builder.
 * A framework exception that was caused by an error in the program logic.
 * The initializer is not compatible with the used builder.
 * This kind of exceptin should directly lead to a fix the source code.
 */
class IncompatibleComponentException extends \LogicException
{
    /**
     * IncompatibleInitializerException constructor.
     *
     * @param InitializerInterface $initializer
     * @param Builder              $builder
     */
    public function __construct(InitializerInterface $initializer, Builder $builder)
    {
        $message = strtr(
            'A program logic exception occurred during bootstrap. '.
            'The {initializer} initializer does not accept an instance {builder}.', [
                '{initializer}' => get_class($initializer),
                '{builder}' => get_class($builder),
            ]
        );

        parent::__construct($message);
    }
}

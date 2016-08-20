<?php

namespace Boot\Console;

use Boot\Builder;
use Boot\Console\CompilerPass\CommandCompilerPass;

/**
 * Class ConsoleBuilder.
 */
class ConsoleBuilder extends Builder
{
    /**
     * ConsoleBuilder constructor.
     *
     * @param string $projectDir
     */
    public function __construct($projectDir)
    {
        parent::__construct($projectDir);

        // A compiler pass needed to correctly load (and cache) the registered console commands.
        // It may not be very straight forward and thus confusing to be confronted with compiler passes during a
        // "simple" bootstrap... Instead offer this console builder and add the compiler pass behind the scenes.
        $this->beforeOptimization(new CommandCompilerPass());
    }
}

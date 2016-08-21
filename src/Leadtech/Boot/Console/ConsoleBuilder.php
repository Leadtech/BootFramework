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

        // A compiler pass needed to correctly load the registered console commands.
        // It may be confusing to be confronted with compiler passes.
        // Offer this console builder and add the compiler pass behind the scenes.
        $this->beforeOptimization(new CommandCompilerPass());
    }
}

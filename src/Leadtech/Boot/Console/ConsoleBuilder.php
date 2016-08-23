<?php

namespace Boot\Console;

use Boot\Builder;
use Boot\Console\CompilerPass\CommandCompilerPass;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConsoleBuilder.
 */
class ConsoleBuilder extends Builder
{
    /** @var  string */
    private $consoleServiceIdentifier = 'console';

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
        $this->beforeOptimization(new CommandCompilerPass($this->getConsoleServiceIdentifier()));
    }

    /**
     * The ID of the console service in the service container.
     *
     * @param string $serviceIdentifier
     *
     * @return $this
     */
    public function consoleServiceIdentifier($serviceIdentifier)
    {
        $this->consoleServiceIdentifier = $serviceIdentifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getConsoleServiceIdentifier()
    {
        return $this->consoleServiceIdentifier;
    }

    /**
     * @return Application|ContainerInterface
     */
    public function build()
    {
        // Build service container
        $serviceContainer = parent::build();

        // Create console application. The application decorates the container instance only to encapsulate the code
        // that is needed to run the console component.
        return new Application(
            $serviceContainer,
            $this->getConsoleServiceIdentifier()
        );
    }
}

<?php

namespace Boot\Console;

use Boot\Builder;
use Boot\Console\CompilerPass\ConsoleCompilerPass;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConsoleBuilder.
 */
class ConsoleBuilder extends Builder
{
    /** @var  string */
    private $consoleServiceIdentifier;

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
        // A compiler pass used to dynamically load the registered console commands using the console_command tag.
        $this->beforeOptimization(new ConsoleCompilerPass(
            $this->getConsoleServiceIdentifier(),
            $this->getAppName(),
            $this->getAppVersion()
        ));

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

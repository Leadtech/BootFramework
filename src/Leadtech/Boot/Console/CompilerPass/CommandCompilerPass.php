<?php

namespace Boot\Console\CompilerPass;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CommandCompilerPass.
 *
 * Automatically register tagged console commands to the console service.
 *
 * @author  Daan Biesterbos <daan@leadtech.nl>
 */
class CommandCompilerPass implements CompilerPassInterface
{
    /** @var  string */
    private $serviceIdentifiers;

    /**
     * CommandCompilerPass constructor.
     *
     * @param string $serviceIdentifier The console service identifier, by default 'console'
     */
    public function __construct($serviceIdentifier)
    {
        $this->serviceIdentifiers = $serviceIdentifier;
    }

    /**
     * @source 7 10  Look for tagged commands and add commands to console.
     *
     * @param ContainerBuilder $container Symfony2 container builder
     *
     * @throws RuntimeException Thrown while processing a missing or invalid console command
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->serviceIdentifiers)) {
            throw new RuntimeException("The `{$this->serviceIdentifiers}` service is not registered.");
        }

        /** @var Application $console */
        $console = $container->get($this->serviceIdentifiers);
        $taggedServices = $container->findTaggedServiceIds('console_command');
        foreach ($taggedServices as $commandId => $attributes) {

            // Get command
            $command = $container->get($commandId);
            if (!$command instanceof Command) {
                throw new RuntimeException(
                    "Invalid service (id: `$commandId`). Only instances of ".Command::class.' '.
                    'can be tagged as console command.'
                );
            }

            // Add command to console when the container is not cached.
            // It seems like this compiler pass is never used in a cached container.
            // The solution below solves that problem. We'll still add the command
            // to the console here because the solution below seems to work only if we
            // load the container from cache. And we want this compiler pass to work in
            // both scenarios.
            $console->add($command);

            // Add static/cachable definition that works within a compiled container.
            if ($container->hasDefinition($this->serviceIdentifiers)) {
                $definition = $container->findDefinition($this->serviceIdentifiers);
                $definition->addMethodCall(
                    'add',
                    [new Reference($commandId)]
                );
            }
        }
    }
}

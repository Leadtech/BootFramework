<?php

namespace Boot\Console\CompilerPass;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use RuntimeException;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CommandCompilerPass.
 *
 * Automatically register tagged console commands to the console service.
 *
 * @author  Daan Biesterbos <daan@leadtech.nl>
 */
class ConsoleCompilerPass implements CompilerPassInterface
{
    /** @var  string */
    private $serviceIdentifier;

    /** @var string  */
    private $appName;

    /** @var string  */
    private $appVersion;

    /**
     * CommandCompilerPass constructor.
     *
     * @param string $serviceIdentifier The console service identifier, by default 'console'
     * @param string $appName           The application name, by default 'UNKNOWN'
     * @param string $appVersion        A version number, by default 'UNKNOWN'
     */
    public function __construct($serviceIdentifier, $appName, $appVersion)
    {
        $this->serviceIdentifier = $serviceIdentifier;
        $this->appName = $appName;
        $this->appVersion = $appVersion;
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
        // Create or update console service definition
        $this->createOrUpdateServiceDefinition($container);

        // Get the console and verify type
        $console = $this->verifyAndGetConsoleService($container, $this->serviceIdentifier);

        // Find services with the 'console_command' tag
        $taggedServices = $container->findTaggedServiceIds('console_command');

        // Iterate the console commands and add them to the console component
        foreach ($taggedServices as $commandServiceId => $attributes) {

            // Get command
            $command = $container->get($commandServiceId);

            // Add the command to the console instance
            $console->add($command);

            // Add the definition to add the commands in the compiled service container as well.
            if ($container->hasDefinition($this->serviceIdentifier)) {
                $definition = $container->findDefinition($this->serviceIdentifier);
                $definition->addMethodCall('add', [new Reference($commandServiceId)]);
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function createOrUpdateServiceDefinition(ContainerBuilder $container)
    {
        // First check if the console service is already registered.
        // This code will sure to give preference to user registered services.
        if ($container->hasDefinition($this->serviceIdentifier)) {
            $consoleDefinition = $container->getDefinition($this->serviceIdentifier);

            // Set name only if not set already
            $methodDefinitions = [];
            foreach ($consoleDefinition->getMethodCalls() as $methodCall) {
                $methodDefinitions[$methodCall[0]] = $methodCall[1];
            }

            // Add definition for setName() if needed
            if (!isset($methodDefinitions['setName']) || $methodDefinitions['setName'][0] == 'UNKNOWN') {
                $consoleDefinition->addMethodCall('setName', [$this->appName]);
            }

            // Set version only if not set already
            if (!isset($methodDefinitions['setVersion']) || $methodDefinitions['setVersion'][0] == 'UNKNOWN') {
                $consoleDefinition->addMethodCall('setVersion', [$this->appVersion]);
            }

        } else {
            // Create new definition
            $consoleDefinition = new Definition(Application::class);
            $consoleDefinition->addMethodCall('setName', [$this->appName]);
            $consoleDefinition->addMethodCall('setVersion', [$this->appVersion]);
            $container->addDefinitions([
                $this->serviceIdentifier => $consoleDefinition,
            ]);

            // Update the current service instance, or the service from the compiled service container would be
            // different from the uncached version.
            $console = $container->get($this->serviceIdentifier);
            if (empty($console->getName()) || $console->getName() == 'UNKNOWN') {
                $console->setName($this->appName);
            }
            if (empty($console->getVersion()) || $console->getVersion() == 'UNKNOWN') {
                $console->setVersion($this->appVersion);
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param $serviceId
     *
     * @return object
     *
     * @throws \Throwable
     */
    private function verifyAndGetConsoleService(ContainerBuilder $container, $serviceId)
    {
        $console = $container->get($serviceId);
        if (!$console instanceof Application) {
            throw new \LogicException(
                'Program logic flow exception! '.
                "The service id {$serviceId} is reserved for an instance of ".Application::class
            );
        }

        return $console;
    }
}

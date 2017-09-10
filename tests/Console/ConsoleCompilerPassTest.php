<?php

namespace Boot\Tests\Console;

use Boot\Console\CompilerPass\ConsoleCompilerPass;
use Boot\Tests\AbstractTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ConsoleCompilerPassTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function verifyNewServiceContext()
    {
        $builder = new ContainerBuilder();

        // Add command definition
        $definition = new Definition('Symfony\Component\Console\Command\HelpCommand', ['help-command']);
        $definition->addTag('console_command');
        $builder->addDefinitions([$definition]);

        $compilerPass = new ConsoleCompilerPass('console', 'test-app', '1.0');
        $compilerPass->process($builder);

        $refl = new \ReflectionMethod($compilerPass, 'verifyAndGetConsoleService');
        $refl->setAccessible(true);
        $console = $refl->invoke($compilerPass, $builder, 'console');

        $this->assertInstanceOf(Application::class, $console);
        $this->assertEquals('test-app', $console->getName());

        $compilerPass->process($builder);
    }

    /**
     * @test
     */
    public function throwsExceptionWhenConsoleServiceNotValid()
    {
        $commandCompiler = new ConsoleCompilerPass('consoleServiceId', 'myApp', '1.2.3');

        // Create container
        $container = new ContainerBuilder();
        $container->set('consoleServiceId', new \stdClass());

        // Create reflection method
        $refl = new \ReflectionMethod($commandCompiler, 'verifyAndGetConsoleService');
        $refl->setAccessible(true);

        // Define the expected exception and trigger the method that should throw the exception
        $this->expectException(\LogicException::class);
        $refl->invoke($commandCompiler, $container, 'consoleServiceId');
    }

    /**
     * @test
     */
    public function verifyUpdatedServiceContext()
    {
        $builder = new ContainerBuilder();

        $builder->set('console', new Application('initial-app', '1.0'));
        $console1 = $builder->get('console');
        $this->assertEquals('initial-app', $console1->getName());

        $compilerPass = new ConsoleCompilerPass('console', 'test-app', '3.0');
        $compilerPass->process($builder);

        /* @var Application $console */
        $console2 = $builder->get('console');

        // The app name and version of the initial service should be reused when explicitly defined...
        $this->assertEquals('initial-app', $console2->getName());
        $this->assertEquals('1.0', $console2->getVersion());

        // The console service after processing the compiler pass should be the same as the initial instance.
        $this->assertSame($console1, $console2);
    }

    /**
     * @test
     */
    public function replaceDefaultAppNameAndVersionValues()
    {
        $builder = new ContainerBuilder();

        // Add command definition
        $definition = new Definition('Symfony\Component\Console\Command\HelpCommand', ['help-command']);
        $definition->addTag('console_command');
        $builder->addDefinitions([$definition]);

        $builder = new ContainerBuilder();

        $builder->set('console', new Application());
        $console1 = $builder->get('console');
        $this->assertEquals('UNKNOWN', $console1->getName());
        $this->assertEquals('UNKNOWN', $console1->getVersion());

        $compilerPass = new ConsoleCompilerPass('console', 'initial-app', '1.0');
        $compilerPass->process($builder);

        $refl = new \ReflectionMethod($compilerPass, 'verifyAndGetConsoleService');
        $refl->setAccessible(true);
        $console2 = $refl->invoke($compilerPass, $builder, 'console');

        $this->assertInstanceOf(Application::class, $console2);

        // The app name and version were undefined initially and the command compiler pass should have set the app
        // and version rather than reusing the 'UNKNOWN' values.
        $this->assertEquals('initial-app', $console2->getName());
        $this->assertEquals('1.0', $console2->getVersion());
    }
}

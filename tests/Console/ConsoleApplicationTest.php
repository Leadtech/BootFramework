<?php

namespace Boot\Tests\Console;

use Boot\Console\Application;
use Boot\Tests\AbstractTestCase;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Tests\Input\InputDefinitionTest;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConsoleApplicationTest
 *
 * @package Boot\Tests\Console
 */
class ConsoleApplicationTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function runApplication()
    {
        $mock = $this->createMock(InputDefinitionTest::class);
        $definition = $this->getMock(InputDefinitionTest::class, ['addOption'], [], '', false, false);

        $definition
            ->expects($this->once())
            ->method('addOption');

        $console = $this->getMockBuilder(ConsoleApplication::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->setMethods(['getDefinition', 'run'])
            ->getMock();

        $console
            ->expects($this->once())
            ->method('getDefinition')
            ->willReturn($definition);

        $console
            ->expects($this->once())
            ->method('run');

        $serviceContainer = $this->getMockBuilder(ContainerInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();

        $serviceContainer
            ->expects($this->once())
            ->method('get')
            ->with('consoleServiceId')
            ->willReturn($console);

        $app = new Application($serviceContainer, 'consoleServiceId');
        $app->run();
    }
}
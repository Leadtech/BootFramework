<?php

namespace Boot\Tests\Console;

use Boot\Console\Application;
use Boot\Console\ConsoleBuilder;
use Boot\Tests\AbstractTestCase;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ConsoleBuilderTest
 *
 * @package Boot\Tests\Console
 */
class ConsoleBuilderTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function verifyBuild()
    {
        $builder = new ConsoleBuilder(__DIR__);

        $builder->consoleServiceIdentifier('consoleServiceId');

        $this->assertEquals('consoleServiceId', $builder->getConsoleServiceIdentifier());

        // Verify that there are no compiler passes prior to invoking the build method
        $this->assertCount(0, $builder->getCompilerPasses());

        $result = $builder->build();

        $this->assertCount(1, $builder->getCompilerPasses(), 'The ConsoleCompilerPass should have been added!');

        $this->assertInstanceOf(Application::class, $result);

        $this->assertInstanceOf(ConsoleApplication::class, $result->get('consoleServiceId'));
    }
}
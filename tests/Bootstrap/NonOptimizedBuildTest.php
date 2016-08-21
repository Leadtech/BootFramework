<?php

namespace Boot\Tests\Bootstrap;

use Boot\Builder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BuilderTest.
 *
 * @license MIT
 */
class NonOptimizedBuildTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ContainerInterface */
    private $container;

    public function setUp()
    {
        $builder = new Builder(__DIR__.'/../Assets/Bootstrap/BuilderTest');

        $this->container = $builder
            ->appName('test1')
            ->environment('dev')
            ->configDirs([
                'src/ModuleA/Resources',
                'src/ModuleB/Resources',
                realpath(__DIR__.'/../Assets/Bootstrap/BuilderTest/src/ModuleC/src/Resources'),
            ])
            ->build();
    }

    /**
     * Test the application bootstrap in dev mode. The tests may look like the exact same thing as the previous test
     * for the production environment, none the less, due to for example optimizations in prod mode it is possible that
     * the two environments do not always execute the same code. With this test I intent to ensure that the tests work
     * in both circumstances.
     *
     * @test
     */
    public function developmentBootstrap()
    {
        $container = $this->container;

        $this->assertEquals(1, $container->getParameter('module_a_parameter'));
        $this->assertEquals(2, $container->getParameter('module_b_parameter'));
        $this->assertEquals(3, $container->getParameter('module_c_parameter'));

        $this->assertInstanceOf('DateTime', $container->get('module_a_global_service'));
        $this->assertInstanceOf('DateTime', $container->get('module_b_global_service'));
        $this->assertInstanceOf('DateTime', $container->get('module_c_global_service'));

        $this->assertInstanceOf('DateTime', $container->get('module_a_dev_service'));
        $this->assertInstanceOf('DateTime', $container->get('module_b_dev_service'));
        $this->assertInstanceOf('DateTime', $container->get('module_c_dev_service'));
    }
}

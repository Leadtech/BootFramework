<?php

namespace Boot\Tests\Bootstrap;

use Boot\Builder;
use Symfony\Component\ClassLoader\Psr4ClassLoader;

/**
 * Class BuilderTest.
 *
 * @license MIT
 */
class AppBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function productionBootstrap()
    {
        // Just for demo purposes, auto loading could be moved to composer config
        $loader = new Psr4ClassLoader();
        $loader->addPrefix('Boot\\', __DIR__.'/../src/Services');
        $loader->register();

        $container = (new Builder(__DIR__.'/../Assets/Bootstrap/BuilderTest'))
            ->appName('test1')
            ->caching('cache', false)
            ->environment('prod')
            ->parameter('extra_param_1', 123)
            ->parameter('extra_param_2', 1234)
            ->configDir('src/ModuleA/Resources')
            ->configDir('src/ModuleB/Resources')
            ->configDir('src/ModuleC/src/Resources')
            ->build();

        $this->assertEquals(1, $container->getParameter('module_a_parameter'));
        $this->assertEquals(2, $container->getParameter('module_b_parameter'));
        $this->assertEquals(3, $container->getParameter('module_c_parameter'));

        $this->assertInstanceOf('DateTime', $container->get('module_a_global_service'));
        $this->assertInstanceOf('DateTime', $container->get('module_b_global_service'));
        $this->assertInstanceOf('DateTime', $container->get('module_c_global_service'));
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
        $container = (new Builder(__DIR__.'/../Assets/Bootstrap/BuilderTest'))
            ->appName('test1')
            ->caching('cache', false)
            ->environment('dev')
            ->configDirs([
                'src/ModuleA/Resources',
                'src/ModuleB/Resources',
                realpath(__DIR__.'/../Assets/Bootstrap/BuilderTest/src/ModuleC/src/Resources'),
            ])
            ->build();

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

    /**
     * @test
     */
    public function failWhenAppNameNonAlphaNumeric()
    {
        $this->setExpectedException('InvalidArgumentException');
        (new Builder(__DIR__.'/../Assets/Bootstrap/BuilderTest'))->appName('#invalid.');
    }
}

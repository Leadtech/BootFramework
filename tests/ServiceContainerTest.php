<?php
namespace Boot;

/**
 * Class ServiceContainerTest
 * @package Boot
 * @license MIT
 */

class ServiceContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function buildProductionContainer()
    {
        $container = (new Builder(__DIR__ . '/Assets/SomeProject'))
            ->appName('test1')
            ->caching('cache', false)
            ->environment('prod')
            ->parameter('extra_param_1', 123)
            ->parameter('extra_param_2', 1234)
            ->path('src/ModuleA/Resources')
            ->path('src/ModuleB/Resources')
            ->path('src/ModuleC/src/Resources')
            ->build();

        $this->assertEquals(1, $container->getParameter('module_a_parameter'));
        $this->assertEquals(2, $container->getParameter('module_b_parameter'));
        $this->assertEquals(3, $container->getParameter('module_c_parameter'));

        $this->assertInstanceOf('DateTime', $container->get('module_a_global_service'));
        $this->assertInstanceOf('DateTime', $container->get('module_b_global_service'));
        $this->assertInstanceOf('DateTime', $container->get('module_c_global_service'));
    }


    /**
     * @test
     */
    public function buildDevelopmentContainer()
    {
        $container = (new Builder(__DIR__ . '/Assets/SomeProject'))
            ->appName('test1')
            ->caching('cache', false)
            ->environment('dev')
            ->paths([
                'src/ModuleA/Resources',
                'src/ModuleB/Resources',
                realpath(__DIR__ . '/Assets/SomeProject/src/ModuleC/src/Resources'),
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
    public function appNameMustBeAlphaNumeric()
    {
        $this->setExpectedException('InvalidArgumentException');
        (new Builder(__DIR__ . '/Assets/SomeProject'))->appName('#invalid.');
    }
}
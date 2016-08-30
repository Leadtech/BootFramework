<?php

namespace Boot\Tests\Bootstrap;

use Boot\Boot;
use Boot\Builder;
use Boot\IO\FileUtils;
use Symfony\Component\DependencyInjection\Compiler\ExtensionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ExpressionLanguageProvider;

/**
 * Class BuilderTest.
 *
 * @license MIT
 */
class OptimizedBuildTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function productionBootstrap()
    {
        // Create builder
        $builder = $this->createBuilder();

        // Make sure no cache files exist
        FileUtils::truncateFolder($builder->getCompiledClassDir(), false);

        // Bootstrap the application
        $container = $builder->build();

        // Should be isntance of ContainerBuilder, is now cached so next time we do this we get a compiled container.
        $this->assertInstanceOf(ContainerBuilder::class, $container);

        // Create the same builder
        $builder = $this->createBuilder();

        // Bootstrap again
        $cachedContainer = $builder->build();

        // Should be the compiled variant
        $this->assertInstanceOf('CompiledTest1Prod', $cachedContainer);

        $this->assertEquals(1, $container->getParameter('module_a_parameter'));
        $this->assertEquals(1, $cachedContainer->getParameter('module_a_parameter'));
        $this->assertEquals(2, $container->getParameter('module_b_parameter'));
        $this->assertEquals(2, $cachedContainer->getParameter('module_b_parameter'));
        $this->assertEquals(3, $container->getParameter('module_c_parameter'));
        $this->assertEquals(3, $cachedContainer->getParameter('module_c_parameter'));

        $this->assertInstanceOf('DateTime', $container->get('module_a_global_service'));
        $this->assertInstanceOf('DateTime', $cachedContainer->get('module_a_global_service'));
        $this->assertInstanceOf('DateTime', $container->get('module_b_global_service'));
        $this->assertInstanceOf('DateTime', $cachedContainer->get('module_b_global_service'));
        $this->assertInstanceOf('DateTime', $container->get('module_c_global_service'));
        $this->assertInstanceOf('DateTime', $cachedContainer->get('module_c_global_service'));

        FileUtils::truncateFolder($builder->getCompiledClassDir(), true);
    }

    /**
     * @return Builder
     */
    protected function createBuilder()
    {
        $builder = new Builder(__DIR__.'/../Assets/Bootstrap/BuilderTestProd');
        $builder
            ->appName('test1')
            ->optimize('cache')
            ->environment(Boot::PRODUCTION)
            ->parameter('extra_param_1', 123)
            ->parameter('extra_param_2', 1234)
            ->configDir('src/ModuleA/Resources')
            ->configDir('src/ModuleB/Resources')
            ->configDir('src/ModuleC/src/Resources')
            ->expr(new ExpressionLanguageProvider())
            ->onRemoving(new ExtensionCompilerPass())
        ;

        return $builder;
    }
}

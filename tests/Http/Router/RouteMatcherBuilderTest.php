<?php

namespace Boot\Tests\Http\Router;

use Boot\Http\Router\RouteMatcherBuilder;
use Boot\Tests\AbstractTestCase;
use Symfony\Component\DependencyInjection\ExpressionLanguageProvider;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class RouteMatcherBuilderTest.
 */
class RouteMatcherBuilderTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function setAndGetExpressionProviders()
    {
        $builder = new RouteMatcherBuilder();
        $builder->addExpressionLanguageProvider(new ExpressionLanguageProvider());
        $providers = $builder->getExpressionLanguageProviders();
        $this->assertCount(1, $providers);
    }

    /**
     * @test
     */
    public function throwExceptionWhenDirNotExistsAndNotCreated()
    {
        $this->expectException(\InvalidArgumentException::class);
        $builder = new RouteMatcherBuilder();
        $builder->optimize('/w00t/non-existing-path/', false);
    }

    /**
     * @test  test ensures that the mkdir method is invoked and that the optimize method will not catch the exception.
     */
    public function throwExceptionWhenCreateDirFails()
    {
        $this->expectException(IOException::class);
        $builder = new RouteMatcherBuilder();

        $fs = $this->getMock(Filesystem::class, ['mkdir']);
        $fs->expects($this->once())->method('mkdir')->willThrowException(new IOException("dir not created"));

        $builder->setFileSystem($fs);
        $builder->optimize('w00t/non-existing-path/');
    }
}

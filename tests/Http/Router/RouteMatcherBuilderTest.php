<?php

namespace Boot\Tests\Http\Router;

use Boot\Http\Router\RouteMatcherBuilder;
use Symfony\Component\DependencyInjection\ExpressionLanguageProvider;

/**
 * Class RouteMatcherBuilderTest.
 */
class RouteMatcherBuilderTest extends \PHPUnit_Framework_TestCase
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
}

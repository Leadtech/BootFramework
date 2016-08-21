<?php

namespace Boot\Tests\Http;

use Boot\Boot;
use Boot\Http\WebBuilder;

/**
 * Class WebBuilderTest.
 *
 * Runs the AbstractWebBuilderTest in production mode.
 */
class WebBuilderProductionTest extends AbstractWebBuilderTest
{
    /**
     *
     */
    public function setUp()
    {
        // Execute once, first execution will resolve the container and will compile
        // the container.
        parent::setUp();

        // The next call will actually load the compiled version
        parent::setUp();
    }

    /**
     * @return WebBuilder
     */
    public function createBuilder()
    {
        return (new WebBuilder(__DIR__.'/../Assets/Bootstrap/BuilderTestProd'))
            ->appName('test1')
            ->optimize('cache')
            ->environment(Boot::PRODUCTION);
    }


}

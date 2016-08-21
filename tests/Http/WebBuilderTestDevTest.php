<?php

namespace Boot\Tests\Http;

use Boot\Boot;
use Boot\Http\WebBuilder;

/**
 * Class WebBuilderTest.
 *
 * Runs the AbstractWebBuilderTest in dev mode.
 */
class WebBuilderTestDevTest extends AbstractWebBuilderTest
{
    /**
     * @return WebBuilder
     */
    public function createBuilder()
    {
        return (new WebBuilder(__DIR__.'/../Assets/Bootstrap/BuilderTest'))
            ->appName('test1')
            ->environment(Boot::DEVELOPMENT);
    }
}

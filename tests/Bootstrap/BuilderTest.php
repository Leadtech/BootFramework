<?php

namespace Boot\Tests\Bootstrap;

use Boot\Builder;

/**
 * Class BuilderTest.
 *
 * @license MIT
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function throwExceptionWhenAppNameNonAlphaNumeric()
    {
        $this->setExpectedException('InvalidArgumentException');
        (new Builder(__DIR__.'/../Assets/Bootstrap/BuilderTest'))->appName('#invalid.');
    }

    /**
     * @test
     */
    public function throwExceptionWhenConfigDirNotExists()
    {
        $builder = new Builder(__DIR__);
        $builder->configDir('w00t/rofl');
        $this->setExpectedException(\InvalidArgumentException::class);
        $builder->build();
    }
}

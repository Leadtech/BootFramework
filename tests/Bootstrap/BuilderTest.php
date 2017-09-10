<?php

namespace Boot\Tests\Bootstrap;

use Boot\Builder;
use Boot\Tests\AbstractTestCase;

/**
 * Class BuilderTest.
 *
 * @license MIT
 */
class BuilderTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function throwExceptionWhenAppNameNonAlphaNumeric()
    {
        $this->expectException('InvalidArgumentException');
        (new Builder(__DIR__.'/../Assets/Bootstrap/BuilderTest'))->appName('#invalid.');
    }

    /**
     * @test
     */
    public function throwExceptionWhenConfigDirNotExists()
    {
        $builder = new Builder(__DIR__);
        $builder->configDir('w00t/rofl');
        $this->expectException(\InvalidArgumentException::class);
        $builder->build();
    }
}

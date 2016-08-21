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
    public function failWhenAppNameNonAlphaNumeric()
    {
        $this->setExpectedException('InvalidArgumentException');
        (new Builder(__DIR__.'/../Assets/Bootstrap/BuilderTest'))->appName('#invalid.');
    }
}

<?php

namespace Boot\Tests\Http\Router;

use Boot\Http\Router\RouteOptions;

/**
 * Class RouteOptionsTest.
 *
 * This feels like a rather dumb test since all we are going to test is the constructor, getters and setters.
 * But to get a good code coverage I need to do it.
 */
class RouteOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function gettingAndSettingValues()
    {
        $defaults = ['foo' => 'bar'];
        $requirements = ['foo' => 'bar|foo|foobar'];
        $routeOptions = new RouteOptions('routename', $defaults, $requirements);

        $this->assertEquals('routename', $routeOptions->getRouteName());
        $this->assertEquals($requirements, $routeOptions->getRequirements());
        $this->assertEquals($defaults, $routeOptions->getDefaults());

        $routeOptions->setDefaults(null);
        $this->assertNull($routeOptions->getDefaults());

        $routeOptions->setRequirements(null);
        $this->assertNull($routeOptions->getRequirements());

        $routeOptions->setRouteName(null);
        $this->assertNull($routeOptions->getRouteName());
    }
}

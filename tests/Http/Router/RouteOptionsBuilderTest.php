<?php

namespace Boot\Tests\Http\Router;

use Boot\Http\Router\RouteOptions;
use Boot\Http\Router\RouteOptionsBuilder;
use Boot\Http\Security\RemoteAccessPolicy;
use Boot\Tests\AbstractTestCase;

/**
 * Class RouteOptionsBuilderTest
 *
 * @package Boot\Tests\Http\Router
 */
class RouteOptionsBuilderTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function verifyResult()
    {
        $result = (new RouteOptionsBuilder)
            ->routeName('some-route')
            ->defaults(['country' => 'NL'])
            ->requirements(['country' => 'NL|US|FR|DL'])
            ->remoteAccessPolicy(RemoteAccessPolicy::forPrivateService())
            ->condition("request.headers.get('User-Agent') matches '/firefox/i'")
            ->build()
        ;

        $this->assertInstanceOf(RouteOptions::class, $result);
        $this->assertEquals('some-route', $result->getRouteName());
        $this->assertEquals(['country' => 'NL'], $result->getDefaults());
        $this->assertEquals("request.headers.get('User-Agent') matches '/firefox/i'", $result->getExpression());
        $this->assertEquals(['country' => 'NL|US|FR|DL'], $result->getRequirements());
        $this->assertEquals(RemoteAccessPolicy::forPrivateService(), $result->getRemoteAccessPolicy());
    }
}
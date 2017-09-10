<?php

namespace Boot\Tests\Http\Router;

use Boot\Boot;
use Boot\Http\Application;
use Boot\Http\Router\RouteOptionsBuilder;
use Boot\Http\WebBuilder;
use Boot\Tests\AbstractTestCase;
use Boot\Tests\Assets\Http\FooService;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RouteMatcherBuilderTest.
 */
class RouteExpressionTest extends AbstractTestCase
{
    /**
     * @test  this test verifies that the service is called, FooService::returnString() will return foobar.
     */
    public function userAgentMatchesFirefox()
    {
        $this->expectOutputString('foobar');
        $app = $this->createApplication();
        $request = Request::create('/some-route');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Android 4.4; Mobile; rv:41.0) Gecko/41.0 Firefox/41.0');
        $app->run($request);
    }

    /**
     * @test  this test verifies that the service is not called when the header does not match the provided expression
     */
    public function userAgentDoesNotMatchFirefox()
    {
        // Actually, an http error response is send.
        // But this is a simpler way to confirm that the service was not invoked , a bit highover but evenly accurate.
        $this->expectOutputString('');
        $app = $this->createApplication();
        $request = Request::create('/some-route');
        $request->headers->set('User-Agent', 'I don\'t need a browser. I prefer curl. Grab and grep baby!');
        $app->run($request);
        $this->assertTrue(headers_sent());
    }

    /**
     * @return Application
     */
    private function createApplication()
    {
        return (new WebBuilder(__DIR__.'/../../Assets/Bootstrap/BuilderTest'))
            ->appName('test1')
            ->environment(Boot::DEVELOPMENT)
            ->get('some-route', FooService::class, 'returnString', (new RouteOptionsBuilder)
                ->routeName('some-route-name')
                ->condition("request.headers.get('User-Agent') matches '/firefox/i'")
                ->build()
            )
            ->build()
        ;
    }
}
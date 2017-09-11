<?php

namespace Boot\Tests\Http\Router;

use Boot\Http\Exception\ServiceClassNotFoundException;
use Boot\Http\Exception\ServiceLogicException;
use Boot\Http\Exception\ServiceMethodNotFoundException;
use Boot\Http\Router\RouteMatch;
use Boot\Tests\AbstractTestCase;
use Boot\Tests\Assets\Http\FooService;
use Symfony\Component\HttpFoundation\Request;

class RouteMatchTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function throwsExceptionWhenServiceNotFound()
    {
        $this->expectException(ServiceClassNotFoundException::class);
        $routeMatch = new RouteMatch([
            '_serviceClass'  => 'FooBar123',
            '_serviceMethod' => null
        ]);

        try {
            $routeMatch->validate();
        } catch(ServiceClassNotFoundException $e) {
            $this->assertEquals('FooBar123', $e->getClassName());
            throw $e;
        }
    }

    /**
     * @test
     */
    public function throwsExceptionWhenMethodNotFound()
    {
        $this->expectException(ServiceMethodNotFoundException::class);
        $routeMatch = new RouteMatch([
            '_serviceClass'  => FooService::class,
            '_serviceMethod' => 'someNonExistentMethod'
        ]);

        try {
            $routeMatch->validate();
        } catch(ServiceMethodNotFoundException $e) {
            $this->assertEquals(FooService::class, $e->getClassName());
            $this->assertEquals('someNonExistentMethod', $e->getMethodName());
            throw $e;
        }
    }

    /**
     * @test
     */
    public function throwsLogicExceptionForInvalidProgramFlow()
    {
        $this->expectException(ServiceLogicException::class);
        $routeMatch = new RouteMatch([
            '_serviceClass'  => get_class($this), // Programming error, not a service!
            '_serviceMethod' => null
        ]);
        $routeMatch->validate();
    }

    /**
     * @test
     */
    public function denyAccessPublicIpRanges()
    {
        $request = new Request();
        $request->server->set('REMOTE_ADDR', gethostbyname('example.com'));

       $routeMatch = new RouteMatch([
           '_publicIpRangesDenied' => true
       ]);

        $this->assertFalse($routeMatch->verifyClient($request), 'Should not grant access to public ip\'s');
        $request->server->set('REMOTE_ADDR', '192.168.0.10');
        $this->assertTrue($routeMatch->verifyClient($request), 'Should not block private ip.');
    }

    /**
     * @test
     */
    public function denyAccessPrivateIpRanges()
    {
        $request = new Request();
        $request->server->set('REMOTE_ADDR', '192.168.0.10');
        $routeMatch = new RouteMatch(['_privateIpRangesDenied' => true]);

        $this->assertFalse($routeMatch->verifyClient($request), 'Should not grant access to private ip\'s');

        $publicIp = gethostbyname('example.com');
        $request->server->set('REMOTE_ADDR', $publicIp);
        $this->assertTrue($routeMatch->verifyClient($request), 'Should not block public IP!');

        $routeMatch = new RouteMatch(['_privateIpRangesDenied' => true, '_publicIpRangesDenied' => true]);
        $request->server->set('REMOTE_ADDR', $publicIp);
        $this->assertFalse($routeMatch->verifyClient($request), 'Should block public IP as well!');
    }

    /**
     * @test
     */
    public function denyAccessReservedIpRanges()
    {
        $request = new Request();
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $routeMatch = new RouteMatch(['_reservedIpRangesDenied' => true]);
        $this->assertFalse($routeMatch->verifyClient($request), 'Should not grant access to reserved ip ranges');
    }

    /**
     * @test
     */
    public function denyAccessBlacklistedIpv4Range()
    {
        $request = Request::createFromGlobals();
        $routeMatch = new RouteMatch(['_blacklistIps' => [$request->getClientIp()]]);
        $this->assertFalse($routeMatch->verifyClient($request), 'Should not grant access to blacklisted IP\'s');
    }

    /**
     * @test
     */
    public function denyAccessBlackListedIpv6Address()
    {
        $request = new Request();
        $request->server->set('REMOTE_ADDR', '0:0:0:0:0:ffff:5596:4c33');
        $routeMatch = new RouteMatch(['_blacklistIps' => ['0:0:0:0:0:ffff:5596:4c33']]);
        $this->assertFalse($routeMatch->verifyClient($request), 'Should not grant access to blacklisted IP\'s');
    }

    /**
     * @test
     */
    public function denyAccessVBlackListedHost()
    {
        $request = Request::createFromGlobals();
        $request->headers->set('HOST', 'foo.example.com');
        $routeMatch['_blacklistHosts'][] = 'example.com';
        $routeMatch = new RouteMatch(['_blacklistHosts' => ['example.com']]);
        $this->assertFalse($routeMatch->verifyClient($request), 'Should not grant access to blacklisted host.');
    }

    /**
     * @test
     */
    public function allowAccessWhiteListedIpv4Range()
    {
        $request = new Request();
        $request->server->set('REMOTE_ADDR', '192.168.0.10');
        $routeMatch = new RouteMatch(['_privateIpRangesDenied' => true]);

        // First call without whitelisted ip range, should return false
        $this->assertFalse($routeMatch->verifyClient($request), 'Should not grant access to private ip\'s');

        // Should accept wildcards
        $routeMatch = new RouteMatch([
            '_privateIpRangesDenied' => true,
            '_whitelistIps' => ['192.168.*.*']
        ]);
        $this->assertTrue($routeMatch->verifyClient($request), 'Should grant access to IP addresses with or without wildcards');

        // Should accept start-end ranges
        $routeMatch = new RouteMatch([
            '_privateIpRangesDenied' => true,
            '_whitelistIps' => ['192.168.0.09-192.168.0.11']
        ]);
        $this->assertTrue($routeMatch->verifyClient($request), 'Should grant access to IP ranges in start-end format');
    }

    /**
     * @test
     */
    public function allowAccessWhiteListedIpv6Address()
    {
        $request = new Request();
        $request->server->set('REMOTE_ADDR', '0:0:0:0:0:ffff:5596:4c33');
        $routeMatch['_publicIpRangesDenied'] = true;
        $routeMatch = new RouteMatch(['_publicIpRangesDenied' => true]);

        // First call without whitelisted ip range, should return false
        $this->assertFalse($routeMatch->verifyClient($request), 'Should not grant access to private ip\'s');

        // Should accept ipv6 address when whitelisted
        $routeMatch = new RouteMatch([
            '_publicIpRangesDenied' => true,
            '_whitelistIps' => ['0:0:0:0:0:ffff:5596:4c33']
        ]);
        $this->assertTrue($routeMatch->verifyClient($request), 'Should grant access to IP addresses with or without wildcards');
    }

    /**
     * @test
     */
    public function allowAccessWhiteListedHost()
    {
        $request = Request::createFromGlobals();
        $request->headers->set('HOST', 'foo.example.com');
        $request->server->set('REMOTE_ADDR', '192.168.0.10');

        $routeMatch = new RouteMatch(['_privateIpRangesDenied' => true]);

        $this->assertFalse($routeMatch->verifyClient($request), 'Should not grant access to private IP addresses.');

        // Should accept when host is whitelisted
        $routeMatch = new RouteMatch([
            '_privateIpRangesDenied' => true,
            '_whitelistHosts' => ['example.com']
        ]);

        $this->assertTrue($routeMatch->verifyClient($request), 'Should grant access to white listed host.');
    }

}
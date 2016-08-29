<?php

namespace Boot\Tests\Http;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Boot\Builder;
use Boot\Exception\IncompatibleComponentException;
use Boot\Http\Exception\ServiceClassNotFoundException;
use Boot\Http\Exception\ServiceLogicException;
use Boot\Http\Exception\ServiceMethodNotFoundException;
use Boot\Http\Router\RouteOptions;
use Boot\Http\Service\Validator\ServiceValidator;
use Boot\Http\HttpServiceInitializer;
use Boot\Http\WebBuilder;
use Boot\Tests\Assets\Http\FooService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * Class ServiceDispatcherTest.
 */
class HttpServiceInitializerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ContainerInterface */
    protected $boot;

    /**
     * Set up the unit test.
     */
    public function setUp()
    {
        $this->boot = (new WebBuilder(__DIR__.'/../Assets/Bootstrap/BuilderTest'))
            ->appName('test1')
            ->environment('prod')

            // Will return an array
            ->get('array', FooService::class, 'returnArray', new RouteOptions(
                'array-test'
            ))

            // Will return instance of JsonSerialize
            ->patch('return-json', FooService::class, 'returnJsonSerializable', new RouteOptions(
                'json-test'
            ))

            // Will return instance of Response
            ->put('return-object', FooService::class, 'returnResponseObject', new RouteOptions(
                'response-object-test'
            ))

            // Will return instance of JsonResponse
            ->delete('return-json-object', FooService::class, 'returnJsonResponseObject', new RouteOptions(
                'json-response-object-test'
            ))

            // Will return a string
            ->post('return-string', FooService::class, 'returnString', new RouteOptions(
                'string-test'
            ))

            ->baseUrl('foo/')

            ->build()
        ;
    }

    /**
     * @test
     */
    public function serviceReturnsResponseObject()
    {
        $this->expectOutputString('blaat');
        $this->boot->get('http')->handle(Request::create('/foo/return-object', 'PUT'));
    }

    /**
     * @test
     */
    public function serviceReturnsArray()
    {
        $this->expectOutputString(json_encode([]));
        $this->boot->get('http')->handle(Request::create('/foo/array'));
    }

    /**
     * @test
     */
    public function serviceReturnsJsonResponseObject()
    {
        $this->expectOutputString(json_encode([]));
        $this->boot->get('http')->handle(Request::create('/foo/return-json-object', 'DELETE'));
    }

    /**
     * @test
     */
    public function serviceReturnsJsonSerializableImpl()
    {
        $this->expectOutputString(json_encode([
            'foo' => 'bar',
            'bar' => 'foo',
        ]));
        $this->boot->get('http')->handle(Request::create('/foo/return-json', 'PATCH'));
    }

    /**
     * @test
     */
    public function serviceReturnsString()
    {
        $this->expectOutputString('foobar');
        $this->boot->get('http')->handle(Request::create('/foo/return-string', 'POST'));
    }

    /**
     * @test
     */
    public function throwsExceptionWhenUsingInvalidHttpMethod()
    {
        $this->setExpectedException(MethodNotAllowedException::class);
        $this->boot->get('http')->handle(Request::create('/foo/return-string', 'GET'));
    }

    /**
     * @test
     */
    public function throwsExceptionWhenServiceNotExists()
    {
        // The following output is expected:
        $this->expectOutputString('The service \'foo\' does not exist.');

        /** @var ServiceValidator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder(ServiceValidator::class)->setMethods(['validateService'])->getMock();
        $validator->expects($this->once())
            ->method('validateService')
            ->willThrowException(new ServiceClassNotFoundException('foo', null))
        ;

        $httpInitializer = new HttpServiceInitializer(null);
        $httpInitializer->setServiceValidator($validator);
        $this->invokeService($httpInitializer);
    }

    /**
     * @test
     */
    public function throwsExceptionWhenMethodNotExists()
    {
        // The following output is expected:
        $this->expectOutputString('The someMethod method does not exist.');

        /** @var ServiceValidator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder(ServiceValidator::class)->setMethods(['validateService'])->getMock();
        $validator->expects($this->once())
            ->method('validateService')
            ->willThrowException(new ServiceMethodNotFoundException(null, null))
        ;

        $httpInitializer = new HttpServiceInitializer(null);
        $httpInitializer->setServiceValidator($validator);
        $this->invokeService($httpInitializer, null, 'someMethod');
    }

    /**
     * @test
     */
    public function throwsExceptionWhenWrongBuilderIsProvided()
    {
        $httpInitializer = new HttpServiceInitializer(null);
        $invalidBuilder = new Builder(__DIR__);

        // initializer should return false when invalid builder is not accepted
        $this->assertFalse($httpInitializer->accept($invalidBuilder));

        // should throw exception when providing the invalid builder for initialization
        $this->setExpectedException(IncompatibleComponentException::class);
        $httpInitializer->initialize($invalidBuilder);
    }

    /**
     * @test
     */
    public function willAcceptTheWebBuilder()
    {
        $httpInitializer = new HttpServiceInitializer(null);
        $validBuilder = new WebBuilder(__DIR__);

        // initializer should return true when the builder is accepted
        $this->assertTrue($httpInitializer->accept($validBuilder));
    }

    /**
     * A logic exception implies a human error, in this case implementing a service without the correct interface.
     *
     * @test
     */
    public function serviceLogicExceptionHandling()
    {
        // The following output is expected:
        $this->expectOutputString('This service is not available because of technical problems. Please let us know so we can fix this problem as soon as possible.');

        /** @var ServiceValidator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder(ServiceValidator::class)->setMethods(['validateService'])->getMock();
        $validator->expects($this->once())
            ->method('validateService')
            ->willThrowException(new ServiceLogicException(null, null))
        ;

        $httpInitializer = new HttpServiceInitializer(null);
        $httpInitializer->setServiceValidator($validator);
        $this->invokeService($httpInitializer);
    }

    /**
     * A logic exception implies a human error, in this case implementing a service without the correct interface.
     *
     * @test
     */
    public function unknownExceptionHandling()
    {
        // The following output is expected:
        $this->expectOutputString('An unknown error occurred.');

        /** @var ServiceValidator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder(ServiceValidator::class)->setMethods(['validateService'])->getMock();
        $validator->expects($this->once())
            ->method('validateService')
            ->willThrowException(new \Exception())
        ;

        $httpInitializer = new HttpServiceInitializer(null);
        $httpInitializer->setServiceValidator($validator);
        $this->invokeService($httpInitializer);
    }

    /**
     * @test
     */
    public function denyAccessPublicIpRanges()
    {
        $initializer = new HttpServiceInitializer('http', false);

        $request = new Request();
        $request->server->set('REMOTE_ADDR', gethostbyname('example.com'));
        $routeMatch['_publicIpRangesDenied'] = true;

        $refl = new \ReflectionClass($initializer);
        $method = $refl->getMethod('isAccessGranted');
        $method->setAccessible(true);

        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertFalse($accessGranted, 'Should not grant access to public ip\'s');

        $request->server->set('REMOTE_ADDR', '192.168.0.10');
        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertTrue($accessGranted, 'Should not block private ip.');
    }

    /**
     * @test
     */
    public function denyAccessPrivateIpRanges()
    {
        $initializer = new HttpServiceInitializer('http', false);

        $request = new Request();
        $request->server->set('REMOTE_ADDR', '192.168.0.10');
        $routeMatch['_privateIpRangesDenied'] = true;

        $refl = new \ReflectionClass($initializer);
        $method = $refl->getMethod('isAccessGranted');
        $method->setAccessible(true);

        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertFalse($accessGranted, 'Should not grant access to private ip\'s');

        $publicIp = gethostbyname('example.com');
        $request->server->set('REMOTE_ADDR', $publicIp);
        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertTrue($accessGranted, 'Should not block public IP!');

        $routeMatch['_publicIpRangesDenied'] = true;

        $request->server->set('REMOTE_ADDR', $publicIp);
        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertFalse($accessGranted, 'Should block public IP as well!');
    }

    /**
     * @test
     */
    public function denyAccessReservedIpRanges()
    {
        $initializer = new HttpServiceInitializer('http', false);

        $request = new Request();
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $routeMatch['_reservedIpRangesDenied'] = true;

        $refl = new \ReflectionClass($initializer);
        $method = $refl->getMethod('isAccessGranted');
        $method->setAccessible(true);

        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertFalse($accessGranted, 'Should not grant access to reserved ip ranges');
    }

    /**
     * @test
     */
    public function denyAccessBlacklistedIpv4Range()
    {
        $initializer = new HttpServiceInitializer('http', false);

        $request = Request::createFromGlobals();
        $routeMatch['_blacklistIps'][] = $request->getClientIp();

        $refl = new \ReflectionClass($initializer);
        $method = $refl->getMethod('isAccessGranted');
        $method->setAccessible(true);

        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertFalse($accessGranted, 'Should not grant access to blacklisted IP\'s');
    }

    /**
     * @test
     */
    public function denyAccessBlackListedIpv6Address()
    {
        $initializer = new HttpServiceInitializer('http', false);

        $request = new Request();
        $request->server->set('REMOTE_ADDR', '0:0:0:0:0:ffff:5596:4c33');
        $routeMatch['_blacklistIps'][] = '0:0:0:0:0:ffff:5596:4c33';

        $refl = new \ReflectionClass($initializer);
        $method = $refl->getMethod('isAccessGranted');
        $method->setAccessible(true);

        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertFalse($accessGranted, 'Should not grant access to blacklisted IP\'s');
    }

    /**
     * @test
     */
    public function denyAccessVBlackListedHost()
    {
        $initializer = new HttpServiceInitializer('http', false);

        $request = Request::createFromGlobals();
        $request->headers->set('HOST', 'foo.example.com');
        $routeMatch['_blacklistHosts'][] = 'example.com';

        $refl = new \ReflectionClass($initializer);
        $method = $refl->getMethod('isAccessGranted');
        $method->setAccessible(true);

        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertFalse($accessGranted, 'Should not grant access to blacklisted host.');
    }

    /**
     * @test
     */
    public function allowAccessWhiteListedIpv4Range()
    {
        $initializer = new HttpServiceInitializer('http', false);

        $request = new Request();
        $request->server->set('REMOTE_ADDR', '192.168.0.10');
        $routeMatch['_privateIpRangesDenied'] = true;

        $refl = new \ReflectionClass($initializer);
        $method = $refl->getMethod('isAccessGranted');
        $method->setAccessible(true);

        // First call without whitelisted ip range, should return false
        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertFalse($accessGranted, 'Should not grant access to private ip\'s');

        // Should accept white listed ip range
        $routeMatch['_whitelistIps'] = ['192.168.*.*'];
        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertTrue($accessGranted, 'Should grant access to IP addresses with or without wildcards');

        // Should accept white listed ip range
        $routeMatch['_whitelistIps'] = ['192.168.0.09-192.168.0.11'];
        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertTrue($accessGranted, 'Should grant access to IP ranges in start-end format');
    }

    /**
     * @test
     */
    public function allowAccessWhiteListedIpv6Address()
    {
        $initializer = new HttpServiceInitializer('http', false);

        $request = new Request();
        $request->server->set('REMOTE_ADDR', '0:0:0:0:0:ffff:5596:4c33');
        $routeMatch['_publicIpRangesDenied'] = true;

        $refl = new \ReflectionClass($initializer);
        $method = $refl->getMethod('isAccessGranted');
        $method->setAccessible(true);

        // First call without whitelisted ip range, should return false
        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertFalse($accessGranted, 'Should not grant access to private ip\'s');

        // Should accept white listed ipv6 address
        $routeMatch['_whitelistIps'] = ['0:0:0:0:0:ffff:5596:4c33'];
        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertTrue($accessGranted, 'Should grant access to IP addresses with or without wildcards');
    }

    /**
     * @test
     */
    public function allowAccessWhiteListedHost()
    {
        $initializer = new HttpServiceInitializer('http', false);

        $request = Request::createFromGlobals();
        $request->headers->set('HOST', 'foo.example.com');
        $request->server->set('REMOTE_ADDR', '192.168.0.10');

        $routeMatch['_privateIpRangesDenied'] = true;

        $refl = new \ReflectionClass($initializer);
        $method = $refl->getMethod('isAccessGranted');
        $method->setAccessible(true);

        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertFalse($accessGranted, 'Should not grant access to private IP addresses.');

        $routeMatch['_whitelistHosts'][] = 'example.com';
        $accessGranted = $method->invoke($initializer, $routeMatch, $request);
        $this->assertTrue($accessGranted, 'Should grant access to white listed host.');
    }

    /**
     * @test
     */
    public function eventDispatcherAvailable()
    {
        $initializer = new HttpServiceInitializer('http', false);
        $builder = new WebBuilder(__DIR__);
        $initializer->initialize($builder);
        $this->assertInstanceOf(EventDispatcher::class, $initializer->getEventDispatcher());
    }

    /**
     * @test
     */
    public function debugInfoExceptionHandler()
    {
        // The following output is expected:
        $this->expectOutputRegex('/Error: .* on line.*/');

        /** @var ServiceValidator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder(ServiceValidator::class)->setMethods(['validateService'])->getMock();
        $validator->expects($this->once())
            ->method('validateService')
            ->willThrowException(new \Exception())
        ;

        $httpInitializer = new HttpServiceInitializer(null, true);
        $httpInitializer->setServiceValidator($validator);
        $this->invokeService($httpInitializer);
    }

    /**
     * @param HttpServiceInitializer $dispatcher
     * @param string|null            $serviceClass
     * @param string|null            $serviceMethod
     */
    protected function invokeService(HttpServiceInitializer $dispatcher, $serviceClass = null, $serviceMethod = null)
    {
        $refl = new \ReflectionClass($dispatcher);
        $method = $refl->getMethod('invokeService');
        $method->setAccessible(true);
        $method->invoke($dispatcher, $serviceClass, $serviceMethod, Request::createFromGlobals());
    }
}

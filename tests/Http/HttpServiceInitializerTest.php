<?php

namespace Boot\Tests\Http;

use Boot\Http\Router\RouteMatch;
use Boot\Tests\AbstractTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Boot\Builder;
use Boot\Exception\IncompatibleComponentException;
use Boot\Http\Exception\ServiceClassNotFoundException;
use Boot\Http\Exception\ServiceLogicException;
use Boot\Http\Exception\ServiceMethodNotFoundException;
use Boot\Http\Router\RouteOptions;
use Boot\Http\HttpServiceInitializer;
use Boot\Http\WebBuilder;
use Boot\Tests\Assets\Http\FooService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * Class ServiceDispatcherTest.
 */
class HttpServiceInitializerTest extends AbstractTestCase
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
    public function debugMode()
    {
        $initializer = new HttpServiceInitializer('http');
        $initializer->setDebug(true);
        $this->assertTrue($initializer->isDebug());
    }

    /**
     * @test
     */
    public function verifyOutputResponseObjectResponse()
    {
        $this->expectOutputString('blaat');
        $this->boot->get('http')->handle(Request::create('/foo/return-object', 'PUT'));
    }

    /**
     * @test
     */
    public function verifyOutputArrayResponse()
    {
        $this->expectOutputString(json_encode([]));
        $this->boot->get('http')->handle(Request::create('/foo/array'));
    }

    /**
     * @test
     */
    public function verifyOutputJsonResponseObjectResponse()
    {
        $this->expectOutputString(json_encode([]));
        $this->boot->get('http')->handle(Request::create('/foo/return-json-object', 'DELETE'));
    }

    /**
     * @test
     */
    public function verifyOutputJsonSerializableResponse()
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
    public function verifyOutputStringResponse()
    {
        $this->expectOutputString('foobar');
        $this->boot->get('http')->handle(Request::create('/foo/return-string', 'POST'));
    }

    /**
     * @test
     */
    public function throwsExceptionWhenUsingInvalidHttpMethod()
    {
        $this->expectException(MethodNotAllowedException::class);
        $this->boot->get('http')->handle(Request::create('/foo/return-string', 'GET'));
    }

    /**
     * @test
     */
    public function dispatch404NotFound()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|HttpServiceInitializer $initializer */
        $initializer = $this->getMockBuilder(HttpServiceInitializer::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve', 'isDebug'])
            ->getMock();

        $this->expectOutputString('NOT FOUND');

        $initializer
            ->expects($this->any())
            ->method('isDebug')
            ->willReturn(true);

        $initializer
            ->expects($this->once())
            ->method('resolve')
            ->willReturn(false);


        $initializer->handle();
    }

    /**
     * @test
     */
    public function dispatch403Forbidden()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|HttpServiceInitializer $initializer */
        $initializer = $this->getMockBuilder(HttpServiceInitializer::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve', 'isDebug'])
            ->getMock();

        $routeMatch = $this->getMock(RouteMatch::class, ['verifyClient'], [], '', false);
        $routeMatch->expects($this->once())
            ->method('verifyClient')
            ->willReturn(false);

        $this->expectOutputString('CLIENT REJECTED');

        $initializer
            ->expects($this->any())
            ->method('isDebug')
            ->willReturn(true);

        $initializer
            ->expects($this->once())
            ->method('resolve')
            ->willReturn($routeMatch);

        $initializer->handle();
    }

    /**
     * @test
     */
    public function willPrintDebugInfo()
    {
        // the dispatchInternalServerError method should print debug info when debug is enabled
        $initializer = new HttpServiceInitializer('http');
        $initializer->setDebug(true);

        $this->expectOutputRegex('/.*blablabla.*/');

        $method = new \ReflectionMethod($initializer, 'dispatchInternalServerError');
        $method->setAccessible(true);
        $method->invoke($initializer, "some error", new \Exception("blablabla"));
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
        $this->expectException(IncompatibleComponentException::class);
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
     * @param HttpServiceInitializer $dispatcher
     * @param RouteMatch             $routeMatch
     */
    protected function invokeService(HttpServiceInitializer $dispatcher, RouteMatch $routeMatch)
    {
        $refl = new \ReflectionClass($dispatcher);
        $method = $refl->getMethod('invokeService');
        $method->setAccessible(true);
        $method->invoke($dispatcher, $routeMatch, Request::createFromGlobals());
    }

    /**
     * @test
     */
    public function throwsExceptionWhenServiceNotExists()
    {
        // The following output is expected:
        $this->expectOutputString('The service \'foo\' does not exist.');

        /** @var RouteMatch|\PHPUnit_Framework_MockObject_MockObject $routeMatch */
        $routeMatch = $this->getMockBuilder(RouteMatch::class)
            ->disableOriginalConstructor()
            ->setMethods(['getService'])
            ->getMock();

        $routeMatch->expects($this->once())
            ->method('getService')
            ->willThrowException(new ServiceClassNotFoundException('foo', null))
        ;

        $httpInitializer = new HttpServiceInitializer(null);
        $httpInitializer->bootstrap($this->getServiceContainerMock());
        $this->invokeService($httpInitializer, $routeMatch);
    }

    private function getServiceContainerMock()
    {
        return $this->getMockBuilder(ContainerInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * @test
     */
    public function throwsExceptionWhenMethodNotExists()
    {
        // The following output is expected:
        $this->expectOutputString('The someMethod method does not exist.');

        /** @var RouteMatch|\PHPUnit_Framework_MockObject_MockObject $routeMatch */
        $routeMatch = $this->getMockBuilder(RouteMatch::class)
            ->disableOriginalConstructor()
            ->setMethods(['getService'])
            ->getMock();

        $routeMatch->expects($this->once())
            ->method('getService')
            ->willThrowException(new ServiceMethodNotFoundException('foo', 'someMethod'))
        ;

        $httpInitializer = new HttpServiceInitializer(null);
        $httpInitializer->bootstrap($this->getServiceContainerMock());
        $this->invokeService($httpInitializer, $routeMatch);
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

        /** @var RouteMatch|\PHPUnit_Framework_MockObject_MockObject $routeMatch */
        $routeMatch = $this->getMockBuilder(RouteMatch::class)
            ->disableOriginalConstructor()
            ->setMethods(['getService'])
            ->getMock();

        $routeMatch->expects($this->once())
            ->method('getService')
            ->willThrowException(new ServiceLogicException(null, null))
        ;

        $httpInitializer = new HttpServiceInitializer(null);
        $httpInitializer->bootstrap($this->getServiceContainerMock());
        $this->invokeService($httpInitializer, $routeMatch);
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

        /** @var RouteMatch|\PHPUnit_Framework_MockObject_MockObject $routeMatch */
        $routeMatch = $this->getMockBuilder(RouteMatch::class)
            ->disableOriginalConstructor()
            ->setMethods(['getService'])
            ->getMock();

        $routeMatch->expects($this->once())
            ->method('getService')
            ->willThrowException(new \Exception)
        ;


        $httpInitializer = new HttpServiceInitializer(null);
        $httpInitializer->bootstrap($this->getServiceContainerMock());
        $this->invokeService($httpInitializer, $routeMatch);
    }


}

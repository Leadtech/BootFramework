<?php

namespace Boot\Tests\Http;

use Boot\Boot;
use Boot\Http\Exception\ServiceClassNotFoundException;
use Boot\Http\Exception\ServiceLogicException;
use Boot\Http\Exception\ServiceMethodNotFoundException;
use Boot\Http\Router\RouteOptions;
use Boot\Http\Service\Validator\ServiceValidator;
use Boot\Http\ServiceDispatcher;
use Boot\Http\WebBuilder;
use Boot\Tests\Assets\Http\FooService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * Class ServiceDispatcherTest.
 */
class ServiceDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ContainerInterface */
    protected $boot;

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
    public function expectExceptionWhenHttpMethodIsDifferent()
    {
        $this->setExpectedException(MethodNotAllowedException::class);
        $this->boot->get('http')->handle(Request::create('/foo/return-string', 'GET'));
    }

    /**
     * @test
     */
    public function missingServiceHandling()
    {
        // The following output is expected:
        $this->expectOutputString('The service \'foo\' does not exist.');

        /** @var ServiceValidator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder(ServiceValidator::class)->setMethods(['validateService'])->getMock();
        $validator->expects($this->once())
            ->method('validateService')
            ->willThrowException(new ServiceClassNotFoundException('foo', null))
        ;

        $serviceDispatcher = new ServiceDispatcher(null);
        $serviceDispatcher->setServiceValidator($validator);
        $this->invokeService($serviceDispatcher);
    }

    /**
     * @test
     */
    public function missingServiceMethodHandling()
    {
        // The following output is expected:
        $this->expectOutputString('The someMethod method does not exist.');

        /** @var ServiceValidator|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder(ServiceValidator::class)->setMethods(['validateService'])->getMock();
        $validator->expects($this->once())
            ->method('validateService')
            ->willThrowException(new ServiceMethodNotFoundException(null, null))
        ;

        $serviceDispatcher = new ServiceDispatcher(null);
        $serviceDispatcher->setServiceValidator($validator);
        $this->invokeService($serviceDispatcher, null, 'someMethod');
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

        $serviceDispatcher = new ServiceDispatcher(null);
        $serviceDispatcher->setServiceValidator($validator);
        $this->invokeService($serviceDispatcher);
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

        $serviceDispatcher = new ServiceDispatcher(null);
        $serviceDispatcher->setServiceValidator($validator);
        $this->invokeService($serviceDispatcher);
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

        $serviceDispatcher = new ServiceDispatcher(null, true);
        $serviceDispatcher->setServiceValidator($validator);
        $this->invokeService($serviceDispatcher);
    }

    /**
     * @param ServiceDispatcher $dispatcher
     * @param string|null       $serviceClass
     * @param string|null       $serviceMethod
     */
    protected function invokeService(ServiceDispatcher $dispatcher, $serviceClass = null, $serviceMethod = null)
    {
        $refl = new \ReflectionClass($dispatcher);
        $method = $refl->getMethod('invokeService');
        $method->setAccessible(true);
        $method->invoke($dispatcher, $serviceClass, $serviceMethod, Request::createFromGlobals());
    }
}

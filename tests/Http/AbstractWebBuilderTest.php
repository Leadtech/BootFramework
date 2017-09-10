<?php

namespace Boot\Tests\Http;

use Boot\Http\Router\RouteOptions;
use Boot\Http\WebBuilder;
use Boot\Tests\AbstractTestCase;
use Boot\Tests\Assets\Http\FooService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * Class WebBuilderTest.
 */
abstract class AbstractWebBuilderTest extends AbstractTestCase
{
    /** @var  ContainerInterface */
    protected $boot;

    /**
     * @return WebBuilder
     */
    abstract public function createBuilder();

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $this->boot = $this->createBuilder()

            // Will return an array
            ->get('array/{foo}/{bar}', FooService::class, 'returnArray', new RouteOptions(
                'array-test'
            ))

            // Will return an array
            ->get('epic-fail', FooService::class, 'throwsException', new RouteOptions(
                'fail-test'
            ))

            // Will return an array
            ->get('invalid-response', FooService::class, 'returnInvalidResponse', new RouteOptions(
                'invalid-response'
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
        // Tests the expected out + verifies that the service has acess to the route match instance as expected...
        $this->expectOutputString(json_encode(['foo' => 'abc',  'bar' => 'def']));
        $this->boot->get('http')->handle(Request::create('/foo/array/abc/def'));
    }

    /**
     * @test
     */
    public function verifyOutputInvalidResponse()
    {
        $this->expectOutputString('An unknown error occurred.');
        $this->boot->get('http')->handle(Request::create('/foo/invalid-response'));
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
    public function expectExceptionWhenHttpMethodIsDifferent()
    {
        $this->expectException(MethodNotAllowedException::class);
        $this->boot->get('http')->handle(Request::create('/foo/return-string', 'GET'));
    }

    /**
     * @test
     */
    public function expectOutputInCaseOfUnknownError()
    {
        $this->expectOutputString('An unknown error occurred.');
        $this->boot->get('http')->handle(Request::create('/foo/epic-fail', 'GET'));
    }
}

<?php
namespace Boot\Tests\Http;;

use Boot\Http\Router\RouteOptions;
use Boot\Http\WebBuilder;
use Boot\Tests\Assets\Http\FooService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * Class WebBuilderTest
 *
 * @package Boot\Tests\Http
 */
class WebBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ContainerInterface */
    protected $boot;

    public function setUp()
    {
        $this->boot = (new WebBuilder(__DIR__ . '/../Assets/Bootstrap/BuilderTest'))
            ->appName('test1')
            ->caching('cache', false)
            ->environment('prod')

            // Will return an array
            ->get('array', FooService::class, 'returnArray', new RouteOptions(
                'array-test'
            ))

            // Will return instance of JsonSerialize
            ->get('return-json', FooService::class, 'returnJsonSerializable', new RouteOptions(
                'json-test'
            ))

            // Will return instance of Response
            ->get('return-object', FooService::class, 'returnResponseObject', new RouteOptions(
                'response-object-test'
            ))

            // Will return instance of JsonResponse
            ->get('return-json-object', FooService::class, 'returnJsonResponseObject', new RouteOptions(
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
        $this->boot->get('http')->handle(Request::create('/foo/return-object'));
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
        $this->boot->get('http')->handle(Request::create('/foo/return-json-object'));
    }

    /**
     * @test
     */
    public function serviceReturnsJsonSerializableImpl()
    {
        $this->expectOutputString(json_encode([
            'foo' => 'bar',
            'bar' => 'foo'
        ]));
        $this->boot->get('http')->handle(Request::create('/foo/return-json'));
    }

    /**
     * @test
     */
    public function serviceReturnsString()
    {
        $this->expectOutputString("foobar");
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
}
<?php

namespace Boot\Tests\Http;

use Boot\Console\CompilerPass\ConsoleCompilerPass;
use Boot\Http\Application;
use Boot\Http\HttpServiceInitializer;
use Boot\Http\Router\RouteOptions;
use Boot\Http\WebBuilder;
use Boot\Tests\Assets\Http\FooService;
use Psr\Log\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ExpressionLanguageProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * Class WebBuilderTest.
 */
class WebBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ContainerInterface */
    protected $boot;

    public function setUp()
    {
        $this->boot = (new WebBuilder(__DIR__.'/../Assets/Bootstrap/BuilderTest'))
            ->appName('test1')

            ->environment('prod')

            ->expr(new ExpressionLanguageProvider)

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
    public function shouldReturnAppVersion()
    {
        $appVersion = (new WebBuilder(__DIR__))
            ->appVersion('1.13.64')
            ->getAppVersion()
            ;

        $this->assertEquals('1.13.64', $appVersion);
    }

    /**
     * @test
     */
    public function shouldLazyLoadEventDispatcher()
    {
        $builder = new WebBuilder(__DIR__);

        // make property accessible
        $refl = new \ReflectionProperty($builder, 'eventDispatcher');
        $refl->setAccessible(true);

        // verifies that the event dispatcher is not set
        $this->assertNull($refl->getValue($builder));

        // lazy loads instance
        $eventDispatcher = $builder->getEventDispatcher();
        $this->assertSame($eventDispatcher, $refl->getValue($builder));
    }

    /**
     * @test
     */
    public function addingCompilerPasses()
    {
        $builder = new WebBuilder(__DIR__);
        $builder->afterRemoving(new ConsoleCompilerPass('foo', 'bar', '2.0'));
        $builder->beforeOptimization(new ConsoleCompilerPass('foo', 'bar', '2.0'));
        $builder->beforeRemoving(new ConsoleCompilerPass('foo', 'bar', '2.0'));
        $builder->onOptimization(new ConsoleCompilerPass('foo', 'bar', '2.0'));
        $builder->onRemoving(new ConsoleCompilerPass('foo', 'bar', '2.0'));

        $this->assertCount(5, $builder->getCompilerPasses());

        $compilerPassConfigs = [];
        foreach ($builder->getCompilerPasses() as $compilerPass) {
            $compilerPassConfigs[] = $compilerPass[1];
        }

        $this->assertContains(PassConfig::TYPE_AFTER_REMOVING, $compilerPassConfigs);
        $this->assertContains(PassConfig::TYPE_BEFORE_OPTIMIZATION, $compilerPassConfigs);
        $this->assertContains(PassConfig::TYPE_BEFORE_REMOVING, $compilerPassConfigs);
        $this->assertContains(PassConfig::TYPE_OPTIMIZE, $compilerPassConfigs);
        $this->assertContains(PassConfig::TYPE_REMOVE, $compilerPassConfigs);
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
    public function runAppShouldInvokeHttpService()
    {
        $methods = get_class_methods(ContainerInterface::class);

        /** @var Container|\PHPUnit_Framework_MockObject_MockObject $serviceContainer */
        $serviceContainer = $this
            ->getMockBuilder(ContainerInterface::class)
            ->setMethods($methods)
            ->getMockForAbstractClass()
        ;

        $httpService =  $this
            ->getMockBuilder(HttpServiceInitializer::class)
            ->disableOriginalClone()
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock()
        ;

        // The handle method should be invoked once using a default request instance.
        // Note that we will not pass a request to the run method, so at the same time we test if the run method
        // does indeed instantiate a request from globals as expected.
        $httpService
            ->expects($this->once())
            ->method('handle')
            ->with(Request::createFromGlobals());

        // Should request the http service once
        $serviceContainer
            ->expects($this->once())
            ->method('get')
            ->with('theHttpServiceId')
            ->willReturn($httpService);
        ;

        // Run the application
        $application = new Application($serviceContainer, 'theHttpServiceId');
        $application->run();
    }

    /**
     * @test
     */
    public function throwsExceptionWhenTheProjectDirNotExists()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        new WebBuilder('/w00t/bla/bla/bla/foobar');
    }
}

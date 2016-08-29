<?php

namespace Boot\Tests;

use Boot\Tests\Assets\ConcreteServiceContainerDecorator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ServiceContainerDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function decoratedMethodCalls()
    {
        // Get class methods dynamically
        $methods = get_class_methods(ContainerInterface::class);

        /** @var Container|\PHPUnit_Framework_MockObject_MockObject $serviceContainer */
        $serviceContainer = $this
            ->getMockBuilder(ContainerInterface::class)
            ->setMethods($methods)
            ->getMockForAbstractClass();

        // Tracks which methods should return a value,
        $willReturnValue = [];

        // Use reflection to generate parameters
        $args = [];

        // Each method defined in the container interface must be invoked exactly one time.
        foreach ($methods as $method) {

            // Symfony source code is well documented, we can see if the doc block contains the @return annotation.
            // We will always return a result value. For methods that should return a value, we will test if the decorator
            // does return the same value as expected, and on the other hand. If the method should not return a value we
            // will validate that the validator will not return this value. In the end we will know that the decorator
            // will return all values that should be, and ignore the others.
            $refl = new \ReflectionMethod(Container::class, $method);

            $willReturnValue[$method] = $this->docBlockIndicatesReturnValue($refl->getDocComment());

            // Use reflection to determine the parameters up front so we don't have to hardcode the methods.
            foreach ($refl->getParameters() as $parameter) {

                // Use defaults when available
                if ($parameter->isDefaultValueAvailable()) {
                    continue;
                }

                // Assume that no php 7 features are used (disallowing null values)
                // When this changes we would need to refactor this test, but due to backward compatibility it is
                // unlikely that this will change any time soon for a framework like Symfony.
                $paramValue = null;

                // Php wont allow null values for arrays, if type is array we will pass an empty array instead of null
                if ($parameter->isArray()) {
                    $paramValue = array();
                }

                // Use the mock builder if an instance of a specific class or interface is needed
                if ($parameter->getClass()) {
                    $expectedClassName = $parameter->getClass()->getName();
                    $paramValue = $this->getMockBuilder($expectedClassName)
                        ->disableOriginalClone()
                        ->disableOriginalConstructor()
                        ->disableProxyingToOriginalMethods()
                        ->enableArgumentCloning()
                        ->getMock()
                    ;
                }

                $args[$method][$parameter->getName()] = $paramValue;
            }

            $serviceContainer
                ->expects($this->once())
                ->method($method)
                ->willReturn('foo_bar')
            ;
        }

        // Create the decorator
        $decorator = new ConcreteServiceContainerDecorator($serviceContainer);

        // Should return the decorated instance
        $this->assertSame($serviceContainer, $decorator->getInternal());

        // Invoke each (decorated) method
        foreach ($methods as $method) {
            $result = call_user_func_array([$decorator, $method], $args[$method]);
            if ($willReturnValue[$method]) {
                // method should return something
                $this->assertEquals('foo_bar', $result);
            } else {
                // method should not return anything
                $this->assertNull($result);
            }
        }
    }

    /**
     * @param string $docComment
     * @return bool
     */
    private function docBlockIndicatesReturnValue($docComment)
    {
        $lines = explode(PHP_EOL, $docComment);
        foreach ($lines as $line) {
            if (strpos($line, '@return') !== false && strpos($line, 'void') === false) {
                return true;
            }
        }

        return false;
    }
}
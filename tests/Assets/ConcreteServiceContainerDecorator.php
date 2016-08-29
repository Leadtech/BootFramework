<?php

namespace Boot\Tests\Assets;

use Boot\AbstractServiceContainerDecorator;

/**
 * Class ConcreteServiceContainerDecorator
 *
 * The AbstractServiceContainerDecorator provides an easy way to implement the service container at runtime and add
 * additional functionality, such as a run() method to simplify the execution of general use cases such as starting
 * the console or process an incoming request. This class is abstract because its only useful when extended.
 *
 * @package Boot\Tests\Assets
 */
class ConcreteServiceContainerDecorator extends AbstractServiceContainerDecorator
{

}
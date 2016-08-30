<?php

namespace Boot\Tests\Utils;

use Boot\Utils\StringUtils;

/**
 * Test string utilities
 */
class StringUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function convertToSnakeCase()
    {
        $this->assertEquals('foo_bar', StringUtils::snakeCase('fooBar'));
    }

    /**
     * @test
     */
    public function convertToCamelCase()
    {
        $this->assertEquals('fooBar', StringUtils::camelCase('foo_bar', false));
        $this->assertEquals('FooBar', StringUtils::camelCase('foo_bar'));
    }
}
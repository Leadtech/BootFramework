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

    /**
     * @test
     */
    public function extractEnclosedStrings()
    {
        $curlyBracesResult = StringUtils::extractStringsEnclosedWith('/some-uri/{foo}/{bar}/coffee/{foobar}/', '{', '}');
        $this->assertEquals(['foo', 'bar', 'foobar'], $curlyBracesResult);

        $enclosedSameCharResult = StringUtils::extractStringsEnclosedWith('/some-uri/"foo"/"bar"/coffee/"foobar"/', '"', '"');
        $this->assertEquals(['foo', 'bar', 'foobar'], $enclosedSameCharResult);

        // This tests actually tests 2 things, this should return an empty string, and on top this test would fail if
        // the parameters used in the pattern are not properly escaped.
        $noMatchResult = StringUtils::extractStringsEnclosedWith('Blabla', '*' , '*');
        $this->assertEquals([], $noMatchResult);
    }

    /**
     * @test
     */
    public function throwsExceptionWhenOpenCharIsMissing()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        StringUtils::extractStringsEnclosedWith('Blabla', '*' , '');
    }

    /**
     * @test
     */
    public function throwsExceptionWhenCloseCharIsMissing()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        StringUtils::extractStringsEnclosedWith('Blabla', '' , '*');
    }
}
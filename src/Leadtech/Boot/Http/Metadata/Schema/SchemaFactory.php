<?php
namespace Boot\Http\Metadata\Schema;

use Boot\Http\Metadata\Schema\Definition\Base64Definition;
use Boot\Http\Metadata\Schema\Definition\BooleanDefinition;
use Boot\Http\Metadata\Schema\Definition\FloatDefinition;
use Boot\Http\Metadata\Schema\Definition\UnsignedIntegerDefinition;
use Boot\Http\Metadata\Schema\Definition\IterableDefinition;
use Boot\Http\Metadata\Schema\Definition\ObjectDefinition;
use Boot\Http\Metadata\Schema\Definition\StringDefinition;

abstract class SchemaFactory
{
    /**
     * Unsigned 16 bits integer
     *
     * @param string $description
     *
     * @return UnsignedIntegerDefinition
     */
    public function short($description)
    {
        return (new UnsignedIntegerDefinition($description))->i16();
    }

    /**
     * Unsigned 32 bits integer
     *
     * @param string $description
     *
     * @return UnsignedIntegerDefinition
     */
    public function int($description)
    {
        return (new UnsignedIntegerDefinition($description))->i32();
    }

    /**
     * Unsigned 64 bits integer
     *
     * @param string $description
     *
     * @return UnsignedIntegerDefinition
     */
    public function long($description)
    {
        return (new UnsignedIntegerDefinition($description))->i64();
    }

    /**
     * A string definition
     *
     * @param string $description
     *
     * @return StringDefinition
     */
    public function string($description)
    {
        return new StringDefinition($description);
    }

    /**
     * A float (to php a float is the same as  adouble, but that  is the case for other languages that may use the API)
     *
     * @param string $description
     * @param int    $precision
     *
     * @return FloatDefinition
     */
    public function float($description, $precision = 7)
    {
        return (new FloatDefinition($description))->precision($precision);
    }

    /**
     * Alias for  float() but with a higher default precision to reflect an actual double.
     * In PHP a double and a float are the same thing. On C level each floating point value is actual a double.
     *
     * @param string $description
     * @param int    $precision
     *
     * @return FloatDefinition
     */
    public function double($description, $precision = 10)
    {
        return (new FloatDefinition($description))->precision($precision);
    }

    /**
     * A boolean definition
     *
     * @param string $description
     *
     * @return BooleanDefinition
     */
    public function boolean($description)
    {
        return new BooleanDefinition($description);
    }

    /**
     * Base 64 encoded string or byte sequence
     *
     * @param string $description
     *
     * @return Base64Definition
     */
    public function base64($description)
    {
        return new Base64Definition($description);
    }

    /**
     * A date field as defined in RFC3339
     *
     * @param string $description
     *
     * @return StringDefinition
     */
    public function date($description)
    {
        return (new StringDefinition($description))->asDate();
    }

    /**
     * A datetime field as defined in RFC3339
     *
     * @param string $description
     *
     * @return StringDefinition
     */
    public function datetime($description)
    {
        return (new StringDefinition($description))->asDateTime();
    }

    /**
     * A password field. May be used to hint the client to hide the input from view.
     *
     * @param string $description
     *
     * @return StringDefinition
     */
    public function password($description)
    {
        return (new StringDefinition($description))->asDateTime();
    }

    /**
     * @param string $description
     *
     * @return IterableDefinition
     */
    public function iterable($description)
    {
        return new IterableDefinition($description);
    }

    /**
     * @param string $description
     *
     * @return ObjectDefinition
     */
    public function object($description)
    {
        return new ObjectDefinition($description);
    }
}
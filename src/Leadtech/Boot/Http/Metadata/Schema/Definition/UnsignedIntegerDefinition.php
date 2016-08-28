<?php

namespace Boot\Http\Metadata\Schema\Definition;
use Boot\Http\Metadata\Schema\DataTypes;

/**
 * Class UnsignedIntegerDefinition
 *
 * This definition defines unsigned 16-bits, 32-bits and 64-bits integers.
 *
 * How the integers are implemented is platform independent.
 * This package should provide sufficient metadata to generate documentation, or even generate a client for the API.
 * In order to provide a more accurate metadata model only unsigned integers of either 16, 32 and 64 bits are supported.
 * Below are two examples of the problem that we address here.
 *
 * Example 1:
 * In java the primitive data type is always an unsigned 16 bits integer. While in C# a short is in fact a signed 16
 * bits integer. In java a short would have a range of −32,768 to +32,767,  in C# the range for short is 0 to 65,535.
 *
 * Example 2:
 * Many programming languages support unsigned 64 bits integers (often referred to as 'long'  or 'bigint').
 * Unsigned 64 bits integer values range from -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807.
 * Up until PHP 7.0 64 bits integers were not, or actually only experimentally implemented in PHP.
 * Working with these numbers did (or most often still does) require a extension such as bcmath to work with big
 * numbers stored as strings.  Up until PHP 7.0 the 64 bit version of PHP was experimental. PHP 7.0 was just recently
 * released. Also, even modern browsers do not support 64 bits integers. The spec says that 64 bits integers have a max
 * precision of 53 bits. A limit that at this time still applies to some mathematical operations in PHP as well.
 * See for example: http://www.exploringbinary.com/the-safe-range-for-phps-base-convert/
 *
 * Conclusion:
 * Long story short, big integers, especially those with > 53 bit precision are NOT safe to exchange between languages.
 * So big integers need to be transmitted as string to be safe using HTTP.
 *
 * Notes:
 * In php to prevent data loss we should set the JSON_BIGINT_AS_STRING option when decoding a json message containing
 * big numbers. And NEVER enable the JSON_NUMERIC_CHECK option since it will cast each number to an integer!
 *
 *
 * N bit unsigned integer
 *
 * @package Boot\Http\Metadata\Schema\Definition
 */
class UnsignedIntegerDefinition extends NumericDefinition
{
    /** @var int  */
    private $bits = 32;

    /**
     * 16 bits unsigned integer (often referred to as short)
     *
     * @return $this
     */
    public function i16()
    {
        $this->bits = 16;

        return $this;
    }

    /**
     * 32 bits unsigned integer (often referred to as integer)
     *
     * @return $this
     */
    public function i32()
    {
        $this->bits = 32;

        return $this;
    }

    /**
     * 64 bits unsigned integer (often referred to as long or big int)
     *
     * @return $this
     */
    public function i64()
    {
        $this->bits = 64;

        return $this;
    }

    /**
     * @return string
     */
    public function getAtomicType()
    {
        if ($this->bits > 32) {
            return DataTypes::STRING;
        }

        if ($this->bits <= 16) {
            return DataTypes::I16;
        }

        return DataTypes::I32;
    }

    /**
     * @return float
     */
    public function getBase()
    {
        return $this->bits / 8;
    }
}
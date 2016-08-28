<?php

namespace Boot\Http\Metadata\Schema\Definition;

use Boot\Http\Metadata\Schema\DataTypes;

/**
 * Class FloatType
 *
 * Note that in PHP there is no difference between float and double.
 * Decimal numbers are most commonly referred to as floats. While in fact floats in PHP are doubles.
 * On C level all decimal numbers are stored as double.
 *
 * PHP'ish weirdness apart. We need to account for other programming languages. This package was written from the idea
 * to provide metadata to integrate tools like swagger or google api discovery. If we want to be able to provide
 * accurate documentation, or auto generate api clients even we cant threat them as the same type.
 * Assume double by default, but switch to float if the required precision is <= 6.
 * The precision for float is 6 to 9 and platform dependent. Assume double for precision values > 6.
 *
 * @package Boot\Http\Metadata\Schema\Definition
 */
class FloatDefinition extends NumericDefinition
{
    /** @var int  php uses double for all floating point values, hench the default of 10 which would be a double...  */
    protected $precision = 10;

    /**
     * @param int $precision
     *
     * @return $this
     */
    public function precision($precision)
    {
        $this->precision = $precision;

        return $this;
    }

    /**
     * @return string
     */
    public function getAtomicType()
    {
        // Float precision can differ from 6 to 9 depending on the environment.
        // For higher precisions than 6 assume double.
        if ($this->precision <= 6) {
            return DataTypes::FLOAT;
        }

        return DataTypes::DOUBLE;
    }
}
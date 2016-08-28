<?php

namespace Boot\Http\Metadata\Schema\Definition;

/**
 * Class NumericType
 *
 * @package Boot\Http\Metadata\Schema\Definition
 */
abstract class NumericDefinition extends AtomicTypeDefinition
{
    /** @var number|null */
    protected $minValue = null;

    /** @var number|null */
    protected $maxValue = null;

    /**
     * @param number $value  The min value, sub classes may cast their own numeric data type.
     *
     * @return $this
     */
    public function minValue($value)
    {
        $this->minValue = $value;

        return $this;
    }

    /**
     * @param number $value  The max value, sub classes may cast their own numeric data type.
     *
     * @return $this
     */
    public function maxValue($value)
    {
        $this->maxValue = $value;

        return $this;
    }

    /**
     * Short hand method to combine min and max values.
     *
     * @param number $minValue  The min value, sub classes may cast their own numeric data type.
     * @param number $maxValue  The max value, sub classes may cast their own numeric data type.
     *
     * @return $this
     */
    public function inRange($minValue, $maxValue)
    {
        $this->minValue($minValue);
        $this->maxValue($maxValue);

        return $this;
    }
}
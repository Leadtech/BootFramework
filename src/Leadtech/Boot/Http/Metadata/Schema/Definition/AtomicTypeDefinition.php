<?php

namespace Boot\Http\Metadata\Schema\Definition;
use Boot\Http\Metadata\Schema\DataTypes;

/**
 * Class AtomicType
 *
 * @package Boot\Http\Metadata\Schema\Definition
 */
abstract class AtomicTypeDefinition extends TypeDefinition
{
    const ATOMIC_TYPE = DataTypes::UNKNOWN;

    /** @var mixed[] */
    protected $enums = [];

    /** @var mixed|null */
    protected $default = null;

    /**
     * Sets enum list, the value must match one of the given values.
     *
     * @param mixed[] $enums  list of atomic values
     *
     * @return $this
     */
    public function enum($enums)
    {
        $this->enums = $enums;

        return $this;
    }

    /**
     * Sets default value
     *
     * @param $default
     *
     * @return $this
     */
    public function defaultValue($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return string
     */
    public function getAtomicType()
    {
        return static::ATOMIC_TYPE;
    }
}
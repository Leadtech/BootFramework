<?php

namespace Boot\Http\Metadata\Schema\Definition;

/**
 * Class IterableDataType
 *
 * @package Boot\Http\Metadata\Schema\Definition
 */
class IterableDefinition extends TypeDefinition
{
    /** @var TypeDefinition */
    protected $valueSpecs;

    /** @var bool  */
    protected $trimValues = true;

    /** @var int  */
    protected $minOccurs = 0;

    /** @var int  */
    protected $maxOccurs = -1;

    /** @var  string */
    protected $valueSeparator;

    /**
     * @param int $minOccurs
     *
     * @return $this
     */
    public function minOccurs($minOccurs)
    {
        $this->minOccurs((int)$minOccurs);

        return $this;
    }

    /**
     * @param int $maxOccurs
     *
     * @return $this
     */
    public function maxOccurs($maxOccurs)
    {
        $this->maxOccurs((int)$maxOccurs);

        return $this;
    }

    /**
     * @param string $valueSeparator
     *
     * @return $this
     */
    public function valueSeparator($valueSeparator)
    {
        $this->valueSeparator = $valueSeparator;

        return $this;
    }

    /**
     * @return $this
     */
    public function noTrimming()
    {
        $this->trimValues = false;

        return $this;
    }

    /**
     * @param TypeDefinition $valueDefinition
     *
     * @return $this
     */
    public function valueSpecs(TypeDefinition $valueDefinition)
    {
        $this->valueSpecs = $valueDefinition;

        return $this;
    }

}
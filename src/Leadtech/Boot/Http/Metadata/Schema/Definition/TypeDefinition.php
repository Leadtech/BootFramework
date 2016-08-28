<?php

namespace Boot\Http\Metadata\Schema\Definition;

/**
 * Class Type
 *
 * @package Boot\Http\Metadata\Schema\Definition
 */
abstract class TypeDefinition
{
    /** @var bool  */
    private $required = false;

    /** @var bool  */
    private $nullable = false;

    /** @var array  */
    private $metadata = [];

    /** @var string  */
    private $shortDescription = '';

    /** @var string  */
    private $longDescription = '';

    /**
     * TypeDefinition constructor.
     *
     * @param string $shortDescription
     */
    public function __construct($shortDescription = null)
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function shortDescription($description)
    {
        $this->shortDescription = $description;

        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function longDescription($description)
    {
        $this->longDescription = $description;

        return $this;
    }

    /**
     * Additional meta data that can be used for display or documentation purposes.
     *
     * @param array $metadata  key value pair
     *
     * @return $this
     */
    public function metadata(array $metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @return $this
     */
    public function required()
    {
        $this->required = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function optional()
    {
        $this->required = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function notNull()
    {
        $this->nullable = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function nullable()
    {
        $this->nullable = true;

        return $this;
    }
}
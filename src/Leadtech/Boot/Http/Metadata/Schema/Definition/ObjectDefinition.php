<?php

namespace Boot\Http\Metadata\Schema\Definition;

use Boot\Http\Metadata\Decoder\DecoderInterface;
use Boot\Http\Metadata\Encoder\EncoderInterface;

class ObjectDefinition extends TypeDefinition
{
    /** @var  EncoderInterface */
    protected $encoder;

    /** @var  DecoderInterface */
    protected $decoder;

    /** @var TypeDefinition[]  name => type definition pairs */
    protected $properties = [];

    /**
     * @param EncoderInterface $encoder
     *
     * @return $this
     */
    public function encoder(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;

        return $this;
    }

    /**
     * @param DecoderInterface $decoder
     *
     * @return $this
     */
    public function decoder(DecoderInterface $decoder)
    {
        $this->decoder[] = $decoder;

        return $this;
    }

    /**
     * @param string         $name
     * @param TypeDefinition $propertyType
     *
     * @return $this
     */
    public function property($name, TypeDefinition $propertyType)
    {
        $this->properties[$name] = $propertyType;

        return $this;
    }
}
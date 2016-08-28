<?php
namespace Boot\Http\Metadata\Input;

use Boot\Http\Metadata\Schema\Definition\TypeDefinition;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractInput
{
    /** @var  string */
    protected $name;

    /** @var TypeDefinition  */
    protected $typeDefinition;

    /**
     * AbstractInput constructor.
     *
     * @param string         $name
     * @param TypeDefinition $definition
     */
    public function __construct($name, TypeDefinition $definition)
    {
        $this->name = $name;
        $this->typeDefinition = $definition;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    abstract public function validate(Request $request);

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return TypeDefinition
     */
    public function getTypeDefinition()
    {
        return $this->typeDefinition;
    }
}
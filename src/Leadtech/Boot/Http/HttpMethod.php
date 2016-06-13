<?php
namespace Boot\Http;

use Symfony\Component\Routing\Route;

/**
 * Class Method
 *
 * @package Boot\Http
 */
class HttpMethod
{
    /** @var  string [POST|GET|PUT|DELETE|PATCH] */
    private $method;

    /** @var  string */
    public $name;

    /** @var  string */
    public $path;

    /** @var array  */
    public $defaults = [];

    /** @var string  */
    public $expr = '';

    /** @var array  */
    public $requirements = [];

    /**
     * Method constructor.
     *
     * @param string $httpMethod
     * @param string $routeName
     * @param string $routePath
     */
    public function __construct($httpMethod, $routeName, $routePath)
    {
        $this->method = $httpMethod;
        $this->name = $routeName;
        $this->path = $routePath;
    }

    /**
     * @param array $defaults
     * @return HttpMethod
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * @param string $expr
     * @return HttpMethod
     */
    public function setExpr($expr)
    {
        $this->expr = $expr;

        return $this;
    }

    /**
     * @param array $requirements
     * @return HttpMethod
     */
    public function setRequirements($requirements)
    {
        $this->requirements = $requirements;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return ltrim($this->path, '/');
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get expression. Must evaluate to true for the route to match.
     *
     * @return string
     */
    public function getExpr()
    {
        return $this->expr;
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * @return Route
     */
    public function createRoute()
    {
        return new Route(
            $this->getPath(),
            $this->getDefaults(),
            $this->getRequirements(),
            [],
            '',
            [],
            $this->getMethod() ?: [],
            $this->getExpr()
        );
    }
}

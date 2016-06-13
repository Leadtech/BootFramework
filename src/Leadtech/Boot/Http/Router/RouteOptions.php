<?php
namespace Boot\Http\Router;

/**
 * Class RouteOptions
 *
 * @package Boot\Http\Router
 */
class RouteOptions
{
    /** @var  string */
    protected $routeName;

    /** @var array  */
    protected $defaults = [];

    /** @var array  */
    protected $requirements = [];

    /**
     * RouterOptions constructor.
     *
     * @param string     $routeName
     * @param array|null $defaults
     * @param array      $requirements
     */
    public function __construct($routeName, array $defaults = null, array $requirements = null)
    {
        $this->routeName = $routeName;

        if ($defaults) {
            $this->defaults = $defaults;
        }

        if ($requirements) {
            $this->requirements = $requirements;
        }
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @param array $defaults
     *
     * @return RouteOptions
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * @param array $requirements
     *
     * @return RouteOptions
     */
    public function setRequirements($requirements)
    {
        $this->requirements = $requirements;

        return $this;
    }

    /**
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param string $routeName
     * @return RouteOptions
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }




}
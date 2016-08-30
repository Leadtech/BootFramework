<?php

namespace Boot\Http\Router;

use Boot\Http\Security\RemoteAccessPolicy;

/**
 * Class RouteOptions.
 */
class RouteOptions
{
    /** @var  string */
    protected $routeName;

    /** @var array  */
    protected $defaults = [];

    /** @var array  */
    protected $requirements = [];

    /** @var  RemoteAccessPolicy */
    protected $remoteAccessPolicy;

    /**
     * RouterOptions constructor.
     *
     * @param string     $name
     * @param array|null $defaults
     * @param array      $requirements
     */
    public function __construct($name, array $defaults = null, array $requirements = null)
    {
        $this->routeName = $name;

        if ($defaults) {
            $this->defaults = $defaults;
        }

        if ($requirements) {
            $this->requirements = $requirements;
        }
    }

    /**
     * @return RemoteAccessPolicy
     */
    public function getRemoteAccessPolicy()
    {
        if (!$this->remoteAccessPolicy instanceof RemoteAccessPolicy) {
            $this->remoteAccessPolicy = RemoteAccessPolicy::forPublicService();
        }

        return $this->remoteAccessPolicy;
    }

    /**
     * @param RemoteAccessPolicy $remoteAccessPolicy
     *
     * @return RouteOptions
     */
    public function setRemoteAccessPolicy($remoteAccessPolicy)
    {
        $this->remoteAccessPolicy = $remoteAccessPolicy;

        return $this;
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
     *
     * @return RouteOptions
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }
}

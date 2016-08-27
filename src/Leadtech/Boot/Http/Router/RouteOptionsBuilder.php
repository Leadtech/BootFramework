<?php

namespace Boot\Http\Router;
use Boot\Http\Security\RemoteAccessPolicy;

/**
 * Class RouteOptionsBuilder
 *
 * The number of possible parameters to the route options is growing, introduced builder to get a clean and easy
 * to understand (auto-completion etc) solution for creating RouteOptions instances.
 *
 * @package Boot\Http\Router
 */
class RouteOptionsBuilder
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
     * @return RouteOptions
     */
    public function build()
    {
        $subject = new RouteOptions($this->routeName, $this->defaults, $this->requirements);
        if ($this->remoteAccessPolicy) {
            $subject->setRemoteAccessPolicy($this->remoteAccessPolicy);
        }

        return $subject;
    }

    /**
     * @param string $routeName
     *
     * @return $this
     */
    public function routeName($routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * @param array|null $defaults
     *
     * @return $this
     */
    public function defaults($defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * @param array|null $requirements
     *
     * @return $this
     */
    public function requirements($requirements)
    {
        $this->requirements = $requirements;

        return $this;
    }

    /**
     * @param RemoteAccessPolicy $policy
     *
     * @return $this
     */
    public function remoteAccessPolicy(RemoteAccessPolicy $policy)
    {
        $this->remoteAccessPolicy = $policy;

        return $this;
    }
}
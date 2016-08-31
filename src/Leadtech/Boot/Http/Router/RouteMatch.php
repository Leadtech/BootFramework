<?php

namespace Boot\Http\Router;

use Boot\Http\Exception\ServiceClassNotFoundException;
use Boot\Http\Exception\ServiceLogicException;
use Boot\Http\Exception\ServiceMethodNotFoundException;
use Boot\Http\Service\ServiceInterface;
use Boot\Utils\NetworkUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RouteMatch
 *
 * @package Boot\Http\Router
 */
class RouteMatch
{
    /** @var  string */
    private $serviceClassName;

    /** @var  string */
    private $methodName;

    /** @var array  */
    private $ipWhitelist = [];

    /** @var array  */
    private $hostsWhitelist = [];

    /** @var array  */
    private $ipBlacklist = [];

    /** @var array  */
    private $hostsBlacklist = [];

    /** @var bool  */
    private $publicIpRangesDenied = false;

    /** @var bool  */
    private $privateIpRangesDenied = false;

    /** @var bool  */
    private $reservedIpRangesDenied = false;

    /** @var array  */
    protected $routeParams = [];

    /** @var array  list of expected properties */
    private static $propertyMap = [
        '_serviceClass'           => 'serviceClassName' ,
        '_serviceMethod'          => 'methodName',
        '_privateIpRangesDenied'  => 'privateIpRangesDenied',
        '_reservedIpRangesDenied' => 'reservedIpRangesDenied',
        '_publicIpRangesDenied'   => 'publicIpRangesDenied',
        '_whitelistIps'           => 'ipWhitelist',
        '_whitelistHosts'         => 'hostsWhitelist',
        '_blacklistIps'           => 'ipBlacklist',
        '_blacklistHosts'         => 'hostsBlacklist'
    ];

    /**
     * RouteMatch constructor.
     *
     * @param array $routeMatch
     */
    public function __construct(array $routeMatch)
    {
        // Get the route params + route match properties
        foreach ($routeMatch as $key => $value) {
            if ($this->isClassMember($key, $routeMatch[$key])) {
                $this->{$propertyName} = $routeMatch[$key];
            } else if($this->isRouteParam($key)) {
                $this->routeParams[$key] = $routeMatch[$key];
            }
        }
    }

    /**
     * @param ContainerInterface $serviceContainer
     * @return mixed
     */
    public function getService(ContainerInterface $serviceContainer)
    {
        $this->validate();

        return call_user_func([$this->getServiceClassName(), 'createService'], $serviceContainer);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function verifyClient(Request $request)
    {
        // Declare vars
        $clientIp = $request->getClientIp();
        $host = $request->getHost();

        // Verify IP range
        $accessGranted = true;
        if ($this->isPublicIpRangesDenied() && NetworkUtils::isPublicIpRange($clientIp)
            || $this->isPrivateIpRangesDenied() && NetworkUtils::isPrivateIpRange($clientIp)
            || $this->isReservedIpRangesDenied() && NetworkUtils::isReservedIpRange($clientIp)) {
            // Access denied!
            $accessGranted = false;
        }

        // Verify black/white list
        if ($accessGranted) {
            // Grant access unless the client is on the blacklist.
            return !NetworkUtils::checkIp($clientIp, $this->getIpBlacklist())
                && !NetworkUtils::checkHost($host, $this->getHostsBlacklist());
        } else {
            // Deny access unless the client is on a white list
            return NetworkUtils::checkIp($clientIp, $this->getIpWhitelist())
                || NetworkUtils::checkHost($host, $this->getHostsWhitelist());
        }
    }

    /**
     * @throws ServiceClassNotFoundException
     * @throws ServiceLogicException
     * @throws ServiceMethodNotFoundException
     */
    public function validate()
    {
        if (!class_exists($this->serviceClassName)) {
            throw new ServiceClassNotFoundException($this->serviceClassName);
        }

        // Check if the service exists and implements the ServiceInterface.
        if (!$this->isServiceImplementation($this->serviceClassName)) {
            throw new ServiceLogicException($this->serviceClassName, $this->methodName,
                'The service must implement '.ServiceInterface::class
            );
        }

        if (!method_exists($this->serviceClassName, $this->methodName)) {
            throw new ServiceMethodNotFoundException($this->serviceClassName, $this->methodName);
        }
    }

    /**
     * @return string
     */
    public function getServiceClassName()
    {
        return $this->serviceClassName;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return array
     */
    public function getIpWhitelist()
    {
        return $this->ipWhitelist;
    }

    /**
     * @return array
     */
    public function getHostsWhitelist()
    {
        return $this->hostsWhitelist;
    }

    /**
     * @return array
     */
    public function getIpBlacklist()
    {
        return $this->ipBlacklist;
    }

    /**
     * @return array
     */
    public function getHostsBlacklist()
    {
        return $this->hostsBlacklist;
    }

    /**
     * @return boolean
     */
    public function isPublicIpRangesDenied()
    {
        return $this->publicIpRangesDenied;
    }

    /**
     * @return boolean
     */
    public function isPrivateIpRangesDenied()
    {
        return $this->privateIpRangesDenied;
    }

    /**
     * @return boolean
     */
    public function isReservedIpRangesDenied()
    {
        return $this->reservedIpRangesDenied;
    }

    /**
     * @param string $className for example  MyService::class
     *
     * @return bool
     */
    protected function isServiceImplementation($className)
    {
        return in_array(ServiceInterface::class, (array) @class_implements($className, true));
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    private function isClassMember($key, $value)
    {
        return !empty($value) && array_key_exists($key, static::$propertyMap);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function isRouteParam($key)
    {
        return substr($key, 0, 1) !== '_';
    }
}
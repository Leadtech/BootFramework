<?php

namespace Boot\Http\Security;

/**
 * Class RemoteAccessPolicy.
 */
class RemoteAccessPolicy
{
    /** @var bool */
    private $publicIpRangesDenied = false;

    /** @var bool */
    private $privateIpRangesDenied = false;

    /** @var bool */
    private $reservedIpRangedDenied = false;

    /** @var array */
    private $whitelistIps = [];

    /** @var array */
    private $whitelistHosts = [];

    /** @var array */
    private $blacklistIps = [];

    /** @var array */
    private $blacklistHosts = [];

    /**
     * Factory method to create the default policy for public services.
     *
     * @return RemoteAccessPolicy
     */
    public static function forPublicService()
    {
        $policy = new self();
        $policy->allowAll();

        return $policy;
    }

    /**
     * Factory method to create the default policy for private services.
     *
     * @return RemoteAccessPolicy
     */
    public static function forPrivateService()
    {
        $policy = new self();
        $policy->allowAll()->denyPublicIpRanges();

        return $policy;
    }

    /**
     * @return $this
     */
    public function denyAll()
    {
        $this->reservedIpRangedDenied = true;
        $this->privateIpRangesDenied = true;
        $this->publicIpRangesDenied = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function allowAll()
    {
        $this->reservedIpRangedDenied = false;
        $this->privateIpRangesDenied = false;
        $this->publicIpRangesDenied = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublicIpRangesDenied()
    {
        return $this->publicIpRangesDenied;
    }

    /**
     * @return $this
     */
    public function denyPublicIpRanges()
    {
        $this->publicIpRangesDenied = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function allowPublicIpRanges()
    {
        $this->publicIpRangesDenied = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrivateIpRangesDenied()
    {
        return $this->privateIpRangesDenied;
    }

    /**
     * @return $this
     */
    public function denyPrivateIpRanges()
    {
        $this->privateIpRangesDenied = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function allowPrivateIpRanges()
    {
        $this->privateIpRangesDenied = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReservedIpRangedDenied()
    {
        return $this->reservedIpRangedDenied;
    }

    /**
     * @return $this
     */
    public function denyReservedIpRanges()
    {
        $this->reservedIpRangedDenied = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function allowReversedIpRanges()
    {
        $this->reservedIpRangedDenied = false;

        return $this;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function allowHost($host)
    {
        $this->whitelistHosts[] = $host;

        return $this;
    }

    /**
     * @param string $ip any ipv4 or ipv6 ip address, for ipv4 ranges are also supported e.g.  188.33.*
     *
     * @return $this
     */
    public function allowIpAddress($ip)
    {
        $this->whitelistIps[] = $ip;

        return $this;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function denyHost($host)
    {
        $this->blacklistHosts[] = $host;

        return $this;
    }

    /**
     * @param string $ip any ipv4 or ipv6 ip address, for ipv4 ranges are also supported e.g.  188.33.*
     *
     * @return $this
     */
    public function denyIpAddress($ip)
    {
        $this->blacklistIps[] = $ip;

        return $this;
    }

    /**
     * @return array
     */
    public function getWhitelistIps()
    {
        return $this->whitelistIps;
    }

    /**
     * @return array
     */
    public function getWhitelistHosts()
    {
        return $this->whitelistHosts;
    }

    /**
     * @return array
     */
    public function getBlacklistIps()
    {
        return $this->blacklistIps;
    }

    /**
     * @return array
     */
    public function getBlacklistHosts()
    {
        return $this->blacklistHosts;
    }
}

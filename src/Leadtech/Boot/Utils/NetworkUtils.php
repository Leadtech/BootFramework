<?php

namespace Boot\Utils;

use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Class NetworkUtils.
 *
 * The credits for the comprehensive IP related methods in this class should go to cloud flare.
 * Their functions are more complete than anything else I could find, plus these functions come from the opensource
 * utils repository.
 */
class NetworkUtils extends IpUtils
{
    /**
     * Checks if an IPv4 or IPv6 address is contained in the list of given IPs or subnets.
     *
     * @param string       $requestIp IP to check
     * @param string|array $ips       List of IPs or subnets (can be a string if only a single one)
     *
     * @return bool Whether the IP is valid
     */
    public static function checkIp($requestIp, $ips)
    {
        if (!is_array($ips)) {
            $ips = [$ips];
        }

        $method = substr_count($requestIp, ':') > 1 ? 'checkIp6' : 'checkIp4';

        foreach ($ips as $ip) {
            if (static::$method($requestIp, $ip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $requestIp
     * @param string $ip
     *
     * @return bool
     */
    public static function checkIp4($requestIp, $ip)
    {
        if (!parent::checkIp4($requestIp, $ip)) {
            // More comprehensive check for ipv4 addresses:
            // 1. Wildcard format:     1.2.3.*  or  127.0.*.* etc
            // 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
            // 3. Start-End IP format: 1.2.3.0-1.2.3.255
            return static::ipv4InRange($requestIp, $ip);
        }

        return true;
    }

    /**
     * @param string $ipAddress ipv4 or ipv6 ip address
     *
     * @return bool
     */
    public static function isPublicIpRange($ipAddress)
    {
        return (bool) filter_var(
            $ipAddress,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE
        );
    }

    /**
     * @param string $ipAddress ipv4 or ipv6 ip address
     *
     * @return bool
     */
    public static function isPrivateIpRange($ipAddress)
    {
        return !filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
    }

    /**
     * @param string $ipAddress ipv4 or ipv6 ip address
     *
     * @return bool
     */
    public static function isReservedIpRange($ipAddress)
    {
        return !filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * @param array  $hosts
     * @param string $host
     *
     * @return bool
     */
    public static function checkHost($host, array $hosts)
    {
        foreach ($hosts as $val) {
            if ($host === $val || preg_match('/^.+\.'.preg_quote($val).'$/', $host)) {
                return true;
            }
        }

        return false;
    }

    /**
     * In order to simplify working with IP addresses (in binary) and their
     * netmasks, it is easier to ensure that the binary strings are padded
     * with zeros out to 32 characters - IP addresses are 32 bit numbers.
     *
     * @param $dec
     *
     * @return string
     */
    //private static function decbin32($dec)
    //{
    //    return str_pad(decbin($dec), 32, '0', STR_PAD_LEFT);
    //}

    /**
     * This function takes 2 arguments, an IP address and a "range" in several
     * different formats.
     * Network ranges can be specified as:
     * 1. Wildcard format:     1.2.3.*
     * 2. Start-End IP format: 1.2.3.0-1.2.3.255.
     * 3. Not a range, but providing the same ip address as range will be considered the "same range".
     *
     * The function will return true if the supplied IP is within the range.
     * Note little validation is done on the range inputs - it expects you to
     * use one of the above 3 formats.
     *
     * @param $ip
     * @param $range
     *
     * @return bool
     */
    public static function ipv4InRange($ip, $range)
    {
        if ($ip === $range) {
            return true;
        }

        // range might be 255.255.*.*
        if (strpos($range, '*') !== false) {
            // Convert to A-B format by setting * to 0 for A and 255 for B
            $lower = str_replace('*', '0', $range);
            $upper = str_replace('*', '255', $range);
            $range = "$lower-$upper";
        }

        // range might be 1.2.3.0-1.2.3.255
        if (strpos($range, '-') !== false) {
            list($lower, $upper) = explode('-', $range, 2);
            $lowerDec = (float) sprintf('%u', ip2long($lower));
            $upperDec = (float) sprintf('%u', ip2long($upper));
            $ipDec    = (float) sprintf('%u', ip2long($ip));

            return $ipDec >= $lowerDec && $ipDec <= $upperDec;
        }

        return false;
    }
}

<?php

namespace Boot\Utils;

use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Class NetworkUtils
 *
 * The credits for the comprehensive IP related methods in this class should go to cloud flare.
 * Their functions are more complete than anything else I could find, plus these functions come from the opensource
 * utils repository.
 *
 * @package Boot\Utils
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
            $ips = array($ips);
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
     * @param string $ipAddress   ipv4 or ipv6 ip address
     *
     * @return boolean
     */
    public static function isPublicIpRange($ipAddress)
    {
        return filter_var(
            $ipAddress,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE
        );
    }

    /**
     * @param string $ipAddress   ipv4 or ipv6 ip address
     *
     * @return boolean
     */
    public static function isPrivateIpRange($ipAddress)
    {
        return !filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
    }

    /**
     * @param string $ipAddress   ipv4 or ipv6 ip address
     *
     * @return boolean
     */
    public static function isReservedIpRange($ipAddress)
    {
        return !filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * @param array  $hosts
     * @param string $host
     * @param bool   $includeSubDomains
     *
     * @return bool
     */
    public static function checkHost($host, array $hosts, $includeSubDomains = true)
    {
        foreach ($hosts as $val) {
            if ($host === $val) {
                return true;
            } else if ($includeSubDomains && preg_match('/^.+\.' . preg_quote($val) . '$/', $host)) {
                // Allow the host or any sub domain of this host
                if (!preg_match('/^.*' . preg_quote($val) . '$/', $host)) {
                    return true;
                }
            }
        }

        return false;
    }
    
    /**
     * In order to simplify working with IP addresses (in binary) and their
     * netmasks, it is easier to ensure that the binary strings are padded
     * with zeros out to 32 characters - IP addresses are 32 bit numbers
     *
     * @param $dec
     * @return string
     */
    private static function decbin32 ($dec) {
        return str_pad(decbin($dec), 32, '0', STR_PAD_LEFT);
    }

    /**
     * This function takes 2 arguments, an IP address and a "range" in several
     * different formats.
     * Network ranges can be specified as:
     * 1. Wildcard format:     1.2.3.*
     * 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
     * 3. Start-End IP format: 1.2.3.0-1.2.3.255
     * The function will return true if the supplied IP is within the range.
     * Note little validation is done on the range inputs - it expects you to
     * use one of the above 3 formats.
     * 
     * @param $ip
     * @param $range
     * @return bool
     */
    protected static function ipv4InRange($ip, $range) {
        if (strpos($range, '/') !== false) {
            // $range is in IP/NETMASK format
            list($range, $netmask) = explode('/', $range, 2);
            if (strpos($netmask, '.') !== false) {
                // $netmask is a 255.255.0.0 format
                $netmask = str_replace('*', '0', $netmask);
                $netmaskDec = ip2long($netmask);
                return ( (ip2long($ip) & $netmaskDec) == (ip2long($range) & $netmaskDec) );
            } else {
                // $netmask is a CIDR size block
                // fix the range argument
                $x = explode('.', $range);
                while(count($x)<4) $x[] = '0';
                list($a,$b,$c,$d) = $x;
                $range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
                $rangeDev = ip2long($range);
                $ipDec = ip2long($ip);

                # Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
                #$netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));

                # Strategy 2 - Use math to create it
                $wildcardDec = pow(2, (32-$netmask)) - 1;
                $netmaskDec = ~ $wildcardDec;

                return (($ipDec & $netmaskDec) == ($rangeDev & $netmaskDec));
            }
        } else {
            // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
            if (strpos($range, '*') !==false) { // a.b.*.* format
                // Just convert to A-B format by setting * to 0 for A and 255 for B
                $lower = str_replace('*', '0', $range);
                $upper = str_replace('*', '255', $range);
                $range = "$lower-$upper";
            }

            if (strpos($range, '-')!==false) { // A-B format
                list($lower, $upper) = explode('-', $range, 2);
                $lowerDec = (float)sprintf("%u",ip2long($lower));
                $upperDec = (float)sprintf("%u",ip2long($upper));
                $ipDec = (float)sprintf("%u",ip2long($ip));
                return ( ($ipDec>=$lowerDec) && ($ipDec<=$upperDec) );
            }
            return false;
        }
    }

    /**
     * @param $ip
     * @return string
     */
    protected static function ip2long6($ip) {
        if (substr_count($ip, '::')) {
            $ip = str_replace('::', str_repeat(':0000', 8 - substr_count($ip, ':')) . ':', $ip);
        }

        $ip = explode(':', $ip);
        $rIp = '';
        foreach ($ip as $v) {
            $rIp .= str_pad(base_convert($v, 16, 2), 16, 0, STR_PAD_LEFT);
        }

        return base_convert($rIp, 2, 10);
    }

    /**
     * Get the ipv6 full format and return it as a decimal value.
     *
     * @param string $ip
     *
     * @return string
     */
    protected static function getIpv6Full($ip)
    {
        $pieces = explode ("/", $ip, 2);
        $leftPiece = $pieces[0];
        $rightPiece = $pieces[1];
        // Extract out the main IP pieces
        $ipPieces = explode("::", $leftPiece, 2);
        $mainIpPiece = $ipPieces[0];
        $lastIpPiece = $ipPieces[1];
        // Pad out the shorthand entries.
        $mainIpPieces = explode(":", $mainIpPiece);
        foreach($mainIpPieces as $key=>$val) {
            $mainIpPieces[$key] = str_pad($mainIpPieces[$key], 4, "0", STR_PAD_LEFT);
        }
        // Check to see if the last IP block (part after ::) is set
        $size = count($mainIpPieces);
        if (trim($lastIpPiece) != "") {
            $lastPiece = str_pad($lastIpPiece, 4, "0", STR_PAD_LEFT);

            // Build the full form of the IPV6 address considering the last IP block set
            for ($i = $size; $i < 7; $i++) {
                $mainIpPieces[$i] = "0000";
            }
            $mainIpPieces[7] = $lastPiece;
        }
        else {
            // Build the full form of the IPV6 address
            for ($i = $size; $i < 8; $i++) {
                $mainIpPieces[$i] = "0000";
            }
        }

        // Rebuild the final long form IPV6 address
        $finalIp = implode(":", $mainIpPieces);

        return static::ip2long6($finalIp);
    }


    /**
     * Determine whether the IPV6 address is within range.
     * $ip is the IPV6 address in decimal format to check if its within the IP range created by the cloudflare IPV6
     * address, $rangeIp. $ip and $rangeIp are converted to full IPV6 format.
     *
     * Returns true if the IPV6 address, $ip,  is within the range from $rangeIp.
     * False otherwise.
     *
     * @param $ipv6
     * @param $rangeIp
     * @return bool
     */
    protected static function ipv6InRange($ipv6, $rangeIp)
    {
        $pieces = explode ("/", $rangeIp, 2);
        $leftPiece = $pieces[0];
        $rightPiece = $pieces[1];
        // Extract out the main IP pieces
        $ipPieces = explode("::", $leftPiece, 2);
        $mainIpPiece = $ipPieces[0];
        $lastIpPiece = $ipPieces[1];
        // Pad out the shorthand entries.
        $mainIpPieces = explode(":", $mainIpPiece);
        foreach($mainIpPieces as $key=>$val) {
            $mainIpPieces[$key] = str_pad($mainIpPieces[$key], 4, "0", STR_PAD_LEFT);
        }
        // Create the first and last pieces that will denote the IPV6 range.
        $first = $mainIpPieces;
        $last = $mainIpPieces;
        // Check to see if the last IP block (part after ::) is set
        $lastPiece = "";
        $size = count($mainIpPieces);
        if (trim($lastIpPiece) != "") {
            $lastPiece = str_pad($lastIpPiece, 4, "0", STR_PAD_LEFT);

            // Build the full form of the IPV6 address considering the last IP block set
            for ($i = $size; $i < 7; $i++) {
                $first[$i] = "0000";
                $last[$i] = "ffff";
            }
            $mainIpPieces[7] = $lastPiece;
        }
        else {
            // Build the full form of the IPV6 address
            for ($i = $size; $i < 8; $i++) {
                $first[$i] = "0000";
                $last[$i] = "ffff";
            }
        }
        // Rebuild the final long form IPV6 address
        $first = static::ip2long6(implode(":", $first));
        $last = static::ip2long6(implode(":", $last));
        $inRange = ($ipv6 >= $first && $ipv6 <= $last);

        return $inRange;
    }
}
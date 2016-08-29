<?php

namespace Boot\Tests\Utils;

use Boot\Utils\NetworkUtils;

class NetworkUtilsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function publicIpv4Ranges()
    {
        $this->assertTrue(NetworkUtils::isPublicIpRange(gethostbyname("example.com")));
        $this->assertTrue(NetworkUtils::isPrivateIpRange('192.168.0.1'));
        $this->assertTrue(NetworkUtils::isReservedIpRange('127.0.0.1'));
        $this->assertTrue(NetworkUtils::checkIp('93.184.216.34', ['93.184.216.*']));
        $this->assertTrue(NetworkUtils::checkHost('translate.google.nl', ['google.nl']));
        $this->assertTrue(NetworkUtils::checkHost('translate.google.nl', ['translate.google.nl']));
        $this->assertFalse(NetworkUtils::checkHost('google.nl', ['translate.google.nl']));
        $this->assertTrue(NetworkUtils::checkIp4('93.184.216.34', '93.184.216.34/32'), 'Comparison to ip range in CIDR notation failed!');
        $this->assertTrue(NetworkUtils::ipv4InRange('93.184.216.34', '93.184.*.*'));
        $this->assertTrue(NetworkUtils::ipv4InRange('93.184.216.34', '93.184.216.34/32'));
        $this->assertFalse(NetworkUtils::ipv4InRange('93.184.216.34', '1.1.1.*'));
    }
}
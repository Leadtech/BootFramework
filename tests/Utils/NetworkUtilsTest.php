<?php

namespace Boot\Tests\Utils;

use Boot\Utils\NetworkUtils;

class NetworkUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function ipv4Ranges()
    {
        $this->assertTrue(NetworkUtils::isPublicIpRange(gethostbyname('example.com')));
        $this->assertTrue(NetworkUtils::isPrivateIpRange('192.168.0.1'));
        $this->assertTrue(NetworkUtils::isReservedIpRange('127.0.0.1'));
        $this->assertTrue(NetworkUtils::checkIp('93.184.216.34', ['93.184.216.*']));
        $this->assertTrue(NetworkUtils::checkIp('93.184.216.34', '93.184.216.*'));
        $this->assertTrue(NetworkUtils::checkIp4('93.184.216.34', '93.184.216.34/32'), 'Comparison to ip range in CIDR notation failed!');
        $this->assertTrue(NetworkUtils::ipv4InRange('93.184.216.34', '93.184.*.*'));
        $this->assertTrue(NetworkUtils::ipv4InRange('93.184.216.34', '93.184.216.34'));
        $this->assertTrue(NetworkUtils::ipv4InRange('93.184.216.34', '93.184.216.3-93.184.216.36'));
        $this->assertFalse(NetworkUtils::ipv4InRange('93.184.216.34', '93.184.216.36-93.184.200.36'));
        $this->assertFalse(NetworkUtils::ipv4InRange('93.184.216.34', '1.1.1.*'));
        $this->assertFalse(NetworkUtils::ipv4InRange('93.184.216.34', '93.184.216.35'));
    }

    /**
     * @test
     */
    public function checkHosts()
    {
        $this->assertTrue(NetworkUtils::checkHost('translate.google.nl', ['google.nl']));
        $this->assertTrue(NetworkUtils::checkHost('translate.google.nl', ['translate.google.nl']));
        $this->assertFalse(NetworkUtils::checkHost('google.nl', ['translate.google.nl', 'google.com']), 'The host google.nl is neither equal or a sub domain of any of the provided hosts!');
    }
}

<?php

namespace Boot\Tests\Http\Security;

use Boot\Http\Security\RemoteAccessPolicy;

/**
 * Class RemoteAccessPolicyTest
 *
 * @package Boot\Tests\Http\Security
 */
class RemoteAccessPolicyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function defaults()
    {
        $policy = new RemoteAccessPolicy();
        $this->assertFalse($policy->isPrivateIpRangesDenied());
        $this->assertFalse($policy->isPublicIpRangesDenied());
        $this->assertFalse($policy->isReservedIpRangedDenied());
    }

    /**
     * @test
     */
    public function denyAndAllowAccessIpRanges()
    {
        $policy = new RemoteAccessPolicy();

        $this->assertFalse($policy->isPrivateIpRangesDenied());
        $policy->denyPrivateIpRanges();
        $this->assertTrue($policy->isPrivateIpRangesDenied());
        $policy->allowPrivateIpRanges();
        $this->assertFalse($policy->isPrivateIpRangesDenied());

        $this->assertFalse($policy->isPublicIpRangesDenied());
        $policy->denyPublicIpRanges();
        $this->assertTrue($policy->isPublicIpRangesDenied());
        $policy->allowPublicIpRanges();
        $this->assertFalse($policy->isPublicIpRangesDenied());

        $this->assertFalse($policy->isReservedIpRangedDenied());
        $policy->denyReservedIpRanges();
        $this->assertTrue($policy->isReservedIpRangedDenied());
        $policy->allowReversedIpRanges();
        $this->assertFalse($policy->isReservedIpRangedDenied());

        $policy->allowAll();
        $this->assertFalse($policy->isPrivateIpRangesDenied());
        $this->assertFalse($policy->isPublicIpRangesDenied());
        $this->assertFalse($policy->isReservedIpRangedDenied());

        $policy->denyAll();
        $this->assertTrue($policy->isPrivateIpRangesDenied());
        $this->assertTrue($policy->isPublicIpRangesDenied());
        $this->assertTrue($policy->isReservedIpRangedDenied());
    }

    /**
     * @test
     */
    public function defaultPolicyForPrivateServices()
    {
        $policy = RemoteAccessPolicy::forPrivateService();
        $this->assertTrue($policy->isPublicIpRangesDenied());
        $this->assertFalse($policy->isPrivateIpRangesDenied());
        $this->assertFalse($policy->isReservedIpRangedDenied());
    }

    /**
     * @test
     */
    public function defaultPolicyForPublicServices()
    {
        $policy = RemoteAccessPolicy::forPublicService();
        $this->assertFalse($policy->isPublicIpRangesDenied());
        $this->assertFalse($policy->isPrivateIpRangesDenied());
        $this->assertFalse($policy->isReservedIpRangedDenied());
    }

    /**
     * @test
     */
    public function blacklistHostsAndIpAddresses()
    {
        $policy = RemoteAccessPolicy::forPublicService();
        $policy->denyHost('google.com');
        $policy->denyIpAddress('127.0.0.1');
        $this->assertEquals(['google.com'], $policy->getBlacklistHosts());
        $this->assertEquals(['127.0.0.1'], $policy->getBlacklistIps());
    }

    /**
     * @test
     */
    public function whitelistHostsAndIpAddresses()
    {
        $policy = RemoteAccessPolicy::forPublicService();
        $policy->allowHost('google.com');
        $policy->allowIpAddress('127.0.0.1');
        $this->assertEquals(['google.com'], $policy->getWhitelistHosts());
        $this->assertEquals(['127.0.0.1'], $policy->getWhitelistIps());
    }
}
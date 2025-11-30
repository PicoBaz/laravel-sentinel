<?php

namespace PicoBaz\Sentinel\Tests\Feature;

use Orchestra\Testbench\TestCase;
use PicoBaz\Sentinel\Modules\SecurityMonitor\SecurityHelper;
use PicoBaz\Sentinel\Providers\SentinelServiceProvider;

class SecurityMonitorTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [SentinelServiceProvider::class];
    }

    public function test_can_check_ip_blacklist()
    {
        $ip = '192.168.1.100';
        
        $this->assertFalse(SecurityHelper::isIpBlacklisted($ip));
        
        SecurityHelper::addToBlacklist($ip, 'Test reason');
        
        $this->assertTrue(SecurityHelper::isIpBlacklisted($ip));
    }

    public function test_security_score_calculation()
    {
        $ip = '192.168.1.200';
        
        $score = SecurityHelper::getSecurityScore($ip);
        
        $this->assertEquals(100, $score);
    }

    public function test_threat_level_determination()
    {
        $this->assertEquals('low', SecurityHelper::getThreatLevel(100));
        $this->assertEquals('low', SecurityHelper::getThreatLevel(85));
        $this->assertEquals('medium', SecurityHelper::getThreatLevel(60));
        $this->assertEquals('high', SecurityHelper::getThreatLevel(30));
        $this->assertEquals('critical', SecurityHelper::getThreatLevel(10));
    }

    public function test_can_remove_from_blacklist()
    {
        $ip = '192.168.1.150';
        
        SecurityHelper::addToBlacklist($ip);
        $this->assertTrue(SecurityHelper::isIpBlacklisted($ip));
        
        SecurityHelper::removeFromBlacklist($ip);
        $this->assertFalse(SecurityHelper::isIpBlacklisted($ip));
    }
}

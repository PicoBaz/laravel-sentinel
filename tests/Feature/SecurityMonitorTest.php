<?php

namespace PicoBaz\Sentinel\Tests\Feature;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use PicoBaz\Sentinel\Models\SecurityBlacklist;
use PicoBaz\Sentinel\Modules\SecurityMonitor\SecurityHelper;
use PicoBaz\Sentinel\Tests\TestCase;

class SecurityMonitorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    // ── isIpBlacklisted ───────────────────────────────────────

    public function test_ip_not_in_config_blacklist_returns_false(): void
    {
        config(['sentinel.security.blacklist' => []]);

        $this->assertFalse(SecurityHelper::isIpBlacklisted('192.168.1.1'));
    }

    public function test_ip_in_config_blacklist_returns_true(): void
    {
        config(['sentinel.security.blacklist' => ['192.168.1.100', '10.0.0.5']]);

        $this->assertTrue(SecurityHelper::isIpBlacklisted('192.168.1.100'));
        $this->assertTrue(SecurityHelper::isIpBlacklisted('10.0.0.5'));
        $this->assertFalse(SecurityHelper::isIpBlacklisted('192.168.1.99'));
    }

    // ── addToBlacklist via SecurityBlacklist Model ────────────

    public function test_security_blacklist_model_create_stores_record(): void
    {
        SecurityBlacklist::create([
            'ip' => '10.10.10.10',
            'reason' => 'SQL Injection attempt',
            'threat_count' => 3,
            'security_score' => 70,
            'threat_level' => 'medium',
            'auto_blocked' => false,
            'blocked_at' => now(),
            'last_threat_at' => now(),
        ]);

        $this->assertDatabaseHas('sentinel_security_blacklist', [
            'ip' => '10.10.10.10',
            'reason' => 'SQL Injection attempt',
        ]);
    }

    public function test_security_blacklist_ip_is_unique(): void
    {
        $this->expectException(UniqueConstraintViolationException::class);

        SecurityBlacklist::create([
            'ip' => '1.2.3.4',
            'reason' => 'First block',
            'threat_count' => 1,
            'security_score' => 90,
            'threat_level' => 'low',
            'auto_blocked' => false,
            'blocked_at' => now(),
            'last_threat_at' => now(),
        ]);

        SecurityBlacklist::create([
            'ip' => '1.2.3.4',
            'reason' => 'Duplicate',
            'threat_count' => 1,
            'security_score' => 90,
            'threat_level' => 'low',
            'auto_blocked' => false,
            'blocked_at' => now(),
            'last_threat_at' => now(),
        ]);
    }

    public function test_increment_threat_reduces_security_score(): void
    {
        $record = SecurityBlacklist::create([
            'ip' => '5.5.5.5',
            'reason' => 'Test',
            'threat_count' => 0,
            'security_score' => 100,
            'threat_level' => 'low',
            'auto_blocked' => false,
            'blocked_at' => now(),
            'last_threat_at' => now(),
        ]);

        $record->incrementThreat();
        $record->refresh();

        $this->assertEquals(1, $record->threat_count);
        $this->assertEquals(90, $record->security_score);
    }

    public function test_increment_threat_updates_threat_level_to_critical(): void
    {
        $record = SecurityBlacklist::create([
            'ip' => '6.6.6.6',
            'reason' => 'Test',
            'threat_count' => 0,
            'security_score' => 15,
            'threat_level' => 'high',
            'auto_blocked' => false,
            'blocked_at' => now(),
            'last_threat_at' => now(),
        ]);

        $record->incrementThreat();
        $record->refresh();

        $this->assertEquals('critical', $record->threat_level);
    }

    public function test_scope_auto_blocked_filters_correctly(): void
    {
        SecurityBlacklist::create([
            'ip' => '7.7.7.7',
            'reason' => 'Auto',
            'threat_count' => 5,
            'security_score' => 50,
            'threat_level' => 'medium',
            'auto_blocked' => true,
            'blocked_at' => now(),
            'last_threat_at' => now(),
        ]);

        SecurityBlacklist::create([
            'ip' => '8.8.8.8',
            'reason' => 'Manual',
            'threat_count' => 1,
            'security_score' => 90,
            'threat_level' => 'low',
            'auto_blocked' => false,
            'blocked_at' => now(),
            'last_threat_at' => now(),
        ]);

        $autoBlocked = SecurityBlacklist::autoBlocked()->get();

        $this->assertCount(1, $autoBlocked);
        $this->assertEquals('7.7.7.7', $autoBlocked->first()->ip);
    }

    public function test_scope_critical_filters_by_threat_level(): void
    {
        SecurityBlacklist::create([
            'ip' => '9.9.9.9',
            'reason' => 'Critical IP',
            'threat_count' => 25,
            'security_score' => 0,
            'threat_level' => 'critical',
            'auto_blocked' => true,
            'blocked_at' => now(),
            'last_threat_at' => now(),
        ]);

        SecurityBlacklist::create([
            'ip' => '9.9.9.8',
            'reason' => 'Low IP',
            'threat_count' => 1,
            'security_score' => 90,
            'threat_level' => 'low',
            'auto_blocked' => false,
            'blocked_at' => now(),
            'last_threat_at' => now(),
        ]);

        $critical = SecurityBlacklist::critical()->get();

        $this->assertCount(1, $critical);
        $this->assertEquals('9.9.9.9', $critical->first()->ip);
    }

    // ── getThreatLevel ────────────────────────────────────────

    public function test_get_threat_level_for_score_100_is_low(): void
    {
        $this->assertEquals('low', SecurityHelper::getThreatLevel(100));
    }

    public function test_get_threat_level_for_score_60_is_medium(): void
    {
        $this->assertEquals('medium', SecurityHelper::getThreatLevel(60));
    }

    public function test_get_threat_level_for_score_25_is_high(): void
    {
        $this->assertEquals('high', SecurityHelper::getThreatLevel(25));
    }

    public function test_get_threat_level_for_score_10_is_critical(): void
    {
        $this->assertEquals('critical', SecurityHelper::getThreatLevel(10));
    }

    // ── addToBlacklist / removeFromBlacklist (Cache-based) ────

    public function test_add_to_blacklist_stores_ip_in_cache(): void
    {
        config(['sentinel.security.blacklist' => []]);

        SecurityHelper::addToBlacklist('99.99.99.99', 'Test reason');

        $cached = SecurityHelper::getBlacklist();
        $this->assertContains('99.99.99.99', $cached);
    }

    public function test_add_to_blacklist_does_not_duplicate_ip(): void
    {
        config(['sentinel.security.blacklist' => []]);
        Cache::put('sentinel:blacklist', ['99.99.99.99'], now()->addDay());

        SecurityHelper::addToBlacklist('99.99.99.99', 'Duplicate test');

        $cached = SecurityHelper::getBlacklist();
        $this->assertCount(1, array_filter($cached, fn ($ip) => $ip === '99.99.99.99'));
    }

    public function test_remove_from_blacklist_removes_ip_from_cache(): void
    {
        Cache::put('sentinel:blacklist', ['11.11.11.11', '22.22.22.22'], now()->addDay());

        SecurityHelper::removeFromBlacklist('11.11.11.11');

        $cached = SecurityHelper::getBlacklist();
        $this->assertNotContains('11.11.11.11', $cached);
        $this->assertContains('22.22.22.22', $cached);
    }
}

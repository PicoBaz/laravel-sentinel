<?php

namespace PicoBaz\Sentinel\Tests\Feature;

use PicoBaz\Sentinel\Facades\Sentinel;
use PicoBaz\Sentinel\Tests\TestCase;

class ArtisanCommandsTest extends TestCase
{
    // ── sentinel:status ───────────────────────────────────────

    public function test_sentinel_status_command_runs_successfully(): void
    {
        $this->artisan('sentinel:status')
            ->assertExitCode(0);
    }

    public function test_sentinel_status_shows_zero_when_no_logs(): void
    {
        $this->artisan('sentinel:status')
            ->expectsOutputToContain('0')
            ->assertExitCode(0);
    }

    public function test_sentinel_status_shows_correct_log_count(): void
    {
        $this->seedPerformanceLogs(3);
        $this->seedExceptionLogs(2);

        $this->artisan('sentinel:status')
            ->expectsOutputToContain('5')
            ->assertExitCode(0);
    }

    // ── sentinel:security-report ──────────────────────────────

    public function test_security_report_command_runs_successfully(): void
    {
        $this->artisan('sentinel:security-report', ['--hours' => 24])
            ->assertExitCode(0);
    }

    public function test_security_report_with_custom_hours(): void
    {
        $this->artisan('sentinel:security-report', ['--hours' => 48])
            ->assertExitCode(0);
    }

    // ── sentinel:ai-insights ──────────────────────────────────

    public function test_ai_insights_command_runs_successfully(): void
    {
        $this->artisan('sentinel:ai-insights')
            ->assertExitCode(0);
    }

    public function test_ai_insights_command_with_refresh_flag(): void
    {
        $this->artisan('sentinel:ai-insights', ['--refresh' => true])
            ->assertExitCode(0);
    }

    public function test_ai_insights_shows_health_score(): void
    {
        $this->artisan('sentinel:ai-insights')
            ->expectsOutputToContain('Health')
            ->assertExitCode(0);
    }

    // ── sentinel:cost-optimizer ───────────────────────────────

    public function test_cost_optimizer_command_runs_successfully(): void
    {
        $this->artisan('sentinel:cost-optimizer')
            ->assertExitCode(0);
    }

    public function test_cost_optimizer_command_with_refresh_flag(): void
    {
        $this->artisan('sentinel:cost-optimizer', ['--refresh' => true])
            ->assertExitCode(0);
    }

    public function test_cost_optimizer_shows_cost_overview(): void
    {
        $this->artisan('sentinel:cost-optimizer', ['--refresh' => true])
            ->expectsOutputToContain('Cost')
            ->assertExitCode(0);
    }

    // ── sentinel:install ──────────────────────────────────────

    public function test_sentinel_install_command_runs_successfully(): void
    {
        $this->artisan('sentinel:install')
            ->assertExitCode(0);
    }
}

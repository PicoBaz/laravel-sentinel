<?php

namespace PicoBaz\Sentinel\Tests\Feature;

use Illuminate\Support\Facades\Http;
use PicoBaz\Sentinel\Models\SentinelLog;
use PicoBaz\Sentinel\Notifications\Channels\DiscordChannel;
use PicoBaz\Sentinel\Notifications\Channels\SlackChannel;
use PicoBaz\Sentinel\Notifications\Channels\TelegramChannel;
use PicoBaz\Sentinel\Tests\TestCase;

class NotificationChannelsTest extends TestCase
{
    private SentinelLog $criticalLog;

    private SentinelLog $warningLog;

    private SentinelLog $infoLog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->criticalLog = SentinelLog::create([
            'type' => 'exception',
            'data' => ['message' => 'Critical error occurred', 'file' => '/app/Test.php', 'line' => 1],
            'severity' => 'critical',
            'created_at' => now(),
        ]);

        $this->warningLog = SentinelLog::create([
            'type' => 'query',
            'data' => ['sql' => 'SELECT *', 'time' => 1500],
            'severity' => 'warning',
            'created_at' => now(),
        ]);

        $this->infoLog = SentinelLog::create([
            'type' => 'performance',
            'data' => ['url' => '/api/test', 'response_time' => 200],
            'severity' => 'info',
            'created_at' => now(),
        ]);
    }

    // ── Telegram ──────────────────────────────────────────────

    public function test_telegram_sends_message_when_credentials_configured(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);

        config(['sentinel.notifications.telegram.bot_token' => 'fake-bot-token']);
        config(['sentinel.notifications.telegram.chat_id' => '123456789']);

        $channel = new TelegramChannel;
        $channel->send($this->criticalLog);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.telegram.org')
                && str_contains($request->url(), 'sendMessage')
                && $request['chat_id'] === '123456789'
                && $request['parse_mode'] === 'HTML'
                && str_contains($request['text'], 'CRITICAL');
        });
    }

    public function test_telegram_does_not_send_when_bot_token_missing(): void
    {
        Http::fake();

        config(['sentinel.notifications.telegram.bot_token' => null]);
        config(['sentinel.notifications.telegram.chat_id' => '123456789']);

        $channel = new TelegramChannel;
        $channel->send($this->criticalLog);

        Http::assertNothingSent();
    }

    public function test_telegram_does_not_send_when_chat_id_missing(): void
    {
        Http::fake();

        config(['sentinel.notifications.telegram.bot_token' => 'fake-token']);
        config(['sentinel.notifications.telegram.chat_id' => null]);

        $channel = new TelegramChannel;
        $channel->send($this->criticalLog);

        Http::assertNothingSent();
    }

    public function test_telegram_message_contains_severity_emoji_for_critical(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        config(['sentinel.notifications.telegram.bot_token' => 'tok']);
        config(['sentinel.notifications.telegram.chat_id' => '1']);

        $channel = new TelegramChannel;
        $channel->send($this->criticalLog);

        Http::assertSent(function ($request) {
            return str_contains($request['text'], '🚨');
        });
    }

    public function test_telegram_message_contains_warning_emoji(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        config(['sentinel.notifications.telegram.bot_token' => 'tok']);
        config(['sentinel.notifications.telegram.chat_id' => '1']);

        $channel = new TelegramChannel;
        $channel->send($this->warningLog);

        Http::assertSent(function ($request) {
            return str_contains($request['text'], '⚠️');
        });
    }

    // ── Slack ─────────────────────────────────────────────────

    public function test_slack_sends_message_when_webhook_configured(): void
    {
        Http::fake([
            'hooks.slack.com/*' => Http::response('ok', 200),
        ]);

        config(['sentinel.notifications.slack.webhook_url' => 'https://hooks.slack.com/fake']);

        $channel = new SlackChannel;
        $channel->send($this->criticalLog);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'hooks.slack.com')
                && isset($request['attachments'])
                && $request['attachments'][0]['color'] === 'danger';
        });
    }

    public function test_slack_does_not_send_when_webhook_missing(): void
    {
        Http::fake();

        config(['sentinel.notifications.slack.webhook_url' => null]);

        $channel = new SlackChannel;
        $channel->send($this->criticalLog);

        Http::assertNothingSent();
    }

    public function test_slack_uses_warning_color_for_warning_severity(): void
    {
        Http::fake(['hooks.slack.com/*' => Http::response('ok')]);

        config(['sentinel.notifications.slack.webhook_url' => 'https://hooks.slack.com/fake']);

        $channel = new SlackChannel;
        $channel->send($this->warningLog);

        Http::assertSent(function ($request) {
            return $request['attachments'][0]['color'] === 'warning';
        });
    }

    public function test_slack_uses_good_color_for_info_severity(): void
    {
        Http::fake(['hooks.slack.com/*' => Http::response('ok')]);

        config(['sentinel.notifications.slack.webhook_url' => 'https://hooks.slack.com/fake']);

        $channel = new SlackChannel;
        $channel->send($this->infoLog);

        Http::assertSent(function ($request) {
            return $request['attachments'][0]['color'] === 'good';
        });
    }

    public function test_slack_message_contains_log_type(): void
    {
        Http::fake(['hooks.slack.com/*' => Http::response('ok')]);

        config(['sentinel.notifications.slack.webhook_url' => 'https://hooks.slack.com/fake']);

        $channel = new SlackChannel;
        $channel->send($this->criticalLog);

        Http::assertSent(function ($request) {
            return str_contains($request['text'], 'exception');
        });
    }

    // ── Discord ───────────────────────────────────────────────

    public function test_discord_sends_embed_when_webhook_configured(): void
    {
        Http::fake([
            'discord.com/*' => Http::response([], 204),
        ]);

        config(['sentinel.notifications.discord.webhook_url' => 'https://discord.com/api/webhooks/fake']);

        $channel = new DiscordChannel;
        $channel->send($this->criticalLog);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'discord.com')
                && isset($request['embeds'])
                && $request['embeds'][0]['color'] === 15158332;
        });
    }

    public function test_discord_does_not_send_when_webhook_missing(): void
    {
        Http::fake();

        config(['sentinel.notifications.discord.webhook_url' => null]);

        $channel = new DiscordChannel;
        $channel->send($this->criticalLog);

        Http::assertNothingSent();
    }

    public function test_discord_uses_yellow_color_for_warning(): void
    {
        Http::fake(['discord.com/*' => Http::response([], 204)]);

        config(['sentinel.notifications.discord.webhook_url' => 'https://discord.com/api/webhooks/fake']);

        $channel = new DiscordChannel;
        $channel->send($this->warningLog);

        Http::assertSent(function ($request) {
            return $request['embeds'][0]['color'] === 16776960;
        });
    }

    public function test_discord_uses_blue_color_for_info(): void
    {
        Http::fake(['discord.com/*' => Http::response([], 204)]);

        config(['sentinel.notifications.discord.webhook_url' => 'https://discord.com/api/webhooks/fake']);

        $channel = new DiscordChannel;
        $channel->send($this->infoLog);

        Http::assertSent(function ($request) {
            return $request['embeds'][0]['color'] === 3447003;
        });
    }

    public function test_discord_embed_contains_timestamp(): void
    {
        Http::fake(['discord.com/*' => Http::response([], 204)]);

        config(['sentinel.notifications.discord.webhook_url' => 'https://discord.com/api/webhooks/fake']);

        $channel = new DiscordChannel;
        $channel->send($this->criticalLog);

        Http::assertSent(function ($request) {
            return isset($request['embeds'][0]['timestamp']);
        });
    }
}

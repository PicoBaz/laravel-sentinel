<?php

namespace PicoBaz\Sentinel\Notifications\Channels;

use Illuminate\Support\Facades\Http;

class DiscordChannel
{
    public function send($log)
    {
        $webhookUrl = config('sentinel.notifications.discord.webhook_url');

        if (!$webhookUrl) {
            return;
        }

        Http::post($webhookUrl, [
            'embeds' => [
                [
                    'title' => "Sentinel Alert: {$log->type}",
                    'color' => $this->getSeverityColor($log->severity),
                    'fields' => [
                        [
                            'name' => 'Type',
                            'value' => $log->type,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Severity',
                            'value' => strtoupper($log->severity),
                            'inline' => true,
                        ],
                        [
                            'name' => 'Data',
                            'value' => "```json\n" . json_encode($log->data, JSON_PRETTY_PRINT) . "\n```",
                        ],
                    ],
                    'timestamp' => $log->created_at->toIso8601String(),
                    'footer' => [
                        'text' => 'Laravel Sentinel',
                    ],
                ],
            ],
        ]);
    }

    protected function getSeverityColor($severity)
    {
        return match($severity) {
            'critical' => 15158332,
            'warning' => 16776960,
            default => 3447003,
        };
    }
}

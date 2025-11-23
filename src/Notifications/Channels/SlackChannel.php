<?php

namespace PicoBaz\Sentinel\Notifications\Channels;

use Illuminate\Support\Facades\Http;

class SlackChannel
{
    public function send($log)
    {
        $webhookUrl = config('sentinel.notifications.slack.webhook_url');

        if (!$webhookUrl) {
            return;
        }

        Http::post($webhookUrl, [
            'text' => "Sentinel Alert: {$log->type}",
            'attachments' => [
                [
                    'color' => $this->getSeverityColor($log->severity),
                    'fields' => [
                        [
                            'title' => 'Type',
                            'value' => $log->type,
                            'short' => true,
                        ],
                        [
                            'title' => 'Severity',
                            'value' => strtoupper($log->severity),
                            'short' => true,
                        ],
                        [
                            'title' => 'Data',
                            'value' => "```" . json_encode($log->data, JSON_PRETTY_PRINT) . "```",
                        ],
                    ],
                    'footer' => 'Laravel Sentinel',
                    'ts' => $log->created_at->timestamp,
                ],
            ],
        ]);
    }

    protected function getSeverityColor($severity)
    {
        return match($severity) {
            'critical' => 'danger',
            'warning' => 'warning',
            default => 'good',
        };
    }
}

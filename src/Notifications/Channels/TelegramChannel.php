<?php

namespace PicoBaz\Sentinel\Notifications\Channels;

use Illuminate\Support\Facades\Http;

class TelegramChannel
{
    public function send($log)
    {
        $botToken = config('sentinel.notifications.telegram.bot_token');
        $chatId = config('sentinel.notifications.telegram.chat_id');

        if (!$botToken || !$chatId) {
            return;
        }

        $message = $this->formatMessage($log);

        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
    }

    protected function formatMessage($log)
    {
        $emoji = $this->getSeverityEmoji($log->severity);
        
        return sprintf(
            "%s <b>Sentinel Alert</b>\n\n<b>Type:</b> %s\n<b>Severity:</b> %s\n<b>Time:</b> %s\n\n<code>%s</code>",
            $emoji,
            $log->type,
            strtoupper($log->severity),
            $log->created_at->format('Y-m-d H:i:s'),
            json_encode($log->data, JSON_PRETTY_PRINT)
        );
    }

    protected function getSeverityEmoji($severity)
    {
        return match($severity) {
            'critical' => '🚨',
            'warning' => '⚠️',
            default => 'ℹ️',
        };
    }
}

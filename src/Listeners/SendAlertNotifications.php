<?php

namespace PicoBaz\Sentinel\Listeners;

use PicoBaz\Sentinel\Events\AlertTriggered;
use PicoBaz\Sentinel\Notifications\Channels\TelegramChannel;
use PicoBaz\Sentinel\Notifications\Channels\SlackChannel;
use PicoBaz\Sentinel\Notifications\Channels\DiscordChannel;
use Illuminate\Support\Facades\Mail;

class SendAlertNotifications
{
    public function handle(AlertTriggered $event)
    {
        $channels = config('sentinel.notifications.channels');

        if ($channels['telegram']) {
            (new TelegramChannel())->send($event->log);
        }

        if ($channels['slack']) {
            (new SlackChannel())->send($event->log);
        }

        if ($channels['discord']) {
            (new DiscordChannel())->send($event->log);
        }

        if ($channels['email']) {
            $this->sendEmail($event->log);
        }
    }

    protected function sendEmail($log)
    {
        $recipients = config('sentinel.notifications.email.recipients');
        
        foreach ($recipients as $recipient) {
            Mail::raw($this->formatMessage($log), function ($message) use ($recipient, $log) {
                $message->to($recipient)
                    ->subject("Sentinel Alert: {$log->type} [{$log->severity}]");
            });
        }
    }

    protected function formatMessage($log)
    {
        return sprintf(
            "Type: %s\nSeverity: %s\nTime: %s\nData: %s",
            $log->type,
            $log->severity,
            $log->created_at,
            json_encode($log->data, JSON_PRETTY_PRINT)
        );
    }
}

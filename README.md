# 🛡️ Laravel Sentinel

Advanced monitoring and alerting system for Laravel applications with real-time notifications across multiple channels.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/php-%5E8.1-blue)
![Laravel](https://img.shields.io/badge/laravel-%5E10.0%7C%5E11.0%5E12.0-red)

## ✨ Features

- 🔍 **Query Monitoring** - Detect and log slow database queries
- 💾 **Memory Monitoring** - Track memory usage and prevent leaks
- 🚨 **Exception Monitoring** - Catch and categorize exceptions
- ⚡ **Performance Monitoring** - Monitor response times
- 📊 **Beautiful Dashboard** - Real-time metrics visualization
- 🔔 **Multi-Channel Alerts** - Slack, Telegram, Discord, Email
- 🧩 **Modular Architecture** - Easily extend with custom modules
- ⚙️ **Smart Thresholds** - Configurable alert triggers
- 📈 **Analytics** - Detailed performance insights

## 📦 Installation

```bash
composer require picobaz/laravel-sentinel
```

### Publish Configuration

```bash
php artisan sentinel:install
```

This will:
- Publish configuration file to `config/sentinel.php`
- Publish views to `resources/views/vendor/sentinel`
- Run migrations

## ⚙️ Configuration

Edit `config/sentinel.php`:

```php
return [
    'enabled' => true,
    
    'modules' => [
        'queryMonitor' => true,
        'memoryMonitor' => true,
        'exceptionMonitor' => true,
        'performanceMonitor' => true,
    ],
    
    'thresholds' => [
        'query_time' => 1000,
        'memory_usage' => 128,
        'response_time' => 2000,
    ],
    
    'notifications' => [
        'channels' => [
            'telegram' => true,
            'slack' => false,
            'email' => true,
            'discord' => false,
        ],
    ],
];
```

### Environment Variables

```env
SENTINEL_ENABLED=true

SENTINEL_QUERY_TIME_THRESHOLD=1000
SENTINEL_MEMORY_THRESHOLD=128
SENTINEL_RESPONSE_TIME_THRESHOLD=2000

SENTINEL_TELEGRAM_ENABLED=true
SENTINEL_TELEGRAM_BOT_TOKEN=your_bot_token
SENTINEL_TELEGRAM_CHAT_ID=your_chat_id

SENTINEL_SLACK_ENABLED=false
SENTINEL_SLACK_WEBHOOK=your_webhook_url

SENTINEL_EMAIL_ENABLED=true
SENTINEL_EMAIL_RECIPIENTS=admin@example.com,dev@example.com
```

## 🚀 Usage

### Automatic Monitoring

Sentinel automatically monitors your application once installed. All modules run in the background.

### Manual Logging

```php
use PicoBaz\Sentinel\Facades\Sentinel;

Sentinel::log('custom', [
    'action' => 'user_login',
    'user_id' => 123,
    'ip' => request()->ip(),
]);
```

### Middleware

Add to specific routes:

```php
Route::middleware(['sentinel'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### Dashboard

Access the dashboard at: `http://your-app.test/sentinel`

### Artisan Commands

```bash
php artisan sentinel:status
```

## 🧩 Creating Custom Modules

```php
namespace App\Sentinel\Modules;

class CustomModule
{
    public function boot()
    {
        // Your monitoring logic
    }
}
```

Register in `config/sentinel.php`:

```php
'modules' => [
    'customModule' => true,
],
```

## 📱 Notification Channels

### Telegram

1. Create a bot via [@BotFather](https://t.me/botfather)
2. Get your chat ID from [@userinfobot](https://t.me/userinfobot)
3. Configure in `.env`

### Slack

1. Create incoming webhook in Slack
2. Add webhook URL to `.env`

### Discord

1. Create webhook in Discord server settings
2. Add webhook URL to `.env`

## 📊 Metrics API

```php
GET /sentinel/metrics/{type}?hours=24
```

Types: `query`, `memory`, `exception`, `performance`

Response:
```json
[
    {
        "id": 1,
        "type": "query",
        "data": {...},
        "severity": "warning",
        "created_at": "2024-01-01 12:00:00"
    }
]
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 👤 Author

**PicoBaz**
- Email: picobaz3@gmail.com
- GitHub: [@PicoBaz](https://github.com/PicoBaz)
- Telegram: [@picobaz](https://t.me/picobaz)

## 🌟 Support

If you find this package helpful, please consider giving it a ⭐ on [GitHub](https://github.com/PicoBaz/laravel-sentinel)!

## 📚 Documentation

For detailed documentation, visit [https://github.com/PicoBaz/laravel-sentinel/wiki](https://github.com/PicoBaz/laravel-sentinel)

## 🐛 Issues

Report issues at [https://github.com/PicoBaz/laravel-sentinel/issues](https://github.com/PicoBaz/laravel-sentinel/issues)

# 🛡️ Laravel Sentinel

Advanced monitoring and alerting system for Laravel applications with real-time notifications across multiple channels.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/php-%5E8.1-blue)
![Laravel](https://img.shields.io/badge/laravel-%5E10.0%7C%5E11.0%7C%5E12.0-red)

## ✨ Features

- 🔍 **Query Monitoring** - Detect and log slow database queries
- 💾 **Memory Monitoring** - Track memory usage and prevent leaks
- 🚨 **Exception Monitoring** - Catch and categorize exceptions
- ⚡ **Performance Monitoring** - Monitor response times
- 🔐 **Security Monitoring** - Track security threats and attacks
- 🤖 **AI Insights & Predictions** - Machine learning powered analysis (NEW v1.2.0)
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
php artisan sentinel:security-report --hours=24
php artisan sentinel:ai-insights --refresh
```

## 🤖 AI Insights & Predictions (NEW in v1.2.0)

### Overview

The AI Insights module uses machine learning algorithms to analyze your application's behavior and provide actionable insights:

**Capabilities:**
- 📊 Pattern recognition and analysis
- 🔍 Anomaly detection across all metrics
- 🔮 Performance predictions (24h and 7-day forecasts)
- 💡 Automated optimization recommendations
- ⚠️ Downtime risk assessment
- 📈 Trend analysis and forecasting

### Features

#### 1. Pattern Analysis
- **Peak Hours Detection** - Identifies when your app experiences highest load
- **Slow Endpoint Identification** - Automatically finds performance bottlenecks
- **Memory Trend Analysis** - Tracks memory usage patterns over time
- **Error Pattern Recognition** - Detects recurring errors and their frequency

#### 2. Anomaly Detection
Uses statistical analysis (Z-score) to detect unusual behavior:
- Response time anomalies
- Memory usage spikes
- Unusual error rates
- Query count anomalies

#### 3. Predictive Analytics
Machine learning predictions for:
- **Performance Trends** - Will your app get slower or faster?
- **Memory Usage** - Predict memory consumption for next 7 days
- **Error Rate Forecasting** - Anticipate error increases
- **Downtime Risk Scoring** - 0-100 risk score with severity levels

#### 4. Smart Recommendations
AI-generated actionable recommendations:
- Optimize slow endpoints
- Scale during peak hours
- Memory optimization suggestions
- Critical issue alerts

### Configuration

```env
SENTINEL_AI_INSIGHTS=true
SENTINEL_AI_ANALYSIS_FREQUENCY=hourly
SENTINEL_AI_PREDICTION_WINDOW=24
SENTINEL_AI_ANOMALY_THRESHOLD=2.5
SENTINEL_AI_MIN_SAMPLES=20
```

### Usage

#### View AI Insights

```bash
php artisan sentinel:ai-insights

# Refresh and view
php artisan sentinel:ai-insights --refresh
```

Output includes:
```
🏥 System Health
Score: 85/100 - Status: GOOD

⚠️  Anomalies Detected
  response_time: 3 anomalies
    Threshold: 2500ms | Max: 4200ms

🔮 Predictions
  📉 Performance: degrading
    Current: 1200ms | 24h: 1350ms | 7d: 1800ms
  
  ⬆️ Memory: increasing
    Current: 85MB | 24h: 92MB | 7d: 115MB

💡 AI Recommendations
  🚨 [critical] Memory Threshold Breach Predicted
    Memory usage is predicted to exceed threshold within 7 days
    → Investigate memory leaks and optimize memory-intensive operations
```

#### Programmatic Access

```php
use PicoBaz\Sentinel\Modules\AIInsights\AIInsightsHelper;

$healthScore = AIInsightsHelper::getHealthScore();

$predictions = AIInsightsHelper::getPredictions();

$anomalies = AIInsightsHelper::getAnomalies();

$recommendations = AIInsightsHelper::getRecommendations();

$summary = AIInsightsHelper::getInsightsSummary();

if (AIInsightsHelper::hasCriticalRecommendations()) {
    
}
```

#### Scheduling

AI analysis runs automatically every hour. Customize in your `AppServiceProvider`:

```php
use PicoBaz\Sentinel\Modules\AIInsights\AIInsightsModule;

$module = app(AIInsightsModule::class);
$module->analyzePatterns();
$module->detectAnomalies();
$module->generatePredictions();
$module->generateRecommendations();
```

### How It Works

#### Pattern Analysis Algorithm
1. Collects logs from past 7 days
2. Groups data by time, endpoint, type
3. Calculates statistical distributions
4. Identifies significant patterns

#### Anomaly Detection (Z-Score)
```
Anomaly = |value - mean| > (threshold * standard_deviation)
Default threshold: 2.5 (captures 99% of normal data)
```

#### Trend Prediction (Linear Regression)
```
Future Value = Current Average + (Trend * Time Period)
Trend = Slope calculated using least squares method
```

#### Health Score Calculation
```
Health Score = 100 - Downtime Risk - (Active Anomalies * 10)
Range: 0-100
Status: Excellent (80+) | Good (60+) | Fair (40+) | Poor (20+) | Critical (<20)
```

### Real-World Examples

#### Example 1: Memory Leak Detection
```bash
$ php artisan sentinel:ai-insights

🔮 Predictions
  ⬆️ Memory: increasing
    Current: 128MB | 7d: 195MB
    ⚠️  WARNING: Threshold breach predicted!

💡 AI Recommendations
  🚨 [critical] Memory Threshold Breach Predicted
    → Investigate memory leaks in scheduled jobs
```

#### Example 2: Performance Degradation
```bash
📊 Patterns Analysis
  🐌 Slowest Endpoints:
    /api/reports: 3500ms avg (245 requests)
    /dashboard: 2100ms avg (1200 requests)

💡 AI Recommendations
  ⚠️ [high] Optimize Slow Endpoints
    → Add database indexes for reports queries
```

#### Example 3: Peak Hour Scaling
```bash
📊 Patterns Analysis
  🕐 Peak Hours: 9:00, 10:00, 11:00, 14:00
    Average Load: 450 | Peak: 1200

💡 AI Recommendations
  📌 [medium] Scale During Peak Hours
    → Consider auto-scaling during 9:00-11:00, 14:00
```

### API Integration

Get insights via dashboard API:

```php
Route::get('/api/sentinel/ai-insights', function () {
    return response()->json(AIInsightsHelper::getInsightsSummary());
});
```

Response:
```json
{
  "patterns": {
    "peak_hours": {"hours": [9, 10, 14], "peak_load": 1200},
    "slow_endpoints": {...}
  },
  "anomalies": {
    "response_time": {"detected": true, "count": 3}
  },
  "predictions": {
    "performance": {"trend": "degrading", "prediction_7d": 1800},
    "downtime_risk": {"level": "medium", "score": 45}
  },
  "recommendations": [...]
}
```

## 🔐 Security Monitoring

### Features

The Security Monitor module tracks and prevents security threats:

- ✅ Failed login attempts
- ✅ SQL Injection attempts
- ✅ XSS (Cross-Site Scripting) attempts
- ✅ Path traversal attempts
- ✅ Command injection attempts
- ✅ Rate limiting violations
- ✅ Unauthorized access attempts
- ✅ File integrity monitoring
- ✅ IP blacklisting (manual & automatic)
- ✅ Security scoring system

### Configuration

```env
SENTINEL_SECURITY_MONITOR=true
SENTINEL_SECURITY_AUTO_BLOCK=true
SENTINEL_SECURITY_AUTO_BLOCK_SCORE=20
SENTINEL_SECURITY_BLACKLIST=192.168.1.100,10.0.0.5
```

### Middleware

Add security middleware to protect routes:

```php
Route::middleware(['sentinel.security'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});
```

### Security Helper

```php
use PicoBaz\Sentinel\Modules\SecurityMonitor\SecurityHelper;

SecurityHelper::getSecurityScore('192.168.1.1');

SecurityHelper::addToBlacklist('192.168.1.100', 'Multiple failed logins');

SecurityHelper::removeFromBlacklist('192.168.1.100');

SecurityHelper::isIpBlacklisted('192.168.1.100');

SecurityHelper::getThreatLevel(45);
```

### File Integrity Monitoring

```php
use PicoBaz\Sentinel\Modules\SecurityMonitor\SecurityMonitorModule;

$monitor = app(SecurityMonitorModule::class);
$monitor->checkFileIntegrity([
    base_path('.env'),
    base_path('composer.json'),
]);
```

### Security Report

Generate comprehensive security reports:

```bash
php artisan sentinel:security-report --hours=24
```

Output includes:
- Failed login attempts
- Suspicious requests by type
- Rate limiting violations
- Top threat IPs with security scores
- Blacklisted IPs

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

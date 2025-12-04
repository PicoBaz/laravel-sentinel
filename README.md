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
- 🤖 **AI Insights & Predictions** - Machine learning powered analysis (v1.2.0)
- 💰 **Cost Optimizer** - Infrastructure cost analysis and optimization (NEW v1.3.0)
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
php artisan sentinel:cost-optimizer --refresh
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

## 💰 Infrastructure Cost Optimizer (NEW in v1.3.0)

### Overview

Optimize your infrastructure costs with AI-powered analysis and actionable recommendations. Track spending, identify waste, and save money while maintaining performance.

**Key Features:**
- 💻 Multi-Cloud Cost Analysis (AWS, DigitalOcean, Linode)
- 📊 Complete Cost Breakdown (Compute, Database, Storage, Network, Cache)
- 💡 Smart Optimization Recommendations
- 📈 ROI Calculator & Payback Analysis
- ⚡ Efficiency Scoring (A-F Grade)
- 💰 Potential Savings Identification

### Quick Start

```bash
# Analyze infrastructure costs
php artisan sentinel:cost-optimizer

# Force refresh analysis
php artisan sentinel:cost-optimizer --refresh
```

### Sample Output

```
💰 Cost Overview
┌─────────────────────┬──────────┐
│ Metric              │ Value    │
├─────────────────────┼──────────┤
│ Monthly Cost        │ $152.50  │
│ Yearly Cost         │ $1,830.00│
│ Cost per 1K Requests│ $0.0234  │
└─────────────────────┴──────────┘

📊 Cost Breakdown
┌──────────┬──────────────┬───────┬──────────────┐
│ Category │ Monthly Cost │ Share │ Distribution │
├──────────┼──────────────┼───────┼──────────────┤
│ Compute  │ $60.00       │ 39.3% │ ████░░░░░░░░ │
│ Database │ $45.00       │ 29.5% │ ███░░░░░░░░░ │
│ Storage  │ $15.00       │ 9.8%  │ █░░░░░░░░░░░ │
│ Network  │ $30.00       │ 19.7% │ ██░░░░░░░░░░ │
│ Cache    │ $2.50        │ 1.6%  │ ░░░░░░░░░░░░ │
└──────────┴──────────────┴───────┴──────────────┘

⚡ Efficiency Score
Grade: B | Score: 82/100
Good! Some minor optimizations available.

💡 Optimization Recommendations
  ⚠️ [high] Database Query Optimization
    ⚡ Performance Gain: 40-80%
    → Review queries with: php artisan sentinel:query-report

  📌 [medium] Image Optimization
    💰 Savings: $12.00/month
    → Implement WebP format and lazy loading

💵 Total Potential Savings: $12.00/month ($144.00/year)
```

### Programmatic Usage

```php
use PicoBaz\Sentinel\Modules\CostOptimizer\CostOptimizerHelper;

// Get total costs
$monthly = CostOptimizerHelper::getTotalMonthlyCost();
$yearly = CostOptimizerHelper::getTotalYearlyCost();

// Cost breakdown by category
$breakdown = CostOptimizerHelper::getCostBreakdown();
// Returns: ['compute' => 60.00, 'database' => 45.00, ...]

// Get potential savings
$savings = CostOptimizerHelper::getPotentialSavings();

// Efficiency metrics
$score = CostOptimizerHelper::getEfficiencyScore();  // 0-100
$grade = CostOptimizerHelper::getEfficiencyGrade();  // A, B, C, D, F

// Cost per request
$perRequest = CostOptimizerHelper::getCostPerRequest();

// ROI calculation
$roi = CostOptimizerHelper::calculateROI(1000);
// Returns: [
//   'annual_savings' => 144.00,
//   'implementation_cost' => 1000,
//   'payback_months' => 6.9,
//   'roi_percent' => -85.6,
//   'break_even_date' => '2025-07-15'
// ]

// Get all optimizations
$optimizations = CostOptimizerHelper::getOptimizations();
foreach ($optimizations as $opt) {
    echo "{$opt['title']}: Save \${$opt['savings']}/month\n";
}

// Get complete analysis
$analysis = CostOptimizerHelper::getCostAnalysis();
```

### Cost Analysis Features

#### 1. Compute Cost Analysis
- Server utilization tracking
- Upsize/downsize recommendations
- Multi-instance cost aggregation
- Provider-specific pricing

#### 2. Database Cost Analysis
- Query performance analysis
- Missing index detection
- Cache opportunity identification
- Optimization suggestions with performance impact

#### 3. Storage Cost Analysis
- Storage usage tracking
- Compression recommendations
- Lifecycle policy suggestions
- Cost per GB calculation

#### 4. Network/CDN Cost Analysis
- Bandwidth usage tracking
- CDN hit rate optimization
- Image optimization recommendations
- Potential bandwidth reduction

#### 5. Cache Cost Analysis
- Cache effectiveness scoring
- ROI calculation
- Query caching opportunities
- Performance gain estimation

### Optimization Categories

**Server Sizing:**
```
Utilization < 30% → Downgrade recommendation
Utilization > 80% → Upgrade recommendation
Utilization 30-80% → Optimal
```

**Database Optimizations:**
- Add missing indexes (0-90% speedup)
- Fix N+1 queries (40-80% speedup)
- Implement query caching (30-60% speedup)

**Network Optimizations:**
- Optimize CDN cache (potential 40% savings)
- Image format optimization (WebP)
- Lazy loading implementation

**Storage Optimizations:**
- Enable compression (30% savings)
- Lifecycle policies (20% savings)

### Configuration

Add to your `.env`:

```env
SENTINEL_COST_OPTIMIZER=true

# Provider Configuration
SENTINEL_COST_PROVIDER=aws
SENTINEL_COST_INSTANCE_TYPE=t3.small
SENTINEL_COST_INSTANCE_COUNT=1

# Database
SENTINEL_COST_DB_PROVIDER=aws
SENTINEL_COST_DB_TYPE=rds.t3.small

# Storage
SENTINEL_COST_STORAGE_PROVIDER=aws
SENTINEL_COST_STORAGE_GB=100

# Network/CDN
SENTINEL_COST_CDN_PROVIDER=aws
SENTINEL_COST_BANDWIDTH_GB=500
SENTINEL_COST_CDN_HIT_RATE=70

# Cache
SENTINEL_COST_CACHE_PROVIDER=aws
SENTINEL_COST_CACHE_INSTANCE=cache.t3.micro

# Analysis
SENTINEL_COST_ANALYSIS_FREQUENCY=daily
```

### Supported Providers

**AWS:**
- EC2 instances (t3 family)
- RDS databases
- S3 storage
- CloudFront CDN
- ElastiCache

**DigitalOcean:**
- Droplets (Basic plans)
- Managed Databases
- Spaces storage

**Linode:**
- Compute instances
- All standard plans

### Real-World Examples

#### Example 1: Over-Provisioned Server
```
Analysis:
- Instance: t3.medium ($60/month)
- Utilization: 25%
- Recommendation: Downgrade to t3.small

Result:
💰 Savings: $30/month ($360/year)
⏱️ Implementation: 1 hour
Risk: Low
```

#### Example 2: Database Performance
```
Analysis:
- Slow queries: 145
- Missing indexes: 12
- Avg query time: 850ms

Recommendations:
1. Add indexes (Performance: 70% faster)
2. Cache frequent queries (Load reduction: 60%)

Result:
⚡ Performance: 70% improvement
💰 Cost: $12.50/month (caching)
📊 ROI: 340%
```

#### Example 3: CDN Optimization
```
Analysis:
- Bandwidth: 2TB/month
- CDN Hit Rate: 55%
- Cost: $170/month

Recommendations:
- Optimize cache headers
- Enable WebP images
- Target hit rate: 85%

Result:
💰 Savings: $68/month ($816/year)
📈 Performance: 40% faster load times
```

### Efficiency Grading

```
Grade A (90-100): Excellent - Well optimized
Grade B (80-89):  Good - Minor improvements available
Grade C (70-79):  Fair - Consider optimizations
Grade D (60-69):  Poor - Optimization recommended
Grade F (<60):    Critical - Immediate action needed
```

### Integration with Other Modules

Cost Optimizer works seamlessly with other Sentinel modules:

- **AI Insights**: Correlates costs with performance predictions
- **Query Monitor**: Identifies expensive database operations
- **Performance Monitor**: Links slow endpoints to resource costs
- **Memory Monitor**: Tracks memory-related cost implications

### API Endpoints

```php
Route::get('/api/sentinel/costs/overview', function () {
    return [
        'monthly' => CostOptimizerHelper::getTotalMonthlyCost(),
        'breakdown' => CostOptimizerHelper::getCostBreakdown(),
        'efficiency' => CostOptimizerHelper::getEfficiencyScore(),
    ];
});

Route::get('/api/sentinel/costs/savings', function () {
    return [
        'potential_monthly' => CostOptimizerHelper::getPotentialSavings(),
        'potential_yearly' => CostOptimizerHelper::getPotentialSavings() * 12,
        'optimizations' => CostOptimizerHelper::getOptimizations(),
    ];
});
```

### Best Practices

1. **Run Analysis Regularly**: Daily automated analysis catches cost drift
2. **Review Recommendations**: Prioritize high-impact, low-risk optimizations
3. **Track Changes**: Monitor cost trends after implementing optimizations
4. **Test Before Production**: Validate sizing changes in staging first
5. **Document Decisions**: Keep track of why certain recommendations were accepted/rejected

### Cost Savings Calculator

The module includes a sophisticated ROI calculator:

```php
$roi = CostOptimizerHelper::calculateROI($implementationCost = 1000);

// Output:
[
    'annual_savings' => 144.00,
    'implementation_cost' => 1000,
    'payback_months' => 6.9,
    'roi_percent' => -85.6,  // Negative first year, positive after
    'break_even_date' => '2025-07-15'
]
```


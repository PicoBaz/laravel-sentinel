# Changelog

All notable changes to `laravel-sentinel` will be documented in this file.
## [1.3.1] - 2024-12-04
### Fix Bug
- fix **migrations** bug

---
## [1.3.0] - 2024-12-04

### Added
- 💰 **Infrastructure Cost Optimizer Module** - Smart cost analysis and optimization
  - Multi-cloud cost tracking (AWS, DigitalOcean, Linode)
  - Real-time cost analysis
    - Compute costs with utilization tracking
    - Database costs with performance metrics
    - Storage costs with usage analytics
    - Network/CDN costs with hit rate analysis
    - Cache costs with ROI calculation
  - Cost breakdown by category
  - Monthly and yearly projections
  - Cost per request calculation
- 💡 **Smart Optimization Engine**
  - Server sizing recommendations (upgrade/downgrade)
  - Database query optimization suggestions
    - Missing index detection
    - N+1 query identification
    - Cache opportunity analysis
  - Storage optimization
    - Compression recommendations
    - Lifecycle policy suggestions
  - Network optimization
    - CDN hit rate improvement
    - Image optimization (WebP, lazy loading)
    - Bandwidth reduction strategies
  - Cache effectiveness analysis
- 📊 **Financial Analytics**
  - Efficiency scoring system (0-100)
  - Grade system (A-F)
  - Potential savings calculation
  - ROI calculator
  - Payback period analysis
  - Break-even date estimation
- 🎯 **CostOptimizerCommand** - CLI tool with rich output
  - Cost overview with tables
  - Visual cost breakdown with progress bars
  - Efficiency scoring display
  - Prioritized optimization recommendations
  - ROI scenarios comparison
- 📈 **CostOptimizerHelper** - Programmatic API
  - `getTotalMonthlyCost()` - Monthly infrastructure cost
  - `getTotalYearlyCost()` - Annual cost projection
  - `getCostBreakdown()` - Category-wise costs
  - `getPotentialSavings()` - Optimization savings
  - `getEfficiencyScore()` - Performance score
  - `getEfficiencyGrade()` - Letter grade (A-F)
  - `getCostPerRequest()` - Unit economics
  - `calculateROI()` - Investment analysis

### Enhanced
- Configuration system with 13 new cost-related settings
- Provider-specific pricing data
- Automatic daily cost analysis scheduling
- Integration with Query Monitor for database cost analysis
- Integration with Performance Monitor for compute utilization

### Configuration
- Added `SENTINEL_COST_OPTIMIZER` module toggle
- Added provider configuration variables
  - `SENTINEL_COST_PROVIDER`
  - `SENTINEL_COST_INSTANCE_TYPE`
  - `SENTINEL_COST_INSTANCE_COUNT`
  - `SENTINEL_COST_DB_TYPE`
  - `SENTINEL_COST_STORAGE_GB`
  - `SENTINEL_COST_BANDWIDTH_GB`
  - `SENTINEL_COST_CDN_HIT_RATE`
  - And more...
- Added `cost_optimizer` configuration section

### Commands
- `php artisan sentinel:cost-optimizer` - Display cost analysis
- `php artisan sentinel:cost-optimizer --refresh` - Force refresh analysis

### Algorithms
- Server utilization calculation from performance metrics
- Query caching opportunity detection (>5 repeats)
- Indexing score calculation based on slow queries
- Efficiency grading formula with weighted factors
- ROI calculation with implementation cost consideration

### Provider Support
- **AWS**: EC2, RDS, S3, CloudFront, ElastiCache
- **DigitalOcean**: Droplets, Databases, Spaces
- **Linode**: Compute instances (all tiers)

### Documentation
- Complete Cost Optimizer section in README
- Real-world optimization examples
- Configuration guide
- API reference
- Best practices guide

---
  - Create teams via CLI
  - List all teams
  - View team members
  - Display user statistics
  - Show leaderboards (team & global)

### Enhanced
- Auto-assignment of critical issues to on-call members
- Notification system extended for team collaboration
- Dashboard can display team statistics
- Extended logging for team activities

### Database
- 6 new tables for team collaboration
  - `sentinel_teams`
  - `sentinel_team_members`
  - `sentinel_team_responsibilities`
  - `sentinel_issues`
  - `sentinel_team_notifications`
  - `sentinel_issue_comments`

### Configuration
- Added `SENTINEL_TEAM_COLLABORATION` environment variable
- Added `SENTINEL_AUTO_ASSIGN_CRITICAL` for auto-assignment
- Added `SENTINEL_DIGEST_EMAILS` for email digests
- Added `SENTINEL_GAMIFICATION` for points/badges
- Added `team_collaboration` configuration section

### Commands
- `php artisan sentinel:team create` - Create new team
- `php artisan sentinel:team list` - List all teams
- `php artisan sentinel:team members --team=X` - List team members
- `php artisan sentinel:team stats --user=X` - Show user statistics
- `php artisan sentinel:team leaderboard --team=X --period=week` - Show leaderboard

### API
- `TeamHelper::assignIssue()` - Assign issue to user
- `TeamHelper::resolveIssue()` - Mark issue as resolved
- `TeamHelper::addComment()` - Add comment to issue
- `TeamHelper::getTeamLeaderboard()` - Get team rankings
- `TeamHelper::getUserStats()` - Get user statistics

---

## [1.2.0] - 2024-11-25

### Added
- 🤖 **AI Insights & Predictions Module** - Machine learning powered application analysis
  - Pattern recognition and analysis
    - Peak hours detection
    - Slow endpoint identification
    - Memory trend analysis
    - Error pattern recognition
  - Anomaly detection using Z-score algorithm
    - Response time anomalies
    - Memory usage spikes
    - Error rate anomalies
    - Query count anomalies
  - Predictive analytics with linear regression
    - Performance trend forecasting (24h & 7-day)
    - Memory usage predictions
    - Error rate forecasting
    - Downtime risk assessment
  - Smart AI-generated recommendations
    - Optimization suggestions
    - Scaling recommendations
    - Critical issue alerts
  - System health scoring (0-100)
  - Automatic hourly analysis via Laravel scheduler
- 🎯 **AI Insights Command** - `php artisan sentinel:ai-insights`
- 📊 **AIInsightsHelper** - Programmatic access to AI insights
- 📈 **Statistical Analysis Engine** - Z-score, linear regression, trend calculation

### Enhanced
- Dashboard can now display AI insights and predictions
- Extended notification system to include AI-generated alerts
- Improved caching strategy for AI computations

### Configuration
- Added `SENTINEL_AI_INSIGHTS` environment variable
- Added `SENTINEL_AI_ANALYSIS_FREQUENCY` for scheduling control
- Added `SENTINEL_AI_PREDICTION_WINDOW` for forecast window
- Added `SENTINEL_AI_ANOMALY_THRESHOLD` for sensitivity tuning
- Added `ai_insights` configuration section in `config/sentinel.php`

### Algorithms
- Z-score anomaly detection (configurable threshold: default 2.5σ)
- Linear regression for trend prediction
- Statistical analysis (mean, standard deviation, variance)
- Pattern matching and frequency analysis

### Commands
- `php artisan sentinel:ai-insights` - Display AI insights and predictions
- `php artisan sentinel:ai-insights --refresh` - Force refresh analysis

---

## [1.1.0] - 2024-11-25

### Added
- 🔐 **Security Monitor Module** - Comprehensive security monitoring system
  - Failed login attempt tracking
  - SQL Injection detection
  - XSS (Cross-Site Scripting) detection
  - Path traversal detection
  - Command injection detection
  - Rate limiting violation monitoring
  - Unauthorized access tracking
  - File integrity monitoring
  - IP blacklisting (manual & automatic)
  - Security scoring system per IP
  - Threat level classification (Low, Medium, High, Critical)
- 🛡️ **Security Middleware** - Protect routes with security checks
- 📊 **Security Report Command** - Generate detailed security reports (`php artisan sentinel:security-report`)
- 🔧 **Security Helper Class** - Utilities for security management
- ⚙️ **Security Configuration** - Extensive security module configuration options

### Enhanced
- Dashboard now shows security metrics
- Extended notification system to include security alerts
- Improved logging with security event categorization

### Configuration
- Added `SENTINEL_SECURITY_MONITOR` environment variable
- Added `SENTINEL_SECURITY_AUTO_BLOCK` for automatic IP blocking
- Added `SENTINEL_SECURITY_AUTO_BLOCK_SCORE` for blocking threshold
- Added `SENTINEL_SECURITY_BLACKLIST` for manual IP blacklist
- Added security configuration section in `config/sentinel.php`

### Commands
- `php artisan sentinel:security-report` - Generate security report for specified hours

### Middleware
- `sentinel.security` - Apply security monitoring to specific routes

---

## [1.0.0] - 2024-11-24

### Added
- 🔍 **Query Monitor Module** - Track slow database queries
- 💾 **Memory Monitor Module** - Monitor memory usage
- 🚨 **Exception Monitor Module** - Track application exceptions
- ⚡ **Performance Monitor Module** - Monitor response times
- 📊 **Dashboard** - Beautiful web-based dashboard
- 🔔 **Multi-Channel Notifications**
  - Telegram integration
  - Slack integration
  - Discord integration
  - Email notifications
- 🧩 **Modular Architecture** - Easy to extend
- ⚙️ **Smart Thresholds** - Configurable alert triggers
- 📈 **Analytics** - Performance metrics and statistics

### Commands
- `php artisan sentinel:install` - Install and configure Sentinel
- `php artisan sentinel:status` - View monitoring status

### Middleware
- `sentinel` - Add performance tracking headers

### Configuration
- Threshold configuration for query time, memory, response time
- Module enable/disable controls
- Notification channel configuration
- Dashboard settings

---

## Versioning

We follow [Semantic Versioning](https://semver.org/):
- **MAJOR** version for incompatible API changes
- **MINOR** version for new functionality in a backward compatible manner
- **PATCH** version for backward compatible bug fixes

---

## Upgrade Guide

### From 1.1.x to 1.2.0

1. Update your `composer.json`:
```bash
composer update picobaz/laravel-sentinel
```

2. Publish updated config:
```bash
php artisan vendor:publish --tag=sentinel-config --force
```

3. Add AI Insights environment variables to `.env` (optional):
```env
SENTINEL_AI_INSIGHTS=true
SENTINEL_AI_ANALYSIS_FREQUENCY=hourly
SENTINEL_AI_PREDICTION_WINDOW=24
SENTINEL_AI_ANOMALY_THRESHOLD=2.5
```

4. Run AI analysis:
```bash
php artisan sentinel:ai-insights --refresh
```

5. (Optional) Schedule hourly analysis in `app/Console/Kernel.php`:
```php
// Analysis runs automatically via module boot
// No manual scheduling needed
```

**Note:** AI Insights requires at least 20 data points for accurate predictions. Allow the system to collect data for a few hours before expecting detailed insights.

### From 1.0.x to 1.1.0

1. Update your `composer.json`:
```bash
composer update picobaz/laravel-sentinel
```

2. Publish updated config:
```bash
php artisan vendor:publish --tag=sentinel-config --force
```

3. Add security environment variables to `.env`:
```env
SENTINEL_SECURITY_MONITOR=true
SENTINEL_SECURITY_AUTO_BLOCK=true
SENTINEL_SECURITY_AUTO_BLOCK_SCORE=20
```

4. (Optional) Apply security middleware to routes:
```php
Route::middleware(['sentinel.security'])->group(function () {
    // Your protected routes
});
```

5. Test security monitoring:
```bash
php artisan sentinel:security-report
```

---

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## Credits

- **PicoBaz** - [@PicoBaz](https://github.com/PicoBaz)
- **All Contributors**

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

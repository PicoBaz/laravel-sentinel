# Changelog

All notable changes to `laravel-sentinel` will be documented in this file.

## [1.3.0] - 2024-12-01

### Added
- 👥 **Team Collaboration Module** - Complete real-time team management system
  - Team creation and management
  - Team member roles (member, lead, admin)
  - Team responsibilities assignment
  - On-call scheduling support
- 📌 **Issue Tracking System**
  - Auto-assign critical issues to on-call members
  - Manual issue assignment
  - Issue status tracking (open, in_progress, resolved, closed)
  - Priority management (low, medium, high, critical)
  - Issue resolution tracking with timestamps
- 💬 **Collaboration Features**
  - Comment system for issues
  - Real-time notifications to participants
  - @mention support (future enhancement)
- 🔔 **Smart Notification System**
  - Per-user notification preferences
  - Severity and type filters
  - Quiet hours configuration
  - Multi-channel support (email, Telegram, Slack, database)
  - Database notifications for in-app display
- 📧 **Digest Emails**
  - Daily digest at configurable time
  - Weekly digest on configurable day
  - Personalized statistics per user
  - Team performance metrics
- 🏆 **Gamification System**
  - Points system for issue resolution
  - Badge awards for achievements
    - First Resolver
    - Resolver 10, 50, 100
    - Critical Expert
    - Speed Demon
  - Team leaderboards
  - Global leaderboard
  - Points-based ranking
- 📊 **Team Analytics**
  - Individual user statistics
  - Team performance metrics
  - Average resolution time tracking
  - Issue count by status/priority
  - Team member ranking
- 🎯 **Team Management Command**
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

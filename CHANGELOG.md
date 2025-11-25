# Changelog

All notable changes to `laravel-sentinel` will be documented in this file.

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
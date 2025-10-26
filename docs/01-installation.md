# Installation

This guide walks you through installing Akira Debugger in your Laravel application.

## Requirements

Before installing, ensure your environment meets these requirements:

- **PHP:** 8.2, 8.3, or 8.4
- **Laravel:** 11.x or 12.x
- **Composer:** 2.0 or higher

### Recommended Environment

For the best experience:

- PHP 8.4+ (for latest features)
- Laravel 12+ (for newest framework features)
- Development environment (not recommended for production)

## Installation Steps

### 1. Install via Composer

Install the package as a development dependency:

```bash
composer require akira/laravel-debugger --dev
```

The `--dev` flag ensures the package is only loaded in development environments.

### 2. Verify Installation

After installation, verify the package was loaded:

```bash
php artisan about
```

You should see `akira/laravel-debugger` in the packages list.

### 3. Test the Installation

Open Laravel Tinker and test the debugger:

```bash
php artisan tinker
```

```php
ad('Hello, Akira Debugger!');
```

If you see no errors, the installation was successful!

## Auto-Discovery

Akira Debugger uses Laravel's package auto-discovery. The service provider is automatically registered - no manual configuration needed.

### Manual Registration (Optional)

If you've disabled auto-discovery, manually register the service provider in `config/app.php`:

```php
'providers' => [
    // Other providers...
    Akira\Debugger\AkiraServiceProvider::class,
],
```

## Environment Configuration

### Development Only

By default, the debugger is enabled when `APP_DEBUG=true`. Add to your `.env`:

```env
APP_DEBUG=true
DEBUGGER_ENABLED=true
```

### Production Safety

**Important:** Never enable the debugger in production. Add to your production `.env`:

```env
APP_DEBUG=false
DEBUGGER_ENABLED=false
```

The debugger automatically disables itself when:
- `APP_ENV=production`
- `DEBUGGER_ENABLED=false`
- `APP_DEBUG=false`

## Publishing Configuration

### Optional Configuration File

To customize the debugger behavior, publish the configuration file:

```bash
php artisan vendor:publish --provider="Akira\Debugger\AkiraServiceProvider"
```

This creates `config/debugger.php` where you can configure:
- Individual watchers
- Query thresholds
- Event filters
- Output preferences

### Docker Configuration

If using Docker, publish with Docker-specific settings:

```bash
php artisan debugger:publish-config --docker
```

This automatically sets the host to `host.docker.internal`.

### Homestead Configuration

For Homestead environments:

```bash
php artisan debugger:publish-config --homestead
```

This sets the host to `10.0.2.2`.

## Verifying Installation

Run this comprehensive check:

```bash
php artisan tinker --execute="
echo 'Testing Akira Debugger...' . PHP_EOL;
ad('Basic debug works');
collect([1, 2, 3])->debug('Collection debug works');
echo 'All tests passed!' . PHP_EOL;
"
```

Expected output:
```
Testing Akira Debugger...
All tests passed!
```

## Post-Installation

After successful installation:

1. ✅ **Configure watchers** - See [Configuration](02-configuration.md)
2. ✅ **Learn basic usage** - See [Quick Start](03-quick-start.md)
3. ✅ **Explore features** - See [Basic Usage](04-basic-usage.md)

## Uninstallation

To remove the package:

```bash
composer remove akira/laravel-debugger
```

Clean up published files:

```bash
rm config/debugger.php
rm ray.php  # if exists
```

## Troubleshooting

### Package Not Found

If Composer can't find the package:

```bash
composer clear-cache
composer update akira/laravel-debugger
```

### Service Provider Not Loaded

Clear Laravel caches:

```bash
php artisan config:clear
php artisan cache:clear
php artisan clear-compiled
```

### Class Not Found Errors

Regenerate Composer autoload:

```bash
composer dump-autoload
```

## Next Steps

- [Configure watchers](02-configuration.md)
- [Quick start guide](03-quick-start.md)
- [Basic usage examples](04-basic-usage.md)

---

[← Back to Index](README.md) | [Next: Configuration →](02-configuration.md)

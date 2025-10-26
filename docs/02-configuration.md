# Configuration

Learn how to configure Akira Debugger to match your debugging needs.

## Configuration File

The configuration file controls all debugger behavior. Publish it with:

```bash
php artisan debugger:publish-config
```

This creates `config/debugger.php` in your Laravel application.

## Configuration Structure

### Basic Configuration

```php
<?php

return [
    // Enable/disable the entire debugger
    'enable' => env('DEBUGGER_ENABLED', env('APP_DEBUG', false)),
    
    // Connection settings
    'host' => env('DEBUGGER_HOST', 'localhost'),
    'port' => env('DEBUGGER_PORT', 23517),
];
```

### Watcher Configuration

Control which features are active:

```php
'watchers' => [
    'cache' => env('SEND_CACHE_TO_DEBUGGER', false),
    'dumps' => env('SEND_DUMPS_TO_DEBUGGER', true),
    'events' => env('SEND_EVENTS_TO_DEBUGGER', false),
    'exceptions' => env('SEND_EXCEPTIONS_TO_DEBUGGER', true),
    'jobs' => env('SEND_JOBS_TO_DEBUGGER', false),
    'log' => env('SEND_LOG_TO_DEBUGGER', true),
    'mails' => env('SEND_MAILS_TO_DEBUGGER', true),
    'queries' => env('SEND_QUERIES_TO_DEBUGGER', false),
    'requests' => env('SEND_REQUESTS_TO_DEBUGGER', false),
    'views' => env('SEND_VIEWS_TO_DEBUGGER', false),
],
```

### Query-Specific Settings

```php
// Query debugging options
'slow_query_threshold_milliseconds' => env('SLOW_QUERY_THRESHOLD_MS', 500),

// Individual query type watchers
'watchers' => [
    'queries' => true,              // All queries
    'slow_queries' => true,         // Only slow queries
    'duplicate_queries' => true,    // Duplicate detection
    'update_queries' => false,      // UPDATE statements
    'insert_queries' => false,      // INSERT statements
    'delete_queries' => false,      // DELETE statements
    'select_queries' => false,      // SELECT statements
],
```

## Environment Variables

### Core Settings

```env
# Master switch
DEBUGGER_ENABLED=true

# Connection
DEBUGGER_HOST=localhost
DEBUGGER_PORT=23517
```

### Watcher Toggles

```env
# Feature watchers
SEND_CACHE_TO_DEBUGGER=false
SEND_DUMPS_TO_DEBUGGER=true
SEND_EVENTS_TO_DEBUGGER=false
SEND_EXCEPTIONS_TO_DEBUGGER=true
SEND_JOBS_TO_DEBUGGER=false
SEND_LOG_TO_DEBUGGER=true
SEND_MAILS_TO_DEBUGGER=true
SEND_REQUESTS_TO_DEBUGGER=false
SEND_VIEWS_TO_DEBUGGER=false
SEND_HTTP_CLIENT_TO_DEBUGGER=false
```

### Query Watchers

```env
# Query debugging
SEND_QUERIES_TO_DEBUGGER=false
SEND_SLOW_QUERIES_TO_DEBUGGER=true
SEND_DUPLICATE_QUERIES_TO_DEBUGGER=true
SEND_UPDATE_QUERIES_TO_DEBUGGER=false
SEND_INSERT_QUERIES_TO_DEBUGGER=false
SEND_DELETE_QUERIES_TO_DEBUGGER=false
SEND_SELECT_QUERIES_TO_DEBUGGER=false

# Query thresholds
SLOW_QUERY_THRESHOLD_MS=500
```

## Common Configurations

### Development Setup

Maximum debugging for local development:

```env
DEBUGGER_ENABLED=true
SEND_QUERIES_TO_DEBUGGER=true
SEND_SLOW_QUERIES_TO_DEBUGGER=true
SEND_DUPLICATE_QUERIES_TO_DEBUGGER=true
SEND_EXCEPTIONS_TO_DEBUGGER=true
SEND_MAILS_TO_DEBUGGER=true
SEND_LOG_TO_DEBUGGER=true
```

### Performance Testing

Focus on performance issues:

```env
DEBUGGER_ENABLED=true
SEND_QUERIES_TO_DEBUGGER=false
SEND_SLOW_QUERIES_TO_DEBUGGER=true
SEND_DUPLICATE_QUERIES_TO_DEBUGGER=true
SLOW_QUERY_THRESHOLD_MS=100
```

### Email Testing

Only monitor emails:

```env
DEBUGGER_ENABLED=true
SEND_MAILS_TO_DEBUGGER=true
SEND_QUERIES_TO_DEBUGGER=false
SEND_EXCEPTIONS_TO_DEBUGGER=false
```

### Production Debugging

Minimal, essential debugging only:

```env
DEBUGGER_ENABLED=false  # Disabled by default
SEND_EXCEPTIONS_TO_DEBUGGER=true  # Only exceptions
SEND_SLOW_QUERIES_TO_DEBUGGER=true
SLOW_QUERY_THRESHOLD_MS=1000  # Very slow only
```

## Docker Configuration

### Docker Compose

In your `.env`:

```env
DEBUGGER_HOST=host.docker.internal
DEBUGGER_PORT=23517
```

Or use the command:

```bash
php artisan debugger:publish-config --docker
```

### Docker Network

If using a custom network:

```env
DEBUGGER_HOST=your-host-machine-ip
```

## Runtime Configuration

### Programmatic Control

Enable/disable watchers at runtime:

```php
// In a service provider or bootstrap file
config(['debugger.watchers.queries' => true]);
config(['debugger.slow_query_threshold_milliseconds' => 200]);
```

### Conditional Enabling

Enable only for specific users:

```php
// In AppServiceProvider
public function boot()
{
    if (auth()->check() && auth()->user()->is_developer) {
        config(['debugger.enable' => true]);
    }
}
```

### Environment-Based

Different settings per environment:

```php
'enable' => match(app()->environment()) {
    'local' => true,
    'staging' => auth()->check() && auth()->user()->isAdmin(),
    'production' => false,
},
```

## Advanced Configuration

### Custom Filters

Filter which events to debug:

```php
'ignored_events' => [
    'Illuminate\Auth\Events\Login',
    'Illuminate\Database\Events\QueryExecuted', // If using query watcher
],
```

### Custom Thresholds

```php
'thresholds' => [
    'slow_query_ms' => 500,
    'http_timeout_ms' => 5000,
    'cache_miss_threshold' => 10,
],
```

## Configuration Best Practices

### 1. Use Environment Variables

Always use `.env` for sensitive settings:

```php
// Good
'enable' => env('DEBUGGER_ENABLED', false),

// Bad
'enable' => true,
```

### 2. Default to Disabled

Safe defaults prevent production issues:

```php
'enable' => env('DEBUGGER_ENABLED', false), // Not true
```

### 3. Document Custom Settings

Add comments to your config:

```php
// Enable slow query detection (threshold: 500ms)
'watchers' => [
    'slow_queries' => env('SEND_SLOW_QUERIES_TO_DEBUGGER', true),
],
```

### 4. Version Control

**Include:** `config/debugger.php`  
**Exclude:** `.env`, `.env.local`

## Troubleshooting

### Config Not Loading

Clear config cache:

```bash
php artisan config:clear
php artisan config:cache
```

### Changes Not Applied

Restart your development server after configuration changes.

### Environment Variables Not Working

Ensure `.env` file exists and is readable:

```bash
ls -la .env
cat .env | grep DEBUGGER
```

## Next Steps

- [Quick start guide](03-quick-start.md)
- [Understanding watchers](06-watchers.md)
- [Query debugging](07-query-debugging.md)

---

[← Installation](01-installation.md) | [Back to Index](README.md) | [Next: Quick Start →](03-quick-start.md)

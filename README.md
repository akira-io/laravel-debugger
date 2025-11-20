![img.png](docs/assets/banner.png)

<div align="center">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/akira/laravel-debugger.svg?style=flat-square)](https://packagist.org/packages/akira/laravel-debugger)
[![Total Downloads](https://img.shields.io/packagist/dt/akira/laravel-debugger.svg?style=flat-square)](https://packagist.org/packages/akira/laravel-debugger)

</div>


**Advanced debugging toolkit for Laravel 12+ built with PHP 8.4 features.**

Akira Debugger is a modern, strictly-typed debugging package designed specifically for Laravel 12+ applications. Built
from the ground up with PHP 8.4's latest features including strict types, readonly properties, and modern attributes.

## Requirements

- **PHP:** ^8.4
- **Laravel:** ^12.0
- **Dependencies:** See [composer.json](composer.json)

## Features

- **Laravel 12+ exclusive** - Built for the latest framework features
- **PHP 8.4 strict typing** - Full type safety throughout
- **Query debugging** - Monitor SQL queries, detect N+1, slow queries
- **Mail debugging** - Inspect sent emails and mailables
- **Event tracking** - Watch Laravel events as they fire
- **Job monitoring** - Track queued jobs and their execution
- **HTTP debugging** - Log HTTP client requests and responses
- **Cache monitoring** - Track cache hits, misses, and operations
- **View debugging** - Inspect rendered views and their data
- ️ **Exception tracking** - Catch and log exceptions with full context

## Installation

Install the package via Composer:

```bash
composer require akira/laravel-debugger --dev
```

The package will automatically register its service provider.

### Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=debugger-config
```

This creates `config/debugger.php` where you can customize watchers and behavior.

## Quick Start

### Basic Debugging

The simplest way to debug with `ad()`:

```php
// Debug a variable
ad($user);

// Debug with label
ad('Current User', $user);

// Debug and stop execution
debugAndDie($user);

// Debug multiple values
ad($user, $orders, $settings);
```

Learn more in [Basic Usage](docs/04-basic-usage.md).

## Usage

### Query Debugging

Monitor database queries in real-time:

```php
// Enable query logging
ad()->showQueries();

// Find slow queries (threshold in milliseconds)
ad()->slowQueries(100);

// Detect duplicate queries
ad()->showDuplicateQueries();

// Detect N+1 query problems
ad()->showConditionalQueries(function ($query) {
    return $query->toSql();
});
```

Learn more in [Query Debugging](docs/07-query-debugging.md).

### Event Monitoring

Track Laravel events as they fire:

```php
// Watch all events
ad()->showEvents();

// Get events info
ad()->events();
```

See [Event Debugging](docs/09-event-debugging.md) for details.

### Job Debugging

Monitor queued jobs and their execution:

```php
// Monitor all job execution
ad()->showJobs();

// Get jobs info
ad()->jobs();
```

Read [Job Debugging](docs/10-job-debugging.md) for advanced usage.

### Mail Debugging

Inspect sent emails without sending:

```php
// Log all sent emails
ad()->showMails();

// Debug a mailable
ad()->mailable(new OrderConfirmation($order));
```

Details in [Mail Debugging](docs/08-mail-debugging.md).

### HTTP Debugging

Monitor HTTP client requests and responses:

```php
// Track all HTTP requests
ad()->showHttpClientRequests();

// HTTP requests are automatically logged
$response = Http::get('https://api.example.com/users');
```

Learn more in [HTTP Debugging](docs/11-http-debugging.md).

### Cache Monitoring

Track cache operations:

```php
// Monitor cache hits and misses
ad()->showCache();
```

See [Cache Debugging](docs/12-cache-debugging.md).

### View Debugging

Inspect rendered views and their data:

```php
// Watch view rendering
ad()->showViews();
```

Read [View Debugging](docs/13-view-debugging.md).

### Commands

```bash
# Clear debugger data
php artisan debugger:clean
```

## Configuration

The `config/debugger.php` file allows you to:

- Enable/disable specific watchers
- Configure query thresholds
- Set up filters
- Customize output formats

Example configuration:

```php
return [
    'enable' => env('DEBUGGER_ENABLED', env('APP_DEBUG', false)),
    
    'watchers' => [
        'queries' => true,
        'mails' => true,
        'events' => true,
        'jobs' => true,
        'cache' => true,
        'http' => true,
        'views' => true,
        'exceptions' => true,
    ],
    
    'query_threshold' => 100, // ms
    
    'ignored_events' => [
        // Events to ignore
    ],
];
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test -- --coverage
```

## Code Quality

### Format Code (Pint)

```bash
composer format
```

### Static Analysis (Larastan)

```bash
composer analyse
```

### Refactor (Rector)

```bash
composer refactor
```

## Development

This package follows strict coding standards:

- **PSR-12** via Laravel Pint
- **Level Max** static analysis via Larastan
- **PHP 8.4+** features throughout
- **100% type coverage** with strict types
- **Pest** for testing

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

Built with inspiration from Spatie's Laravel Ray, reimagined for modern Laravel and PHP.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

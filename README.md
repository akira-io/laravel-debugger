# Akira Debugger

[![Latest Version on Packagist](https://img.shields.io/packagist/v/akira/laravel-debugger.svg?style=flat-square)](https://packagist.org/packages/akira/laravel-debugger)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/akira/laravel-debugger/tests?label=tests)](https://github.com/akira/laravel-debugger/actions?query=workflow%3Atests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/akira/laravel-debugger.svg?style=flat-square)](https://packagist.org/packages/akira/laravel-debugger)

**Advanced debugging toolkit for Laravel 12+ built with PHP 8.4 features.**

Akira Debugger is a modern, strictly-typed debugging package designed specifically for Laravel 12+ applications. Built from the ground up with PHP 8.4's latest features including strict types, readonly properties, and modern attributes.

## Requirements

- **PHP:** ^8.2|^8.3|^8.4
- **Laravel:** ^11.0|^12.0
- **Dependencies:** See [composer.json](composer.json)

> **Note:** This package is built with strict typing and modern PHP features. While it supports PHP 8.2+, it's optimized for PHP 8.4 and Laravel 12.

## Features

- 🎯 **Laravel 12+ exclusive** - Built for the latest framework features
- ⚡ **PHP 8.4 strict typing** - Full type safety throughout
- 🔍 **Query debugging** - Monitor SQL queries, detect N+1, slow queries
- 📧 **Mail debugging** - Inspect sent emails and mailables
- �� **Event tracking** - Watch Laravel events as they fire
- 📦 **Job monitoring** - Track queued jobs and their execution
- 🌐 **HTTP debugging** - Log HTTP client requests and responses
- 💾 **Cache monitoring** - Track cache hits, misses, and operations
- 🎨 **View debugging** - Inspect rendered views and their data
- ⚠️ **Exception tracking** - Catch and log exceptions with full context

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

## Usage

### Basic Debugging

```php
// Using debug() - recommended
debug($variable);
debug('User Data', $user);

// Using ray() - from spatie/ray (also works)
ray($variable);

// Debug and die
rd($user);
```

> **Note:** Both `debug()` and `ray()` work. We recommend `debug()` for consistency with the Akira Debugger naming, but `ray()` from `spatie/ray` is fully functional.

### Collections

```php
// Enable query watcher
Debugger::showQueries();

// Detect slow queries (threshold in ms)
Debugger::slowQueries(100);

// Detect duplicate queries
Debugger::duplicateQueries();

// Detect N+1 queries
Debugger::conditionalQueries();
```

### Event Monitoring

```php
// Watch specific event
Debugger::event(OrderCreated::class);

// Watch all events
Debugger::events();
```

### Job Debugging

```php
// Monitor job execution
Debugger::jobs();

// Debug specific job
Debugger::job(ProcessOrderJob::class);
```

### HTTP Client Debugging

```php
// Monitor HTTP requests
Debugger::http();

// Make request (automatically logged)
Http::get('https://api.example.com/users');
```

### Mail Debugging

```php
// Monitor sent emails
Debugger::mails();

// Debug mailable
Debugger::mailable(new OrderShipped($order));
```

### Cache Monitoring

```php
// Watch cache operations
Debugger::cache();
```

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

# Akira Debugger - Quick Start Guide

## Installation

```bash
composer require akira/laravel-debugger --dev
```

## Basic Usage

### Debug Variables

```php
// Using debug() - recommended
debug($user);
debug('User data', $user);

// Using ray() - also works
ray($user);

// Debug and die
rd($user);
```

> **Note:** Both `debug()` and `ray()` work perfectly. Use whichever you prefer!

### Collections

```php
User::all()->debug();
User::all()->debug('All users');
```

### Queries

```php
// Enable query logging
debug()->showQueries();

// Detect slow queries (threshold in ms)
debug()->slowQueries(100);

// Detect duplicate queries
debug()->duplicateQueries();

// Detect N+1 queries
debug()->showQueries()->queries();
```

### Mail

```php
// Monitor all sent emails
debug()->mails();

// Debug specific mailable
debug()->mailable(new OrderShipped($order));
```

### Events

```php
// Monitor all events
debug()->events();

// Monitor specific event
debug()->event(OrderCreated::class);
```

### Jobs

```php
// Monitor all jobs
debug()->jobs();

// Debug specific job
debug()->job(ProcessOrderJob::class);
```

### HTTP Client

```php
// Monitor HTTP requests
debug()->http();

// Then make requests
Http::get('https://api.example.com/users');
```

### Cache

```php
// Monitor cache operations
debug()->cache();
```

### Views

```php
// Monitor rendered views
debug()->views();
```

### Blade Directives

```blade
{{-- Debug variable --}}
@debug($user)

{{-- Debug all view data --}}
@xdebug

{{-- Measure performance --}}
@measure
```

## Configuration

Publish config file:

```bash
php artisan debugger:publish-config
```

Edit `debugger.php`:

```php
return [
    'enable' => env('DEBUGGER_ENABLED', env('APP_DEBUG', false)),
    'host' => env('DEBUGGER_HOST', 'localhost'),
    'port' => env('DEBUGGER_PORT', 23517),
    
    'watchers' => [
        'queries' => true,
        'mails' => true,
        'events' => true,
        // ... more watchers
    ],
    
    'slow_query_threshold_milliseconds' => 500,
];
```

## Environment Variables

Add to your `.env` file:

```env
# Enable debugger (defaults to APP_DEBUG value)
DEBUGGER_ENABLED=true

# Ray app connection
DEBUGGER_HOST=localhost
DEBUGGER_PORT=23517

# Enable specific watchers
SEND_QUERIES_TO_DEBUGGER=true
SEND_SLOW_QUERIES_TO_DEBUGGER=true
SEND_DUPLICATE_QUERIES_TO_DEBUGGER=true
SEND_MAILS_TO_DEBUGGER=true
SEND_JOBS_TO_DEBUGGER=true
SEND_EVENTS_TO_DEBUGGER=true
SEND_CACHE_TO_DEBUGGER=true
SEND_HTTP_CLIENT_REQUESTS_TO_DEBUGGER=true
SEND_VIEWS_TO_DEBUGGER=true
SEND_EXCEPTIONS_TO_DEBUGGER=true
SEND_DUMPS_TO_DEBUGGER=true
SEND_LOG_CALLS_TO_DEBUGGER=true

# Query thresholds
SLOW_QUERY_THRESHOLD_MS=500
```

## Commands

```bash
# Clean up debug calls from code
php artisan debugger:clean

# Publish configuration
php artisan debugger:publish-config

# For Docker
php artisan debugger:publish-config --docker

# For Homestead
php artisan debugger:publish-config --homestead
```

## Collection Macros

```php
collect([1, 2, 3])->debug();
User::all()->debug('Users');
```

## Stringable Macros

```php
str('Hello World')->debug();
str('Test')->debug('String value');
```

## Query Builder Macros

```php
User::query()->where('active', true)->debug();
DB::table('users')->debug();
```

## Test Response Macros

```php
$this->get('/api/users')->debug();
```

## Tips & Tricks

### Conditional Debugging

```php
debug()->if(app()->isProduction() === false);
debug($user);
```

### Once

```php
debug()->once($variable);
```

### Show App

```php
debug()->showApp();
```

### Measure

```php
debug()->measure(function() {
    // Code to measure
});
```

### Stop Time

```php
debug()->stopTime('timer-name');
```

## Docker Setup

```php
// debugger.php
return [
    'host' => env('DEBUGGER_HOST', 'host.docker.internal'),
    // ...
];
```

Or use the command:

```bash
php artisan debugger:publish-config --docker
```

## Helper Functions

The package provides these global helper functions:

- `debug(...$arguments)` - Primary debugging function
- `rd(...$arguments)` - Debug and die

```php
debug('Hello World');
debug($user, $order);
rd($data); // Debug and exit
```

> **Note:** The `ray()` function from the underlying `spatie/ray` package is still available, but `debug()` is the recommended function for Akira Debugger.

## Common Use Cases

### Debug API Response

```php
$response = Http::get('https://api.example.com/users');
debug($response->json());
```

### Debug Eloquent Relationship

```php
$user = User::with('posts')->first();
debug($user->posts);
```

### Debug Form Request

```php
public function store(StoreUserRequest $request)
{
    debug($request->validated());
    // ...
}
```

### Debug Queue Job

```php
class ProcessOrder implements ShouldQueue
{
    public function handle()
    {
        debug($this->order);
        // ...
    }
}
```

## Requirements

- PHP 8.2+, 8.3, or 8.4
- Laravel 11+ or 12+
- Ray app running (optional)

## Resources

- [Full Documentation](README.md)
- [Refactoring Summary](REFACTORING_SUMMARY.md)
- [Status Report](STATUS_REPORT.md)

---

**Package:** akira/laravel-debugger  
**Version:** 1.0.0-dev  
**License:** MIT

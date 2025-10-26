# Akira Debugger - Quick Start Guide

## Installation

```bash
composer require akira/laravel-debugger --dev
```

## Basic Usage

### Debug Variables

```php
ad($user);
ad('User data', $user);

// Using ray() - also works
ray($user);

debugAndDie($user);
```

> **Note:** Both `ad()` and `ray()` work perfectly. Use whichever you prefer!

### Collections

```php
User::all()->debug();
User::all()->debug('All users');
```

### Queries

```php
// Enable query logging
ad()->showQueries();

// Detect slow queries (threshold in ms)
ad()->slowQueries(100);

// Detect duplicate queries
ad()->duplicateQueries();

// Detect N+1 queries
ad()->showQueries()->queries();
```

### Mail

```php
// Monitor all sent emails
ad()->mails();

// Debug specific mailable
ad()->mailable(new OrderShipped($order));
```

### Events

```php
// Monitor all events
ad()->events();

// Monitor specific event
ad()->event(OrderCreated::class);
```

### Jobs

```php
// Monitor all jobs
ad()->jobs();

// Debug specific job
ad()->job(ProcessOrderJob::class);
```

### HTTP Client

```php
// Monitor HTTP requests
ad()->http();

// Then make requests
Http::get('https://api.example.com/users');
```

### Cache

```php
// Monitor cache operations
ad()->cache();
```

### Views

```php
// Monitor rendered views
ad()->views();
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
ad()->if(app()->isProduction() === false);
ad($user);
```

### Once

```php
ad()->once($variable);
```

### Show App

```php
ad()->showApp();
```

### Measure

```php
ad()->measure(function() {
    // Code to measure
});
```

### Stop Time

```php
ad()->stopTime('timer-name');
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

- `ad(...$arguments)` - Primary debugging function
- `debugAndDie(...$arguments)` - Debug and die

```php
ad('Hello World');
ad($user, $order);
debugAndDie($data); // Debug and exit
```

> **Note:** The `ray()` function from the underlying `spatie/ray` package is still available, but `ad()` is the recommended function for Akira Debugger.

## Common Use Cases

### Debug API Response

```php
$response = Http::get('https://api.example.com/users');
ad($response->json());
```

### Debug Eloquent Relationship

```php
$user = User::with('posts')->first();
ad($user->posts);
```

### Debug Form Request

```php
public function store(StoreUserRequest $request)
{
    ad($request->validated());
    // ...
}
```

### Debug Queue Job

```php
class ProcessOrder implements ShouldQueue
{
    public function handle()
    {
        ad($this->order);
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

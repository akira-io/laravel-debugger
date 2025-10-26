# Quick Start Guide

Get up and running with Akira Debugger in 5 minutes.

## Prerequisites

Ensure you have:
- Laravel 11+ or 12+ installed
- PHP 8.2+ configured
- Akira Debugger installed (see [Installation](01-installation.md))

## Your First Debug Call

### 1. Basic Debug

Open any controller, route, or class and try:

```php
debug('Hello, Akira Debugger!');
```

### 2. Debug a Variable

```php
$user = User::first();
debug($user);
```

### 3. Debug with a Label

```php
debug('Current User', $user);
```

That's it! You're debugging!

## Common Use Cases

### Debugging Queries

Enable query watching:

```php
// In your .env
SEND_QUERIES_TO_DEBUGGER=true
```

Now all queries are automatically logged.

### Debugging Collections

```php
$users = User::all();
$users->debug('All Users');
```

### Debugging in Blade

```blade
@debug($user)
@xdebug {{-- Debug all view data --}}
```

### Debug and Stop

```php
rd($user); // Debugs and stops execution
```

## Next Steps

- [Learn all features](04-basic-usage.md)
- [Configure watchers](02-configuration.md)
- [Query debugging](07-query-debugging.md)

---

[← Configuration](02-configuration.md) | [Back to Index](README.md) | [Next: Basic Usage →](04-basic-usage.md)

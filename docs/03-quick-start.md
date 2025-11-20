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
ad('Hello, Akira Debugger!');
```

### 2. Debug a Variable

```php
$user = User::first();
ad($user);
```

### 3. Debug with a Label

```php
ad('Current User', $user);
```

That's it! You're debugging!

## Common Use Cases

### Debugging Queries

Enable query watching in your code:

```php
// Enable query logging
ad()->showQueries();
```

Now all queries are automatically logged.

### Debugging Collections

```php
$users = User::all();
ad('All Users', $users);
```

### Debugging in Blade

```blade
ad($user)
```

### Debug and Stop

```php
debugAndDie($user); // Debugs and stops execution
```

## Next Steps

- [Learn all features](04-basic-usage.md)
- [Configure watchers](02-configuration.md)
- [Query debugging](07-query-debugging.md)

---

[← Configuration](02-configuration.md) | [Back to Index](README.md) | [Next: Basic Usage →](04-basic-usage.md)

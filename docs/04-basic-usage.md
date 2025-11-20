# Basic Usage

Learn the fundamental concepts and everyday usage of Akira Debugger.

## Core Concept

Akira Debugger provides a simple, fluent interface for debugging your Laravel applications. Instead of using `var_dump()`, `dd()`, or writing to logs, you send debugging information to a visual debugger interface.

## The `ad()` Function

The primary way to interact with the debugger is through the global `ad()` function.

### Simple Debugging

Send any value to the debugger:

```php
$user = User::find(1);
ad($user);
```

### Multiple Arguments

Debug multiple values at once:

```php
ad($user, $orders, $settings);
```

### With Labels

Add descriptive labels to your debug output:

```php
ad('Current User', $user);
ad('User Orders', $user->orders);
```

### Method Chaining

The debugger supports fluent method chaining:

```php
ad()
    ->label('User Data')
    ->send($user)
    ->label('Orders')
    ->send($user->orders);
```

## Debugging Different Data Types

### Scalars

```php
ad('Hello World');
ad(42);
ad(3.14);
ad(true);
ad(null);
```

### Arrays

```php
$config = [
    'api_key' => 'secret',
    'timeout' => 30,
    'retries' => 3,
];

ad($config);
ad('API Configuration', $config);
```

### Objects

```php
$user = new User();
$user->name = 'John Doe';
$user->email = 'john@example.com';

ad($user);
```

### Collections

```php
$users = User::all();
ad($users);

// Or use the collection macro
$users->debug();
$users->debug('All Users');
```

### Eloquent Models

```php
$user = User::with('posts', 'comments')->find(1);
ad($user);

// Shows model attributes, relationships, and metadata
```

### Query Builders

```php
$query = User::where('active', true);
ad($query);

// Or use the query macro
User::where('active', true)->debug();
```

## Debug and Die

Use `debugAndDie()` to debug and stop execution:

```php
debugAndDie($user);
// Execution stops here
```

This is equivalent to:

```php
ad($user);
exit(1);
```

## Conditional Debugging

### Using Conditionals

Only debug when a condition is met:

```php
if ($user->is_admin) {
    ad('Admin User', $user);
}
```

### Ternary Debugging

```php
$user ? ad('User Found', $user) : ad('User Not Found');
```

### With Collections

```php
$users->when($users->count() > 100, function ($users) {
    ad('Large User Set', $users->count());
});
```

## Debugging Context

### Adding Context

Provide context for better understanding:

```php
ad()->context([
    'user_id' => auth()->id(),
    'route' => request()->route()->getName(),
    'method' => request()->method(),
])->send($data);
```

### Stack Traces

Include a stack trace:

```php
ad()->trace()->send($variable);
```

### Caller Information

Show where the debug call originated:

```php
ad()->caller()->send($data);
```

## Debugging Complex Scenarios

### Inside Loops

```php
foreach ($users as $user) {
    if ($user->hasRole('admin')) {
        ad("Admin User #{$user->id}", $user);
    }
}
```

### In Closures

```php
$users->each(function ($user) {
    ad("Processing {$user->name}", $user);
});
```

### In Middleware

```php
public function handle($request, Closure $next)
{
    ad('Request', $request->all());

    $response = $next($request);

    ad('Response', $response->getContent());

    return $response;
}
```

### In Jobs

```php
class ProcessOrder implements ShouldQueue
{
    public function handle()
    {
        ad('Processing Order', $this->order);

        // Job logic...

        ad('Order Processed', $this->order->fresh());
    }
}
```

## Debugging Performance

### Measuring Execution Time

```php
ad()->measure(function () {
    // Code to measure
    User::all();
});
```

### Named Timers

```php
ad()->startTimer('query-execution');

// Your code here
$users = User::with('posts')->get();

ad()->stopTimer('query-execution');
```

### Memory Usage

```php
ad()->memory();
ad('Before Operation')->memory();

// Heavy operation
$data = processLargeDataset();

ad('After Operation')->memory();
```

## Output Customization

### Labels and Context

Add context to your debug output:

```php
ad()->context([
    'user_id' => auth()->id(),
    'route' => request()->route()->getName(),
])->send($data);
```

### Stack Traces

Include a stack trace with your debug:

```php
ad()->trace()->send($variable);
```

## Best Practices

### 1. Use Descriptive Labels

```php
// Good
ad('Active Users Query Result', $users);

// Less helpful
ad($users);
```

### 2. Remove Debug Calls Before Committing

Use the cleanup command:

```bash
php artisan debugger:clean
```

### 3. Debug at Strategic Points

```php
public function processPayment($order)
{
    ad('Starting Payment', $order);

    $result = $this->gateway->charge($order->total);
    ad('Gateway Response', $result);

    if ($result->success) {
        ad('Payment Successful');
        return true;
    }

    ad('Payment Failed', $result->error);
    return false;
}
```

### 4. Use Appropriate Functions

- `ad()` - Normal debugging
- `debugAndDie()` - Debug and stop
- `->debug()` - For collections/queries

### 5. Leverage Watchers

Instead of manually debugging queries:

```php
// Don't do this
DB::listen(function ($query) {
    ad($query);
});

// Do this
config(['debugger.watchers.queries' => true]);
```

## Common Patterns

### Debugging Request/Response Cycles

```php
// Controller
public function store(Request $request)
{
    ad('Incoming Request', $request->all());

    $user = User::create($request->validated());
    ad('Created User', $user);

    return response()->json($user);
}
```

### Debugging Service Classes

```php
class UserService
{
    public function createUser(array $data)
    {
        ad('Creating User', $data);

        $user = User::create($data);
        ad('User Created', $user->id);

        $this->sendWelcomeEmail($user);
        ad('Welcome Email Sent');

        return $user;
    }
}
```

### Debugging Event Handlers

```php
class SendOrderNotification
{
    public function handle(OrderPlaced $event)
    {
        ad('Order Placed Event', [
            'order_id' => $event->order->id,
            'total' => $event->order->total,
        ]);

        // Handle event...
    }
}
```

## Next Steps

- [Helper Functions Reference](05-helper-functions.md)
- [Understanding Watchers](06-watchers.md)
- [Query Debugging](07-query-debugging.md)

---

[← Configuration](02-configuration.md) | [Back to Index](README.md) | [Next: Helper Functions →](05-helper-functions.md)

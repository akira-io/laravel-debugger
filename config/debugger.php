<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Akira Debugger
    |--------------------------------------------------------------------------
    |
    | This option controls whether the Akira Debugger is enabled or disabled.
    | By default, it inherits the value of APP_DEBUG, but you can override
    | it by setting DEBUGGER_ENABLED in your environment file.
    |
    */

    'enable' => env('DEBUGGER_ENABLED', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Debugger Host & Port
    |--------------------------------------------------------------------------
    |
    | Define the network host and port used to connect to the Akira Debugger
    | desktop or CLI application. The default connection focuses on localhost
    | on port 23517, but you can change it to match your environment.
    |
    */

    'host' => env('DEBUGGER_HOST', 'localhost'),

    'port' => env('DEBUGGER_PORT', 23517),

    /*
    |--------------------------------------------------------------------------
    | Watchers
    |--------------------------------------------------------------------------
    |
    | The following list defines which parts of your application will be
    | actively observed by the Akira Debugger. Each watcher can be toggled
    | using environment variables for full control in different setups.
    |
    | Example:
    | SEND_LOG_TO_DEBUGGER=true
    | SEND_HTTP_CLIENT_TO_DEBUGGER=false
    |
    */

    'watchers' => [
        'cache' => env('SEND_CACHE_TO_DEBUGGER', false),
        'dumps' => env('SEND_DUMPS_TO_DEBUGGER', true),
        'jobs' => env('SEND_JOBS_TO_DEBUGGER', false),
        'mails' => env('SEND_MAILS_TO_DEBUGGER', true),
        'log' => env('SEND_LOG_TO_DEBUGGER', true),
        'queries' => env('SEND_QUERIES_TO_DEBUGGER', false),
        'duplicate_queries' => env('SEND_DUPLICATE_QUERIES_TO_DEBUGGER', false),
        'slow_queries' => env('SEND_SLOW_QUERIES_TO_DEBUGGER', false),
        'update_queries' => env('SEND_UPDATE_QUERIES_TO_DEBUGGER', false),
        'insert_queries' => env('SEND_INSERT_QUERIES_TO_DEBUGGER', false),
        'delete_queries' => env('SEND_DELETE_QUERIES_TO_DEBUGGER', false),
        'select_queries' => env('SEND_SELECT_QUERIES_TO_DEBUGGER', false),
        'requests' => env('SEND_REQUESTS_TO_DEBUGGER', false),
        'http_client' => env('SEND_HTTP_CLIENT_TO_DEBUGGER', false),
        'views' => env('SEND_VIEWS_TO_DEBUGGER', false),
        'exceptions' => env('SEND_EXCEPTIONS_TO_DEBUGGER', true),
        'deprecated' => env('SEND_DEPRECATED_TO_DEBUGGER', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Slow Query Threshold
    |--------------------------------------------------------------------------
    |
    | Any database query exceeding this duration (in milliseconds) will be
    | considered "slow" and reported to the debugger if enabled. Adjust
    | this threshold to suit your application's performance profile.
    |
    */

    'slow_query_threshold_milliseconds' => env('SLOW_QUERY_THRESHOLD_MS', 500),

    /*
    |--------------------------------------------------------------------------
    | Application & View Behavior
    |--------------------------------------------------------------------------
    |
    | The following options allow finer control over what information is sent
    | to the Akira Debugger. You can automatically capture instances created
    | via app()->make() and control whether raw variable values should
    | always be sent to the debugger for more detailed inspection.
    |
    */

    'send_application_make_to_debugger' => env('SEND_APPLICATION_MAKE_TO_DEBUGGER', false),

    'always_send_raw_values' => env('ALWAYS_SEND_RAW_VALUES_TO_DEBUGGER', false),

];

<?php

declare(strict_types=1);

return [
    /*
     * Enable/disable all Akira Debugger functionality
     */
    'enable' => env('DEBUGGER_ENABLED', env('APP_DEBUG', false)),

    /*
     * The host where the Ray app is running
     */
    'host' => env('DEBUGGER_HOST', 'localhost'),

    /*
     * The port number where the Ray app is listening
     */
    'port' => env('DEBUGGER_PORT', 23517),

    /*
     * Watchers configuration
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
     * Query threshold in milliseconds for slow query detection
     */
    'slow_query_threshold_milliseconds' => env('SLOW_QUERY_THRESHOLD_MS', 500),

    /*
     * Automatically sent to Ray when using app()->make()
     */
    'send_application_make_to_ray' => env('SEND_APPLICATION_MAKE_TO_RAY', false),

    /*
     * Automatically send the rendered views to Ray
     */
    'always_send_raw_values' => env('ALWAYS_SEND_RAW_VALUES', false),
];

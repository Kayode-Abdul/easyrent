<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'commission_monitoring' => [
            'driver' => 'single',
            'path' => storage_path('logs/commission_monitoring.log'),
            'level' => 'info',
        ],

        'payment_tracking' => [
            'driver' => 'single',
            'path' => storage_path('logs/payment_tracking.log'),
            'level' => 'info',
        ],

        'payment_monitoring' => [
            'driver' => 'single',
            'path' => storage_path('logs/payment_monitoring.log'),
            'level' => 'info',
        ],

        'fraud_monitoring' => [
            'driver' => 'single',
            'path' => storage_path('logs/fraud_monitoring.log'),
            'level' => 'warning',
        ],

        'commission_audit' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commission_audit.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 30,
        ],

        'commission_errors' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commission_errors.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 60,
        ],

        // EasyRent Link Authentication System Logging Channels
        'easyrent_invitations' => [
            'driver' => 'daily',
            'path' => storage_path('logs/easyrent_invitations.log'),
            'level' => 'info',
            'days' => 30,
        ],

        'easyrent_auth' => [
            'driver' => 'daily',
            'path' => storage_path('logs/easyrent_auth.log'),
            'level' => 'info',
            'days' => 30,
        ],

        'easyrent_payments' => [
            'driver' => 'daily',
            'path' => storage_path('logs/easyrent_payments.log'),
            'level' => 'info',
            'days' => 60,
        ],

        'easyrent_errors' => [
            'driver' => 'daily',
            'path' => storage_path('logs/easyrent_errors.log'),
            'level' => 'error',
            'days' => 90,
        ],

        'easyrent_performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/easyrent_performance.log'),
            'level' => 'info',
            'days' => 14,
        ],

        'easyrent_sessions' => [
            'driver' => 'daily',
            'path' => storage_path('logs/easyrent_sessions.log'),
            'level' => 'info',
            'days' => 7,
        ],

        'easyrent_security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/easyrent_security.log'),
            'level' => 'warning',
            'days' => 90,
        ],

        'easyrent_emails' => [
            'driver' => 'daily',
            'path' => storage_path('logs/easyrent_emails.log'),
            'level' => 'info',
            'days' => 30,
        ],

        'easyrent_assignments' => [
            'driver' => 'daily',
            'path' => storage_path('logs/easyrent_assignments.log'),
            'level' => 'info',
            'days' => 60,
        ],

        // Payment Calculation Service Logging Channels
        'payment_calculations' => [
            'driver' => 'daily',
            'path' => storage_path('logs/payment_calculations.log'),
            'level' => 'info',
            'days' => 30,
            'replace_placeholders' => true,
        ],

        'payment_errors' => [
            'driver' => 'daily',
            'path' => storage_path('logs/payment_errors.log'),
            'level' => 'error',
            'days' => 90,
            'replace_placeholders' => true,
        ],

        'payment_audit' => [
            'driver' => 'daily',
            'path' => storage_path('logs/payment_audit.log'),
            'level' => 'info',
            'days' => 60,
            'replace_placeholders' => true,
        ],

        'payment_performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/payment_performance.log'),
            'level' => 'info',
            'days' => 14,
            'replace_placeholders' => true,
        ],
    ],

];

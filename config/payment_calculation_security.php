<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Calculation Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security settings for the payment calculation system
    | including rate limiting, input validation, and access control settings.
    |
    */

    'rate_limiting' => [
        /*
        |--------------------------------------------------------------------------
        | Calculation Rate Limits
        |--------------------------------------------------------------------------
        |
        | These settings control how many calculation requests can be made
        | within specific time periods to prevent abuse and ensure system stability.
        |
        */
        'requests_per_minute' => env('CALC_RATE_LIMIT_PER_MINUTE', 30),
        'requests_per_hour' => env('CALC_RATE_LIMIT_PER_HOUR', 200),
        'suspicious_threshold' => env('CALC_SUSPICIOUS_THRESHOLD', 50), // Per 5 minutes
        
        /*
        |--------------------------------------------------------------------------
        | User-Specific Rate Limits
        |--------------------------------------------------------------------------
        |
        | Additional rate limits for authenticated users to prevent account abuse.
        |
        */
        'user_requests_per_minute' => env('CALC_USER_RATE_LIMIT_PER_MINUTE', 60),
        'user_requests_per_hour' => env('CALC_USER_RATE_LIMIT_PER_HOUR', 400),
    ],

    'input_validation' => [
        /*
        |--------------------------------------------------------------------------
        | Input Value Limits
        |--------------------------------------------------------------------------
        |
        | These settings define the acceptable ranges for calculation inputs
        | to prevent overflow, underflow, and other calculation errors.
        |
        */
        'max_apartment_price' => env('CALC_MAX_APARTMENT_PRICE', 999999999.99),
        'min_apartment_price' => env('CALC_MIN_APARTMENT_PRICE', 0.00),
        'max_rental_duration' => env('CALC_MAX_RENTAL_DURATION', 120), // 10 years
        'min_rental_duration' => env('CALC_MIN_RENTAL_DURATION', 1),
        
        /*
        |--------------------------------------------------------------------------
        | Pricing Configuration Limits
        |--------------------------------------------------------------------------
        |
        | Limits for pricing configuration complexity to prevent abuse.
        |
        */
        'max_pricing_rules' => env('CALC_MAX_PRICING_RULES', 10),
        'max_json_depth' => env('CALC_MAX_JSON_DEPTH', 5),
        
        /*
        |--------------------------------------------------------------------------
        | Allowed Pricing Types
        |--------------------------------------------------------------------------
        |
        | List of valid pricing types that the system accepts.
        |
        */
        'allowed_pricing_types' => ['total', 'monthly'],
    ],

    'security_monitoring' => [
        /*
        |--------------------------------------------------------------------------
        | Threat Detection
        |--------------------------------------------------------------------------
        |
        | Settings for detecting and responding to security threats in input data.
        |
        */
        'enable_injection_detection' => env('CALC_ENABLE_INJECTION_DETECTION', true),
        'enable_xss_detection' => env('CALC_ENABLE_XSS_DETECTION', true),
        'enable_sql_injection_detection' => env('CALC_ENABLE_SQL_INJECTION_DETECTION', true),
        'enable_command_injection_detection' => env('CALC_ENABLE_COMMAND_INJECTION_DETECTION', true),
        
        /*
        |--------------------------------------------------------------------------
        | Logging Configuration
        |--------------------------------------------------------------------------
        |
        | Settings for security event logging and monitoring.
        |
        */
        'log_security_events' => env('CALC_LOG_SECURITY_EVENTS', true),
        'log_rate_limit_violations' => env('CALC_LOG_RATE_LIMIT_VIOLATIONS', true),
        'log_access_control_violations' => env('CALC_LOG_ACCESS_CONTROL_VIOLATIONS', true),
    ],

    'access_control' => [
        /*
        |--------------------------------------------------------------------------
        | Pricing Configuration Access Control
        |--------------------------------------------------------------------------
        |
        | Settings for controlling who can modify pricing configurations.
        |
        */
        'require_authentication' => env('CALC_REQUIRE_AUTH_FOR_PRICING_CONFIG', true),
        'allowed_roles' => [
            'admin',
            'super_admin',
            'property_manager'
        ],
        
        /*
        |--------------------------------------------------------------------------
        | API Access Control
        |--------------------------------------------------------------------------
        |
        | Settings for API endpoint access control.
        |
        */
        'require_api_key' => env('CALC_REQUIRE_API_KEY', true),
        'api_key_header' => env('CALC_API_KEY_HEADER', 'X-API-Key'),
    ],

    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Rate Limiting Cache Configuration
        |--------------------------------------------------------------------------
        |
        | Settings for caching rate limiting data.
        |
        */
        'rate_limit_cache_prefix' => 'calc_rate_limit',
        'rate_limit_cache_ttl' => [
            'minute' => 60,
            'hour' => 3600,
            'suspicious' => 300, // 5 minutes
        ],
    ],

    'responses' => [
        /*
        |--------------------------------------------------------------------------
        | Error Response Configuration
        |--------------------------------------------------------------------------
        |
        | Settings for customizing error responses.
        |
        */
        'include_debug_info' => env('CALC_INCLUDE_DEBUG_INFO', false),
        'show_validation_details' => env('CALC_SHOW_VALIDATION_DETAILS', true),
        'show_security_details' => env('CALC_SHOW_SECURITY_DETAILS', false),
    ],
];
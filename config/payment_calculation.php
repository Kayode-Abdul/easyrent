<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Calculation Service Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the PaymentCalculationService
    | that handles apartment payment calculations across the EasyRent system.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Pricing Type
    |--------------------------------------------------------------------------
    |
    | The default pricing type to use when none is specified. This should be
    | either 'total' or 'monthly'. The 'total' type treats the apartment
    | price as the complete rental amount, while 'monthly' multiplies by duration.
    |
    */

    'default_pricing_type' => env('PAYMENT_CALC_DEFAULT_PRICING_TYPE', 'total'),

    /*
    |--------------------------------------------------------------------------
    | Validation Limits
    |--------------------------------------------------------------------------
    |
    | These limits are used to validate input parameters and prevent
    | calculation errors or system abuse.
    |
    */

    'validation' => [
        'max_rental_duration' => env('PAYMENT_CALC_MAX_RENTAL_DURATION', 120), // 10 years maximum
        'max_apartment_price' => env('PAYMENT_CALC_MAX_APARTMENT_PRICE', 999999999.99),
        'min_apartment_price' => env('PAYMENT_CALC_MIN_APARTMENT_PRICE', 0.01),
        'max_calculation_result' => env('PAYMENT_CALC_MAX_RESULT', 9999999999.99),
        'precision_decimal_places' => env('PAYMENT_CALC_PRECISION', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Thresholds
    |--------------------------------------------------------------------------
    |
    | These thresholds determine when to log special events for monitoring
    | and alerting purposes.
    |
    */

    'monitoring' => [
        'high_value_threshold' => env('PAYMENT_CALC_HIGH_VALUE_THRESHOLD', 1000000.00),
        'suspicious_duration_threshold' => env('PAYMENT_CALC_SUSPICIOUS_DURATION', 60),
        'enable_calculation_logging' => env('PAYMENT_CALC_ENABLE_LOGGING', true),
        'enable_performance_monitoring' => env('PAYMENT_CALC_ENABLE_PERFORMANCE_MONITORING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for error handling and recovery mechanisms.
    |
    */

    'error_handling' => [
        'enable_fallback_logic' => env('PAYMENT_CALC_ENABLE_FALLBACK', true),
        'fallback_pricing_type' => env('PAYMENT_CALC_FALLBACK_PRICING_TYPE', 'total'),
        'log_fallback_usage' => env('PAYMENT_CALC_LOG_FALLBACK', true),
        'enable_overflow_protection' => env('PAYMENT_CALC_ENABLE_OVERFLOW_PROTECTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching calculation results to improve performance.
    |
    */

    'caching' => [
        'enable_result_caching' => env('PAYMENT_CALC_ENABLE_CACHING', true),
        'cache_ttl' => env('PAYMENT_CALC_CACHE_TTL', 60), // 1 hour in minutes
        'cache_key_prefix' => env('PAYMENT_CALC_CACHE_PREFIX', 'payment_calc'),
        'enable_bulk_caching' => env('PAYMENT_CALC_ENABLE_BULK_CACHING', true),
        'bulk_cache_ttl' => env('PAYMENT_CALC_BULK_CACHE_TTL', 30), // 30 minutes
        'enable_apartment_config_caching' => env('PAYMENT_CALC_ENABLE_APARTMENT_CONFIG_CACHING', true),
        'apartment_config_cache_ttl' => env('PAYMENT_CALC_APARTMENT_CONFIG_CACHE_TTL', 120), // 2 hours
        'max_cached_calculations_per_hour' => env('PAYMENT_CALC_MAX_CACHED_PER_HOUR', 10000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit and Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for audit trails and detailed logging.
    |
    */

    'audit' => [
        'enable_audit_logging' => env('PAYMENT_CALC_ENABLE_AUDIT', true),
        'log_calculation_steps' => env('PAYMENT_CALC_LOG_STEPS', true),
        'log_high_value_calculations' => env('PAYMENT_CALC_LOG_HIGH_VALUE', true),
        'log_zero_price_calculations' => env('PAYMENT_CALC_LOG_ZERO_PRICE', true),
        'audit_log_channel' => env('PAYMENT_CALC_AUDIT_CHANNEL', 'payment_calculations'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Lifecycle Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for service lifecycle management and dependency injection.
    |
    */

    'service' => [
        'singleton' => env('PAYMENT_CALC_SINGLETON', true),
        'lazy_loading' => env('PAYMENT_CALC_LAZY_LOADING', false),
        'enable_service_monitoring' => env('PAYMENT_CALC_SERVICE_MONITORING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Optimization
    |--------------------------------------------------------------------------
    |
    | Configuration for performance optimization features.
    |
    */

    'performance' => [
        'enable_query_optimization' => env('PAYMENT_CALC_ENABLE_QUERY_OPTIMIZATION', true),
        'enable_database_indexes' => env('PAYMENT_CALC_ENABLE_DATABASE_INDEXES', true),
        'batch_size' => env('PAYMENT_CALC_BATCH_SIZE', 100),
        'enable_performance_monitoring' => env('PAYMENT_CALC_ENABLE_PERFORMANCE_MONITORING', true),
        'slow_query_threshold_ms' => env('PAYMENT_CALC_SLOW_QUERY_THRESHOLD', 100),
        'enable_cache_warmup' => env('PAYMENT_CALC_ENABLE_CACHE_WARMUP', true),
        'warmup_scenarios_count' => env('PAYMENT_CALC_WARMUP_SCENARIOS', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Feature flags to enable/disable specific functionality.
    |
    */

    'features' => [
        'enable_additional_charges' => env('PAYMENT_CALC_ENABLE_ADDITIONAL_CHARGES', true),
        'enable_complex_pricing_rules' => env('PAYMENT_CALC_ENABLE_COMPLEX_PRICING', false),
        'enable_promotional_pricing' => env('PAYMENT_CALC_ENABLE_PROMOTIONAL', false),
        'enable_bulk_calculations' => env('PAYMENT_CALC_ENABLE_BULK', true),
        'enable_optimized_calculations' => env('PAYMENT_CALC_ENABLE_OPTIMIZED', true),
        'enable_pre_calculation' => env('PAYMENT_CALC_ENABLE_PRE_CALCULATION', true),
    ],

];
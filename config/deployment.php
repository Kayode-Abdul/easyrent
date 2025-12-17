<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Deployment Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for deployment procedures
    | including backup settings, rollback procedures, and validation rules.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Payment Calculation Service Deployment
    |--------------------------------------------------------------------------
    |
    | Configuration specific to the Payment Calculation Service deployment
    | and rollback procedures.
    |
    */

    'payment_calculation' => [
        
        /*
        |--------------------------------------------------------------------------
        | Backup Configuration
        |--------------------------------------------------------------------------
        */
        
        'backup' => [
            'enabled' => env('PAYMENT_CALC_BACKUP_ENABLED', true),
            'directory' => env('PAYMENT_CALC_BACKUP_DIR', '/var/backups/easyrent/payment_calculation'),
            'retention_days' => env('PAYMENT_CALC_BACKUP_RETENTION', 30),
            'compress' => env('PAYMENT_CALC_BACKUP_COMPRESS', true),
            
            // Tables to backup specifically for payment calculations
            'tables' => [
                'apartments',
                'profoma_receipts', 
                'payments',
                'apartment_invitations',
                'payment_invitations'
            ],
            
            // Files to backup
            'files' => [
                'app/Services/Payment',
                'config/payment_calculation.php',
                'app/Providers/PaymentCalculationServiceProvider.php',
                'app/Http/Controllers/ProfomaController.php',
                'app/Http/Controllers/ApartmentInvitationController.php',
                'app/Http/Controllers/PaymentController.php'
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Validation Configuration
        |--------------------------------------------------------------------------
        */
        
        'validation' => [
            'enabled' => env('PAYMENT_CALC_VALIDATION_ENABLED', true),
            'strict_mode' => env('PAYMENT_CALC_VALIDATION_STRICT', true),
            
            // Required files for deployment validation
            'required_files' => [
                'app/Services/Payment/PaymentCalculationServiceInterface.php',
                'app/Services/Payment/PaymentCalculationService.php',
                'app/Services/Payment/PaymentCalculationResult.php',
                'config/payment_calculation.php'
            ],
            
            // Required database tables
            'required_tables' => [
                'apartments',
                'profoma_receipts',
                'payments'
            ],
            
            // Required PHP extensions
            'required_extensions' => [
                'bcmath',
                'json',
                'pdo',
                'mysql'
            ],
            
            // Test calculations for validation
            'test_calculations' => [
                [
                    'apartment_price' => 100000,
                    'rental_duration' => 12,
                    'pricing_type' => 'total',
                    'expected_result' => 100000
                ],
                [
                    'apartment_price' => 50000,
                    'rental_duration' => 12,
                    'pricing_type' => 'monthly',
                    'expected_result' => 600000
                ],
                [
                    'apartment_price' => 0,
                    'rental_duration' => 1,
                    'pricing_type' => 'total',
                    'expected_result' => 0
                ]
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Rollback Configuration
        |--------------------------------------------------------------------------
        */
        
        'rollback' => [
            'enabled' => env('PAYMENT_CALC_ROLLBACK_ENABLED', true),
            'automatic_triggers' => env('PAYMENT_CALC_AUTO_ROLLBACK', false),
            'max_rollback_time' => env('PAYMENT_CALC_MAX_ROLLBACK_TIME', 600), // 10 minutes
            
            // Conditions that trigger automatic rollback
            'auto_rollback_conditions' => [
                'calculation_error_rate_threshold' => 0.05, // 5% error rate
                'response_time_threshold' => 5000, // 5 seconds
                'service_unavailable_duration' => 60 // 1 minute
            ],
            
            // Health check endpoints for rollback validation
            'health_checks' => [
                'service_availability' => true,
                'calculation_accuracy' => true,
                'database_connectivity' => true,
                'configuration_loading' => true
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Monitoring Configuration
        |--------------------------------------------------------------------------
        */
        
        'monitoring' => [
            'enabled' => env('PAYMENT_CALC_MONITORING_ENABLED', true),
            'real_time_alerts' => env('PAYMENT_CALC_REAL_TIME_ALERTS', true),
            
            // Metrics to monitor during deployment
            'metrics' => [
                'calculation_response_time',
                'calculation_error_rate',
                'service_availability',
                'database_query_performance',
                'cache_hit_rate'
            ],
            
            // Alert thresholds
            'thresholds' => [
                'error_rate' => 0.01, // 1%
                'response_time' => 1000, // 1 second
                'availability' => 0.99 // 99%
            ],
            
            // Notification channels
            'notifications' => [
                'email' => env('PAYMENT_CALC_ALERT_EMAIL', 'admin@easyrent.com'),
                'slack' => env('PAYMENT_CALC_ALERT_SLACK'),
                'sms' => env('PAYMENT_CALC_ALERT_SMS', false)
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Migration Configuration
        |--------------------------------------------------------------------------
        */
        
        'migrations' => [
            'enabled' => env('PAYMENT_CALC_MIGRATIONS_ENABLED', true),
            'timeout' => env('PAYMENT_CALC_MIGRATION_TIMEOUT', 300), // 5 minutes
            
            // Specific migrations for payment calculation service
            'payment_calculation_migrations' => [
                '2025_12_15_055139_add_pricing_configuration_to_apartments_table',
                '2025_12_15_061759_add_calculation_fields_to_profoma_receipts_table',
                '2025_12_15_070000_migrate_existing_payment_calculation_data'
            ],
            
            // Data migration settings
            'data_migration' => [
                'batch_size' => env('PAYMENT_CALC_MIGRATION_BATCH_SIZE', 1000),
                'default_pricing_type' => 'total',
                'preserve_existing_calculations' => true
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Performance Configuration
        |--------------------------------------------------------------------------
        */
        
        'performance' => [
            'cache_warmup' => env('PAYMENT_CALC_CACHE_WARMUP', true),
            'pre_calculation' => env('PAYMENT_CALC_PRE_CALCULATION', true),
            'optimization_enabled' => env('PAYMENT_CALC_OPTIMIZATION', true),
            
            // Cache warmup scenarios
            'warmup_scenarios' => [
                ['price' => 50000, 'duration' => 6, 'type' => 'total'],
                ['price' => 100000, 'duration' => 12, 'type' => 'total'],
                ['price' => 200000, 'duration' => 24, 'type' => 'total'],
                ['price' => 25000, 'duration' => 6, 'type' => 'monthly'],
                ['price' => 50000, 'duration' => 12, 'type' => 'monthly'],
                ['price' => 75000, 'duration' => 24, 'type' => 'monthly']
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | General Deployment Settings
    |--------------------------------------------------------------------------
    */
    
    'general' => [
        
        /*
        |--------------------------------------------------------------------------
        | Maintenance Mode Configuration
        |--------------------------------------------------------------------------
        */
        
        'maintenance' => [
            'enabled' => env('DEPLOYMENT_MAINTENANCE_ENABLED', true),
            'message' => env('DEPLOYMENT_MAINTENANCE_MESSAGE', 'System upgrade in progress'),
            'retry_after' => env('DEPLOYMENT_MAINTENANCE_RETRY', 60),
            'allowed_ips' => env('DEPLOYMENT_MAINTENANCE_ALLOWED_IPS', ''),
            'bypass_key' => env('DEPLOYMENT_MAINTENANCE_BYPASS_KEY', '')
        ],

        /*
        |--------------------------------------------------------------------------
        | Logging Configuration
        |--------------------------------------------------------------------------
        */
        
        'logging' => [
            'enabled' => env('DEPLOYMENT_LOGGING_ENABLED', true),
            'log_file' => env('DEPLOYMENT_LOG_FILE', '/var/log/easyrent/deployment.log'),
            'log_level' => env('DEPLOYMENT_LOG_LEVEL', 'info'),
            'max_file_size' => env('DEPLOYMENT_LOG_MAX_SIZE', '10M'),
            'retention_days' => env('DEPLOYMENT_LOG_RETENTION', 30)
        ],

        /*
        |--------------------------------------------------------------------------
        | Security Configuration
        |--------------------------------------------------------------------------
        */
        
        'security' => [
            'require_confirmation' => env('DEPLOYMENT_REQUIRE_CONFIRMATION', true),
            'allowed_users' => env('DEPLOYMENT_ALLOWED_USERS', ''),
            'require_backup' => env('DEPLOYMENT_REQUIRE_BACKUP', true),
            'validate_checksums' => env('DEPLOYMENT_VALIDATE_CHECKSUMS', true)
        ],

        /*
        |--------------------------------------------------------------------------
        | Notification Configuration
        |--------------------------------------------------------------------------
        */
        
        'notifications' => [
            'enabled' => env('DEPLOYMENT_NOTIFICATIONS_ENABLED', true),
            'channels' => [
                'email' => env('DEPLOYMENT_NOTIFY_EMAIL', 'admin@easyrent.com'),
                'slack' => env('DEPLOYMENT_NOTIFY_SLACK'),
                'teams' => env('DEPLOYMENT_NOTIFY_TEAMS')
            ],
            
            // Events to notify about
            'events' => [
                'deployment_started' => true,
                'deployment_completed' => true,
                'deployment_failed' => true,
                'rollback_initiated' => true,
                'rollback_completed' => true,
                'validation_failed' => true
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment-Specific Settings
    |--------------------------------------------------------------------------
    */
    
    'environments' => [
        
        'production' => [
            'require_manual_approval' => true,
            'backup_required' => true,
            'validation_strict' => true,
            'maintenance_mode' => true,
            'rollback_enabled' => true,
            'monitoring_enabled' => true
        ],
        
        'staging' => [
            'require_manual_approval' => false,
            'backup_required' => true,
            'validation_strict' => true,
            'maintenance_mode' => false,
            'rollback_enabled' => true,
            'monitoring_enabled' => true
        ],
        
        'development' => [
            'require_manual_approval' => false,
            'backup_required' => false,
            'validation_strict' => false,
            'maintenance_mode' => false,
            'rollback_enabled' => false,
            'monitoring_enabled' => false
        ]
    ]

];
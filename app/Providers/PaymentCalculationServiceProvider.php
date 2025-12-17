<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Services\Payment\PaymentCalculationService;
use App\Services\Payment\PaymentCalculationServiceInterface;

class PaymentCalculationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register the payment calculation service with proper lifecycle management
        $this->registerPaymentCalculationService();
        
        // Register service health monitoring
        $this->registerServiceHealthMonitoring();
        
        // Register service configuration validation
        $this->registerConfigurationValidation();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Configure logging channels
        $this->configureLoggingChannels();
        
        // Validate service configuration
        $this->validateServiceConfiguration();
        
        // Register service monitoring commands if in console
        if ($this->app->runningInConsole()) {
            $this->registerConsoleCommands();
        }
    }

    /**
     * Register the PaymentCalculationService with proper configuration
     *
     * @return void
     */
    protected function registerPaymentCalculationService()
    {
        $config = config('payment_calculation', []);
        $useSingleton = $config['service']['singleton'] ?? true;
        
        // Register the concrete service
        if ($useSingleton) {
            $this->app->singleton(PaymentCalculationService::class, function ($app) use ($config) {
                return $this->createPaymentCalculationService($app, $config);
            });
        } else {
            $this->app->bind(PaymentCalculationService::class, function ($app) use ($config) {
                return $this->createPaymentCalculationService($app, $config);
            });
        }
        
        // Bind interface to implementation
        $this->app->bind(PaymentCalculationServiceInterface::class, PaymentCalculationService::class);
        
        // Register the PaymentCalculationMonitoringService
        $this->app->singleton(\App\Services\Monitoring\PaymentCalculationMonitoringService::class);
        
        // Register the PaymentCalculationAuditLogger
        $this->app->singleton(\App\Services\Audit\PaymentCalculationAuditLogger::class);
        
        // Register the PaymentCalculationCacheService
        $this->app->singleton(\App\Services\Cache\PaymentCalculationCacheService::class);
        
        // Register the PaymentCalculationQueryOptimizer
        $this->app->singleton(\App\Services\Payment\PaymentCalculationQueryOptimizer::class);
        
        // Create alias for easier access
        $this->app->alias(PaymentCalculationService::class, 'payment.calculation');
        $this->app->alias(PaymentCalculationServiceInterface::class, 'payment.calculation.interface');
    }

    /**
     * Create PaymentCalculationService instance with dependency injection
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param array $config
     * @return PaymentCalculationService
     */
    protected function createPaymentCalculationService($app, array $config)
    {
        try {
            // Resolve security service dependency
            $securityService = $app->make(\App\Services\Security\PaymentCalculationSecurityService::class);
            
            // Resolve monitoring service dependency
            $monitoringService = $app->make(\App\Services\Monitoring\PaymentCalculationMonitoringService::class);
            
            // Resolve audit logger dependency
            $auditLogger = $app->make(\App\Services\Audit\PaymentCalculationAuditLogger::class);
            
            // Resolve cache service dependency
            $cacheService = $app->make(\App\Services\Cache\PaymentCalculationCacheService::class);
            
            // Create service instance with dependency injection
            $service = new PaymentCalculationService($securityService, $monitoringService, $auditLogger, $cacheService);
            
            // Log service creation if monitoring is enabled
            if ($config['service']['enable_service_monitoring'] ?? true) {
                Log::channel('payment_calculations')->info('PaymentCalculationService instantiated', [
                    'singleton' => $config['service']['singleton'] ?? true,
                    'lazy_loading' => $config['service']['lazy_loading'] ?? false,
                    'config_loaded' => !empty($config),
                    'timestamp' => now()->toISOString(),
                    'memory_usage' => memory_get_usage(true),
                    'peak_memory' => memory_get_peak_usage(true)
                ]);
            }
            
            return $service;
            
        } catch (\Exception $e) {
            Log::channel('payment_errors')->error('Failed to create PaymentCalculationService', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'config' => $config
            ]);
            
            // Return basic instance as fallback with security service
            $securityService = $app->make(\App\Services\Security\PaymentCalculationSecurityService::class);
            $monitoringService = $app->make(\App\Services\Monitoring\PaymentCalculationMonitoringService::class);
            $auditLogger = $app->make(\App\Services\Audit\PaymentCalculationAuditLogger::class);
            $cacheService = $app->make(\App\Services\Cache\PaymentCalculationCacheService::class);
            return new PaymentCalculationService($securityService, $monitoringService, $auditLogger, $cacheService);
        }
    }

    /**
     * Register service health monitoring
     *
     * @return void
     */
    protected function registerServiceHealthMonitoring()
    {
        $this->app->singleton('payment.calculation.health', function ($app) {
            return function () use ($app) {
                try {
                    $service = $app->make(PaymentCalculationServiceInterface::class);
                    
                    // Perform health check calculations
                    $testCases = [
                        ['price' => 100.0, 'duration' => 1, 'type' => 'total'],
                        ['price' => 50.0, 'duration' => 12, 'type' => 'monthly'],
                    ];
                    
                    $results = [];
                    foreach ($testCases as $test) {
                        $result = $service->calculatePaymentTotal(
                            $test['price'], 
                            $test['duration'], 
                            $test['type']
                        );
                        $results[] = [
                            'test' => $test,
                            'success' => $result->isValid,
                            'error' => $result->errorMessage
                        ];
                    }
                    
                    $allPassed = collect($results)->every(fn($r) => $r['success']);
                    
                    return [
                        'status' => $allPassed ? 'healthy' : 'unhealthy',
                        'service' => 'PaymentCalculationService',
                        'timestamp' => now()->toISOString(),
                        'tests_passed' => collect($results)->where('success', true)->count(),
                        'total_tests' => count($results),
                        'details' => $results
                    ];
                    
                } catch (\Exception $e) {
                    return [
                        'status' => 'unhealthy',
                        'service' => 'PaymentCalculationService',
                        'timestamp' => now()->toISOString(),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ];
                }
            };
        });
    }

    /**
     * Register configuration validation
     *
     * @return void
     */
    protected function registerConfigurationValidation()
    {
        $this->app->singleton('payment.calculation.config.validator', function ($app) {
            return function () use ($app) {
                $config = config('payment_calculation', []);
                $errors = [];
                
                // Validate required configuration sections
                $requiredSections = ['validation', 'monitoring', 'error_handling', 'service'];
                foreach ($requiredSections as $section) {
                    if (!isset($config[$section])) {
                        $errors[] = "Missing required configuration section: {$section}";
                    }
                }
                
                // Validate numeric limits
                $numericValidations = [
                    'validation.max_rental_duration' => ['min' => 1, 'max' => 1200],
                    'validation.max_apartment_price' => ['min' => 1, 'max' => PHP_FLOAT_MAX],
                    'validation.precision_decimal_places' => ['min' => 0, 'max' => 10],
                ];
                
                foreach ($numericValidations as $key => $limits) {
                    $value = data_get($config, $key);
                    if ($value !== null) {
                        if ($value < $limits['min'] || $value > $limits['max']) {
                            $errors[] = "Configuration {$key} value {$value} is outside valid range [{$limits['min']}, {$limits['max']}]";
                        }
                    }
                }
                
                return [
                    'valid' => empty($errors),
                    'errors' => $errors,
                    'config_sections' => array_keys($config),
                    'timestamp' => now()->toISOString()
                ];
            };
        });
    }

    /**
     * Configure logging channels for payment calculations
     *
     * @return void
     */
    protected function configureLoggingChannels()
    {
        $config = config('payment_calculation.audit', []);
        
        if ($config['enable_audit_logging'] ?? true) {
            // Ensure payment calculation log channels exist
            $channels = ['payment_calculations', 'payment_errors', 'payment_audit', 'payment_performance'];
            
            foreach ($channels as $channel) {
                if (!config("logging.channels.{$channel}")) {
                    Log::warning("Payment calculation log channel '{$channel}' not configured in logging.php");
                }
            }
        }
    }

    /**
     * Validate service configuration on boot
     *
     * @return void
     */
    protected function validateServiceConfiguration()
    {
        try {
            $validator = $this->app->make('payment.calculation.config.validator');
            $validation = $validator();
            
            if (!$validation['valid']) {
                Log::channel('payment_errors')->warning('PaymentCalculationService configuration validation failed', [
                    'errors' => $validation['errors'],
                    'timestamp' => now()->toISOString()
                ]);
            } else {
                Log::channel('payment_calculations')->info('PaymentCalculationService configuration validated successfully', [
                    'config_sections' => $validation['config_sections'],
                    'timestamp' => now()->toISOString()
                ]);
            }
            
        } catch (\Exception $e) {
            Log::channel('payment_errors')->error('Failed to validate PaymentCalculationService configuration', [
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ]);
        }
    }

    /**
     * Register console commands for service management
     *
     * @return void
     */
    protected function registerConsoleCommands()
    {
        // Register artisan commands for service management
        $this->commands([
            // Add custom commands here if needed
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            PaymentCalculationService::class,
            PaymentCalculationServiceInterface::class,
            'payment.calculation',
            'payment.calculation.interface',
            'payment.calculation.health',
            'payment.calculation.config.validator',
        ];
    }
}
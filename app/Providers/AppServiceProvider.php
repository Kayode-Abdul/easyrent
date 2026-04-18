<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind SessionManagerInterface to SessionManager implementation
        $this->app->bind(
            \App\Services\Session\SessionManagerInterface::class,
            \App\Services\Session\SessionManager::class
        );
        
        // Register Marketer Qualification Service
        $this->app->singleton(\App\Services\Marketer\MarketerQualificationService::class);
        
        // Register Payment Integration Service
        $this->app->singleton(\App\Services\Payment\PaymentIntegrationService::class);
        
        // Payment Calculation Service is now registered via PaymentCalculationServiceProvider
        
        // Register Email Delivery Tracker
        $this->app->singleton(\App\Services\Email\EmailDeliveryTracker::class);
        
        // Bind EmailNotificationInterface to EmailNotificationService implementation
        $this->app->bind(
            \App\Services\Email\EmailNotificationInterface::class,
            \App\Services\Email\EmailNotificationService::class
        );
        
        // Register EasyRent Logger
        $this->app->singleton(\App\Services\Logging\EasyRentLogger::class);
        
        // Bind EasyRentLoggerInterface to EasyRentLogger implementation
        $this->app->bind(
            \App\Services\Logging\EasyRentLoggerInterface::class,
            \App\Services\Logging\EasyRentLogger::class
        );
        
        // Register Security Services with error handling
        $this->app->singleton(\App\Services\Security\SuspiciousActivityDetector::class, function ($app) {
            try {
                return new \App\Services\Security\SuspiciousActivityDetector(
                    $app->make(\App\Services\Logging\EasyRentLogger::class)
                );
            } catch (\Exception $e) {
                \Log::error('Failed to create SuspiciousActivityDetector: ' . $e->getMessage());
                return null;
            }
        });
        
        $this->app->singleton(\App\Services\Security\SecurityBreachResponseService::class, function ($app) {
            try {
                return new \App\Services\Security\SecurityBreachResponseService(
                    $app->make(\App\Services\Logging\EasyRentLogger::class)
                );
            } catch (\Exception $e) {
                \Log::error('Failed to create SecurityBreachResponseService: ' . $e->getMessage());
                return null;
            }
        });
        
        $this->app->singleton(\App\Services\Security\InputValidationService::class, function ($app) {
            try {
                return new \App\Services\Security\InputValidationService();
            } catch (\Exception $e) {
                \Log::error('Failed to create InputValidationService: ' . $e->getMessage());
                return null;
            }
        });
        
        // Register Error Handling Services
        $this->app->bind(
            \App\Services\ErrorHandling\ErrorHandlerInterface::class,
            \App\Services\ErrorHandling\EasyRentErrorHandler::class
        );
        
        $this->app->singleton(\App\Services\ErrorHandling\EasyRentErrorHandler::class);
        $this->app->singleton(\App\Services\ErrorHandling\ErrorRecoveryService::class);
        $this->app->singleton(\App\Services\Monitoring\ErrorMonitoringService::class);
        
        $this->app->singleton(\App\Services\Monitoring\SystemHealthMonitor::class, function ($app) {
            try {
                return new \App\Services\Monitoring\SystemHealthMonitor();
            } catch (\Exception $e) {
                \Log::error('Failed to create SystemHealthMonitor: ' . $e->getMessage());
                return null;
            }
        });
        
        // Register Cache Services
        $this->app->bind(
            \App\Services\Cache\EasyRentCacheInterface::class,
            \App\Services\Cache\EasyRentCacheService::class
        );
        
        $this->app->singleton(\App\Services\Cache\EasyRentCacheService::class);
        
        // Register Performance Monitoring Service
        $this->app->singleton(\App\Services\Monitoring\PerformanceMonitoringService::class);
    }



    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Pagination\Paginator::useBootstrap();
    }
}

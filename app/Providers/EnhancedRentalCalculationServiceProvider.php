<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Payment\EnhancedRentalCalculationService;

class EnhancedRentalCalculationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(EnhancedRentalCalculationService::class, function ($app) {
            return new EnhancedRentalCalculationService();
        });
        
        // Register alias for easier access
        $this->app->alias(EnhancedRentalCalculationService::class, 'enhanced.rental.calculation');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
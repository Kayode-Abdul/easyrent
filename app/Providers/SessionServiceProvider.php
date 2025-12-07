<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Session\SessionManagerInterface;
use App\Services\Session\SessionManager;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(SessionManagerInterface::class, SessionManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
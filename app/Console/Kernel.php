<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\BackfillRewardDetails::class,
        \App\Console\Commands\RunCommissionAudit::class,
        \App\Console\Commands\MonitorCommissionHealth::class,
        \App\Console\Commands\ValidateSystemIntegration::class,
        \App\Console\Commands\CreateTestRegionalScopes::class,
        \App\Console\Commands\TestRegionalManagerManagement::class,
        \App\Console\Commands\TestCommissionRatesRoute::class,
        \App\Console\Commands\TestDatabaseConnection::class,
        \App\Console\Commands\SyncLegacyRoles::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // Run payment reminders daily at 9 AM
        $schedule->command('payments:send-reminders')
                ->dailyAt('09:00')
                ->timezone('Africa/Lagos');

        // Run commission audit daily at 2 AM
        $schedule->command('commission:audit --period=daily')
                ->dailyAt('02:00')
                ->timezone('Africa/Lagos');

        // Run weekly commission audit on Mondays at 3 AM
        $schedule->command('commission:audit --period=weekly --verify-all')
                ->weeklyOn(1, '03:00')
                ->timezone('Africa/Lagos');

        // Run commission health monitoring every 15 minutes
        $schedule->command('commission:monitor-health --alert-threshold=warning')
                ->everyFifteenMinutes();

        // Reset payment tracking hourly metrics every hour
        $schedule->call(function () {
            app(\App\Services\Monitoring\PaymentSuccessTracker::class)->resetHourlyMetrics();
        })->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

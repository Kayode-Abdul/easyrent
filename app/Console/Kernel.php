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
        \App\Console\Commands\CleanupExpiredSessions::class,
        \App\Console\Commands\CleanupExpiredInvitations::class,
        \App\Console\Commands\ProcessMarketerQualifications::class,
        \App\Console\Commands\ProcessFailedEmails::class,
        \App\Console\Commands\CleanupEasyRentLogs::class,
        \App\Console\Commands\MonitorSystemHealth::class,
        \App\Console\Commands\CleanupInvitationDatabase::class,
        \App\Console\Commands\CalculateInvitationMetrics::class,
        \App\Console\Commands\OptimizeEasyRentCache::class,
        \App\Console\Commands\MonitorEasyRentPerformance::class,
        \App\Console\Commands\AuditBrokenFeatures::class,
        \App\Console\Commands\PaymentCalculationHealthCheck::class,
        \App\Console\Commands\AnalyzePaymentCalculationData::class,
        \App\Console\Commands\ValidatePaymentCalculationMigration::class,
        \App\Console\Commands\MonitorPaymentCalculations::class,
        \App\Console\Commands\OptimizePaymentCalculationPerformance::class,
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

        // Clean up expired invitation sessions every 6 hours
        $schedule->command('sessions:cleanup --force')
                ->everySixHours()
                ->timezone('Africa/Lagos');

        // Clean up expired invitation sessions every hour
        $schedule->command('invitations:cleanup --sessions')
                ->hourly()
                ->timezone('Africa/Lagos');
        
        // Mark expired invitations daily at 1 AM
        $schedule->command('invitations:cleanup --invitations')
                ->dailyAt('01:00')
                ->timezone('Africa/Lagos');
        
        // Full invitation cleanup daily at 4 AM
        $schedule->command('invitations:cleanup --all')
                ->dailyAt('04:00')
                ->timezone('Africa/Lagos');
        
        // Process marketer qualifications daily at 5 AM
        $schedule->command('marketer:process-qualifications')
                ->dailyAt('05:00')
                ->timezone('Africa/Lagos');
        
        // Process failed emails every 30 minutes
        $schedule->command('emails:process-failed --retry-limit=3 --batch-size=50')
                ->everyThirtyMinutes()
                ->timezone('Africa/Lagos');
        
        // Clean up old EasyRent log files weekly on Sundays at 2 AM
        $schedule->command('easyrent:cleanup-logs --days=90')
                ->weeklyOn(0, '02:00')
                ->timezone('Africa/Lagos');
        
        // Monitor system health every 15 minutes
        $schedule->command('easyrent:monitor-health')
                ->everyFifteenMinutes()
                ->timezone('Africa/Lagos');
        
        // Send health alerts for critical issues every hour
        $schedule->command('easyrent:monitor-health --alert')
                ->hourly()
                ->timezone('Africa/Lagos');
        
        // Clean up invitation database daily at 3 AM
        $schedule->command('easyrent:cleanup-invitations --force')
                ->dailyAt('03:00')
                ->timezone('Africa/Lagos');
        
        // Calculate invitation metrics daily at 6 AM
        $schedule->command('easyrent:calculate-metrics --days=1')
                ->dailyAt('06:00')
                ->timezone('Africa/Lagos');
        
        // Calculate weekly metrics on Sundays at 7 AM
        $schedule->command('easyrent:calculate-metrics --days=7 --force')
                ->weeklyOn(0, '07:00')
                ->timezone('Africa/Lagos');
        
        // Optimize cache daily at 12 AM
        $schedule->command('easyrent:optimize-cache --warmup --cleanup')
                ->dailyAt('00:00')
                ->timezone('Africa/Lagos');
        
        // Full cache optimization weekly on Saturdays at 11 PM
        $schedule->command('easyrent:optimize-cache --all')
                ->weeklyOn(6, '23:00')
                ->timezone('Africa/Lagos');
        
        // Monitor performance every 4 hours
        $schedule->command('easyrent:monitor-performance --hours=4 --summary')
                ->everyFourHours()
                ->timezone('Africa/Lagos');
        
        // Run payment calculation health check every 30 minutes
        $schedule->command('payment:health-check --log')
                ->everyThirtyMinutes()
                ->timezone('Africa/Lagos');
        
        // Monitor payment calculations every hour
        $schedule->command('payment:monitor --hours=1 --alerts')
                ->hourly()
                ->timezone('Africa/Lagos');
        
        // Generate payment calculation monitoring reports daily at 8 AM
        $schedule->command('payment:monitor --hours=24 --dashboard')
                ->dailyAt('08:00')
                ->timezone('Africa/Lagos');
        
        // Optimize payment calculation performance daily at 2:30 AM
        $schedule->command('payment-calc:optimize --cache-warmup --cleanup-cache')
                ->dailyAt('02:30')
                ->timezone('Africa/Lagos');
        
        // Full payment calculation optimization weekly on Sundays at 1:30 AM
        $schedule->command('payment-calc:optimize --all')
                ->weeklyOn(0, '01:30')
                ->timezone('Africa/Lagos');
        
        // Pre-calculate common scenarios every 6 hours
        $schedule->command('payment-calc:optimize --pre-calculate')
                ->everySixHours()
                ->timezone('Africa/Lagos');
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

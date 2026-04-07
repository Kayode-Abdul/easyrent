<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApartmentInvitation;
use Illuminate\Support\Facades\Log;

class CleanupExpiredInvitations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invitations:cleanup 
                            {--sessions : Only cleanup expired session data}
                            {--invitations : Only cleanup expired invitations}
                            {--all : Cleanup both sessions and invitations (default)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired invitation sessions and mark expired invitations';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting invitation cleanup process...');
        
        $cleanupSessions = $this->option('sessions') || $this->option('all') || 
                          (!$this->option('sessions') && !$this->option('invitations'));
        $cleanupInvitations = $this->option('invitations') || $this->option('all') || 
                             (!$this->option('sessions') && !$this->option('invitations'));
        
        $totalCleaned = 0;
        
        if ($cleanupSessions) {
            $this->info('Cleaning up expired session data...');
            $expiredSessions = ApartmentInvitation::cleanupExpiredSessions();
            $this->info("Cleaned up {$expiredSessions} expired sessions.");
            $totalCleaned += $expiredSessions;
        }
        
        if ($cleanupInvitations) {
            $this->info('Marking expired invitations...');
            $expiredInvitations = ApartmentInvitation::expireOldInvitations();
            $this->info("Marked {$expiredInvitations} invitations as expired.");
            $totalCleaned += $expiredInvitations;
        }
        
        // Reset rate limits for all invitations (daily reset)
        $this->info('Resetting rate limits...');
        $resetCount = ApartmentInvitation::where('rate_limit_count', '>', 0)
            ->where('rate_limit_reset_at', '<=', now())
            ->update([
                'rate_limit_count' => 0,
                'rate_limit_reset_at' => now()->addHour()
            ]);
        $this->info("Reset rate limits for {$resetCount} invitations.");
        
        // Log cleanup summary
        Log::info('Invitation cleanup completed', [
            'expired_sessions' => $cleanupSessions ? $expiredSessions : 0,
            'expired_invitations' => $cleanupInvitations ? $expiredInvitations : 0,
            'rate_limits_reset' => $resetCount,
            'total_cleaned' => $totalCleaned,
            'completed_at' => now()
        ]);
        
        $this->info("Cleanup completed! Total items processed: {$totalCleaned}");
        
        return Command::SUCCESS;
    }
}
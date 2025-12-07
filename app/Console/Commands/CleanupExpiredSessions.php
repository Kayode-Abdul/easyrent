<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Session\SessionManagerInterface;
use App\Models\ApartmentInvitation;

class CleanupExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:cleanup {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired invitation session data from both session storage and database';

    /**
     * The session manager instance
     */
    protected SessionManagerInterface $sessionManager;

    /**
     * Create a new command instance.
     */
    public function __construct(SessionManagerInterface $sessionManager)
    {
        parent::__construct();
        $this->sessionManager = $sessionManager;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting session cleanup...');

        // Clean up session storage
        $sessionsCleaned = $this->sessionManager->cleanupExpiredSessions();
        
        // Clean up database session data
        $databaseCleaned = $this->cleanupDatabaseSessions();

        $totalCleaned = $sessionsCleaned + $databaseCleaned;

        if ($totalCleaned > 0) {
            $this->info("Cleanup completed successfully!");
            $this->line("- Session storage cleaned: {$sessionsCleaned}");
            $this->line("- Database records cleaned: {$databaseCleaned}");
            $this->line("- Total cleaned: {$totalCleaned}");
        } else {
            $this->info('No expired sessions found to clean up.');
        }

        return Command::SUCCESS;
    }

    /**
     * Clean up expired session data from database
     */
    private function cleanupDatabaseSessions(): int
    {
        $cleanedCount = 0;

        try {
            // Find invitations with expired session data
            $expiredInvitations = ApartmentInvitation::whereNotNull('session_data')
                ->where(function ($query) {
                    $query->where('session_expires_at', '<', now())
                          ->orWhere('session_expires_at', null);
                })
                ->where('status', '!=', ApartmentInvitation::STATUS_USED)
                ->get();

            foreach ($expiredInvitations as $invitation) {
                // Only clear session data if it's actually expired
                if ($invitation->isSessionExpired() || !$invitation->session_expires_at) {
                    $invitation->clearSessionData();
                    $cleanedCount++;
                }
            }

            // Also clean up very old invitations (older than 7 days) regardless of status
            $veryOldInvitations = ApartmentInvitation::whereNotNull('session_data')
                ->where('created_at', '<', now()->subDays(7))
                ->get();

            foreach ($veryOldInvitations as $invitation) {
                $invitation->clearSessionData();
                $cleanedCount++;
            }

        } catch (\Exception $e) {
            $this->error('Error cleaning database sessions: ' . $e->getMessage());
        }

        return $cleanedCount;
    }
}

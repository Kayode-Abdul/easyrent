<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanupEasyRentLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'easyrent:cleanup-logs {--days=90 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old EasyRent Link Authentication System log files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Cleaning up EasyRent log files older than {$days} days (before {$cutoffDate->format('Y-m-d')})");
        
        $logDirectory = storage_path('logs');
        $easyRentLogPatterns = [
            'easyrent_invitations-*.log',
            'easyrent_auth-*.log',
            'easyrent_payments-*.log',
            'easyrent_errors-*.log',
            'easyrent_performance-*.log',
            'easyrent_sessions-*.log',
            'easyrent_security-*.log',
            'easyrent_emails-*.log',
            'easyrent_assignments-*.log',
        ];
        
        $totalDeleted = 0;
        $totalSize = 0;
        
        foreach ($easyRentLogPatterns as $pattern) {
            $files = File::glob($logDirectory . '/' . $pattern);
            
            foreach ($files as $file) {
                $fileDate = $this->extractDateFromLogFile($file);
                
                if ($fileDate && $fileDate->lt($cutoffDate)) {
                    $fileSize = File::size($file);
                    $totalSize += $fileSize;
                    
                    if (File::delete($file)) {
                        $totalDeleted++;
                        $this->line("Deleted: " . basename($file) . " (" . $this->formatBytes($fileSize) . ")");
                    } else {
                        $this->error("Failed to delete: " . basename($file));
                    }
                }
            }
        }
        
        if ($totalDeleted > 0) {
            $this->info("Successfully deleted {$totalDeleted} log files, freed " . $this->formatBytes($totalSize));
        } else {
            $this->info("No old log files found to delete.");
        }
        
        return 0;
    }
    
    /**
     * Extract date from log file name
     */
    private function extractDateFromLogFile(string $filePath): ?Carbon
    {
        $filename = basename($filePath);
        
        // Match pattern like: easyrent_invitations-2024-01-15.log
        if (preg_match('/easyrent_\w+-(\d{4}-\d{2}-\d{2})\.log$/', $filename, $matches)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $matches[1]);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
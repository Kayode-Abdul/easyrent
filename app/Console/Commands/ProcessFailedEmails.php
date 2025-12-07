<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;

class ProcessFailedEmails extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'emails:process-failed 
                            {--retry-limit=3 : Maximum number of retry attempts}
                            {--batch-size=50 : Number of failed jobs to process at once}';

    /**
     * The console command description.
     */
    protected $description = 'Process failed email jobs and retry them with exponential backoff';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $retryLimit = (int) $this->option('retry-limit');
        $batchSize = (int) $this->option('batch-size');

        $this->info("Processing failed email jobs (retry limit: {$retryLimit}, batch size: {$batchSize})");

        // Get failed jobs from the failed_jobs table
        $failedJobs = DB::table('failed_jobs')
            ->where('payload', 'like', '%Mail%')
            ->orderBy('failed_at', 'asc')
            ->limit($batchSize)
            ->get();

        if ($failedJobs->isEmpty()) {
            $this->info('No failed email jobs found.');
            return 0;
        }

        $processed = 0;
        $retried = 0;
        $removed = 0;

        foreach ($failedJobs as $failedJob) {
            $processed++;
            
            try {
                $payload = json_decode($failedJob->payload, true);
                $attempts = $this->getJobAttempts($failedJob->id);

                if ($attempts >= $retryLimit) {
                    // Remove job if it has exceeded retry limit
                    DB::table('failed_jobs')->where('id', $failedJob->id)->delete();
                    $removed++;
                    
                    $this->warn("Removed job {$failedJob->id} after {$attempts} attempts");
                    
                    // Log the permanent failure
                    Log::error('Email job permanently failed after max retries', [
                        'job_id' => $failedJob->id,
                        'attempts' => $attempts,
                        'payload' => $payload
                    ]);
                    
                    continue;
                }

                // Calculate delay based on attempt number (exponential backoff)
                $delay = pow(2, $attempts) * 60; // 1min, 2min, 4min, 8min...
                
                // Retry the job
                Queue::later($delay, $payload['job'], $payload['data']);
                
                // Remove from failed jobs table
                DB::table('failed_jobs')->where('id', $failedJob->id)->delete();
                
                $retried++;
                
                $this->info("Retried job {$failedJob->id} (attempt {$attempts}) with {$delay}s delay");
                
            } catch (\Exception $e) {
                $this->error("Failed to process job {$failedJob->id}: " . $e->getMessage());
                
                Log::error('Failed to process failed email job', [
                    'job_id' => $failedJob->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Processed {$processed} failed jobs: {$retried} retried, {$removed} removed");
        
        return 0;
    }

    /**
     * Get the number of attempts for a job based on its history
     */
    private function getJobAttempts(string $jobId): int
    {
        // This is a simplified approach - in a real implementation,
        // you might want to track attempts in a separate table
        return 1; // Default to 1 attempt for now
    }
}
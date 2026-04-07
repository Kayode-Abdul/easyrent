<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Payment\PaymentCalculationServiceInterface;
use Illuminate\Support\Facades\Log;

class PaymentCalculationHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:health-check 
                            {--detailed : Show detailed test results}
                            {--log : Log results to payment calculation logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform health check on PaymentCalculationService';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Running PaymentCalculationService Health Check...');
        
        try {
            // Get the service health check function
            $healthCheck = app('payment.calculation.health');
            $result = $healthCheck();
            
            // Display results
            $this->displayResults($result);
            
            // Log results if requested
            if ($this->option('log')) {
                $this->logResults($result);
            }
            
            // Return appropriate exit code
            return $result['status'] === 'healthy' ? 0 : 1;
            
        } catch (\Exception $e) {
            $this->error('Health check failed: ' . $e->getMessage());
            
            if ($this->option('log')) {
                Log::channel('payment_errors')->error('Health check command failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'timestamp' => now()->toISOString()
                ]);
            }
            
            return 1;
        }
    }

    /**
     * Display health check results
     *
     * @param array $result
     * @return void
     */
    protected function displayResults(array $result)
    {
        // Display status
        if ($result['status'] === 'healthy') {
            $this->info('✅ PaymentCalculationService is HEALTHY');
        } else {
            $this->error('❌ PaymentCalculationService is UNHEALTHY');
        }
        
        // Display basic info
        $this->line('Service: ' . $result['service']);
        $this->line('Timestamp: ' . $result['timestamp']);
        
        if (isset($result['tests_passed'], $result['total_tests'])) {
            $this->line("Tests: {$result['tests_passed']}/{$result['total_tests']} passed");
        }
        
        // Display error if present
        if (isset($result['error'])) {
            $this->error('Error: ' . $result['error']);
        }
        
        // Display detailed results if requested
        if ($this->option('detailed') && isset($result['details'])) {
            $this->line('');
            $this->info('Detailed Test Results:');
            
            foreach ($result['details'] as $index => $test) {
                $status = $test['success'] ? '✅' : '❌';
                $this->line("  Test " . ($index + 1) . ": {$status}");
                $this->line("    Price: {$test['test']['price']}");
                $this->line("    Duration: {$test['test']['duration']}");
                $this->line("    Type: {$test['test']['type']}");
                
                if (!$test['success'] && $test['error']) {
                    $this->line("    Error: {$test['error']}");
                }
            }
        }
    }

    /**
     * Log health check results
     *
     * @param array $result
     * @return void
     */
    protected function logResults(array $result)
    {
        if ($result['status'] === 'healthy') {
            Log::channel('payment_calculations')->info('PaymentCalculationService health check passed', $result);
        } else {
            Log::channel('payment_errors')->warning('PaymentCalculationService health check failed', $result);
        }
    }
}
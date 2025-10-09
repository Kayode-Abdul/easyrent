<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Audit\CommissionAuditService;
use Carbon\Carbon;

class RunCommissionAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:audit 
                            {--period=daily : Audit period (daily, weekly, monthly)}
                            {--region= : Specific region to audit}
                            {--date= : Specific date to audit (YYYY-MM-DD)}
                            {--verify-all : Verify all payments in period}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run commission calculation audit and verification';

    protected $auditService;

    /**
     * Create a new command instance.
     */
    public function __construct(CommissionAuditService $auditService)
    {
        parent::__construct();
        $this->auditService = $auditService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->option('period');
        $region = $this->option('region');
        $specificDate = $this->option('date');
        $verifyAll = $this->option('verify-all');

        $this->info('Starting commission audit...');

        // Determine date range
        [$startDate, $endDate] = $this->getDateRange($period, $specificDate);

        $this->info("Auditing period: {$startDate->toDateString()} to {$endDate->toDateString()}");
        
        if ($region) {
            $this->info("Region filter: {$region}");
        }

        // Run reconciliation
        $this->info('Running commission reconciliation...');
        $reconciliation = $this->auditService->reconcileCommissions($startDate, $endDate, $region);

        $this->displayReconciliationResults($reconciliation);

        // Verify individual payments if requested
        if ($verifyAll) {
            $this->info('Verifying individual payments...');
            $this->verifyIndividualPayments($startDate, $endDate, $region);
        }

        // Generate audit report
        $this->info('Generating audit report...');
        $report = $this->auditService->generateAuditReport($startDate, $endDate, $region);

        $this->displayAuditReport($report);

        $this->info('Commission audit completed successfully.');

        return 0;
    }

    /**
     * Get date range based on period and specific date
     */
    private function getDateRange(string $period, ?string $specificDate): array
    {
        if ($specificDate) {
            $date = Carbon::parse($specificDate);
            return [$date->startOfDay(), $date->endOfDay()];
        }

        $now = Carbon::now();

        switch ($period) {
            case 'weekly':
                return [$now->startOfWeek(), $now->endOfWeek()];
            case 'monthly':
                return [$now->startOfMonth(), $now->endOfMonth()];
            case 'daily':
            default:
                return [$now->startOfDay(), $now->endOfDay()];
        }
    }

    /**
     * Display reconciliation results
     */
    private function displayReconciliationResults(array $reconciliation): void
    {
        $summary = $reconciliation['summary'];

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Payments', $summary['total_payments']],
                ['Expected Total', '$' . number_format($summary['expected_total'], 2)],
                ['Actual Total', '$' . number_format($summary['actual_total'], 2)],
                ['Difference', '$' . number_format($summary['total_difference'], 2)],
                ['Discrepancies', $summary['discrepancy_count']],
                ['Accuracy Rate', $summary['accuracy_rate'] . '%']
            ]
        );

        if (!empty($reconciliation['discrepancies'])) {
            $this->warn('Discrepancies found:');
            foreach ($reconciliation['discrepancies'] as $discrepancy) {
                $this->line("- Payment ID {$discrepancy['payment_id']}: " . 
                          implode(', ', $discrepancy['errors'] ?? [$discrepancy['type'] ?? 'Unknown error']));
            }
        }

        if (!empty($reconciliation['recommendations'])) {
            $this->info('Recommendations:');
            foreach ($reconciliation['recommendations'] as $recommendation) {
                $this->line("- {$recommendation}");
            }
        }
    }

    /**
     * Verify individual payments
     */
    private function verifyIndividualPayments(Carbon $startDate, Carbon $endDate, ?string $region): void
    {
        $paymentsQuery = \App\Models\CommissionPayment::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($region) {
            $paymentsQuery->whereHas('marketer', function($q) use ($region) {
                $q->where('state', $region);
            });
        }

        $payments = $paymentsQuery->get();
        $verificationResults = [];

        $progressBar = $this->output->createProgressBar($payments->count());
        $progressBar->start();

        foreach ($payments as $payment) {
            $verification = $this->auditService->verifyCommissionCalculation($payment->id);
            
            if (!$verification['verified']) {
                $verificationResults[] = [
                    'payment_id' => $payment->id,
                    'errors' => $verification['errors']
                ];
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        if (!empty($verificationResults)) {
            $this->warn('Payment verification failures:');
            foreach ($verificationResults as $result) {
                $this->line("Payment {$result['payment_id']}: " . implode(', ', $result['errors']));
            }
        } else {
            $this->info('All payments verified successfully.');
        }
    }

    /**
     * Display audit report
     */
    private function displayAuditReport(array $report): void
    {
        $this->info('Audit Report Summary:');

        $errorStats = $report['error_statistics'];
        $verificationStats = $report['verification_statistics'];

        $this->table(
            ['Category', 'Count'],
            [
                ['Total Errors', $errorStats['total_errors']],
                ['Critical Errors', $errorStats['critical_errors']],
                ['Errors', $errorStats['errors']],
                ['Warnings', $errorStats['warnings']],
                ['Total Verifications', $verificationStats['total_verifications']],
                ['Successful Verifications', $verificationStats['successful_verifications']],
                ['Verification Success Rate', $verificationStats['success_rate'] . '%']
            ]
        );
    }
}
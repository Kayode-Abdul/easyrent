<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Marketer\MarketerQualificationService;
use Illuminate\Support\Facades\Log;

class ProcessMarketerQualifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketer:process-qualifications 
                            {--dry-run : Show what would be processed without making changes}
                            {--user-id= : Process qualification for specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending marketer qualifications and promote eligible users';

    protected $qualificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(MarketerQualificationService $qualificationService)
    {
        parent::__construct();
        $this->qualificationService = $qualificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting marketer qualification processing...');

        try {
            if ($this->option('user-id')) {
                return $this->processSpecificUser($this->option('user-id'));
            }

            if ($this->option('dry-run')) {
                return $this->dryRun();
            }

            return $this->processPendingQualifications();

        } catch (\Exception $e) {
            $this->error('Error processing marketer qualifications: ' . $e->getMessage());
            Log::error('Marketer qualification command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Process qualifications for a specific user
     */
    protected function processSpecificUser($userId)
    {
        $user = \App\Models\User::where('user_id', $userId)->first();
        
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $this->info("Processing qualification for user: {$user->first_name} {$user->last_name} ({$user->email})");

        $result = $this->qualificationService->evaluateUserQualification($user);

        $this->displayUserResult($result, $user);

        if ($result['promoted']) {
            $this->info('✅ User has been promoted to marketer!');
        } elseif ($result['is_marketer_after']) {
            $this->info('ℹ️  User is already a marketer.');
        } elseif ($result['qualified_after']) {
            $this->warn('⚠️  User is qualified but promotion failed.');
        } else {
            $this->info('ℹ️  User does not qualify for marketer status yet.');
        }

        return 0;
    }

    /**
     * Show what would be processed without making changes
     */
    protected function dryRun()
    {
        $this->info('🔍 Dry run mode - no changes will be made');
        
        $pendingUsers = $this->qualificationService->getPendingQualifications();
        $stats = $this->qualificationService->getQualificationStatistics();

        $this->info("Current Statistics:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Users', $stats['total_users']],
                ['Current Marketers', $stats['total_marketers']],
                ['Qualified Non-Marketers', $stats['qualified_non_marketers']],
                ['Total Referrals', $stats['total_referrals']],
                ['Successful Referrals', $stats['successful_referrals']]
            ]
        );

        if ($pendingUsers->isEmpty()) {
            $this->info('✅ No users are pending marketer qualification.');
            return 0;
        }

        $this->info("\n📋 Users pending marketer qualification:");
        
        $tableData = [];
        foreach ($pendingUsers as $user) {
            $qualificationStatus = $user->getMarketerQualificationStatus();
            $tableData[] = [
                $user->user_id,
                $user->first_name . ' ' . $user->last_name,
                $user->email,
                $qualificationStatus['total_referrals'],
                $qualificationStatus['landlord_referrals'],
                $qualificationStatus['qualifying_referrals']
            ];
        }

        $this->table(
            ['User ID', 'Name', 'Email', 'Total Referrals', 'Landlord Referrals', 'Qualifying Referrals'],
            $tableData
        );

        $this->info("\n💡 Run without --dry-run to process these qualifications.");
        
        return 0;
    }

    /**
     * Process all pending qualifications
     */
    protected function processPendingQualifications()
    {
        $pendingUsers = $this->qualificationService->getPendingQualifications();

        if ($pendingUsers->isEmpty()) {
            $this->info('✅ No users are pending marketer qualification.');
            return 0;
        }

        $this->info("Found {$pendingUsers->count()} users pending qualification...");

        $progressBar = $this->output->createProgressBar($pendingUsers->count());
        $progressBar->start();

        $results = [];
        $promotions = 0;
        $errors = 0;

        foreach ($pendingUsers as $user) {
            try {
                $result = $this->qualificationService->evaluateUserQualification($user);
                $results[] = $result;

                if ($result['promoted']) {
                    $promotions++;
                    $this->line("\n✅ Promoted: {$user->first_name} {$user->last_name} ({$user->email})");
                } elseif ($result['error']) {
                    $errors++;
                    $this->line("\n❌ Error for {$user->email}: {$result['error']}");
                }

            } catch (\Exception $e) {
                $errors++;
                $this->line("\n❌ Exception for {$user->email}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->info("\n\n📊 Processing Summary:");
        $this->info("Total processed: {$pendingUsers->count()}");
        $this->info("Promotions: {$promotions}");
        $this->info("Errors: {$errors}");

        if ($promotions > 0) {
            $this->info("🎉 {$promotions} users have been promoted to marketer status!");
        }

        return 0;
    }

    /**
     * Display detailed result for a user
     */
    protected function displayUserResult(array $result, \App\Models\User $user)
    {
        $qualificationStatus = $user->getMarketerQualificationStatus();

        $this->info("\nQualification Details:");
        $this->table(
            ['Metric', 'Value'],
            [
                ['User ID', $user->user_id],
                ['Name', $user->first_name . ' ' . $user->last_name],
                ['Email', $user->email],
                ['Was Marketer', $result['was_marketer'] ? 'Yes' : 'No'],
                ['Is Marketer Now', $result['is_marketer_after'] ? 'Yes' : 'No'],
                ['Qualified Before', $result['qualified_before'] ? 'Yes' : 'No'],
                ['Qualified After', $result['qualified_after'] ? 'Yes' : 'No'],
                ['Promoted', $result['promoted'] ? 'Yes' : 'No'],
                ['Total Referrals', $qualificationStatus['total_referrals']],
                ['Landlord Referrals', $qualificationStatus['landlord_referrals']],
                ['Qualifying Referrals', $qualificationStatus['qualifying_referrals']]
            ]
        );

        if (!empty($qualificationStatus['qualifying_referral_details'])) {
            $this->info("\nQualifying Referral Details:");
            $referralData = [];
            foreach ($qualificationStatus['qualifying_referral_details'] as $detail) {
                $referralData[] = [
                    $detail['referred_user_name'],
                    $detail['referred_user_email'],
                    $detail['successful_payments'],
                    $detail['referral_date']->format('Y-m-d')
                ];
            }
            $this->table(
                ['Referred User', 'Email', 'Successful Payments', 'Referral Date'],
                $referralData
            );
        }
    }
}
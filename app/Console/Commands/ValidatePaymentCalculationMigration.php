<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Apartment;
use App\Models\ProfomaReceipt;

class ValidatePaymentCalculationMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:validate-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate that the payment calculation data migration completed successfully';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Payment Calculation Migration Validation');
        $this->info('=======================================');

        $errors = [];
        $warnings = [];

        // Requirement 3.3: Set default pricing_type for existing apartments
        $this->info('✓ Checking apartment pricing_type configuration...');
        $apartmentsWithoutType = Apartment::whereNull('pricing_type')->count();
        if ($apartmentsWithoutType > 0) {
            $errors[] = "Found {$apartmentsWithoutType} apartments without pricing_type";
        } else {
            $this->line('  ✓ All apartments have pricing_type set');
        }

        $invalidPricingTypes = Apartment::whereNotIn('pricing_type', ['total', 'monthly'])->count();
        if ($invalidPricingTypes > 0) {
            $errors[] = "Found {$invalidPricingTypes} apartments with invalid pricing_type";
        } else {
            $this->line('  ✓ All apartments have valid pricing_type values');
        }

        // Requirement 4.2: Preserve existing calculation results without modification
        $this->info('');
        $this->info('✓ Checking proforma receipt preservation...');
        $receiptsWithoutMethod = ProfomaReceipt::whereNull('calculation_method')->count();
        if ($receiptsWithoutMethod > 0) {
            $errors[] = "Found {$receiptsWithoutMethod} proforma receipts without calculation_method";
        } else {
            $this->line('  ✓ All proforma receipts have calculation_method set');
        }

        $receiptsWithoutLog = ProfomaReceipt::whereNull('calculation_log')->count();
        if ($receiptsWithoutLog > 0) {
            $warnings[] = "Found {$receiptsWithoutLog} proforma receipts without calculation_log";
        } else {
            $this->line('  ✓ All proforma receipts have calculation_log set');
        }

        // Check that amounts were preserved
        $receipts = ProfomaReceipt::whereNotNull('calculation_log')->get();
        foreach ($receipts as $receipt) {
            $log = $receipt->calculation_log;
            if (isset($log['preserved_amount'])) {
                $preservedAmount = (float) $log['preserved_amount'];
                $currentAmount = (float) $receipt->amount;
                if (abs($preservedAmount - $currentAmount) > 0.01) {
                    $errors[] = "Receipt {$receipt->id}: preserved amount ({$preservedAmount}) doesn't match current amount ({$currentAmount})";
                }
            }
        }

        if (empty($errors)) {
            $this->line('  ✓ All proforma receipt amounts preserved correctly');
        }

        // Data integrity checks
        $this->info('');
        $this->info('✓ Checking data integrity...');
        $negativeAmounts = Apartment::where('amount', '<', 0)->count();
        if ($negativeAmounts > 0) {
            $warnings[] = "Found {$negativeAmounts} apartments with negative amounts";
        } else {
            $this->line('  ✓ No apartments with negative amounts');
        }

        $negativeReceiptAmounts = ProfomaReceipt::where('amount', '<', 0)->count();
        if ($negativeReceiptAmounts > 0) {
            $warnings[] = "Found {$negativeReceiptAmounts} proforma receipts with negative amounts";
        } else {
            $this->line('  ✓ No proforma receipts with negative amounts');
        }

        // Relationship integrity
        $this->info('');
        $this->info('✓ Checking relationship integrity...');
        $orphanedReceipts = ProfomaReceipt::whereNotNull('apartment_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('apartments')
                    ->whereColumn('apartments.id', 'profoma_receipt.apartment_id');
            })->count();

        if ($orphanedReceipts > 0) {
            $errors[] = "Found {$orphanedReceipts} proforma receipts with invalid apartment references";
        } else {
            $this->line('  ✓ All proforma receipts have valid apartment references');
        }

        // Summary
        $this->info('');
        $this->info(str_repeat('=', 50));
        $this->info('VALIDATION SUMMARY');
        $this->info(str_repeat('=', 50));

        if (empty($errors)) {
            $this->info('✅ MIGRATION SUCCESSFUL - All requirements met');
        } else {
            $this->error('❌ MIGRATION FAILED - Errors found:');
            foreach ($errors as $error) {
                $this->error("  • {$error}");
            }
        }

        if (!empty($warnings)) {
            $this->warn('');
            $this->warn('⚠️  WARNINGS:');
            foreach ($warnings as $warning) {
                $this->warn("  • {$warning}");
            }
        }

        // Statistics
        $totalApartments = Apartment::count();
        $totalReceipts = ProfomaReceipt::count();
        $totalPricingApartments = Apartment::where('pricing_type', 'total')->count();
        $monthlyPricingApartments = Apartment::where('pricing_type', 'monthly')->count();

        $this->info('');
        $this->info('📊 STATISTICS:');
        $this->line("  • Total apartments: {$totalApartments}");
        $this->line("  • Apartments with 'total' pricing: {$totalPricingApartments}");
        $this->line("  • Apartments with 'monthly' pricing: {$monthlyPricingApartments}");
        $this->line("  • Total proforma receipts: {$totalReceipts}");

        // Calculation method distribution
        $methodDistribution = ProfomaReceipt::select('calculation_method', DB::raw('count(*) as count'))
            ->groupBy('calculation_method')
            ->get()
            ->pluck('count', 'calculation_method')
            ->toArray();

        $this->line('  • Calculation method distribution:');
        foreach ($methodDistribution as $method => $count) {
            $this->line("    - {$method}: {$count}");
        }

        $this->info('');
        $this->info('✅ Migration validation completed');

        return empty($errors) ? 0 : 1;
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Apartment;
use App\Models\ProfomaReceipt;
use App\Services\Payment\PaymentCalculationServiceInterface;

class AnalyzePaymentCalculationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:analyze-data 
                            {--detailed : Show detailed analysis for each record}
                            {--validate : Validate calculation consistency}
                            {--fix-inconsistencies : Attempt to fix found inconsistencies}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze payment calculation data for consistency and integrity';

    /**
     * The payment calculation service
     */
    private PaymentCalculationServiceInterface $calculationService;

    /**
     * Create a new command instance.
     */
    public function __construct(PaymentCalculationServiceInterface $calculationService)
    {
        parent::__construct();
        $this->calculationService = $calculationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Analyzing Payment Calculation Data');
        $this->info('=====================================');

        // Basic statistics
        $this->showBasicStatistics();

        // Detailed analysis if requested
        if ($this->option('detailed')) {
            $this->showDetailedAnalysis();
        }

        // Validation if requested
        if ($this->option('validate')) {
            $this->validateCalculationConsistency();
        }

        // Fix inconsistencies if requested
        if ($this->option('fix-inconsistencies')) {
            $this->fixInconsistencies();
        }

        return 0;
    }

    /**
     * Show basic statistics about the payment calculation data
     */
    private function showBasicStatistics(): void
    {
        $this->info("\n📊 Basic Statistics:");

        // Apartment statistics
        $totalApartments = Apartment::count();
        $totalPricingApartments = Apartment::where('pricing_type', 'total')->count();
        $monthlyPricingApartments = Apartment::where('pricing_type', 'monthly')->count();
        $apartmentsWithConfig = Apartment::whereNotNull('price_configuration')->count();

        $this->table(['Metric', 'Count'], [
            ['Total Apartments', $totalApartments],
            ['Total Pricing Type', $totalPricingApartments],
            ['Monthly Pricing Type', $monthlyPricingApartments],
            ['With Price Configuration', $apartmentsWithConfig],
        ]);

        // Proforma receipt statistics
        $totalReceipts = ProfomaReceipt::count();
        $receiptsWithMethod = ProfomaReceipt::whereNotNull('calculation_method')->count();
        $receiptsWithLog = ProfomaReceipt::whereNotNull('calculation_log')->count();

        $this->table(['Metric', 'Count'], [
            ['Total Proforma Receipts', $totalReceipts],
            ['With Calculation Method', $receiptsWithMethod],
            ['With Calculation Log', $receiptsWithLog],
        ]);

        // Calculation method distribution
        $methodDistribution = ProfomaReceipt::select('calculation_method', DB::raw('count(*) as count'))
            ->whereNotNull('calculation_method')
            ->groupBy('calculation_method')
            ->get();

        if ($methodDistribution->isNotEmpty()) {
            $this->info("\n📈 Calculation Method Distribution:");
            $this->table(['Method', 'Count'], 
                $methodDistribution->map(fn($item) => [$item->calculation_method, $item->count])->toArray()
            );
        }
    }

    /**
     * Show detailed analysis of payment calculation data
     */
    private function showDetailedAnalysis(): void
    {
        $this->info("\n🔍 Detailed Analysis:");

        // Analyze apartment pricing distribution
        $this->analyzeApartmentPricing();

        // Analyze proforma calculation patterns
        $this->analyzeProformaCalculations();

        // Check for potential data quality issues
        $this->checkDataQualityIssues();
    }

    /**
     * Analyze apartment pricing distribution
     */
    private function analyzeApartmentPricing(): void
    {
        $this->info("\n🏠 Apartment Pricing Analysis:");

        // Price range analysis
        $priceStats = Apartment::selectRaw('
            MIN(amount) as min_price,
            MAX(amount) as max_price,
            AVG(amount) as avg_price,
            COUNT(*) as total_count
        ')->first();

        $this->table(['Metric', 'Value'], [
            ['Minimum Price', number_format($priceStats->min_price, 2)],
            ['Maximum Price', number_format($priceStats->max_price, 2)],
            ['Average Price', number_format($priceStats->avg_price, 2)],
            ['Total Count', $priceStats->total_count],
        ]);

        // Price distribution by pricing type
        $priceByType = Apartment::select('pricing_type', DB::raw('AVG(amount) as avg_amount, COUNT(*) as count'))
            ->groupBy('pricing_type')
            ->get();

        if ($priceByType->isNotEmpty()) {
            $this->info("\n💰 Average Price by Pricing Type:");
            $this->table(['Pricing Type', 'Average Amount', 'Count'], 
                $priceByType->map(fn($item) => [
                    $item->pricing_type, 
                    number_format($item->avg_amount, 2), 
                    $item->count
                ])->toArray()
            );
        }
    }

    /**
     * Analyze proforma calculation patterns
     */
    private function analyzeProformaCalculations(): void
    {
        $this->info("\n📋 Proforma Calculation Analysis:");

        // Duration analysis
        $durationStats = ProfomaReceipt::whereNotNull('duration')
            ->selectRaw('
                MIN(duration) as min_duration,
                MAX(duration) as max_duration,
                AVG(duration) as avg_duration,
                COUNT(*) as count_with_duration
            ')->first();

        if ($durationStats->count_with_duration > 0) {
            $this->table(['Metric', 'Value'], [
                ['Minimum Duration', $durationStats->min_duration . ' months'],
                ['Maximum Duration', $durationStats->max_duration . ' months'],
                ['Average Duration', number_format($durationStats->avg_duration, 1) . ' months'],
                ['Records with Duration', $durationStats->count_with_duration],
            ]);
        }

        // Amount analysis
        $amountStats = ProfomaReceipt::selectRaw('
            MIN(amount) as min_amount,
            MAX(amount) as max_amount,
            AVG(amount) as avg_amount,
            COUNT(*) as total_count
        ')->first();

        $this->table(['Metric', 'Value'], [
            ['Minimum Amount', number_format($amountStats->min_amount, 2)],
            ['Maximum Amount', number_format($amountStats->max_amount, 2)],
            ['Average Amount', number_format($amountStats->avg_amount, 2)],
            ['Total Records', $amountStats->total_count],
        ]);
    }

    /**
     * Check for potential data quality issues
     */
    private function checkDataQualityIssues(): void
    {
        $this->info("\n⚠️  Data Quality Issues:");

        $issues = [];

        // Check for apartments with negative amounts
        $negativeAmounts = Apartment::where('amount', '<', 0)->count();
        if ($negativeAmounts > 0) {
            $issues[] = "Found {$negativeAmounts} apartments with negative amounts";
        }

        // Check for apartments with zero amounts
        $zeroAmounts = Apartment::where('amount', '=', 0)->count();
        if ($zeroAmounts > 0) {
            $issues[] = "Found {$zeroAmounts} apartments with zero amounts";
        }

        // Check for proforma receipts with negative amounts
        $negativeReceiptAmounts = ProfomaReceipt::where('amount', '<', 0)->count();
        if ($negativeReceiptAmounts > 0) {
            $issues[] = "Found {$negativeReceiptAmounts} proforma receipts with negative amounts";
        }

        // Check for proforma receipts with zero duration
        $zeroDuration = ProfomaReceipt::where('duration', '=', 0)->count();
        if ($zeroDuration > 0) {
            $issues[] = "Found {$zeroDuration} proforma receipts with zero duration";
        }

        // Check for orphaned proforma receipts (no matching apartment)
        // Note: apartment_id in proforma_receipt refers to apartments.id, not apartments.apartment_id
        $orphanedReceipts = ProfomaReceipt::whereNotNull('apartment_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('apartments')
                    ->whereColumn('apartments.id', 'profoma_receipt.apartment_id');
            })->count();
        if ($orphanedReceipts > 0) {
            $issues[] = "Found {$orphanedReceipts} proforma receipts with no matching apartment";
        }

        if (empty($issues)) {
            $this->info("✅ No data quality issues found!");
        } else {
            foreach ($issues as $issue) {
                $this->warn("⚠️  {$issue}");
            }
        }
    }

    /**
     * Validate calculation consistency
     */
    private function validateCalculationConsistency(): void
    {
        $this->info("\n🔍 Validating Calculation Consistency:");

        $inconsistencies = [];
        $receiptsChecked = 0;
        $consistentCalculations = 0;

        // Get proforma receipts with apartment data for validation
        $receipts = ProfomaReceipt::whereNotNull('apartment_id')
            ->whereNotNull('duration')
            ->with(['apartment' => function ($query) {
                $query->select('id', 'apartment_id', 'amount', 'pricing_type');
            }])
            ->get();

        foreach ($receipts as $receipt) {
            if (!$receipt->apartment) {
                continue;
            }

            $receiptsChecked++;
            
            // Calculate what the amount should be using current service
            $calculationResult = $this->calculationService->calculatePaymentTotal(
                $receipt->apartment->amount,
                $receipt->duration,
                $receipt->apartment->pricing_type
            );

            $expectedAmount = $calculationResult->totalAmount;
            $actualAmount = $receipt->amount;
            $difference = abs($expectedAmount - $actualAmount);

            // Allow for small floating point differences
            if ($difference < 0.01) {
                $consistentCalculations++;
            } else {
                $inconsistencies[] = [
                    'receipt_id' => $receipt->id,
                    'apartment_id' => $receipt->apartment_id,
                    'expected' => $expectedAmount,
                    'actual' => $actualAmount,
                    'difference' => $difference,
                    'pricing_type' => $receipt->apartment->pricing_type,
                    'duration' => $receipt->duration
                ];
            }
        }

        $this->info("Checked {$receiptsChecked} proforma receipts");
        $this->info("Consistent calculations: {$consistentCalculations}");
        $this->info("Inconsistencies found: " . count($inconsistencies));

        if (!empty($inconsistencies)) {
            $this->warn("\n⚠️  Calculation Inconsistencies Found:");
            
            // Show first 10 inconsistencies
            $displayInconsistencies = array_slice($inconsistencies, 0, 10);
            $this->table(
                ['Receipt ID', 'Apartment ID', 'Expected', 'Actual', 'Difference', 'Type', 'Duration'],
                array_map(function ($inc) {
                    return [
                        $inc['receipt_id'],
                        $inc['apartment_id'],
                        number_format($inc['expected'], 2),
                        number_format($inc['actual'], 2),
                        number_format($inc['difference'], 2),
                        $inc['pricing_type'],
                        $inc['duration']
                    ];
                }, $displayInconsistencies)
            );

            if (count($inconsistencies) > 10) {
                $this->info("... and " . (count($inconsistencies) - 10) . " more inconsistencies");
            }
        } else {
            $this->info("✅ All calculations are consistent!");
        }
    }

    /**
     * Attempt to fix found inconsistencies
     */
    private function fixInconsistencies(): void
    {
        $this->info("\n🔧 Fixing Inconsistencies:");
        
        if (!$this->confirm('This will update proforma receipt amounts to match current calculation logic. Continue?')) {
            $this->info('Fix operation cancelled.');
            return;
        }

        $fixed = 0;
        $errors = 0;

        // Get receipts that might need fixing
        $receipts = ProfomaReceipt::whereNotNull('apartment_id')
            ->whereNotNull('duration')
            ->with('apartment')
            ->get();

        foreach ($receipts as $receipt) {
            if (!$receipt->apartment) {
                continue;
            }

            try {
                // Calculate correct amount
                $calculationResult = $this->calculationService->calculatePaymentTotal(
                    $receipt->apartment->amount,
                    $receipt->duration,
                    $receipt->apartment->pricing_type
                );

                $expectedAmount = $calculationResult->totalAmount;
                $actualAmount = $receipt->amount;
                $difference = abs($expectedAmount - $actualAmount);

                // Fix if difference is significant
                if ($difference >= 0.01) {
                    $receipt->update([
                        'amount' => $expectedAmount,
                        'calculation_method' => 'corrected_by_migration',
                        'calculation_log' => array_merge(
                            $receipt->calculation_log ?? [],
                            [
                                'corrected_at' => now()->toISOString(),
                                'old_amount' => $actualAmount,
                                'new_amount' => $expectedAmount,
                                'correction_reason' => 'Fixed calculation inconsistency'
                            ]
                        )
                    ]);
                    
                    $fixed++;
                    $this->info("Fixed receipt {$receipt->id}: {$actualAmount} → {$expectedAmount}");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error fixing receipt {$receipt->id}: " . $e->getMessage());
            }
        }

        $this->info("\n✅ Fix operation completed:");
        $this->info("Records fixed: {$fixed}");
        $this->info("Errors encountered: {$errors}");
    }
}
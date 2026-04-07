<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Apartment;
use App\Models\ProfomaReceipt;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration handles existing data to ensure compatibility with the new
     * payment calculation system while preserving existing calculation results.
     *
     * @return void
     */
    public function up()
    {
        Log::info('Starting payment calculation data migration');
        
        // Step 1: Ensure all apartments have a pricing_type set
        $this->setDefaultPricingTypeForApartments();
        
        // Step 2: Analyze and update existing proforma calculations
        $this->analyzeAndUpdateProformaCalculations();
        
        // Step 3: Validate data integrity
        $this->validateDataIntegrity();
        
        Log::info('Payment calculation data migration completed successfully');
    }

    /**
     * Set default pricing_type for existing apartments that don't have it set
     */
    private function setDefaultPricingTypeForApartments(): void
    {
        Log::info('Setting default pricing_type for apartments');
        
        // Count apartments without pricing_type
        $apartmentsWithoutType = Apartment::whereNull('pricing_type')->count();
        
        if ($apartmentsWithoutType > 0) {
            Log::info("Found {$apartmentsWithoutType} apartments without pricing_type");
            
            // Set default pricing_type to 'total' for backward compatibility
            $updated = Apartment::whereNull('pricing_type')
                ->update([
                    'pricing_type' => 'total',
                    'price_configuration' => null // Ensure clean configuration
                ]);
            
            Log::info("Updated {$updated} apartments with default pricing_type 'total'");
        } else {
            Log::info('All apartments already have pricing_type set');
        }
        
        // Validate that all apartments now have valid pricing_type
        $invalidPricingTypes = Apartment::whereNotIn('pricing_type', ['total', 'monthly'])->count();
        if ($invalidPricingTypes > 0) {
            Log::warning("Found {$invalidPricingTypes} apartments with invalid pricing_type");
            
            // Fix invalid pricing types
            Apartment::whereNotIn('pricing_type', ['total', 'monthly'])
                ->update(['pricing_type' => 'total']);
            
            Log::info("Fixed invalid pricing_type values, set to 'total'");
        }
    }

    /**
     * Analyze existing proforma calculations and update calculation metadata
     */
    private function analyzeAndUpdateProformaCalculations(): void
    {
        Log::info('Analyzing existing proforma calculations');
        
        // Get all proforma receipts that don't have calculation_method set
        $receiptsToUpdate = ProfomaReceipt::whereNull('calculation_method')->get();
        
        Log::info("Found {$receiptsToUpdate->count()} proforma receipts to analyze");
        
        foreach ($receiptsToUpdate as $receipt) {
            $this->analyzeAndUpdateSingleReceipt($receipt);
        }
        
        Log::info('Completed proforma calculation analysis');
    }

    /**
     * Analyze and update a single proforma receipt
     */
    private function analyzeAndUpdateSingleReceipt(ProfomaReceipt $receipt): void
    {
        try {
            // Get the associated apartment if it exists
            // Note: The apartment_id in proforma_receipt actually refers to the apartments.id (primary key)
            // not the apartments.apartment_id field
            $apartment = null;
            if ($receipt->apartment_id) {
                $apartment = Apartment::find($receipt->apartment_id);
            }
            
            // Determine the calculation method used based on the data
            $calculationMethod = $this->determineCalculationMethod($receipt, $apartment);
            
            // Create calculation log for audit purposes
            $calculationLog = $this->createCalculationLog($receipt, $apartment, $calculationMethod);
            
            // Update the receipt with calculation metadata
            // IMPORTANT: We preserve the existing amount - we don't recalculate
            $receipt->update([
                'calculation_method' => $calculationMethod,
                'calculation_log' => $calculationLog
            ]);
            
            Log::debug("Updated receipt {$receipt->id} with calculation_method: {$calculationMethod}");
            
        } catch (\Exception $e) {
            Log::error("Failed to analyze receipt {$receipt->id}: " . $e->getMessage());
            
            // Set a fallback calculation method for problematic records
            $receipt->update([
                'calculation_method' => 'legacy_unknown',
                'calculation_log' => [
                    'method' => 'legacy_unknown',
                    'error' => $e->getMessage(),
                    'migrated_at' => now()->toISOString(),
                    'preserved_amount' => $receipt->amount
                ]
            ]);
        }
    }

    /**
     * Determine what calculation method was likely used for an existing receipt
     */
    private function determineCalculationMethod(ProfomaReceipt $receipt, ?Apartment $apartment): string
    {
        // If no apartment is found, we can't determine the method
        if (!$apartment) {
            return 'legacy_no_apartment';
        }
        
        // If duration is available, try to reverse-engineer the calculation
        if ($receipt->duration && $receipt->duration > 0) {
            $apartmentAmount = $apartment->amount;
            $receiptAmount = $receipt->amount;
            
            // Check if the receipt amount matches apartment amount (total pricing)
            if (abs($receiptAmount - $apartmentAmount) < 0.01) {
                return 'legacy_total_pricing';
            }
            
            // Check if the receipt amount matches apartment amount * duration (monthly pricing)
            $monthlyCalculation = $apartmentAmount * $receipt->duration;
            if (abs($receiptAmount - $monthlyCalculation) < 0.01) {
                return 'legacy_monthly_pricing';
            }
            
            // If neither matches exactly, it might be a custom calculation
            return 'legacy_custom_calculation';
        }
        
        // If no duration, assume it was total pricing
        return 'legacy_total_pricing';
    }

    /**
     * Create a calculation log for audit purposes
     */
    private function createCalculationLog(ProfomaReceipt $receipt, ?Apartment $apartment, string $method): array
    {
        $log = [
            'method' => $method,
            'migrated_at' => now()->toISOString(),
            'preserved_amount' => $receipt->amount,
            'migration_note' => 'Amount preserved from legacy calculation'
        ];
        
        if ($apartment) {
            $log['apartment_data'] = [
                'apartment_id' => $apartment->apartment_id,
                'apartment_amount' => $apartment->amount,
                'pricing_type' => $apartment->pricing_type
            ];
        }
        
        if ($receipt->duration) {
            $log['duration'] = $receipt->duration;
            
            if ($apartment) {
                $log['calculation_analysis'] = [
                    'total_pricing_would_be' => $apartment->amount,
                    'monthly_pricing_would_be' => $apartment->amount * $receipt->duration,
                    'actual_amount' => $receipt->amount
                ];
            }
        }
        
        return $log;
    }

    /**
     * Validate data integrity after migration
     */
    private function validateDataIntegrity(): void
    {
        Log::info('Validating data integrity after migration');
        
        // Check that all apartments have valid pricing_type
        $invalidApartments = Apartment::whereNotIn('pricing_type', ['total', 'monthly'])->count();
        if ($invalidApartments > 0) {
            throw new \Exception("Data integrity check failed: {$invalidApartments} apartments have invalid pricing_type");
        }
        
        // Check that all apartments have non-negative amounts
        $negativeAmounts = Apartment::where('amount', '<', 0)->count();
        if ($negativeAmounts > 0) {
            Log::warning("Found {$negativeAmounts} apartments with negative amounts");
        }
        
        // Check that all proforma receipts now have calculation_method
        $receiptsWithoutMethod = ProfomaReceipt::whereNull('calculation_method')->count();
        if ($receiptsWithoutMethod > 0) {
            throw new \Exception("Data integrity check failed: {$receiptsWithoutMethod} receipts still missing calculation_method");
        }
        
        // Validate that calculation_log is valid JSON for all receipts
        $receiptsWithInvalidLog = ProfomaReceipt::whereNotNull('calculation_log')
            ->get()
            ->filter(function ($receipt) {
                return !is_array($receipt->calculation_log);
            })
            ->count();
            
        if ($receiptsWithInvalidLog > 0) {
            throw new \Exception("Data integrity check failed: {$receiptsWithInvalidLog} receipts have invalid calculation_log");
        }
        
        // Log summary statistics
        $totalApartments = Apartment::count();
        $totalReceipts = ProfomaReceipt::count();
        $totalPricingApartments = Apartment::where('pricing_type', 'total')->count();
        $monthlyPricingApartments = Apartment::where('pricing_type', 'monthly')->count();
        
        Log::info("Data integrity validation passed:");
        Log::info("- Total apartments: {$totalApartments}");
        Log::info("- Apartments with 'total' pricing: {$totalPricingApartments}");
        Log::info("- Apartments with 'monthly' pricing: {$monthlyPricingApartments}");
        Log::info("- Total proforma receipts: {$totalReceipts}");
        
        // Log calculation method distribution
        $methodDistribution = ProfomaReceipt::select('calculation_method', DB::raw('count(*) as count'))
            ->groupBy('calculation_method')
            ->get()
            ->pluck('count', 'calculation_method')
            ->toArray();
            
        Log::info("Calculation method distribution: " . json_encode($methodDistribution));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Log::info('Reversing payment calculation data migration');
        
        // Clear calculation metadata from proforma receipts
        // Note: We don't restore pricing_type to null as that would break the system
        ProfomaReceipt::whereNotNull('calculation_method')
            ->update([
                'calculation_method' => null,
                'calculation_log' => null
            ]);
        
        Log::info('Payment calculation data migration reversed');
    }
};
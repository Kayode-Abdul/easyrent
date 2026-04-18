<?php

namespace App\Services\Payment;

use App\Models\Apartment;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class EnhancedRentalCalculationService
{
    // Supported rental duration types
    const DURATION_TYPE_HOURLY = 'hourly';
    const DURATION_TYPE_DAILY = 'daily';
    const DURATION_TYPE_WEEKLY = 'weekly';
    const DURATION_TYPE_MONTHLY = 'monthly';
    const DURATION_TYPE_QUARTERLY = 'quarterly';
    const DURATION_TYPE_SEMI_ANNUALLY = 'semi_annually';
    const DURATION_TYPE_YEARLY = 'yearly';
    const DURATION_TYPE_ANNUALLY = 'annually';
    const DURATION_TYPE_BI_ANNUALLY = 'bi_annually';

    // Duration multipliers (in months for standardization)
    const DURATION_MULTIPLIERS = [
        self::DURATION_TYPE_HOURLY => 1/730, // Approximate hours in a month
        self::DURATION_TYPE_DAILY => 1/30,   // Approximate days in a month
        self::DURATION_TYPE_WEEKLY => 1/4.33, // Approximate weeks in a month
        self::DURATION_TYPE_MONTHLY => 1,
        self::DURATION_TYPE_QUARTERLY => 3,
        self::DURATION_TYPE_SEMI_ANNUALLY => 6,
        self::DURATION_TYPE_YEARLY => 12,
        self::DURATION_TYPE_ANNUALLY => 12,
        self::DURATION_TYPE_BI_ANNUALLY => 24,
    ];

    /**
     * Calculate rental cost based on apartment, duration type, and quantity
     */
    public function calculateRentalCost(
        Apartment $apartment, 
        string $durationType, 
        int $quantity
    ): PaymentCalculationResult {
        $calculationId = uniqid('rental_calc_');
        $startTime = microtime(true);
        
        try {
            // Validate inputs
            $validation = $this->validateInputs($apartment, $durationType, $quantity);
            if (!$validation['valid']) {
                return PaymentCalculationResult::failure($validation['error']);
            }

            // Normalize duration type (handle aliases)
            $normalizedType = $this->normalizeDurationType($durationType);
            
            // Calculate the rental cost
            $result = $this->performRentalCalculation($apartment, $normalizedType, $quantity, $calculationId);
            
            // Log the calculation
            $executionTime = (microtime(true) - $startTime) * 1000;
            $this->logRentalCalculation($calculationId, $apartment, $durationType, $quantity, $result, $executionTime);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Enhanced rental calculation failed', [
                'calculation_id' => $calculationId,
                'apartment_id' => $apartment->apartment_id,
                'duration_type' => $durationType,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return PaymentCalculationResult::failure('Rental calculation failed: ' . $e->getMessage());
        }
    }

    /**
     * Calculate rental cost with automatic rate conversion
     */
    public function calculateWithConversion(
        Apartment $apartment,
        string $durationType,
        int $quantity
    ): PaymentCalculationResult {
        $normalizedType = $this->normalizeDurationType($durationType);
        
        // Try direct rate first
        $directRate = $this->getDirectRate($apartment, $normalizedType);
        if ($directRate !== null) {
            $totalAmount = $directRate * $quantity;
            
            return PaymentCalculationResult::success(
                $totalAmount,
                "direct_{$normalizedType}_rate",
                [
                    [
                        'step' => 'direct_rate_calculation',
                        'duration_type' => $normalizedType,
                        'rate' => $directRate,
                        'quantity' => $quantity,
                        'total' => $totalAmount,
                        'method' => 'direct_rate'
                    ]
                ]
            );
        }
        
        // Try conversion from other rates
        return $this->calculateWithRateConversion($apartment, $normalizedType, $quantity);
    }

    /**
     * Get all available rental options for an apartment
     */
    public function getAvailableRentalOptions(Apartment $apartment): array
    {
        $options = [];
        
        foreach (self::DURATION_MULTIPLIERS as $type => $multiplier) {
            $rate = $this->getDirectRate($apartment, $type);
            if ($rate !== null) {
                $options[$type] = [
                    'type' => $type,
                    'rate' => $rate,
                    'formatted_rate' => format_money($rate, $apartment->currency),
                    'period' => $this->getPeriodLabel($type),
                    'available' => true
                ];
            } else {
                // Check if we can convert from other rates
                $convertedRate = $this->getConvertedRate($apartment, $type);
                if ($convertedRate !== null) {
                    $options[$type] = [
                        'type' => $type,
                        'rate' => $convertedRate,
                        'formatted_rate' => format_money($convertedRate, $apartment->currency),
                        'period' => $this->getPeriodLabel($type),
                        'available' => true,
                        'converted' => true
                    ];
                }
            }
        }
        
        return $options;
    }

    /**
     * Validate calculation inputs
     */
    protected function validateInputs(Apartment $apartment, string $durationType, int $quantity): array
    {
        if ($quantity <= 0) {
            return ['valid' => false, 'error' => 'Quantity must be greater than zero'];
        }
        
        if ($quantity > 999) {
            return ['valid' => false, 'error' => 'Quantity too large (maximum 999)'];
        }
        
        $normalizedType = $this->normalizeDurationType($durationType);
        if (!array_key_exists($normalizedType, self::DURATION_MULTIPLIERS)) {
            return ['valid' => false, 'error' => "Unsupported duration type: {$durationType}"];
        }
        
        return ['valid' => true, 'error' => null];
    }

    /**
     * Normalize duration type (handle aliases)
     */
    protected function normalizeDurationType(string $durationType): string
    {
        $aliases = [
            'annually' => 'yearly',
            'annual' => 'yearly',
            'bi_annual' => 'bi_annually',
            'biannual' => 'bi_annually',
            'semi_annual' => 'semi_annually',
            'semiannual' => 'semi_annually',
            'quarter' => 'quarterly',
        ];
        
        return $aliases[strtolower($durationType)] ?? strtolower($durationType);
    }

    /**
     * Perform the actual rental calculation
     */
    protected function performRentalCalculation(
        Apartment $apartment,
        string $durationType,
        int $quantity,
        string $calculationId
    ): PaymentCalculationResult {
        $calculationSteps = [];
        
        // Try direct rate first
        $directRate = $this->getDirectRate($apartment, $durationType);
        
        if ($directRate !== null) {
            $totalAmount = $directRate * $quantity;
            
            $calculationSteps[] = [
                'step' => 'direct_rate_calculation',
                'calculation_id' => $calculationId,
                'duration_type' => $durationType,
                'direct_rate' => $directRate,
                'quantity' => $quantity,
                'total_amount' => $totalAmount,
                'method' => 'direct_rate'
            ];
            
            return PaymentCalculationResult::success(
                $totalAmount,
                "direct_{$durationType}_calculation",
                $calculationSteps
            );
        }
        
        // Try conversion calculation
        return $this->calculateWithRateConversion($apartment, $durationType, $quantity, $calculationSteps);
    }

    /**
     * Get direct rate for a duration type
     */
    protected function getDirectRate(Apartment $apartment, string $durationType): ?float
    {
        switch ($durationType) {
            case self::DURATION_TYPE_HOURLY:
                return $apartment->hourly_rate;
            case self::DURATION_TYPE_DAILY:
                return $apartment->daily_rate;
            case self::DURATION_TYPE_WEEKLY:
                return $apartment->weekly_rate;
            case self::DURATION_TYPE_MONTHLY:
                return $apartment->monthly_rate ?? $apartment->amount;
            case self::DURATION_TYPE_YEARLY:
            case self::DURATION_TYPE_ANNUALLY:
                return $apartment->yearly_rate;
            default:
                return null;
        }
    }

    /**
     * Calculate with rate conversion from available rates
     */
    protected function calculateWithRateConversion(
        Apartment $apartment,
        string $durationType,
        int $quantity,
        array $calculationSteps = []
    ): PaymentCalculationResult {
        // Try to convert from monthly rate (most common)
        if ($apartment->monthly_rate || $apartment->amount) {
            $monthlyRate = $apartment->monthly_rate ?? $apartment->amount;
            $multiplier = self::DURATION_MULTIPLIERS[$durationType] ?? null;
            
            if ($multiplier !== null) {
                switch ($durationType) {
                    case self::DURATION_TYPE_QUARTERLY:
                        $totalAmount = $monthlyRate * ($quantity * 3);
                        $method = "monthly_rate_quarterly_conversion";
                        break;
                        
                    case self::DURATION_TYPE_SEMI_ANNUALLY:
                        $totalAmount = $monthlyRate * ($quantity * 6);
                        $method = "monthly_rate_semi_annual_conversion";
                        break;
                        
                    case self::DURATION_TYPE_YEARLY:
                    case self::DURATION_TYPE_ANNUALLY:
                        $totalAmount = $monthlyRate * ($quantity * 12);
                        $method = "monthly_rate_yearly_conversion";
                        break;
                        
                    case self::DURATION_TYPE_BI_ANNUALLY:
                        $totalAmount = $monthlyRate * ($quantity * 24);
                        $method = "monthly_rate_bi_annual_conversion";
                        break;
                        
                    default:
                        $convertedRate = $monthlyRate * $multiplier;
                        $totalAmount = $convertedRate * $quantity;
                        $method = "monthly_rate_conversion";
                }
                
                $calculationSteps[] = [
                    'step' => 'rate_conversion_calculation',
                    'duration_type' => $durationType,
                    'source_rate' => $monthlyRate,
                    'source_type' => 'monthly',
                    'multiplier' => $multiplier,
                    'quantity' => $quantity,
                    'total_amount' => $totalAmount,
                    'method' => $method
                ];
                
                return PaymentCalculationResult::success(
                    $totalAmount,
                    $method,
                    $calculationSteps
                );
            }
        }
        
        return PaymentCalculationResult::failure("No rate available for {$durationType} and conversion failed");
    }

    /**
     * Get converted rate for a duration type
     */
    protected function getConvertedRate(Apartment $apartment, string $durationType): ?float
    {
        $monthlyRate = $apartment->monthly_rate ?? $apartment->amount;
        if (!$monthlyRate) {
            return null;
        }
        
        $multiplier = self::DURATION_MULTIPLIERS[$durationType] ?? null;
        if ($multiplier === null) {
            return null;
        }
        
        return $monthlyRate * $multiplier;
    }

    /**
     * Get period label for duration type
     */
    protected function getPeriodLabel(string $durationType): string
    {
        $labels = [
            self::DURATION_TYPE_HOURLY => 'per hour',
            self::DURATION_TYPE_DAILY => 'per day',
            self::DURATION_TYPE_WEEKLY => 'per week',
            self::DURATION_TYPE_MONTHLY => 'per month',
            self::DURATION_TYPE_QUARTERLY => 'per quarter',
            self::DURATION_TYPE_SEMI_ANNUALLY => 'per 6 months',
            self::DURATION_TYPE_YEARLY => 'per year',
            self::DURATION_TYPE_ANNUALLY => 'per year',
            self::DURATION_TYPE_BI_ANNUALLY => 'per 2 years',
        ];
        
        return $labels[$durationType] ?? 'per period';
    }

    /**
     * Log rental calculation for audit purposes
     */
    protected function logRentalCalculation(
        string $calculationId,
        Apartment $apartment,
        string $durationType,
        int $quantity,
        PaymentCalculationResult $result,
        float $executionTime
    ): void {
        Log::info('Enhanced rental calculation completed', [
            'calculation_id' => $calculationId,
            'apartment_id' => $apartment->apartment_id,
            'duration_type' => $durationType,
            'quantity' => $quantity,
            'result_valid' => $result->isValid,
            'total_amount' => $result->isValid ? $result->totalAmount : null,
            'calculation_method' => $result->isValid ? $result->calculationMethod : null,
            'error_message' => $result->isValid ? null : $result->errorMessage,
            'execution_time_ms' => $executionTime,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Integrate with existing PaymentCalculationService
     */
    public function integrateWithPaymentService(
        PaymentCalculationServiceInterface $paymentService,
        Apartment $apartment,
        string $durationType,
        int $quantity
    ): PaymentCalculationResult {
        // For backward compatibility, convert to monthly equivalent
        $rentalResult = $this->calculateRentalCost($apartment, $durationType, $quantity);
        
        if (!$rentalResult->isValid) {
            return $rentalResult;
        }
        
        // Use the calculated amount as a "total" pricing type
        return $paymentService->calculatePaymentTotal(
            $rentalResult->totalAmount,
            1, // Always 1 since we've already calculated the total
            'total' // Use total pricing to avoid multiplication
        );
    }
}
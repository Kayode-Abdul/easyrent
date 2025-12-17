<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use ArithmeticError;
use DivisionByZeroError;
use OverflowException;
use App\Services\Security\PaymentCalculationSecurityService;
use App\Services\Monitoring\PaymentCalculationMonitoringService;
use App\Services\Audit\PaymentCalculationAuditLogger;
use App\Services\Cache\PaymentCalculationCacheService;

class PaymentCalculationService implements PaymentCalculationServiceInterface
{
    protected $securityService;
    protected $monitoringService;
    protected $auditLogger;
    protected $cacheService;

    public function __construct(
        PaymentCalculationSecurityService $securityService,
        PaymentCalculationMonitoringService $monitoringService,
        PaymentCalculationAuditLogger $auditLogger,
        PaymentCalculationCacheService $cacheService
    ) {
        $this->securityService = $securityService;
        $this->monitoringService = $monitoringService;
        $this->auditLogger = $auditLogger;
        $this->cacheService = $cacheService;
    }
    // Supported pricing types
    const PRICING_TYPE_TOTAL = 'total';
    const PRICING_TYPE_MONTHLY = 'monthly';

    // Validation constants
    const MAX_RENTAL_DURATION = 120; // 10 years maximum
    const MAX_APARTMENT_PRICE = 999999999.99; // Maximum price to prevent overflow
    const MIN_APARTMENT_PRICE = 0.01; // Minimum non-zero price
    
    // Error handling constants
    const MAX_CALCULATION_RESULT = 9999999999.99; // Maximum final calculation result
    const PRECISION_DECIMAL_PLACES = 2; // Currency precision
    const DEFAULT_FALLBACK_PRICING_TYPE = self::PRICING_TYPE_TOTAL;
    
    // Monitoring thresholds
    const HIGH_VALUE_THRESHOLD = 1000000.00; // Log high-value calculations
    const SUSPICIOUS_DURATION_THRESHOLD = 60; // Log unusually long durations

    /**
     * Calculate payment total with security validation
     */
    public function calculatePaymentTotalSecure(array $inputs): PaymentCalculationResult
    {
        // Sanitize and validate inputs using security service
        $validationResult = $this->securityService->sanitizeCalculationInputs($inputs);
        
        if (!$validationResult['is_valid']) {
            $errorMessage = 'Input validation failed: ' . implode(', ', $validationResult['validation_errors']);
            return PaymentCalculationResult::failure($errorMessage);
        }
        
        if (!empty($validationResult['security_issues'])) {
            $errorMessage = 'Security validation failed: ' . implode(', ', $validationResult['security_issues']);
            return PaymentCalculationResult::failure($errorMessage);
        }
        
        $sanitizedInputs = $validationResult['sanitized_inputs'];
        
        // Extract sanitized values
        $apartmentPrice = $sanitizedInputs['apartment_price'] ?? 0.0;
        $rentalDuration = $sanitizedInputs['rental_duration'] ?? 1;
        $pricingType = $sanitizedInputs['pricing_type'] ?? self::PRICING_TYPE_TOTAL;
        
        // Call the standard calculation method with sanitized inputs
        return $this->calculatePaymentTotal($apartmentPrice, $rentalDuration, $pricingType);
    }

    /**
     * Calculate payment total based on apartment price, rental duration, and pricing type
     */
    public function calculatePaymentTotal(
        float $apartmentPrice, 
        int $rentalDuration, 
        string $pricingType = self::PRICING_TYPE_TOTAL
    ): PaymentCalculationResult {
        $calculationId = uniqid('calc_');
        $startTime = microtime(true);
        
        $inputs = [
            'apartment_price' => $apartmentPrice,
            'rental_duration' => $rentalDuration,
            'pricing_type' => $pricingType
        ];
        
        // Check cache first for performance optimization
        $cachedResult = $this->cacheService->getCachedCalculationResult(
            $apartmentPrice, 
            $rentalDuration, 
            $pricingType
        );
        
        if ($cachedResult !== null) {
            // Record cache hit metrics
            $executionTime = (microtime(true) - $startTime) * 1000;
            $this->monitoringService->recordCalculationPerformance($calculationId, $executionTime, $cachedResult, $inputs);
            
            Log::debug('Payment calculation served from cache', [
                'calculation_id' => $calculationId,
                'apartment_price' => $apartmentPrice,
                'rental_duration' => $rentalDuration,
                'pricing_type' => $pricingType,
                'cache_hit' => true,
                'execution_time_ms' => $executionTime
            ]);
            
            return $cachedResult;
        }
        
        try {
            // Comprehensive input validation with detailed error messages
            $validationResult = $this->validateInputsComprehensive($apartmentPrice, $rentalDuration, $pricingType);
            if (!$validationResult['valid']) {
                $this->logCalculationError($calculationId, 'input_validation_failed', $validationResult['error'], [
                    'apartment_price' => $apartmentPrice,
                    'rental_duration' => $rentalDuration,
                    'pricing_type' => $pricingType
                ]);
                return PaymentCalculationResult::failure($validationResult['error']);
            }

            // Apply fallback logic for ambiguous configurations
            $normalizedPricingType = $this->applyFallbackLogic($pricingType, $apartmentPrice, $rentalDuration);
            if ($normalizedPricingType !== $pricingType) {
                $this->logFallbackUsage($calculationId, $pricingType, $normalizedPricingType, $apartmentPrice, $rentalDuration);
            }

            // Initialize calculation tracking
            $calculationSteps = [];
            $calculationMethod = $this->determineCalculationMethod($normalizedPricingType);
            
            $calculationSteps[] = [
                'step' => 'input_validation',
                'calculation_id' => $calculationId,
                'apartment_price' => $apartmentPrice,
                'rental_duration' => $rentalDuration,
                'original_pricing_type' => $pricingType,
                'normalized_pricing_type' => $normalizedPricingType,
                'fallback_applied' => $normalizedPricingType !== $pricingType,
                'timestamp' => now()->toISOString()
            ];

            // Perform calculation with overflow protection
            $totalAmount = $this->performCalculationWithProtection(
                $apartmentPrice, 
                $rentalDuration, 
                $normalizedPricingType, 
                $calculationSteps,
                $calculationId
            );

            // Apply precision handling
            $totalAmount = $this->applyPrecisionHandling($totalAmount);

            // Final overflow and bounds validation
            $boundsValidation = $this->validateCalculationBounds($totalAmount);
            if (!$boundsValidation['valid']) {
                $this->logCalculationError($calculationId, 'bounds_validation_failed', $boundsValidation['error'], [
                    'calculated_amount' => $totalAmount,
                    'apartment_price' => $apartmentPrice,
                    'rental_duration' => $rentalDuration,
                    'pricing_type' => $normalizedPricingType
                ]);
                return PaymentCalculationResult::failure($boundsValidation['error']);
            }

            $calculationSteps[] = [
                'step' => 'final_result',
                'total_amount' => $totalAmount,
                'calculation_method' => $calculationMethod,
                'precision_applied' => true,
                'bounds_validated' => true,
                'timestamp' => now()->toISOString()
            ];

            // Log monitoring alerts for high-value or suspicious calculations
            $this->performMonitoringChecks($calculationId, $totalAmount, $apartmentPrice, $rentalDuration, $normalizedPricingType);

            $result = PaymentCalculationResult::success(
                $totalAmount,
                $calculationMethod,
                $calculationSteps
            );

            // Cache the successful result for future use
            $this->cacheService->cacheCalculationResult($apartmentPrice, $rentalDuration, $normalizedPricingType, $result);

            // Record performance and accuracy metrics
            $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            $this->monitoringService->recordCalculationPerformance($calculationId, $executionTime, $result, $inputs);
            $this->monitoringService->recordCalculationAccuracy($calculationId, $result, $inputs);

            // Log comprehensive audit trail
            $this->auditLogger->logCalculationAudit($calculationId, $inputs, $result, [
                'execution_time_ms' => $executionTime,
                'method' => __METHOD__,
                'cache_hit' => false
            ]);

            return $result;

        } catch (ArithmeticError $e) {
            $this->logCalculationError($calculationId, 'arithmetic_error', $e->getMessage(), [
                'apartment_price' => $apartmentPrice,
                'rental_duration' => $rentalDuration,
                'pricing_type' => $pricingType,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->monitoringService->recordCalculationError($calculationId, 'arithmetic_error', $e->getMessage(), $inputs);
            return PaymentCalculationResult::failure('Arithmetic calculation error: Invalid mathematical operation');
            
        } catch (DivisionByZeroError $e) {
            $this->logCalculationError($calculationId, 'division_by_zero', $e->getMessage(), [
                'apartment_price' => $apartmentPrice,
                'rental_duration' => $rentalDuration,
                'pricing_type' => $pricingType
            ]);
            
            $this->monitoringService->recordCalculationError($calculationId, 'division_by_zero', $e->getMessage(), $inputs);
            return PaymentCalculationResult::failure('Division by zero error in calculation');
            
        } catch (OverflowException $e) {
            $this->logCalculationError($calculationId, 'overflow_exception', $e->getMessage(), [
                'apartment_price' => $apartmentPrice,
                'rental_duration' => $rentalDuration,
                'pricing_type' => $pricingType
            ]);
            
            $this->monitoringService->recordCalculationError($calculationId, 'overflow_exception', $e->getMessage(), $inputs);
            return PaymentCalculationResult::failure('Calculation result exceeds system limits');
            
        } catch (InvalidArgumentException $e) {
            $this->logCalculationError($calculationId, 'invalid_argument', $e->getMessage(), [
                'apartment_price' => $apartmentPrice,
                'rental_duration' => $rentalDuration,
                'pricing_type' => $pricingType
            ]);
            
            $this->monitoringService->recordCalculationError($calculationId, 'invalid_argument', $e->getMessage(), $inputs);
            return PaymentCalculationResult::failure('Invalid calculation parameters: ' . $e->getMessage());
            
        } catch (\Exception $e) {
            $this->logCalculationError($calculationId, 'unexpected_error', $e->getMessage(), [
                'apartment_price' => $apartmentPrice,
                'rental_duration' => $rentalDuration,
                'pricing_type' => $pricingType,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->monitoringService->recordCalculationError($calculationId, 'unexpected_error', $e->getMessage(), $inputs);
            return PaymentCalculationResult::failure('Unexpected calculation error occurred');
        }
    }

    /**
     * Validate pricing configuration data
     */
    public function validatePricingConfiguration(array $config): bool
    {
        try {
            // Check required fields
            if (!isset($config['pricing_type'])) {
                return false;
            }

            // Validate pricing type
            if (!in_array($config['pricing_type'], [self::PRICING_TYPE_TOTAL, self::PRICING_TYPE_MONTHLY])) {
                return false;
            }

            // Validate optional configuration fields
            if (isset($config['base_price']) && (!is_numeric($config['base_price']) || $config['base_price'] < 0)) {
                return false;
            }

            if (isset($config['duration_multiplier']) && (!is_numeric($config['duration_multiplier']) || $config['duration_multiplier'] <= 0)) {
                return false;
            }

            // Validate complex pricing rules if present
            if (isset($config['pricing_rules']) && !is_array($config['pricing_rules'])) {
                return false;
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Pricing configuration validation failed', [
                'config' => $config,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Log calculation steps for audit purposes
     */
    public function logCalculationSteps(PaymentCalculationResult $result): void
    {
        try {
            Log::info('Payment calculation completed', [
                'calculation_id' => uniqid('calc_'),
                'total_amount' => $result->totalAmount,
                'calculation_method' => $result->calculationMethod,
                'is_valid' => $result->isValid,
                'error_message' => $result->errorMessage,
                'steps_count' => count($result->calculationSteps),
                'calculation_steps' => $result->calculationSteps,
                'timestamp' => now()->toISOString()
            ]);

            // Log error details if calculation failed
            if (!$result->isValid) {
                Log::warning('Payment calculation failed with error', [
                    'error_message' => $result->errorMessage,
                    'calculation_steps' => $result->calculationSteps
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to log calculation steps', [
                'error' => $e->getMessage(),
                'result_summary' => $result->getCalculationSummary()
            ]);
        }
    }

    /**
     * Comprehensive input validation with detailed error messages
     */
    protected function validateInputsComprehensive(float $apartmentPrice, int $rentalDuration, string $pricingType): array
    {
        // Validate apartment price with detailed checks
        if (!is_finite($apartmentPrice)) {
            return ['valid' => false, 'error' => 'Apartment price must be a finite number (not infinity or NaN)'];
        }

        if ($apartmentPrice < 0) {
            return ['valid' => false, 'error' => 'Apartment price cannot be negative. Received: ' . $apartmentPrice];
        }

        if ($apartmentPrice > self::MAX_APARTMENT_PRICE) {
            return ['valid' => false, 'error' => 'Apartment price exceeds maximum allowed value of ' . number_format(self::MAX_APARTMENT_PRICE, 2) . '. Received: ' . number_format($apartmentPrice, 2)];
        }

        // Allow zero price for special cases (free apartments, promotional offers)
        if ($apartmentPrice > 0 && $apartmentPrice < self::MIN_APARTMENT_PRICE) {
            return ['valid' => false, 'error' => 'Apartment price is below minimum allowed value of ' . self::MIN_APARTMENT_PRICE . '. Received: ' . $apartmentPrice];
        }

        // Validate rental duration with detailed checks
        if (!is_int($rentalDuration)) {
            return ['valid' => false, 'error' => 'Rental duration must be an integer. Received type: ' . gettype($rentalDuration)];
        }

        if ($rentalDuration <= 0) {
            return ['valid' => false, 'error' => 'Rental duration must be positive. Received: ' . $rentalDuration];
        }

        if ($rentalDuration > self::MAX_RENTAL_DURATION) {
            return ['valid' => false, 'error' => 'Rental duration exceeds maximum allowed value of ' . self::MAX_RENTAL_DURATION . ' months. Received: ' . $rentalDuration];
        }

        // Validate pricing type with detailed checks
        if (!is_string($pricingType)) {
            return ['valid' => false, 'error' => 'Pricing type must be a string. Received type: ' . gettype($pricingType)];
        }

        $pricingType = trim(strtolower($pricingType));
        if (!in_array($pricingType, [self::PRICING_TYPE_TOTAL, self::PRICING_TYPE_MONTHLY])) {
            return ['valid' => false, 'error' => 'Invalid pricing type "' . $pricingType . '". Must be "total" or "monthly"'];
        }

        // Cross-validation checks
        if ($pricingType === self::PRICING_TYPE_MONTHLY && $apartmentPrice > 0) {
            $projectedTotal = $apartmentPrice * $rentalDuration;
            if ($projectedTotal > self::MAX_CALCULATION_RESULT) {
                return ['valid' => false, 'error' => 'Monthly pricing calculation would exceed maximum result limit. Projected total: ' . number_format($projectedTotal, 2)];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Apply fallback logic for ambiguous configurations
     */
    protected function applyFallbackLogic(string $pricingType, float $apartmentPrice, int $rentalDuration): string
    {
        // Normalize pricing type
        $normalizedType = trim(strtolower($pricingType));
        
        // If pricing type is invalid, apply fallback
        if (!in_array($normalizedType, [self::PRICING_TYPE_TOTAL, self::PRICING_TYPE_MONTHLY])) {
            return self::DEFAULT_FALLBACK_PRICING_TYPE;
        }

        // Apply intelligent fallback for suspicious configurations
        if ($normalizedType === self::PRICING_TYPE_MONTHLY) {
            // If monthly price seems too high for a monthly rate, suggest total pricing
            $projectedTotal = $apartmentPrice * $rentalDuration;
            if ($apartmentPrice > 100000 && $projectedTotal > self::HIGH_VALUE_THRESHOLD * 10) {
                // This looks like a total price being treated as monthly
                return self::PRICING_TYPE_TOTAL;
            }
        }

        return $normalizedType;
    }

    /**
     * Perform calculation with overflow protection
     */
    protected function performCalculationWithProtection(
        float $apartmentPrice, 
        int $rentalDuration, 
        string $pricingType, 
        array &$calculationSteps,
        string $calculationId
    ): float {
        switch ($pricingType) {
            case self::PRICING_TYPE_TOTAL:
                $calculationSteps[] = [
                    'step' => 'total_pricing_calculation',
                    'method' => 'apartment_price_as_total',
                    'apartment_price' => $apartmentPrice,
                    'rental_duration' => $rentalDuration,
                    'note' => 'Using apartment price as total amount without multiplication'
                ];
                return $apartmentPrice;

            case self::PRICING_TYPE_MONTHLY:
                // Pre-calculation overflow check
                if ($apartmentPrice > 0 && $rentalDuration > (self::MAX_CALCULATION_RESULT / $apartmentPrice)) {
                    throw new OverflowException('Monthly calculation would cause overflow: ' . $apartmentPrice . ' * ' . $rentalDuration);
                }

                $totalAmount = $apartmentPrice * $rentalDuration;
                
                // Post-calculation validation
                if (!is_finite($totalAmount)) {
                    throw new ArithmeticError('Monthly calculation resulted in non-finite number');
                }

                $calculationSteps[] = [
                    'step' => 'monthly_pricing_calculation',
                    'method' => 'apartment_price_times_duration',
                    'apartment_price' => $apartmentPrice,
                    'rental_duration' => $rentalDuration,
                    'multiplication_result' => $totalAmount,
                    'overflow_check_passed' => true,
                    'note' => 'Multiplying monthly price by rental duration with overflow protection'
                ];
                return $totalAmount;

            default:
                throw new InvalidArgumentException('Unsupported pricing type after normalization: ' . $pricingType);
        }
    }

    /**
     * Apply precision handling for currency calculations
     */
    protected function applyPrecisionHandling(float $amount): float
    {
        // Round to specified decimal places for currency precision
        return round($amount, self::PRECISION_DECIMAL_PLACES);
    }

    /**
     * Validate calculation bounds
     */
    protected function validateCalculationBounds(float $amount): array
    {
        if (!is_finite($amount)) {
            return ['valid' => false, 'error' => 'Calculation resulted in non-finite number'];
        }

        if ($amount < 0) {
            return ['valid' => false, 'error' => 'Calculation resulted in negative amount: ' . $amount];
        }

        if ($amount > self::MAX_CALCULATION_RESULT) {
            return ['valid' => false, 'error' => 'Calculation result exceeds maximum allowed value of ' . number_format(self::MAX_CALCULATION_RESULT, 2) . '. Result: ' . number_format($amount, 2)];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Log calculation errors with comprehensive context
     */
    protected function logCalculationError(string $calculationId, string $errorType, string $errorMessage, array $context): void
    {
        Log::error('Payment calculation error', [
            'calculation_id' => $calculationId,
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'context' => $context,
            'timestamp' => now()->toISOString(),
            'service' => 'PaymentCalculationService'
        ]);

        // Also log to a specific error channel for monitoring
        Log::channel('payment_errors')->error('Calculation error', [
            'id' => $calculationId,
            'type' => $errorType,
            'message' => $errorMessage,
            'data' => $context
        ]);
    }

    /**
     * Log fallback usage for monitoring
     */
    protected function logFallbackUsage(string $calculationId, string $originalType, string $fallbackType, float $price, int $duration): void
    {
        Log::warning('Payment calculation fallback applied', [
            'calculation_id' => $calculationId,
            'original_pricing_type' => $originalType,
            'fallback_pricing_type' => $fallbackType,
            'apartment_price' => $price,
            'rental_duration' => $duration,
            'timestamp' => now()->toISOString(),
            'service' => 'PaymentCalculationService'
        ]);
    }

    /**
     * Perform monitoring checks for suspicious calculations
     */
    protected function performMonitoringChecks(string $calculationId, float $totalAmount, float $apartmentPrice, int $rentalDuration, string $pricingType): void
    {
        // Log high-value calculations
        if ($totalAmount > self::HIGH_VALUE_THRESHOLD) {
            Log::info('High-value payment calculation', [
                'calculation_id' => $calculationId,
                'total_amount' => $totalAmount,
                'apartment_price' => $apartmentPrice,
                'rental_duration' => $rentalDuration,
                'pricing_type' => $pricingType,
                'threshold' => self::HIGH_VALUE_THRESHOLD,
                'timestamp' => now()->toISOString()
            ]);
        }

        // Log unusually long rental durations
        if ($rentalDuration > self::SUSPICIOUS_DURATION_THRESHOLD) {
            Log::info('Long-duration rental calculation', [
                'calculation_id' => $calculationId,
                'rental_duration' => $rentalDuration,
                'apartment_price' => $apartmentPrice,
                'total_amount' => $totalAmount,
                'pricing_type' => $pricingType,
                'threshold' => self::SUSPICIOUS_DURATION_THRESHOLD,
                'timestamp' => now()->toISOString()
            ]);
        }

        // Log zero-price calculations (might be promotional or error)
        if ($apartmentPrice == 0) {
            Log::info('Zero-price calculation', [
                'calculation_id' => $calculationId,
                'apartment_price' => $apartmentPrice,
                'rental_duration' => $rentalDuration,
                'pricing_type' => $pricingType,
                'timestamp' => now()->toISOString()
            ]);
        }
    }

    /**
     * Determine the calculation method based on pricing type
     */
    protected function determineCalculationMethod(string $pricingType): string
    {
        switch ($pricingType) {
            case self::PRICING_TYPE_TOTAL:
                return 'total_price_no_multiplication';
            case self::PRICING_TYPE_MONTHLY:
                return 'monthly_price_with_duration_multiplication';
            default:
                return 'unknown_method';
        }
    }

    /**
     * Perform the actual calculation based on pricing type
     */
    protected function performCalculation(
        float $apartmentPrice, 
        int $rentalDuration, 
        string $pricingType, 
        array &$calculationSteps
    ): float {
        switch ($pricingType) {
            case self::PRICING_TYPE_TOTAL:
                $calculationSteps[] = [
                    'step' => 'total_pricing_calculation',
                    'method' => 'apartment_price_as_total',
                    'apartment_price' => $apartmentPrice,
                    'rental_duration' => $rentalDuration,
                    'note' => 'Using apartment price as total amount without multiplication'
                ];
                return $apartmentPrice;

            case self::PRICING_TYPE_MONTHLY:
                $totalAmount = $apartmentPrice * $rentalDuration;
                $calculationSteps[] = [
                    'step' => 'monthly_pricing_calculation',
                    'method' => 'apartment_price_times_duration',
                    'apartment_price' => $apartmentPrice,
                    'rental_duration' => $rentalDuration,
                    'multiplication_result' => $totalAmount,
                    'note' => 'Multiplying monthly price by rental duration'
                ];
                return $totalAmount;

            default:
                throw new InvalidArgumentException('Unsupported pricing type: ' . $pricingType);
        }
    }

    /**
     * Get supported pricing types
     */
    public function getSupportedPricingTypes(): array
    {
        return [
            self::PRICING_TYPE_TOTAL => 'Total Price (no multiplication)',
            self::PRICING_TYPE_MONTHLY => 'Monthly Price (multiply by duration)'
        ];
    }

    /**
     * Get validation limits for reference
     */
    public function getValidationLimits(): array
    {
        return [
            'max_rental_duration' => self::MAX_RENTAL_DURATION,
            'max_apartment_price' => self::MAX_APARTMENT_PRICE,
            'min_apartment_price' => self::MIN_APARTMENT_PRICE
        ];
    }

    /**
     * Calculate multiple payment totals in batch for performance
     */
    public function calculateBulkPaymentTotals(array $calculations): array
    {
        $bulkId = uniqid('bulk_');
        $startTime = microtime(true);
        
        // Check if bulk results are cached
        $cachedResults = $this->cacheService->getCachedBulkCalculationResults($bulkId);
        if ($cachedResults !== null) {
            Log::info('Bulk calculation results served from cache', [
                'bulk_id' => $bulkId,
                'calculation_count' => count($cachedResults)
            ]);
            return $cachedResults;
        }
        
        $results = [];
        $cacheHits = 0;
        $cacheMisses = 0;
        
        foreach ($calculations as $index => $calc) {
            if (!isset($calc['apartment_price'], $calc['rental_duration'])) {
                $results[$index] = PaymentCalculationResult::failure('Missing required calculation parameters');
                continue;
            }
            
            $apartmentPrice = (float) $calc['apartment_price'];
            $rentalDuration = (int) $calc['rental_duration'];
            $pricingType = $calc['pricing_type'] ?? self::PRICING_TYPE_TOTAL;
            
            // Try cache first
            $cachedResult = $this->cacheService->getCachedCalculationResult(
                $apartmentPrice, 
                $rentalDuration, 
                $pricingType
            );
            
            if ($cachedResult !== null) {
                $results[$index] = $cachedResult;
                $cacheHits++;
            } else {
                $result = $this->calculatePaymentTotal($apartmentPrice, $rentalDuration, $pricingType);
                $results[$index] = $result;
                $cacheMisses++;
            }
        }
        
        // Cache bulk results for future use
        $this->cacheService->cacheBulkCalculationResults($bulkId, $results);
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        Log::info('Bulk payment calculations completed', [
            'bulk_id' => $bulkId,
            'total_calculations' => count($calculations),
            'cache_hits' => $cacheHits,
            'cache_misses' => $cacheMisses,
            'cache_hit_rate' => count($calculations) > 0 ? round(($cacheHits / count($calculations)) * 100, 2) : 0,
            'execution_time_ms' => $executionTime
        ]);
        
        return $results;
    }
    
    /**
     * Optimized calculation for frequently used apartment configurations
     */
    public function calculateOptimizedPaymentTotal(
        int $apartmentId,
        float $apartmentPrice,
        int $rentalDuration,
        string $pricingType = self::PRICING_TYPE_TOTAL
    ): PaymentCalculationResult {
        // Check apartment-specific pricing configuration cache
        $cachedPricingConfig = $this->cacheService->getCachedApartmentPricingConfig($apartmentId);
        
        if ($cachedPricingConfig) {
            // Use cached pricing configuration if available
            $pricingType = $cachedPricingConfig['pricing_type'] ?? $pricingType;
            
            Log::debug('Using cached apartment pricing configuration', [
                'apartment_id' => $apartmentId,
                'cached_pricing_type' => $pricingType
            ]);
        }
        
        return $this->calculatePaymentTotal($apartmentPrice, $rentalDuration, $pricingType);
    }
    
    /**
     * Pre-calculate and cache common calculation scenarios
     */
    public function preCalculateCommonScenarios(): void
    {
        $commonScenarios = [
            // Common rental amounts and durations
            ['price' => 50000, 'duration' => 6, 'type' => 'total'],
            ['price' => 100000, 'duration' => 12, 'type' => 'total'],
            ['price' => 150000, 'duration' => 24, 'type' => 'total'],
            ['price' => 25000, 'duration' => 6, 'type' => 'monthly'],
            ['price' => 30000, 'duration' => 12, 'type' => 'monthly'],
            ['price' => 35000, 'duration' => 24, 'type' => 'monthly'],
            // Edge cases
            ['price' => 0, 'duration' => 1, 'type' => 'total'],
            ['price' => 1000000, 'duration' => 1, 'type' => 'total'],
        ];
        
        $preCalculatedCount = 0;
        
        foreach ($commonScenarios as $scenario) {
            $result = $this->calculatePaymentTotal(
                $scenario['price'],
                $scenario['duration'],
                $scenario['type']
            );
            
            if ($result->isValid) {
                $preCalculatedCount++;
            }
        }
        
        Log::info('Pre-calculated common payment scenarios', [
            'scenarios_calculated' => $preCalculatedCount,
            'total_scenarios' => count($commonScenarios)
        ]);
    }
    
    /**
     * Get calculation performance metrics with caching statistics
     */
    public function getPerformanceMetrics(int $hours = 24): array
    {
        $baseMetrics = $this->monitoringService->getPerformanceMetrics($hours);
        $cacheMetrics = $this->cacheService->getCachePerformanceStatistics($hours);
        
        return array_merge($baseMetrics, [
            'cache_performance' => $cacheMetrics,
            'cache_usage_summary' => $this->cacheService->getCacheUsageSummary()
        ]);
    }
    
    /**
     * Calculate payment total with additional charges (for proforma calculations)
     */
    public function calculatePaymentTotalWithCharges(
        float $apartmentPrice,
        int $rentalDuration,
        string $pricingType = self::PRICING_TYPE_TOTAL,
        array $additionalCharges = []
    ): PaymentCalculationResult {
        // First calculate the base payment total
        $baseResult = $this->calculatePaymentTotal($apartmentPrice, $rentalDuration, $pricingType);
        
        if (!$baseResult->isValid) {
            return $baseResult;
        }

        try {
            $calculationSteps = $baseResult->calculationSteps;
            $totalAmount = $baseResult->totalAmount;

            // Add additional charges
            $chargesTotal = 0.0;
            foreach ($additionalCharges as $chargeName => $chargeAmount) {
                if (is_numeric($chargeAmount) && $chargeAmount > 0) {
                    $chargesTotal += (float)$chargeAmount;
                    $calculationSteps[] = [
                        'step' => 'additional_charge',
                        'charge_name' => $chargeName,
                        'charge_amount' => (float)$chargeAmount,
                        'running_total' => $totalAmount + $chargesTotal
                    ];
                }
            }

            $finalTotal = $totalAmount + $chargesTotal;

            $calculationSteps[] = [
                'step' => 'final_total_with_charges',
                'base_amount' => $totalAmount,
                'additional_charges_total' => $chargesTotal,
                'final_total' => $finalTotal,
                'timestamp' => now()->toISOString()
            ];

            return PaymentCalculationResult::success(
                $finalTotal,
                $baseResult->calculationMethod . '_with_additional_charges',
                $calculationSteps
            );

        } catch (\Exception $e) {
            Log::error('Payment calculation with charges failed', [
                'apartment_price' => $apartmentPrice,
                'rental_duration' => $rentalDuration,
                'pricing_type' => $pricingType,
                'additional_charges' => $additionalCharges,
                'error' => $e->getMessage()
            ]);

            return PaymentCalculationResult::failure(
                'Calculation with charges error: ' . $e->getMessage()
            );
        }
    }
}
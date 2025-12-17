<?php

namespace App\Services\Payment;

interface PaymentCalculationServiceInterface
{
    /**
     * Calculate payment total with security validation
     *
     * @param array $inputs The input data to validate and calculate
     * @return PaymentCalculationResult
     */
    public function calculatePaymentTotalSecure(array $inputs): PaymentCalculationResult;

    /**
     * Calculate payment total based on apartment price, rental duration, and pricing type
     *
     * @param float $apartmentPrice The base apartment price
     * @param int $rentalDuration The rental duration in months
     * @param string $pricingType The pricing type ('total' or 'monthly')
     * @return PaymentCalculationResult
     */
    public function calculatePaymentTotal(
        float $apartmentPrice, 
        int $rentalDuration, 
        string $pricingType = 'total'
    ): PaymentCalculationResult;
    
    /**
     * Validate pricing configuration data
     *
     * @param array $config The pricing configuration to validate
     * @return bool
     */
    public function validatePricingConfiguration(array $config): bool;
    
    /**
     * Log calculation steps for audit purposes
     *
     * @param PaymentCalculationResult $result The calculation result to log
     * @return void
     */
    public function logCalculationSteps(PaymentCalculationResult $result): void;
}
<?php

namespace App\Services\Payment;

class PaymentCalculationResult
{
    public float $totalAmount;
    public string $calculationMethod;
    public array $calculationSteps;
    public bool $isValid;
    public ?string $errorMessage;

    public function __construct(
        float $totalAmount = 0.0,
        string $calculationMethod = '',
        array $calculationSteps = [],
        bool $isValid = true,
        ?string $errorMessage = null
    ) {
        $this->totalAmount = $totalAmount;
        $this->calculationMethod = $calculationMethod;
        $this->calculationSteps = $calculationSteps;
        $this->isValid = $isValid;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Create a successful calculation result
     */
    public static function success(
        float $totalAmount,
        string $calculationMethod,
        array $calculationSteps
    ): self {
        return new self($totalAmount, $calculationMethod, $calculationSteps, true, null);
    }

    /**
     * Create a failed calculation result
     */
    public static function failure(string $errorMessage): self
    {
        return new self(0.0, '', [], false, $errorMessage);
    }

    /**
     * Get formatted total amount for display
     */
    public function getFormattedTotal(): string
    {
        return number_format($this->totalAmount, 2);
    }

    /**
     * Get calculation summary for logging
     */
    public function getCalculationSummary(): array
    {
        return [
            'total_amount' => $this->totalAmount,
            'calculation_method' => $this->calculationMethod,
            'steps_count' => count($this->calculationSteps),
            'is_valid' => $this->isValid,
            'error_message' => $this->errorMessage
        ];
    }

    /**
     * Convert to array for JSON serialization
     */
    public function toArray(): array
    {
        return [
            'total_amount' => $this->totalAmount,
            'calculation_method' => $this->calculationMethod,
            'calculation_steps' => $this->calculationSteps,
            'is_valid' => $this->isValid,
            'error_message' => $this->errorMessage
        ];
    }
}
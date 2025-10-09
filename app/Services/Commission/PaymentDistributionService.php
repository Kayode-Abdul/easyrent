<?php

namespace App\Services\Commission;

use App\Models\CommissionPayment;
use App\Models\ReferralChain;
use App\Models\User;
use App\Models\ReferralReward;
use App\Services\Commission\MultiTierCommissionCalculator;
use App\Services\Commission\RegionalRateManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentDistributionService
{
    private MultiTierCommissionCalculator $calculator;
    private RegionalRateManager $rateManager;

    public function __construct(
        MultiTierCommissionCalculator $calculator,
        RegionalRateManager $rateManager
    ) {
        $this->calculator = $calculator;
        $this->rateManager = $rateManager;
    }

    /**
     * Distribute multi-tier commission for a referral chain
     *
     * @param float $totalAmount Total commission amount to distribute
     * @param array $referralChain Array of user IDs in hierarchy
     * @param string $region Property region
     * @param int|null $referralChainId Optional referral chain ID for tracking
     * @return array Array of created payment records
     * @throws Exception
     */
    public function distributeMultiTierCommission(
        float $totalAmount,
        array $referralChain,
        string $region,
        ?int $referralChainId = null
    ): array {
        DB::beginTransaction();
        
        try {
            // Calculate commission breakdown
            $commissionBreakdown = $this->calculator->calculateCommissionSplit(
                $totalAmount,
                $referralChain,
                $region
            );

            // Create payment records for each tier
            $paymentRecords = $this->createPaymentRecords(
                $commissionBreakdown,
                $region,
                $referralChainId
            );

            // Link parent-child payment relationships
            $this->linkPaymentHierarchy($paymentRecords);

            DB::commit();

            Log::info('Multi-tier commission distributed successfully', [
                'total_amount' => $totalAmount,
                'region' => $region,
                'referral_chain' => $referralChain,
                'payment_count' => count($paymentRecords)
            ]);

            return $paymentRecords;

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to distribute multi-tier commission', [
                'total_amount' => $totalAmount,
                'referral_chain' => $referralChain,
                'region' => $region,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Create payment records for commission breakdown
     *
     * @param array $commissionBreakdown
     * @param string $region
     * @param int|null $referralChainId
     * @return array
     */
    public function createPaymentRecords(
        array $commissionBreakdown,
        string $region,
        ?int $referralChainId = null
    ): array {
        $paymentRecords = [];

        foreach ($commissionBreakdown as $breakdown) {
            // Skip company profit and entries without user_id
            if ($breakdown['tier'] === MultiTierCommissionCalculator::TIER_COMPANY || 
                !$breakdown['user_id']) {
                continue;
            }

            $payment = $this->createSinglePaymentRecord(
                $breakdown,
                $region,
                $referralChainId
            );

            if ($payment) {
                $paymentRecords[] = $payment;
            }
        }

        return $paymentRecords;
    }

    /**
     * Process a batch of payments
     *
     * @param array $paymentIds Array of payment IDs to process
     * @return array Processing results
     */
    public function processPaymentBatch(array $paymentIds): array
    {
        $results = [
            'successful' => [],
            'failed' => [],
            'total_processed' => 0,
            'total_amount' => 0
        ];

        foreach ($paymentIds as $paymentId) {
            try {
                $payment = CommissionPayment::findOrFail($paymentId);
                
                if ($this->processIndividualPayment($payment)) {
                    $results['successful'][] = $paymentId;
                    $results['total_amount'] += $payment->total_amount;
                } else {
                    $results['failed'][] = [
                        'payment_id' => $paymentId,
                        'reason' => 'Processing failed'
                    ];
                }
                
                $results['total_processed']++;

            } catch (Exception $e) {
                $results['failed'][] = [
                    'payment_id' => $paymentId,
                    'reason' => $e->getMessage()
                ];
                
                Log::error('Payment processing failed', [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Payment batch processed', $results);

        return $results;
    }

    /**
     * Handle failed payments with retry mechanisms
     *
     * @param array $failedPayments Array of failed payment data
     * @return void
     */
    public function handleFailedPayments(array $failedPayments): void
    {
        foreach ($failedPayments as $failedPayment) {
            try {
                $paymentId = $failedPayment['payment_id'] ?? null;
                $reason = $failedPayment['reason'] ?? 'Unknown error';

                if (!$paymentId) {
                    continue;
                }

                $payment = CommissionPayment::find($paymentId);
                if (!$payment) {
                    continue;
                }

                // Mark payment as failed
                $payment->markAsFailed($reason);

                // Determine if retry is appropriate
                if ($this->shouldRetryPayment($payment, $reason)) {
                    $this->schedulePaymentRetry($payment);
                } else {
                    $this->escalateFailedPayment($payment, $reason);
                }

            } catch (Exception $e) {
                Log::error('Failed to handle failed payment', [
                    'failed_payment' => $failedPayment,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Get payment distribution summary for a referral chain
     *
     * @param int $referralChainId
     * @return array
     */
    public function getDistributionSummary(int $referralChainId): array
    {
        $payments = CommissionPayment::where('referral_chain_id', $referralChainId)
            ->with(['marketer', 'parentPayment', 'childPayments'])
            ->get();

        $summary = [
            'total_amount' => $payments->sum('total_amount'),
            'payment_count' => $payments->count(),
            'status_breakdown' => [],
            'tier_breakdown' => [],
            'payments' => []
        ];

        foreach ($payments as $payment) {
            // Status breakdown
            $status = $payment->payment_status;
            $summary['status_breakdown'][$status] = ($summary['status_breakdown'][$status] ?? 0) + 1;

            // Tier breakdown
            $tier = $payment->commission_tier;
            if (!isset($summary['tier_breakdown'][$tier])) {
                $summary['tier_breakdown'][$tier] = [
                    'count' => 0,
                    'total_amount' => 0
                ];
            }
            $summary['tier_breakdown'][$tier]['count']++;
            $summary['tier_breakdown'][$tier]['total_amount'] += $payment->total_amount;

            // Payment details
            $summary['payments'][] = [
                'id' => $payment->id,
                'reference' => $payment->payment_reference,
                'tier' => $payment->commission_tier,
                'amount' => $payment->total_amount,
                'status' => $payment->payment_status,
                'marketer' => $payment->marketer ? [
                    'id' => $payment->marketer->user_id,
                    'name' => $payment->marketer->name,
                    'email' => $payment->marketer->email
                ] : null
            ];
        }

        return $summary;
    }

    /**
     * Validate payment distribution integrity
     *
     * @param int $referralChainId
     * @return array Validation results
     */
    public function validateDistributionIntegrity(int $referralChainId): array
    {
        $payments = CommissionPayment::where('referral_chain_id', $referralChainId)->get();
        
        $validation = [
            'is_valid' => true,
            'issues' => [],
            'total_distributed' => $payments->sum('total_amount'),
            'payment_count' => $payments->count()
        ];

        // Check for duplicate tiers
        $tiers = $payments->pluck('commission_tier')->toArray();
        $duplicateTiers = array_diff_assoc($tiers, array_unique($tiers));
        if (!empty($duplicateTiers)) {
            $validation['is_valid'] = false;
            $validation['issues'][] = 'Duplicate commission tiers found: ' . implode(', ', $duplicateTiers);
        }

        // Check for orphaned payments (missing parent relationships)
        foreach ($payments as $payment) {
            if ($payment->parent_payment_id && !$payments->contains('id', $payment->parent_payment_id)) {
                $validation['is_valid'] = false;
                $validation['issues'][] = "Payment {$payment->id} has invalid parent reference";
            }
        }

        // Check for circular references
        if ($this->hasCircularPaymentReferences($payments)) {
            $validation['is_valid'] = false;
            $validation['issues'][] = 'Circular payment references detected';
        }

        return $validation;
    }

    /**
     * Create a single payment record
     *
     * @param array $breakdown Commission breakdown for this tier
     * @param string $region
     * @param int|null $referralChainId
     * @return CommissionPayment|null
     */
    private function createSinglePaymentRecord(
        array $breakdown,
        string $region,
        ?int $referralChainId = null
    ): ?CommissionPayment {
        if ($breakdown['amount'] <= 0) {
            return null;
        }

        return CommissionPayment::create([
            'marketer_id' => $breakdown['user_id'],
            'total_amount' => $breakdown['amount'],
            'payment_method' => CommissionPayment::METHOD_BANK_TRANSFER,
            'payment_status' => CommissionPayment::STATUS_PENDING,
            'referral_chain_id' => $referralChainId,
            'commission_tier' => $breakdown['tier'],
            'regional_rate_applied' => $breakdown['rate_percentage'],
            'region' => $region,
            'payment_details' => [
                'tier' => $breakdown['tier'],
                'rate_percentage' => $breakdown['rate_percentage'],
                'calculated_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Link parent-child payment relationships
     *
     * @param array $paymentRecords
     * @return void
     */
    private function linkPaymentHierarchy(array $paymentRecords): void
    {
        // Sort payments by tier hierarchy (super_marketer -> marketer -> regional_manager)
        $tierOrder = [
            MultiTierCommissionCalculator::TIER_SUPER_MARKETER => 1,
            MultiTierCommissionCalculator::TIER_MARKETER => 2,
            MultiTierCommissionCalculator::TIER_REGIONAL_MANAGER => 3
        ];

        usort($paymentRecords, function ($a, $b) use ($tierOrder) {
            $orderA = $tierOrder[$a->commission_tier] ?? 999;
            $orderB = $tierOrder[$b->commission_tier] ?? 999;
            return $orderA <=> $orderB;
        });

        // Link each payment to its parent (previous in hierarchy)
        for ($i = 1; $i < count($paymentRecords); $i++) {
            $paymentRecords[$i]->update([
                'parent_payment_id' => $paymentRecords[$i - 1]->id
            ]);
        }
    }

    /**
     * Process an individual payment
     *
     * @param CommissionPayment $payment
     * @return bool
     */
    private function processIndividualPayment(CommissionPayment $payment): bool
    {
        try {
            // Mark as processing
            $payment->markAsProcessing();

            // Simulate payment processing (integrate with actual payment gateway)
            $transactionId = $this->simulatePaymentProcessing($payment);

            if ($transactionId) {
                $payment->markAsCompleted($transactionId);
                return true;
            } else {
                $payment->markAsFailed('Payment gateway rejected transaction');
                return false;
            }

        } catch (Exception $e) {
            $payment->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Simulate payment processing (replace with actual gateway integration)
     *
     * @param CommissionPayment $payment
     * @return string|null
     */
    private function simulatePaymentProcessing(CommissionPayment $payment): ?string
    {
        // Simulate processing delay and success/failure
        // In real implementation, this would integrate with payment gateway
        
        // For simulation, assume 95% success rate
        if (rand(1, 100) <= 95) {
            return 'TXN_' . time() . '_' . $payment->id;
        }
        
        return null;
    }

    /**
     * Determine if a payment should be retried
     *
     * @param CommissionPayment $payment
     * @param string $reason
     * @return bool
     */
    private function shouldRetryPayment(CommissionPayment $payment, string $reason): bool
    {
        // Check retry count in payment details
        $details = $payment->payment_details ?? [];
        $retryCount = $details['retry_count'] ?? 0;

        // Don't retry more than 3 times
        if ($retryCount >= 3) {
            return false;
        }

        // Don't retry for certain failure reasons
        $nonRetryableReasons = [
            'insufficient_funds',
            'invalid_account',
            'account_closed',
            'fraud_detected'
        ];

        foreach ($nonRetryableReasons as $nonRetryableReason) {
            if (stripos($reason, $nonRetryableReason) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Schedule a payment for retry
     *
     * @param CommissionPayment $payment
     * @return void
     */
    private function schedulePaymentRetry(CommissionPayment $payment): void
    {
        $details = $payment->payment_details ?? [];
        $retryCount = ($details['retry_count'] ?? 0) + 1;
        
        // Schedule retry with exponential backoff
        $retryDelay = pow(2, $retryCount) * 60; // 2, 4, 8 minutes
        $scheduledDate = now()->addMinutes($retryDelay);

        $details['retry_count'] = $retryCount;
        $details['retry_scheduled_at'] = $scheduledDate->toISOString();

        $payment->update([
            'payment_status' => CommissionPayment::STATUS_PENDING,
            'scheduled_date' => $scheduledDate,
            'payment_details' => $details
        ]);

        Log::info('Payment scheduled for retry', [
            'payment_id' => $payment->id,
            'retry_count' => $retryCount,
            'scheduled_date' => $scheduledDate
        ]);
    }

    /**
     * Escalate a failed payment for manual review
     *
     * @param CommissionPayment $payment
     * @param string $reason
     * @return void
     */
    private function escalateFailedPayment(CommissionPayment $payment, string $reason): void
    {
        $details = $payment->payment_details ?? [];
        $details['escalated_at'] = now()->toISOString();
        $details['escalation_reason'] = $reason;

        $payment->update([
            'payment_details' => $details,
            'notes' => "Payment escalated for manual review: {$reason}"
        ]);

        // In a real implementation, this would trigger notifications to administrators
        Log::warning('Payment escalated for manual review', [
            'payment_id' => $payment->id,
            'reason' => $reason,
            'amount' => $payment->total_amount
        ]);
    }

    /**
     * Check for circular payment references
     *
     * @param Collection $payments
     * @return bool
     */
    private function hasCircularPaymentReferences(Collection $payments): bool
    {
        $paymentMap = $payments->keyBy('id');

        foreach ($payments as $payment) {
            $visited = [];
            $current = $payment;

            while ($current && $current->parent_payment_id) {
                if (in_array($current->id, $visited)) {
                    return true; // Circular reference found
                }
                
                $visited[] = $current->id;
                $current = $paymentMap->get($current->parent_payment_id);
            }
        }

        return false;
    }
}
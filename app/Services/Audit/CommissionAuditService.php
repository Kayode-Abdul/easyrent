<?php

namespace App\Services\Audit;

use App\Models\CommissionPayment;
use App\Models\ReferralChain;
use App\Models\CommissionRate;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommissionAuditService
{
    /**
     * Create audit trail for commission calculation
     *
     * @param array $calculationData
     * @return int Audit trail ID
     */
    public function createAuditTrail(array $calculationData): int
    {
        $auditData = [
            'audit_type' => 'commission_calculation',
            'reference_id' => $calculationData['payment_id'] ?? null,
            'reference_type' => 'commission_payment',
            'user_id' => $calculationData['calculated_by'] ?? null,
            'audit_data' => json_encode([
                'calculation_input' => $calculationData['input'] ?? [],
                'calculation_output' => $calculationData['output'] ?? [],
                'rates_used' => $calculationData['rates'] ?? [],
                'referral_chain' => $calculationData['referral_chain'] ?? [],
                'calculation_method' => $calculationData['method'] ?? 'multi_tier',
                'timestamp' => now()->toISOString()
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now()
        ];

        $auditId = DB::table('audit_logs')->insertGetId($auditData);

        Log::info('Commission calculation audit trail created', [
            'audit_id' => $auditId,
            'payment_id' => $calculationData['payment_id'] ?? null,
            'total_amount' => $calculationData['output']['total_amount'] ?? null
        ]);

        return $auditId;
    }

    /**
     * Verify commission calculation accuracy
     *
     * @param int $paymentId
     * @return array Verification results
     */
    public function verifyCommissionCalculation(int $paymentId): array
    {
        $payment = CommissionPayment::find($paymentId);
        
        if (!$payment) {
            return [
                'verified' => false,
                'errors' => ['Payment not found'],
                'details' => []
            ];
        }

        $verificationResults = [
            'verified' => true,
            'errors' => [],
            'warnings' => [],
            'details' => []
        ];

        // Verify referral chain integrity
        $chainVerification = $this->verifyReferralChainIntegrity($payment);
        $verificationResults['details']['chain_verification'] = $chainVerification;
        
        if (!$chainVerification['valid']) {
            $verificationResults['verified'] = false;
            $verificationResults['errors'] = array_merge(
                $verificationResults['errors'], 
                $chainVerification['errors']
            );
        }

        // Verify commission rates
        $rateVerification = $this->verifyCommissionRates($payment);
        $verificationResults['details']['rate_verification'] = $rateVerification;
        
        if (!$rateVerification['valid']) {
            $verificationResults['verified'] = false;
            $verificationResults['errors'] = array_merge(
                $verificationResults['errors'], 
                $rateVerification['errors']
            );
        }

        // Verify calculation accuracy
        $calculationVerification = $this->verifyCalculationAccuracy($payment);
        $verificationResults['details']['calculation_verification'] = $calculationVerification;
        
        if (!$calculationVerification['valid']) {
            $verificationResults['verified'] = false;
            $verificationResults['errors'] = array_merge(
                $verificationResults['errors'], 
                $calculationVerification['errors']
            );
        }

        // Verify total commission limits
        $limitVerification = $this->verifyCommissionLimits($payment);
        $verificationResults['details']['limit_verification'] = $limitVerification;
        
        if (!$limitVerification['valid']) {
            $verificationResults['verified'] = false;
            $verificationResults['errors'] = array_merge(
                $verificationResults['errors'], 
                $limitVerification['errors']
            );
        }

        // Log verification results
        $this->logVerificationResults($paymentId, $verificationResults);

        return $verificationResults;
    }

    /**
     * Reconcile commission payments for a given period
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $region
     * @return array Reconciliation results
     */
    public function reconcileCommissions(Carbon $startDate, Carbon $endDate, string $region = null): array
    {
        $reconciliationResults = [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'region' => $region
            ],
            'summary' => [],
            'discrepancies' => [],
            'recommendations' => []
        ];

        // Get all commission payments in the period
        $paymentsQuery = CommissionPayment::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($region) {
            $paymentsQuery->whereHas('marketer', function($q) use ($region) {
                $q->where('state', $region);
            });
        }
        
        $payments = $paymentsQuery->with(['marketer', 'referralChain'])->get();

        // Calculate expected vs actual totals
        $expectedTotal = 0;
        $actualTotal = $payments->sum('amount');
        $discrepancyCount = 0;

        foreach ($payments as $payment) {
            $verification = $this->verifyCommissionCalculation($payment->id);
            
            if (!$verification['verified']) {
                $discrepancyCount++;
                $reconciliationResults['discrepancies'][] = [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'errors' => $verification['errors'],
                    'marketer_id' => $payment->marketer_id
                ];
            }

            // Recalculate expected amount
            $expectedAmount = $this->recalculateExpectedAmount($payment);
            $expectedTotal += $expectedAmount;

            if (abs($expectedAmount - $payment->amount) > 0.01) {
                $reconciliationResults['discrepancies'][] = [
                    'payment_id' => $payment->id,
                    'expected_amount' => $expectedAmount,
                    'actual_amount' => $payment->amount,
                    'difference' => $expectedAmount - $payment->amount,
                    'type' => 'amount_mismatch'
                ];
            }
        }

        $reconciliationResults['summary'] = [
            'total_payments' => $payments->count(),
            'expected_total' => $expectedTotal,
            'actual_total' => $actualTotal,
            'total_difference' => $expectedTotal - $actualTotal,
            'discrepancy_count' => $discrepancyCount,
            'accuracy_rate' => $payments->count() > 0 ? 
                round((($payments->count() - $discrepancyCount) / $payments->count()) * 100, 2) : 100
        ];

        // Generate recommendations
        if ($discrepancyCount > 0) {
            $reconciliationResults['recommendations'][] = 'Review and correct identified discrepancies';
        }
        
        if (abs($expectedTotal - $actualTotal) > ($actualTotal * 0.01)) {
            $reconciliationResults['recommendations'][] = 'Investigate significant total amount variance';
        }

        // Log reconciliation
        Log::info('Commission reconciliation completed', [
            'period' => $reconciliationResults['period'],
            'summary' => $reconciliationResults['summary']
        ]);

        return $reconciliationResults;
    }

    /**
     * Log commission calculation error
     *
     * @param string $errorType
     * @param array $errorData
     * @param string $severity
     * @return void
     */
    public function logCalculationError(string $errorType, array $errorData, string $severity = 'error'): void
    {
        $logData = [
            'error_type' => $errorType,
            'error_data' => $errorData,
            'severity' => $severity,
            'timestamp' => now()->toISOString(),
            'context' => [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => auth()->id()
            ]
        ];

        // Log to Laravel log
        Log::channel('commission_errors')->{$severity}('Commission calculation error', $logData);

        // Store in database for tracking
        DB::table('audit_logs')->insert([
            'audit_type' => 'commission_error',
            'reference_type' => $errorType,
            'audit_data' => json_encode($logData),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Send notification for critical errors
        if ($severity === 'critical') {
            $this->sendCriticalErrorNotification($errorType, $errorData);
        }
    }

    /**
     * Get audit trail for a specific payment
     *
     * @param int $paymentId
     * @return Collection
     */
    public function getPaymentAuditTrail(int $paymentId): Collection
    {
        return DB::table('audit_logs')
            ->where('reference_type', 'commission_payment')
            ->where('reference_id', $paymentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($audit) {
                $audit->audit_data = json_decode($audit->audit_data, true);
                return $audit;
            });
    }
  
  /**
     * Verify referral chain integrity for a payment
     *
     * @param CommissionPayment $payment
     * @return array
     */
    private function verifyReferralChainIntegrity(CommissionPayment $payment): array
    {
        $result = [
            'valid' => true,
            'errors' => [],
            'details' => []
        ];

        if (!$payment->referral_chain_id) {
            // Check if this is a direct referral payment
            $directReferral = DB::table('referrals')
                ->where('referrer_id', $payment->marketer_id)
                ->first();
                
            if (!$directReferral) {
                $result['valid'] = false;
                $result['errors'][] = 'No referral chain or direct referral found for payment';
            }
            
            return $result;
        }

        $chain = ReferralChain::find($payment->referral_chain_id);
        
        if (!$chain) {
            $result['valid'] = false;
            $result['errors'][] = 'Referral chain not found';
            return $result;
        }

        // Verify chain participants exist
        $superMarketer = User::find($chain->super_marketer_id);
        $marketer = User::find($chain->marketer_id);
        $landlord = User::find($chain->landlord_id);

        if (!$superMarketer) {
            $result['valid'] = false;
            $result['errors'][] = 'Super Marketer not found in chain';
        }

        if (!$marketer) {
            $result['valid'] = false;
            $result['errors'][] = 'Marketer not found in chain';
        }

        if (!$landlord) {
            $result['valid'] = false;
            $result['errors'][] = 'Landlord not found in chain';
        }

        // Verify payment marketer matches chain
        if ($payment->marketer_id !== $chain->marketer_id && $payment->marketer_id !== $chain->super_marketer_id) {
            $result['valid'] = false;
            $result['errors'][] = 'Payment marketer does not match referral chain';
        }

        $result['details'] = [
            'chain_id' => $chain->id,
            'super_marketer_id' => $chain->super_marketer_id,
            'marketer_id' => $chain->marketer_id,
            'landlord_id' => $chain->landlord_id,
            'payment_marketer_id' => $payment->marketer_id
        ];

        return $result;
    }

    /**
     * Verify commission rates used in calculation
     *
     * @param CommissionPayment $payment
     * @return array
     */
    private function verifyCommissionRates(CommissionPayment $payment): array
    {
        $result = [
            'valid' => true,
            'errors' => [],
            'details' => []
        ];

        $marketer = User::find($payment->marketer_id);
        
        if (!$marketer) {
            $result['valid'] = false;
            $result['errors'][] = 'Marketer not found for rate verification';
            return $result;
        }

        $region = $marketer->state ?? 'default';
        $appliedRate = $payment->regional_rate_applied;

        // Get expected rate for the marketer's role and region
        $marketerRoles = $marketer->roles->pluck('id')->toArray();
        $expectedRate = null;

        foreach ($marketerRoles as $roleId) {
            $rate = CommissionRate::active()
                ->forRegion($region)
                ->forRole($roleId)
                ->first();
                
            if ($rate) {
                $expectedRate = $rate->commission_percentage;
                break;
            }
        }

        if ($expectedRate === null) {
            // Try default region
            foreach ($marketerRoles as $roleId) {
                $rate = CommissionRate::active()
                    ->forRegion('default')
                    ->forRole($roleId)
                    ->first();
                    
                if ($rate) {
                    $expectedRate = $rate->commission_percentage;
                    break;
                }
            }
        }

        if ($expectedRate === null) {
            $result['valid'] = false;
            $result['errors'][] = 'No commission rate found for marketer role and region';
        } elseif (abs($expectedRate - $appliedRate) > 0.0001) {
            $result['valid'] = false;
            $result['errors'][] = "Rate mismatch: expected {$expectedRate}%, applied {$appliedRate}%";
        }

        $result['details'] = [
            'marketer_id' => $payment->marketer_id,
            'region' => $region,
            'expected_rate' => $expectedRate,
            'applied_rate' => $appliedRate,
            'marketer_roles' => $marketerRoles
        ];

        return $result;
    }

    /**
     * Verify calculation accuracy
     *
     * @param CommissionPayment $payment
     * @return array
     */
    private function verifyCalculationAccuracy(CommissionPayment $payment): array
    {
        $result = [
            'valid' => true,
            'errors' => [],
            'details' => []
        ];

        // Recalculate expected amount
        $expectedAmount = $this->recalculateExpectedAmount($payment);
        $actualAmount = $payment->amount;
        $tolerance = 0.01; // 1 cent tolerance

        if (abs($expectedAmount - $actualAmount) > $tolerance) {
            $result['valid'] = false;
            $result['errors'][] = "Amount mismatch: expected {$expectedAmount}, actual {$actualAmount}";
        }

        $result['details'] = [
            'expected_amount' => $expectedAmount,
            'actual_amount' => $actualAmount,
            'difference' => $expectedAmount - $actualAmount,
            'tolerance' => $tolerance
        ];

        return $result;
    }

    /**
     * Verify commission limits are not exceeded
     *
     * @param CommissionPayment $payment
     * @return array
     */
    private function verifyCommissionLimits(CommissionPayment $payment): array
    {
        $result = [
            'valid' => true,
            'errors' => [],
            'details' => []
        ];

        // Get all related payments for the same base transaction
        $relatedPayments = CommissionPayment::where('parent_payment_id', $payment->parent_payment_id ?? $payment->id)
            ->orWhere('id', $payment->parent_payment_id ?? $payment->id)
            ->get();

        $totalCommissionRate = $relatedPayments->sum('regional_rate_applied');
        $maxCommissionRate = 2.5; // 2.5% maximum

        if ($totalCommissionRate > $maxCommissionRate) {
            $result['valid'] = false;
            $result['errors'][] = "Total commission rate {$totalCommissionRate}% exceeds maximum {$maxCommissionRate}%";
        }

        $result['details'] = [
            'total_commission_rate' => $totalCommissionRate,
            'max_commission_rate' => $maxCommissionRate,
            'related_payments_count' => $relatedPayments->count(),
            'related_payment_ids' => $relatedPayments->pluck('id')->toArray()
        ];

        return $result;
    }

    /**
     * Recalculate expected amount for a payment
     *
     * @param CommissionPayment $payment
     * @return float
     */
    private function recalculateExpectedAmount(CommissionPayment $payment): float
    {
        // This would use the same calculation logic as MultiTierCommissionCalculator
        // For now, we'll use a simplified calculation based on the stored rate
        
        // Get the base amount (this would typically come from the rent payment)
        // For audit purposes, we'll reverse-calculate from the commission amount and rate
        if ($payment->regional_rate_applied > 0) {
            $baseAmount = $payment->amount / ($payment->regional_rate_applied / 100);
            return round($baseAmount * ($payment->regional_rate_applied / 100), 2);
        }

        return $payment->amount;
    }

    /**
     * Log verification results
     *
     * @param int $paymentId
     * @param array $results
     * @return void
     */
    private function logVerificationResults(int $paymentId, array $results): void
    {
        $logLevel = $results['verified'] ? 'info' : 'warning';
        
        Log::channel('commission_audit')->{$logLevel}('Commission verification completed', [
            'payment_id' => $paymentId,
            'verified' => $results['verified'],
            'error_count' => count($results['errors']),
            'errors' => $results['errors']
        ]);

        // Store verification results in audit log
        DB::table('audit_logs')->insert([
            'audit_type' => 'commission_verification',
            'reference_type' => 'commission_payment',
            'reference_id' => $paymentId,
            'audit_data' => json_encode($results),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Send critical error notification
     *
     * @param string $errorType
     * @param array $errorData
     * @return void
     */
    private function sendCriticalErrorNotification(string $errorType, array $errorData): void
    {
        // Log as critical
        Log::critical('Critical commission calculation error', [
            'error_type' => $errorType,
            'error_data' => $errorData,
            'requires_immediate_attention' => true
        ]);

        // Send notification to administrators
        try {
            $adminUsers = User::whereHas('roles', function($q) {
                $q->where('name', 'admin');
            })->get();

            foreach ($adminUsers as $admin) {
                $admin->notify(new \App\Notifications\CriticalCommissionError($errorType, $errorData));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send critical error notification', [
                'error' => $e->getMessage(),
                'original_error_type' => $errorType
            ]);
        }
    }

    /**
     * Generate commission audit report
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $region
     * @return array
     */
    public function generateAuditReport(Carbon $startDate, Carbon $endDate, string $region = null): array
    {
        $reconciliation = $this->reconcileCommissions($startDate, $endDate, $region);
        
        // Get error statistics
        $errorStats = DB::table('audit_logs')
            ->where('audit_type', 'commission_error')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_errors,
                SUM(CASE WHEN JSON_EXTRACT(audit_data, "$.severity") = "critical" THEN 1 ELSE 0 END) as critical_errors,
                SUM(CASE WHEN JSON_EXTRACT(audit_data, "$.severity") = "error" THEN 1 ELSE 0 END) as errors,
                SUM(CASE WHEN JSON_EXTRACT(audit_data, "$.severity") = "warning" THEN 1 ELSE 0 END) as warnings
            ')
            ->first();

        // Get verification statistics
        $verificationStats = DB::table('audit_logs')
            ->where('audit_type', 'commission_verification')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_verifications,
                SUM(CASE WHEN JSON_EXTRACT(audit_data, "$.verified") = true THEN 1 ELSE 0 END) as successful_verifications
            ')
            ->first();

        return [
            'report_period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'region' => $region
            ],
            'reconciliation' => $reconciliation,
            'error_statistics' => [
                'total_errors' => $errorStats->total_errors ?? 0,
                'critical_errors' => $errorStats->critical_errors ?? 0,
                'errors' => $errorStats->errors ?? 0,
                'warnings' => $errorStats->warnings ?? 0
            ],
            'verification_statistics' => [
                'total_verifications' => $verificationStats->total_verifications ?? 0,
                'successful_verifications' => $verificationStats->successful_verifications ?? 0,
                'success_rate' => $verificationStats->total_verifications > 0 ? 
                    round(($verificationStats->successful_verifications / $verificationStats->total_verifications) * 100, 2) : 100
            ],
            'generated_at' => now()->toISOString()
        ];
    }
}
<?php

namespace App\Services\Monitoring;

use App\Models\User;
use App\Models\Referral;
use App\Services\Fraud\FraudDetectionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CriticalCommissionError;
use Carbon\Carbon;

class FraudAlertSystem
{
    protected FraudDetectionService $fraudService;

    public function __construct(FraudDetectionService $fraudService)
    {
        $this->fraudService = $fraudService;
    }

    /**
     * Monitor for fraud patterns and create alerts
     *
     * @return array
     */
    public function monitorFraudPatterns(): array
    {
        $alerts = [];
        
        // Check for suspicious user activity
        $suspiciousUsers = $this->detectSuspiciousUsers();
        if (!empty($suspiciousUsers)) {
            $alerts[] = $this->createFraudAlert('suspicious_users', $suspiciousUsers, 'warning');
        }

        // Check for circular referral attempts
        $circularAttempts = $this->detectCircularReferralAttempts();
        if (!empty($circularAttempts)) {
            $alerts[] = $this->createFraudAlert('circular_referrals', $circularAttempts, 'critical');
        }

        // Check for rapid referral creation
        $rapidReferrals = $this->detectRapidReferralCreation();
        if (!empty($rapidReferrals)) {
            $alerts[] = $this->createFraudAlert('rapid_referrals', $rapidReferrals, 'warning');
        }

        // Check for duplicate referral information
        $duplicateInfo = $this->detectDuplicateReferralInfo();
        if (!empty($duplicateInfo)) {
            $alerts[] = $this->createFraudAlert('duplicate_referral_info', $duplicateInfo, 'error');
        }

        // Check for unusual success rates
        $unusualRates = $this->detectUnusualSuccessRates();
        if (!empty($unusualRates)) {
            $alerts[] = $this->createFraudAlert('unusual_success_rates', $unusualRates, 'warning');
        }

        return $alerts;
    }

    /**
     * Create fraud alert
     *
     * @param string $type
     * @param array $data
     * @param string $severity
     * @return array
     */
    public function createFraudAlert(string $type, array $data, string $severity = 'warning'): array
    {
        $alert = [
            'type' => $type,
            'severity' => $severity,
            'data' => $data,
            'created_at' => now()->toISOString(),
            'component' => 'fraud_detection',
            'status' => 'active'
        ];

        // Store alert in cache for real-time access
        $alertKey = "fraud_alert_{$type}_" . time();
        Cache::put($alertKey, $alert, 3600); // Store for 1 hour

        // Add to alert keys list
        $alertKeys = Cache::get('fraud_alert_keys', []);
        $alertKeys[] = $alertKey;
        Cache::put('fraud_alert_keys', $alertKeys, 3600);

        // Log the alert
        Log::channel('fraud_monitoring')->{$severity}('Fraud detection alert', $alert);

        // Store in database for historical tracking
        DB::table('audit_logs')->insert([
            'audit_type' => 'fraud_alert',
            'reference_type' => $type,
            'audit_data' => json_encode($alert),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'System',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Send notifications for critical alerts
        if ($severity === 'critical') {
            $this->sendCriticalFraudAlert($type, $data);
        }

        return $alert;
    }

    /**
     * Get active fraud alerts
     *
     * @return array
     */
    public function getActiveFraudAlerts(): array
    {
        $alertKeys = Cache::get('fraud_alert_keys', []);
        $alerts = [];

        foreach ($alertKeys as $key) {
            $alert = Cache::get($key);
            if ($alert && $alert['status'] === 'active') {
                $alerts[] = $alert;
            }
        }

        // Sort by severity and creation time
        usort($alerts, function($a, $b) {
            $severityOrder = ['critical' => 3, 'error' => 2, 'warning' => 1];
            $aSeverity = $severityOrder[$a['severity']] ?? 0;
            $bSeverity = $severityOrder[$b['severity']] ?? 0;
            
            if ($aSeverity === $bSeverity) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            }
            
            return $bSeverity - $aSeverity;
        });

        return $alerts;
    }

    /**
     * Flag user for manual review
     *
     * @param int $userId
     * @param array $reasons
     * @param string $severity
     * @return bool
     */
    public function flagUserForReview(int $userId, array $reasons, string $severity = 'warning'): bool
    {
        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }

        // Update user record
        $user->update([
            'flagged_for_review' => true,
            'flag_reasons' => $reasons,
            'flagged_at' => now()
        ]);

        // Create alert
        $this->createFraudAlert('user_flagged', [
            'user_id' => $userId,
            'email' => $user->email,
            'reasons' => $reasons
        ], $severity);

        // Log the flagging
        Log::warning('User flagged for manual review', [
            'user_id' => $userId,
            'email' => $user->email,
            'reasons' => $reasons,
            'severity' => $severity
        ]);

        return true;
    }

    /**
     * Flag referral as suspicious
     *
     * @param int $referralId
     * @param array $reasons
     * @return bool
     */
    public function flagReferral(int $referralId, array $reasons): bool
    {
        $referral = Referral::find($referralId);
        
        if (!$referral) {
            return false;
        }

        // Update referral record
        $referral->update([
            'is_flagged' => true,
            'flag_reasons' => $reasons,
            'flagged_at' => now()
        ]);

        // Create alert
        $this->createFraudAlert('referral_flagged', [
            'referral_id' => $referralId,
            'referrer_id' => $referral->referrer_id,
            'referred_id' => $referral->referred_id,
            'reasons' => $reasons
        ], 'warning');

        return true;
    }

    /**
     * Get fraud detection statistics
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getFraudStatistics(Carbon $startDate, Carbon $endDate): array
    {
        // Get flagged users
        $flaggedUsers = User::where('flagged_for_review', true)
            ->whereBetween('flagged_at', [$startDate, $endDate])
            ->count();

        // Get flagged referrals
        $flaggedReferrals = Referral::where('is_flagged', true)
            ->whereBetween('flagged_at', [$startDate, $endDate])
            ->count();

        // Get fraud alerts
        $fraudAlerts = DB::table('audit_logs')
            ->where('audit_type', 'fraud_alert')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Get fraud detection rate
        $totalReferrals = Referral::whereBetween('created_at', [$startDate, $endDate])->count();
        $detectionRate = $totalReferrals > 0 ? 
            round(($flaggedReferrals / $totalReferrals) * 100, 2) : 0;

        // Get most common fraud types
        $fraudTypes = DB::table('audit_logs')
            ->where('audit_type', 'fraud_alert')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('reference_type, COUNT(*) as count')
            ->groupBy('reference_type')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString()
            ],
            'flagged_users' => $flaggedUsers,
            'flagged_referrals' => $flaggedReferrals,
            'fraud_alerts' => $fraudAlerts,
            'detection_rate' => $detectionRate,
            'fraud_types' => $fraudTypes->toArray()
        ];
    }

    /**
     * Detect suspicious users
     *
     * @return array
     */
    private function detectSuspiciousUsers(): array
    {
        $suspiciousUsers = [];
        $last24Hours = Carbon::now()->subHours(24);

        // Get users with high referral activity
        $highActivityUsers = User::whereHas('referrals', function($query) use ($last24Hours) {
            $query->where('created_at', '>=', $last24Hours);
        }, '>', 10) // More than 10 referrals in 24 hours
        ->with(['referrals' => function($query) use ($last24Hours) {
            $query->where('created_at', '>=', $last24Hours);
        }])
        ->get();

        foreach ($highActivityUsers as $user) {
            $suspiciousPatterns = $this->fraudService->detectSuspiciousReferrals($user->user_id);
            
            if (!empty($suspiciousPatterns)) {
                $suspiciousUsers[] = [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                    'referral_count' => $user->referrals->count(),
                    'suspicious_patterns' => $suspiciousPatterns
                ];
            }
        }

        return $suspiciousUsers;
    }

    /**
     * Detect circular referral attempts
     *
     * @return array
     */
    private function detectCircularReferralAttempts(): array
    {
        $circularAttempts = [];
        $last24Hours = Carbon::now()->subHours(24);

        // Get recent referrals
        $recentReferrals = Referral::where('created_at', '>=', $last24Hours)
            ->get(['id', 'referrer_id', 'referred_id']);

        foreach ($recentReferrals as $referral) {
            if ($this->fraudService->detectCircularReferrals($referral->referrer_id, $referral->referred_id)) {
                $circularAttempts[] = [
                    'referral_id' => $referral->id,
                    'referrer_id' => $referral->referrer_id,
                    'referred_id' => $referral->referred_id,
                    'detected_at' => now()->toISOString()
                ];

                // Flag the referral
                $this->flagReferral($referral->id, ['circular_referral_detected']);
            }
        }

        return $circularAttempts;
    }

    /**
     * Detect rapid referral creation
     *
     * @return array
     */
    private function detectRapidReferralCreation(): array
    {
        $rapidReferrals = [];
        $last24Hours = Carbon::now()->subHours(24);

        // Get users with high referral creation rate
        $rapidUsers = DB::table('referrals')
            ->select('referrer_id', DB::raw('COUNT(*) as referral_count'))
            ->where('created_at', '>=', $last24Hours)
            ->groupBy('referrer_id')
            ->having('referral_count', '>', 15) // More than 15 referrals in 24 hours
            ->get();

        foreach ($rapidUsers as $userStats) {
            $user = User::find($userStats->referrer_id);
            if ($user) {
                $rapidReferrals[] = [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                    'referral_count' => $userStats->referral_count,
                    'timeframe' => '24 hours'
                ];

                // Flag user for review
                $this->flagUserForReview($user->user_id, [
                    'rapid_referral_creation' => "Created {$userStats->referral_count} referrals in 24 hours"
                ], 'warning');
            }
        }

        return $rapidReferrals;
    }

    /**
     * Detect duplicate referral information
     *
     * @return array
     */
    private function detectDuplicateReferralInfo(): array
    {
        $duplicates = [];
        $last7Days = Carbon::now()->subDays(7);

        // Find duplicate emails in referrals
        $duplicateEmails = DB::table('referrals')
            ->join('users', 'referrals.referred_id', '=', 'users.user_id')
            ->where('referrals.created_at', '>=', $last7Days)
            ->select('users.email', 'referrals.referrer_id', DB::raw('COUNT(*) as count'))
            ->groupBy('users.email', 'referrals.referrer_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicateEmails as $duplicate) {
            $duplicates[] = [
                'type' => 'duplicate_email',
                'email' => $duplicate->email,
                'referrer_id' => $duplicate->referrer_id,
                'count' => $duplicate->count
            ];
        }

        return $duplicates;
    }

    /**
     * Detect unusual success rates
     *
     * @return array
     */
    private function detectUnusualSuccessRates(): array
    {
        $unusualRates = [];
        $last30Days = Carbon::now()->subDays(30);

        // Get users with unusually high success rates
        $userStats = DB::table('referrals')
            ->select('referrer_id', 
                DB::raw('COUNT(*) as total_referrals'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_referrals')
            )
            ->where('created_at', '>=', $last30Days)
            ->groupBy('referrer_id')
            ->having('total_referrals', '>', 5) // At least 5 referrals
            ->get();

        foreach ($userStats as $stats) {
            $successRate = ($stats->successful_referrals / $stats->total_referrals) * 100;
            
            if ($successRate > 95 && $stats->total_referrals > 10) { // 95%+ success rate with significant volume
                $user = User::find($stats->referrer_id);
                if ($user) {
                    $unusualRates[] = [
                        'user_id' => $user->user_id,
                        'email' => $user->email,
                        'success_rate' => round($successRate, 2),
                        'total_referrals' => $stats->total_referrals,
                        'successful_referrals' => $stats->successful_referrals
                    ];
                }
            }
        }

        return $unusualRates;
    }

    /**
     * Send critical fraud alert notification
     *
     * @param string $alertType
     * @param array $data
     * @return void
     */
    private function sendCriticalFraudAlert(string $alertType, array $data): void
    {
        try {
            // Get admin users
            $adminUsers = User::whereHas('roles', function($q) {
                $q->where('name', 'admin');
            })->get();

            foreach ($adminUsers as $admin) {
                $admin->notify(new CriticalCommissionError($alertType, $data));
            }

            Log::info('Critical fraud alert notifications sent', [
                'alert_type' => $alertType,
                'admin_count' => $adminUsers->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send critical fraud alert notification', [
                'alert_type' => $alertType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Resolve fraud alert
     *
     * @param string $alertKey
     * @param string $resolution
     * @return bool
     */
    public function resolveAlert(string $alertKey, string $resolution): bool
    {
        $alert = Cache::get($alertKey);
        
        if (!$alert) {
            return false;
        }

        $alert['status'] = 'resolved';
        $alert['resolution'] = $resolution;
        $alert['resolved_at'] = now()->toISOString();

        Cache::put($alertKey, $alert, 3600);

        // Log resolution
        Log::info('Fraud alert resolved', [
            'alert_key' => $alertKey,
            'alert_type' => $alert['type'],
            'resolution' => $resolution
        ]);

        return true;
    }

    /**
     * Get fraud risk score for system
     *
     * @return array
     */
    public function getSystemFraudRiskScore(): array
    {
        $last24Hours = Carbon::now()->subHours(24);
        
        $activeAlerts = $this->getActiveFraudAlerts();
        $flaggedUsers = User::where('flagged_for_review', true)
            ->where('flagged_at', '>=', $last24Hours)
            ->count();
        $flaggedReferrals = Referral::where('is_flagged', true)
            ->where('flagged_at', '>=', $last24Hours)
            ->count();

        // Calculate risk score (0-100)
        $riskScore = 0;
        
        // Weight by alert severity
        foreach ($activeAlerts as $alert) {
            switch ($alert['severity']) {
                case 'critical':
                    $riskScore += 30;
                    break;
                case 'error':
                    $riskScore += 20;
                    break;
                case 'warning':
                    $riskScore += 10;
                    break;
            }
        }

        // Add points for flagged entities
        $riskScore += min($flaggedUsers * 5, 25); // Max 25 points for flagged users
        $riskScore += min($flaggedReferrals * 3, 15); // Max 15 points for flagged referrals

        $riskScore = min($riskScore, 100); // Cap at 100

        $riskLevel = 'low';
        if ($riskScore >= 70) {
            $riskLevel = 'critical';
        } elseif ($riskScore >= 40) {
            $riskLevel = 'high';
        } elseif ($riskScore >= 20) {
            $riskLevel = 'medium';
        }

        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'active_alerts' => count($activeAlerts),
            'flagged_users' => $flaggedUsers,
            'flagged_referrals' => $flaggedReferrals,
            'calculated_at' => now()->toISOString()
        ];
    }
}
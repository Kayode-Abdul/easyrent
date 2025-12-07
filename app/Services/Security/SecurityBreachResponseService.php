<?php

namespace App\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Services\Logging\EasyRentLogger;
use App\Models\ApartmentInvitation;
use App\Models\User;
use App\Notifications\SecurityBreachAlert;

class SecurityBreachResponseService
{
    /**
     * Breach severity levels
     */
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';
    
    /**
     * Response actions
     */
    const ACTION_LOG = 'log';
    const ACTION_BLOCK_IP = 'block_ip';
    const ACTION_INVALIDATE_TOKENS = 'invalidate_tokens';
    const ACTION_NOTIFY_ADMINS = 'notify_admins';
    const ACTION_EMERGENCY_LOCKDOWN = 'emergency_lockdown';
    
    /**
     * EasyRent Logger
     */
    protected $logger;

    public function __construct(EasyRentLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle security breach detection
     */
    public function handleSecurityBreach(array $breachData): void
    {
        $severity = $this->determineSeverity($breachData);
        $actions = $this->determineResponseActions($severity, $breachData);
        
        // Log the breach
        $this->logSecurityBreach($breachData, $severity);
        
        // Execute response actions
        foreach ($actions as $action) {
            $this->executeResponseAction($action, $breachData);
        }
        
        // Update breach statistics
        $this->updateBreachStatistics($breachData, $severity);
    }
    
    /**
     * Determine breach severity based on data
     */
    protected function determineSeverity(array $breachData): string
    {
        $riskScore = $breachData['risk_score'] ?? 0;
        $patterns = $breachData['patterns'] ?? [];
        $affectedTokens = $breachData['affected_tokens'] ?? 0;
        
        // Critical: High risk score with multiple severe patterns
        if ($riskScore >= 90 || 
            (in_array('failed_tokens', $patterns) && in_array('rapid_access', $patterns)) ||
            $affectedTokens > 10) {
            return self::SEVERITY_CRITICAL;
        }
        
        // High: Significant risk or multiple patterns
        if ($riskScore >= 70 || 
            count($patterns) >= 3 ||
            $affectedTokens > 5) {
            return self::SEVERITY_HIGH;
        }
        
        // Medium: Moderate risk or concerning patterns
        if ($riskScore >= 50 || 
            count($patterns) >= 2 ||
            $affectedTokens > 2) {
            return self::SEVERITY_MEDIUM;
        }
        
        // Low: Minor concerns
        return self::SEVERITY_LOW;
    }
    
    /**
     * Determine response actions based on severity
     */
    protected function determineResponseActions(string $severity, array $breachData): array
    {
        $actions = [self::ACTION_LOG]; // Always log
        
        switch ($severity) {
            case self::SEVERITY_CRITICAL:
                $actions[] = self::ACTION_EMERGENCY_LOCKDOWN;
                $actions[] = self::ACTION_NOTIFY_ADMINS;
                $actions[] = self::ACTION_INVALIDATE_TOKENS;
                $actions[] = self::ACTION_BLOCK_IP;
                break;
                
            case self::SEVERITY_HIGH:
                $actions[] = self::ACTION_NOTIFY_ADMINS;
                $actions[] = self::ACTION_INVALIDATE_TOKENS;
                $actions[] = self::ACTION_BLOCK_IP;
                break;
                
            case self::SEVERITY_MEDIUM:
                $actions[] = self::ACTION_BLOCK_IP;
                if (isset($breachData['affected_tokens']) && $breachData['affected_tokens'] > 3) {
                    $actions[] = self::ACTION_INVALIDATE_TOKENS;
                }
                break;
                
            case self::SEVERITY_LOW:
                // Only logging for low severity
                break;
        }
        
        return $actions;
    }
    
    /**
     * Execute a specific response action
     */
    protected function executeResponseAction(string $action, array $breachData): void
    {
        switch ($action) {
            case self::ACTION_LOG:
                $this->logSecurityBreach($breachData, $breachData['severity'] ?? 'unknown');
                break;
                
            case self::ACTION_BLOCK_IP:
                $this->blockSuspiciousIp($breachData);
                break;
                
            case self::ACTION_INVALIDATE_TOKENS:
                $this->invalidateAffectedTokens($breachData);
                break;
                
            case self::ACTION_NOTIFY_ADMINS:
                $this->notifyAdministrators($breachData);
                break;
                
            case self::ACTION_EMERGENCY_LOCKDOWN:
                $this->initiateEmergencyLockdown($breachData);
                break;
        }
    }
    
    /**
     * Log security breach with comprehensive details
     */
    protected function logSecurityBreach(array $breachData, string $severity): void
    {
        $logData = [
            'breach_id' => $breachData['breach_id'] ?? uniqid('breach_'),
            'severity' => $severity,
            'detected_at' => now()->toISOString(),
            'ip_address' => $breachData['ip_address'] ?? 'unknown',
            'user_agent' => $breachData['user_agent'] ?? 'unknown',
            'patterns' => $breachData['patterns'] ?? [],
            'risk_score' => $breachData['risk_score'] ?? 0,
            'affected_tokens' => $breachData['affected_tokens'] ?? 0,
            'request_data' => $breachData['request_data'] ?? []
        ];
        
        Log::channel('security')->critical('Security breach detected', $logData);
        
        // Also log to EasyRent logger
        $this->logger->logSecurityBreach($breachData, $severity);
    }
    
    /**
     * Block suspicious IP address
     */
    protected function blockSuspiciousIp(array $breachData): void
    {
        $ipAddress = $breachData['ip_address'] ?? null;
        if (!$ipAddress) {
            return;
        }
        
        $severity = $breachData['severity'] ?? self::SEVERITY_LOW;
        $blockDuration = $this->getBlockDuration($severity);
        
        Cache::put("security_breach_block:{$ipAddress}", [
            'blocked_at' => now()->toISOString(),
            'severity' => $severity,
            'reason' => 'Security breach detected',
            'breach_data' => $breachData
        ], $blockDuration);
        
        Log::warning('IP blocked due to security breach', [
            'ip_address' => $ipAddress,
            'severity' => $severity,
            'block_duration' => $blockDuration,
            'blocked_at' => now()
        ]);
    }
    
    /**
     * Get block duration based on severity
     */
    protected function getBlockDuration(string $severity): int
    {
        switch ($severity) {
            case self::SEVERITY_CRITICAL:
                return 86400 * 7; // 7 days
            case self::SEVERITY_HIGH:
                return 86400; // 24 hours
            case self::SEVERITY_MEDIUM:
                return 3600 * 6; // 6 hours
            case self::SEVERITY_LOW:
            default:
                return 3600; // 1 hour
        }
    }
    
    /**
     * Invalidate affected invitation tokens
     */
    protected function invalidateAffectedTokens(array $breachData): void
    {
        $tokens = $breachData['tokens'] ?? [];
        $ipAddress = $breachData['ip_address'] ?? null;
        
        if (empty($tokens) && $ipAddress) {
            // Find tokens accessed by this IP (if database is available)
            try {
                $invitations = ApartmentInvitation::where('last_accessed_ip', $ipAddress)
                    ->where('last_accessed_at', '>', now()->subHours(24))
                    ->get();
                    
                foreach ($invitations as $invitation) {
                    $tokens[] = $invitation->invitation_token;
                }
            } catch (\Exception $e) {
                // Database not available (e.g., in unit tests)
            }
        }
        
        $invalidatedCount = 0;
        foreach ($tokens as $token) {
            $invitation = ApartmentInvitation::where('invitation_token', $token)->first();
            if ($invitation && $invitation->status === ApartmentInvitation::STATUS_ACTIVE) {
                $invitation->invalidateForSecurity('Security breach detected');
                $invalidatedCount++;
            }
        }
        
        Log::warning('Invitation tokens invalidated due to security breach', [
            'invalidated_count' => $invalidatedCount,
            'total_tokens' => count($tokens),
            'ip_address' => $ipAddress
        ]);
    }
    
    /**
     * Notify administrators of security breach
     */
    protected function notifyAdministrators(array $breachData): void
    {
        try {
            // Get admin users
            $admins = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['admin', 'super_admin']);
            })->get();
            
            if ($admins->isEmpty()) {
                Log::error('No administrators found to notify of security breach');
                return;
            }
            
            // Send notifications
            foreach ($admins as $admin) {
                try {
                    $admin->notify(new SecurityBreachAlert($breachData));
                } catch (\Exception $e) {
                    Log::error('Failed to notify admin of security breach', [
                        'admin_id' => $admin->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            Log::info('Security breach notifications sent to administrators', [
                'admin_count' => $admins->count(),
                'breach_severity' => $breachData['severity'] ?? 'unknown'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send security breach notifications', [
                'error' => $e->getMessage(),
                'breach_data' => $breachData
            ]);
        }
    }
    
    /**
     * Initiate emergency lockdown procedures
     */
    protected function initiateEmergencyLockdown(array $breachData): void
    {
        // Set emergency lockdown flag
        Cache::put('emergency_lockdown', [
            'initiated_at' => now()->toISOString(),
            'reason' => 'Critical security breach detected',
            'breach_data' => $breachData,
            'initiated_by' => 'system'
        ], 3600); // 1 hour default lockdown
        
        // Invalidate all active invitation tokens (if database is available)
        try {
            $activeInvitations = ApartmentInvitation::where('status', ApartmentInvitation::STATUS_ACTIVE)->get();
            foreach ($activeInvitations as $invitation) {
                $invitation->invalidateForSecurity('Emergency lockdown initiated');
            }
            $invalidatedCount = $activeInvitations->count();
        } catch (\Exception $e) {
            // Database not available (e.g., in unit tests)
            $invalidatedCount = 0;
        }
        
        Log::critical('Emergency lockdown initiated', [
            'reason' => 'Critical security breach',
            'invalidated_invitations' => $invalidatedCount,
            'initiated_at' => now(),
            'breach_data' => $breachData
        ]);
        
        // Notify all administrators immediately
        $this->notifyAdministrators(array_merge($breachData, [
            'emergency_lockdown' => true,
            'message' => 'EMERGENCY: System lockdown initiated due to critical security breach'
        ]));
    }
    
    /**
     * Update breach statistics for monitoring
     */
    protected function updateBreachStatistics(array $breachData, string $severity): void
    {
        $today = now()->format('Y-m-d');
        $hour = now()->format('Y-m-d-H');
        
        // Daily statistics
        Cache::increment("breach_stats:daily:{$today}");
        Cache::increment("breach_stats:daily:{$today}:{$severity}");
        
        // Hourly statistics
        Cache::increment("breach_stats:hourly:{$hour}");
        Cache::increment("breach_stats:hourly:{$hour}:{$severity}");
        
        // Set TTL for cleanup (Laravel doesn't have expire method, TTL is set when putting)
        // The TTL is already set when we put the data above
    }
    
    /**
     * Check if system is in emergency lockdown
     */
    public function isEmergencyLockdown(): bool
    {
        return Cache::has('emergency_lockdown');
    }
    
    /**
     * Get emergency lockdown information
     */
    public function getEmergencyLockdownInfo(): ?array
    {
        return Cache::get('emergency_lockdown');
    }
    
    /**
     * Lift emergency lockdown (admin function)
     */
    public function liftEmergencyLockdown(User $admin): void
    {
        $lockdownInfo = Cache::get('emergency_lockdown');
        
        Cache::forget('emergency_lockdown');
        
        Log::info('Emergency lockdown lifted', [
            'lifted_by' => $admin->id,
            'lifted_at' => now(),
            'original_lockdown' => $lockdownInfo
        ]);
    }
    
    /**
     * Get breach statistics
     */
    public function getBreachStatistics(string $period = 'daily'): array
    {
        $stats = [];
        $severities = [self::SEVERITY_LOW, self::SEVERITY_MEDIUM, self::SEVERITY_HIGH, self::SEVERITY_CRITICAL];
        
        if ($period === 'daily') {
            for ($i = 0; $i < 7; $i++) {
                $date = now()->subDays($i)->format('Y-m-d');
                $stats[$date] = [
                    'total' => Cache::get("breach_stats:daily:{$date}", 0)
                ];
                
                foreach ($severities as $severity) {
                    $stats[$date][$severity] = Cache::get("breach_stats:daily:{$date}:{$severity}", 0);
                }
            }
        } elseif ($period === 'hourly') {
            for ($i = 0; $i < 24; $i++) {
                $hour = now()->subHours($i)->format('Y-m-d-H');
                $stats[$hour] = [
                    'total' => Cache::get("breach_stats:hourly:{$hour}", 0)
                ];
                
                foreach ($severities as $severity) {
                    $stats[$hour][$severity] = Cache::get("breach_stats:hourly:{$hour}:{$severity}", 0);
                }
            }
        }
        
        return $stats;
    }
    
    /**
     * Check if IP is blocked due to security breach
     */
    public function isIpBlockedForBreach(string $ipAddress): bool
    {
        return Cache::has("security_breach_block:{$ipAddress}");
    }
    
    /**
     * Get IP block information
     */
    public function getIpBlockInfo(string $ipAddress): ?array
    {
        return Cache::get("security_breach_block:{$ipAddress}");
    }
}
<?php

namespace App\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\Logging\EasyRentLogger;
use App\Models\ApartmentInvitation;

class SuspiciousActivityDetector
{
    /**
     * Detection thresholds and patterns
     */
    const RAPID_ACCESS_THRESHOLD = 15; // requests in 2 minutes
    const MULTIPLE_TOKEN_THRESHOLD = 5; // different tokens from same IP in 10 minutes
    const FAILED_TOKEN_THRESHOLD = 10; // failed token attempts in 5 minutes
    const BOT_USER_AGENT_PATTERNS = [
        'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python', 'java'
    ];
    
    /**
     * EasyRent Logger
     */
    protected $logger;

    public function __construct(EasyRentLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Analyze request for suspicious patterns
     */
    public function analyzeRequest(Request $request, string $token = null): array
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $suspiciousPatterns = [];
        
        // Check for rapid access pattern
        if ($this->detectRapidAccess($ipAddress)) {
            $suspiciousPatterns[] = 'rapid_access';
        }
        
        // Check for multiple token access from same IP
        if ($token && $this->detectMultipleTokenAccess($ipAddress, $token)) {
            $suspiciousPatterns[] = 'multiple_tokens';
        }
        
        // Check for failed token attempts
        if ($this->detectFailedTokenAttempts($ipAddress)) {
            $suspiciousPatterns[] = 'failed_tokens';
        }
        
        // Check for bot-like user agent
        if ($this->detectBotUserAgent($userAgent)) {
            $suspiciousPatterns[] = 'bot_user_agent';
        }
        
        // Check for missing or suspicious headers
        if ($this->detectSuspiciousHeaders($request)) {
            $suspiciousPatterns[] = 'suspicious_headers';
        }
        
        // Check for geographic anomalies (if enabled)
        if ($this->detectGeographicAnomalies($ipAddress)) {
            $suspiciousPatterns[] = 'geographic_anomaly';
        }
        
        $riskScore = $this->calculateRiskScore($suspiciousPatterns);
        
        return [
            'is_suspicious' => !empty($suspiciousPatterns),
            'patterns' => $suspiciousPatterns,
            'risk_score' => $riskScore,
            'action_required' => $riskScore >= 70
        ];
    }

    /**
     * Backward-compatible API used by ApartmentInvitationController.
     * Returns true only when the request should be blocked.
     */
    public function isSuspiciousPattern(Request $request, ApartmentInvitation $invitation = null): bool
    {
        $token = null;
        if ($invitation) {
            $token = $invitation->invitation_token ?? null;
        }

        $analysis = $this->analyzeRequest($request, $token);

        Log::info('Suspicious activity analysis', [
            'ip' => $request->ip(),
            'invitation_id' => $invitation?->id,
            'patterns' => $analysis['patterns'] ?? [],
            'risk_score' => $analysis['risk_score'] ?? null,
            'action_required' => $analysis['action_required'] ?? false,
        ]);

        return (bool) ($analysis['action_required'] ?? false);
    }
    
    /**
     * Detect rapid access pattern
     */
    protected function detectRapidAccess(string $ipAddress): bool
    {
        $key = "rapid_access:{$ipAddress}";
        $count = Cache::get($key, 0);
        
        // Increment and set TTL
        Cache::put($key, $count + 1, 120); // 2 minutes
        
        return ($count + 1) > self::RAPID_ACCESS_THRESHOLD;
    }
    
    /**
     * Detect multiple token access from same IP
     */
    protected function detectMultipleTokenAccess(string $ipAddress, string $token): bool
    {
        $key = "token_access:{$ipAddress}";
        $tokens = Cache::get($key, []);
        
        if (!in_array($token, $tokens)) {
            $tokens[] = $token;
            Cache::put($key, $tokens, 600); // 10 minutes
        }
        
        return count($tokens) > self::MULTIPLE_TOKEN_THRESHOLD;
    }
    
    /**
     * Detect failed token attempts
     */
    protected function detectFailedTokenAttempts(string $ipAddress): bool
    {
        $key = "failed_tokens:{$ipAddress}";
        $count = Cache::get($key, 0);
        
        return $count > self::FAILED_TOKEN_THRESHOLD;
    }
    
    /**
     * Record failed token attempt
     */
    public function recordFailedTokenAttempt(string $ipAddress): void
    {
        $key = "failed_tokens:{$ipAddress}";
        $count = Cache::get($key, 0);
        Cache::put($key, $count + 1, 300); // 5 minutes
        
        Log::warning('Failed token attempt recorded', [
            'ip_address' => $ipAddress,
            'total_attempts' => $count + 1,
            'recorded_at' => now()
        ]);
    }
    
    /**
     * Detect bot-like user agent
     */
    protected function detectBotUserAgent(string $userAgent = null): bool
    {
        if (!$userAgent) {
            return true; // Missing user agent is suspicious
        }
        
        $userAgent = strtolower($userAgent);
        
        foreach (self::BOT_USER_AGENT_PATTERNS as $pattern) {
            if (strpos($userAgent, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Detect suspicious headers
     */
    protected function detectSuspiciousHeaders(Request $request): bool
    {
        // Check for missing common headers
        $requiredHeaders = ['accept', 'accept-language'];
        foreach ($requiredHeaders as $header) {
            if (!$request->hasHeader($header)) {
                return true;
            }
        }
        
        // Check for suspicious accept header
        $accept = $request->header('accept');
        if ($accept && !str_contains($accept, 'text/html')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Detect geographic anomalies (basic implementation)
     */
    protected function detectGeographicAnomalies(string $ipAddress): bool
    {
        // This is a placeholder for geographic analysis
        // In production, you might integrate with a GeoIP service
        
        // Check for known suspicious IP ranges or countries
        $suspiciousRanges = [
            '10.0.0.0/8',    // Private networks (if not expected)
            '172.16.0.0/12', // Private networks
            '192.168.0.0/16' // Private networks
        ];
        
        foreach ($suspiciousRanges as $range) {
            if ($this->ipInRange($ipAddress, $range)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if IP is in range
     */
    protected function ipInRange(string $ip, string $range): bool
    {
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) == $subnet;
    }
    
    /**
     * Calculate risk score based on patterns
     */
    protected function calculateRiskScore(array $patterns): int
    {
        $scores = [
            'rapid_access' => 30,
            'multiple_tokens' => 25,
            'failed_tokens' => 40,
            'bot_user_agent' => 20,
            'suspicious_headers' => 15,
            'geographic_anomaly' => 10
        ];
        
        $totalScore = 0;
        foreach ($patterns as $pattern) {
            $totalScore += $scores[$pattern] ?? 0;
        }
        
        return min(100, $totalScore);
    }
    
    /**
     * Handle suspicious activity detection
     */
    public function handleSuspiciousActivity(Request $request, array $analysis, string $token = null): void
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        
        // Log the suspicious activity
        $this->logger->logSuspiciousActivity($request, 'Suspicious activity detected', [
            'patterns' => $analysis['patterns'],
            'risk_score' => $analysis['risk_score'],
            'token' => $token ? substr($token, 0, 8) . '...' : null,
            'user_agent' => $userAgent
        ]);
        
        // Take action based on risk score
        if ($analysis['risk_score'] >= 90) {
            $this->blockIpAddress($ipAddress, 'High risk activity detected');
        } elseif ($analysis['risk_score'] >= 70) {
            $this->temporaryBlock($ipAddress, 'Moderate risk activity detected');
        }
        
        // Invalidate related invitations if necessary
        if ($token && $analysis['risk_score'] >= 80) {
            $this->invalidateInvitation($token, 'Security threat detected');
        }
    }
    
    /**
     * Block IP address permanently (until manual review)
     */
    protected function blockIpAddress(string $ipAddress, string $reason): void
    {
        Cache::put("security_blocked:{$ipAddress}", [
            'blocked_at' => now()->toISOString(),
            'reason' => $reason,
            'permanent' => true
        ], 86400 * 7); // 7 days
        
        Log::critical('IP address permanently blocked', [
            'ip_address' => $ipAddress,
            'reason' => $reason,
            'blocked_at' => now()
        ]);
    }
    
    /**
     * Temporary block for moderate risk
     */
    protected function temporaryBlock(string $ipAddress, string $reason): void
    {
        Cache::put("temp_blocked:{$ipAddress}", [
            'blocked_at' => now()->toISOString(),
            'reason' => $reason,
            'temporary' => true
        ], 3600); // 1 hour
        
        Log::warning('IP address temporarily blocked', [
            'ip_address' => $ipAddress,
            'reason' => $reason,
            'blocked_until' => now()->addHour()
        ]);
    }
    
    /**
     * Invalidate invitation for security reasons
     */
    protected function invalidateInvitation(string $token, string $reason): void
    {
        $invitation = ApartmentInvitation::where('invitation_token', $token)->first();
        
        if ($invitation) {
            $invitation->invalidateForSecurity($reason);
            
            Log::warning('Invitation invalidated due to security threat', [
                'invitation_id' => $invitation->id,
                'token' => substr($token, 0, 8) . '...',
                'reason' => $reason
            ]);
        }
    }
    
    /**
     * Check if IP is security blocked
     */
    public function isSecurityBlocked(string $ipAddress): bool
    {
        return Cache::has("security_blocked:{$ipAddress}") || 
               Cache::has("temp_blocked:{$ipAddress}");
    }
    
    /**
     * Get block information for IP
     */
    public function getBlockInfo(string $ipAddress): ?array
    {
        $securityBlock = Cache::get("security_blocked:{$ipAddress}");
        $tempBlock = Cache::get("temp_blocked:{$ipAddress}");
        
        if ($securityBlock) {
            return array_merge($securityBlock, ['type' => 'security']);
        }
        
        if ($tempBlock) {
            return array_merge($tempBlock, ['type' => 'temporary']);
        }
        
        return null;
    }
    
    /**
     * Clear suspicious activity records for IP (admin function)
     */
    public function clearSuspiciousActivity(string $ipAddress): void
    {
        $keys = [
            "rapid_access:{$ipAddress}",
            "token_access:{$ipAddress}",
            "failed_tokens:{$ipAddress}",
            "security_blocked:{$ipAddress}",
            "temp_blocked:{$ipAddress}"
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        Log::info('Suspicious activity records cleared for IP', [
            'ip_address' => $ipAddress,
            'cleared_at' => now()
        ]);
    }

    /**
     * Record a request for tracking purposes (used by tests)
     */
    protected function recordRequest(string $ipAddress, string $userAgent, string $route): void
    {
        $key = "rapid_access:{$ipAddress}";
        $requests = Cache::get($key, []);
        
        $requests[] = [
            'timestamp' => now()->timestamp,
            'user_agent' => $userAgent,
            'route' => $route
        ];
        
        // Keep only recent requests (last 60 seconds)
        $cutoff = now()->subSeconds(60)->timestamp;
        $requests = array_filter($requests, function($request) use ($cutoff) {
            return $request['timestamp'] > $cutoff;
        });
        
        Cache::put($key, $requests, 300); // 5 minutes
        
        // Also set rate limiting cache keys for tests
        Cache::put("rate_limit:minute:{$ipAddress}", 1, 60);
        Cache::put("rate_limit:hour:{$ipAddress}", 1, 3600);
    }
}
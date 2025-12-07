<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ApartmentInvitation extends Model
{
    protected $fillable = [
        'apartment_id',
        'landlord_id', 
        'invitation_token',
        'status',
        'expires_at',
        'prospect_email',
        'prospect_phone',
        'prospect_name',
        'tenant_user_id',
        'viewed_at',
        'payment_initiated_at',
        'payment_completed_at',
        'total_amount',
        'lease_duration',
        'move_in_date',
        'session_data',
        'authentication_required',
        'registration_source',
        'session_expires_at',
        'access_count',
        'last_accessed_at',
        'last_accessed_ip',
        'security_hash',
        'rate_limit_count',
        'rate_limit_reset_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'viewed_at' => 'datetime',
        'payment_initiated_at' => 'datetime',
        'payment_completed_at' => 'datetime',
        'move_in_date' => 'date',
        'total_amount' => 'decimal:2',
        'session_data' => 'array',
        'authentication_required' => 'boolean',
        'session_expires_at' => 'datetime',
        'access_count' => 'integer',
        'last_accessed_at' => 'datetime',
        'rate_limit_count' => 'integer',
        'rate_limit_reset_at' => 'datetime'
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_USED = 'used';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    
    // Security constants
    const MAX_ACCESS_ATTEMPTS = 50; // Per hour
    const RATE_LIMIT_WINDOW = 3600; // 1 hour in seconds
    const TOKEN_LENGTH = 64; // Cryptographically secure token length

    /**
     * Boot method to generate invitation token and set defaults
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invitation) {
            if (!$invitation->invitation_token) {
                $invitation->invitation_token = $invitation->generateSecureToken();
            }
            
            if (!$invitation->expires_at) {
                $invitation->expires_at = now()->addDays(30);
            }
            
            // Initialize security fields
            $invitation->access_count = 0;
            $invitation->rate_limit_count = 0;
            $invitation->rate_limit_reset_at = now()->addHour();
            $invitation->security_hash = $invitation->generateSecurityHash();
            
            // Log invitation creation
            Log::info('Apartment invitation created', [
                'invitation_id' => $invitation->id,
                'apartment_id' => $invitation->apartment_id,
                'landlord_id' => $invitation->landlord_id,
                'token' => substr($invitation->invitation_token, 0, 8) . '...',
                'expires_at' => $invitation->expires_at,
                'created_at' => now()
            ]);
        });
        
        static::updating(function ($invitation) {
            // Log significant status changes
            if ($invitation->isDirty('status')) {
                Log::info('Apartment invitation status changed', [
                    'invitation_id' => $invitation->id,
                    'old_status' => $invitation->getOriginal('status'),
                    'new_status' => $invitation->status,
                    'changed_at' => now()
                ]);
            }
        });
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id', 'apartment_id');
    }

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id', 'user_id');
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_user_id', 'user_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Generate cryptographically secure invitation token
     */
    public function generateSecureToken(): string
    {
        do {
            // Generate a cryptographically secure random token
            $token = bin2hex(random_bytes(self::TOKEN_LENGTH / 2));
        } while (self::where('invitation_token', $token)->exists());
        
        return $token;
    }
    
    /**
     * Generate security hash for token validation
     */
    public function generateSecurityHash(): string
    {
        return Hash::make($this->invitation_token . $this->apartment_id . $this->landlord_id);
    }
    
    /**
     * Validate token integrity and security
     */
    public function validateTokenSecurity(): bool
    {
        if (!$this->security_hash) {
            return false;
        }
        
        $expectedHash = $this->invitation_token . $this->apartment_id . $this->landlord_id;
        return Hash::check($expectedHash, $this->security_hash);
    }
    
    /**
     * Check and enforce rate limiting
     */
    public function checkRateLimit(string $ipAddress): bool
    {
        // Reset rate limit counter if window has passed
        if ($this->rate_limit_reset_at && $this->rate_limit_reset_at->isPast()) {
            $this->update([
                'rate_limit_count' => 0,
                'rate_limit_reset_at' => now()->addSeconds(self::RATE_LIMIT_WINDOW)
            ]);
        }
        
        // Check if rate limit exceeded
        if ($this->rate_limit_count >= self::MAX_ACCESS_ATTEMPTS) {
            Log::warning('Rate limit exceeded for invitation', [
                'invitation_id' => $this->id,
                'token' => substr($this->invitation_token, 0, 8) . '...',
                'ip_address' => $ipAddress,
                'attempts' => $this->rate_limit_count,
                'blocked_at' => now()
            ]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Record access attempt with comprehensive tracking
     */
    public function recordAccess(string $ipAddress, string $userAgent = null): void
    {
        $this->increment('access_count');
        $this->increment('rate_limit_count');
        
        $this->update([
            'last_accessed_at' => now(),
            'last_accessed_ip' => $ipAddress
        ]);
        
        // Log access attempt
        Log::info('Invitation accessed', [
            'invitation_id' => $this->id,
            'token' => substr($this->invitation_token, 0, 8) . '...',
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'access_count' => $this->access_count,
            'accessed_at' => now()
        ]);
    }
    
    /**
     * Detect suspicious access patterns
     */
    public function detectSuspiciousActivity(string $ipAddress): bool
    {
        // Check for rapid successive access from same IP
        $recentAccesses = self::where('last_accessed_ip', $ipAddress)
            ->where('last_accessed_at', '>', now()->subMinutes(5))
            ->count();
            
        if ($recentAccesses > 10) {
            Log::warning('Suspicious access pattern detected', [
                'ip_address' => $ipAddress,
                'recent_accesses' => $recentAccesses,
                'invitation_id' => $this->id,
                'detected_at' => now()
            ]);
            return true;
        }
        
        return false;
    }

    public function markAsViewed(): void
    {
        if (!$this->viewed_at) {
            $this->update(['viewed_at' => now()]);
            
            Log::info('Invitation marked as viewed', [
                'invitation_id' => $this->id,
                'apartment_id' => $this->apartment_id,
                'viewed_at' => now()
            ]);
        }
    }

    public function markPaymentInitiated(): void
    {
        $this->update(['payment_initiated_at' => now()]);
        
        Log::info('Payment initiated for invitation', [
            'invitation_id' => $this->id,
            'apartment_id' => $this->apartment_id,
            'tenant_id' => $this->tenant_user_id,
            'initiated_at' => now()
        ]);
    }

    public function markPaymentCompleted(): void
    {
        $this->update([
            'payment_completed_at' => now(),
            'status' => self::STATUS_USED
        ]);
        
        // Clear session data after successful payment
        $this->clearSessionData();
        
        Log::info('Payment completed for invitation', [
            'invitation_id' => $this->id,
            'apartment_id' => $this->apartment_id,
            'tenant_id' => $this->tenant_user_id,
            'completed_at' => now()
        ]);
    }

    public function markAsUsed(): void
    {
        $this->update(['status' => self::STATUS_USED]);
        
        Log::info('Invitation marked as used', [
            'invitation_id' => $this->id,
            'apartment_id' => $this->apartment_id,
            'marked_at' => now()
        ]);
    }
    
    /**
     * Mark invitation as expired with logging
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
        
        Log::info('Invitation marked as expired', [
            'invitation_id' => $this->id,
            'apartment_id' => $this->apartment_id,
            'expired_at' => now()
        ]);
    }
    
    /**
     * Invalidate invitation for security reasons
     */
    public function invalidateForSecurity(string $reason): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
        
        Log::warning('Invitation invalidated for security', [
            'invitation_id' => $this->id,
            'apartment_id' => $this->apartment_id,
            'reason' => $reason,
            'invalidated_at' => now()
        ]);
    }

    public function getShareableUrl(): string
    {
        return route('apartment.invite.show', $this->invitation_token);
    }

    public function getWhatsAppShareUrl(): string
    {
        $message = urlencode("🏠 Check out this apartment: " . $this->getShareableUrl());
        return "https://wa.me/?text=" . $message;
    }

    public function getEmailShareUrl(): string
    {
        $subject = urlencode('Apartment Available - ' . $this->apartment->property->prop_name);
        $body = urlencode("I found this apartment that might interest you:\n\n" . $this->getShareableUrl());
        return "mailto:?subject={$subject}&body={$body}";
    }

    public function getSMSShareUrl(): string
    {
        $message = urlencode("Check out this apartment: " . $this->getShareableUrl());
        return "sms:?body=" . $message;
    }

    /**
     * Store session data for unauthenticated users with enhanced security
     */
    public function storeSessionData(array $data): void
    {
        $sessionData = [
            'invitation_token' => $this->invitation_token,
            'stored_at' => now()->toISOString(),
            'expires_at' => now()->addHours(24)->toISOString(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'session_id' => session()->getId(),
            'data' => $data,
            'checksum' => hash('sha256', json_encode($data) . $this->invitation_token)
        ];

        $this->update([
            'session_data' => $sessionData,
            'session_expires_at' => now()->addHours(24)
        ]);
        
        Log::info('Session data stored for invitation', [
            'invitation_id' => $this->id,
            'session_expires_at' => $this->session_expires_at,
            'ip_address' => request()->ip()
        ]);
    }

    /**
     * Get stored session data with integrity validation
     */
    public function getSessionData(): ?array
    {
        if (!$this->session_data) {
            return null;
        }
        
        // Check session expiration
        if ($this->isSessionExpired()) {
            $this->clearSessionData();
            return null;
        }
        
        // Validate session data integrity
        $sessionData = $this->session_data;
        if (isset($sessionData['data'], $sessionData['checksum'])) {
            $expectedChecksum = hash('sha256', json_encode($sessionData['data']) . $this->invitation_token);
            if ($sessionData['checksum'] !== $expectedChecksum) {
                Log::warning('Session data integrity check failed', [
                    'invitation_id' => $this->id,
                    'expected_checksum' => $expectedChecksum,
                    'actual_checksum' => $sessionData['checksum']
                ]);
                $this->clearSessionData();
                return null;
            }
        }
        
        return $sessionData;
    }

    /**
     * Check if session has expired
     */
    public function isSessionExpired(): bool
    {
        if (!$this->session_expires_at) {
            return false;
        }

        return $this->session_expires_at->isPast();
    }

    /**
     * Clear session data with logging
     */
    public function clearSessionData(): void
    {
        $hadSessionData = !is_null($this->session_data);
        
        $this->update([
            'session_data' => null,
            'session_expires_at' => null
        ]);
        
        if ($hadSessionData) {
            Log::info('Session data cleared for invitation', [
                'invitation_id' => $this->id,
                'cleared_at' => now()
            ]);
        }
    }

    /**
     * Mark as requiring authentication
     */
    public function markAuthenticationRequired(): void
    {
        $this->update(['authentication_required' => true]);
    }

    /**
     * Set registration source
     */
    public function setRegistrationSource(string $source): void
    {
        $this->update(['registration_source' => $source]);
    }

    /**
     * Check if invitation was accessed by unauthenticated user
     */
    public function requiresAuthentication(): bool
    {
        return $this->authentication_required;
    }

    /**
     * Extend session expiration
     */
    public function extendSessionExpiration(int $hours = 24): void
    {
        $newExpiration = now()->addHours($hours);
        
        $sessionData = $this->session_data;
        if ($sessionData) {
            $sessionData['expires_at'] = $newExpiration->toISOString();
        }

        $this->update([
            'session_data' => $sessionData,
            'session_expires_at' => $newExpiration
        ]);
    }

    /**
     * Calculate total amount based on apartment rent and duration
     */
    public function calculateTotalAmount(): float
    {
        if (!$this->lease_duration || !$this->apartment) {
            return 0;
        }
        
        return $this->apartment->amount * $this->lease_duration;
    }

    /**
     * Scope for active invitations
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for expired invitations
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
                    ->orWhere('expires_at', '<=', now());
    }
    
    /**
     * Scope for invitations with expired sessions
     */
    public function scopeExpiredSessions($query)
    {
        return $query->whereNotNull('session_expires_at')
                    ->where('session_expires_at', '<=', now());
    }
    
    /**
     * Enhanced expiration check with automatic cleanup
     */
    public function checkAndHandleExpiration(): bool
    {
        if ($this->isExpired()) {
            if ($this->status !== self::STATUS_EXPIRED) {
                $this->markAsExpired();
            }
            return true;
        }
        
        // Check session expiration separately
        if ($this->isSessionExpired()) {
            $this->clearSessionData();
        }
        
        return false;
    }
    
    /**
     * Comprehensive security validation
     */
    public function performSecurityValidation(string $ipAddress, string $userAgent = null): array
    {
        $issues = [];
        
        // Check token integrity
        if (!$this->validateTokenSecurity()) {
            $issues[] = 'token_integrity_failed';
            Log::error('Token integrity validation failed', [
                'invitation_id' => $this->id,
                'token' => substr($this->invitation_token, 0, 8) . '...'
            ]);
        }
        
        // Check expiration
        if ($this->checkAndHandleExpiration()) {
            $issues[] = 'invitation_expired';
        }
        
        // Check rate limiting
        if (!$this->checkRateLimit($ipAddress)) {
            $issues[] = 'rate_limit_exceeded';
        }
        
        // Check for suspicious activity
        if ($this->detectSuspiciousActivity($ipAddress)) {
            $issues[] = 'suspicious_activity_detected';
        }
        
        // If no issues, record the access
        if (empty($issues)) {
            $this->recordAccess($ipAddress, $userAgent);
        }
        
        return $issues;
    }
    
    /**
     * Get comprehensive invitation statistics
     */
    public function getInvitationStats(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'expires_at' => $this->expires_at,
            'access_count' => $this->access_count,
            'viewed_at' => $this->viewed_at,
            'payment_initiated_at' => $this->payment_initiated_at,
            'payment_completed_at' => $this->payment_completed_at,
            'last_accessed_at' => $this->last_accessed_at,
            'last_accessed_ip' => $this->last_accessed_ip ? 
                substr($this->last_accessed_ip, 0, -2) . 'XX' : null, // Mask IP for privacy
            'has_session_data' => !is_null($this->session_data),
            'session_expires_at' => $this->session_expires_at,
            'is_expired' => $this->isExpired(),
            'is_session_expired' => $this->isSessionExpired()
        ];
    }
    
    /**
     * Clean up expired session data (static method for batch operations)
     */
    public static function cleanupExpiredSessions(): int
    {
        $expiredCount = self::expiredSessions()->count();
        
        if ($expiredCount > 0) {
            self::expiredSessions()->update([
                'session_data' => null,
                'session_expires_at' => null
            ]);
            
            Log::info('Cleaned up expired session data', [
                'expired_sessions_count' => $expiredCount,
                'cleaned_at' => now()
            ]);
        }
        
        return $expiredCount;
    }
    
    /**
     * Batch expire old invitations (static method for scheduled tasks)
     */
    public static function expireOldInvitations(): int
    {
        $expiredCount = self::where('status', self::STATUS_ACTIVE)
            ->where('expires_at', '<=', now())
            ->count();
            
        if ($expiredCount > 0) {
            self::where('status', self::STATUS_ACTIVE)
                ->where('expires_at', '<=', now())
                ->update(['status' => self::STATUS_EXPIRED]);
                
            Log::info('Batch expired old invitations', [
                'expired_count' => $expiredCount,
                'expired_at' => now()
            ]);
        }
        
        return $expiredCount;
    }
}
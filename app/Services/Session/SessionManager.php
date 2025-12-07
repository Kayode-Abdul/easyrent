<?php

namespace App\Services\Session;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ApartmentInvitation;
use App\Services\Cache\EasyRentCacheInterface;

class SessionManager implements SessionManagerInterface
{
    /**
     * Session key prefix for invitation contexts
     */
    private const SESSION_PREFIX = 'invitation_context_';
    
    /**
     * Cache key prefix for session expiration tracking
     */
    private const CACHE_PREFIX = 'session_expiry_';
    
    /**
     * Default session lifetime in hours
     */
    private const DEFAULT_LIFETIME_HOURS = 24;

    protected $cacheService;

    public function __construct(EasyRentCacheInterface $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Store invitation context data in session
     */
    public function storeInvitationContext(string $token, array $data): void
    {
        try {
            // Validate token
            if (empty($token)) {
                throw new \InvalidArgumentException('Token cannot be empty');
            }

            // Add metadata to the data
            $contextData = [
                'invitation_token' => $token,
                'stored_at' => now()->toISOString(),
                'expires_at' => now()->addHours(self::DEFAULT_LIFETIME_HOURS)->toISOString(),
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
                'data' => $data
            ];

            // Store in session
            Session::put($this->getSessionKey($token), $contextData);
            
            // Store expiration in cache for cleanup
            Cache::put(
                $this->getCacheKey($token), 
                now()->addHours(self::DEFAULT_LIFETIME_HOURS)->toISOString(),
                now()->addHours(self::DEFAULT_LIFETIME_HOURS + 1) // Cache slightly longer than session
            );

            // Also cache session data for performance
            $this->cacheService->cacheSessionData(session()->getId(), $contextData);

            Log::info('Invitation context stored', [
                'token' => substr($token, 0, 8) . '...',
                'data_keys' => array_keys($data),
                'expires_at' => $contextData['expires_at']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to store invitation context', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve invitation context data from session
     */
    public function retrieveInvitationContext(string $token): ?array
    {
        try {
            // First try to get from cache for performance
            $cachedData = $this->cacheService->getCachedSessionData(session()->getId());
            if ($cachedData && isset($cachedData['invitation_token']) && $cachedData['invitation_token'] === $token) {
                return $cachedData;
            }

            $sessionKey = $this->getSessionKey($token);
            $contextData = Session::get($sessionKey);

            if (!$contextData) {
                return null;
            }

            // Check if session has expired
            $expiresAt = Carbon::parse($contextData['expires_at']);
            if ($expiresAt->isPast()) {
                $this->clearInvitationContext($token);
                Log::info('Expired invitation context removed', [
                    'token' => substr($token, 0, 8) . '...',
                    'expired_at' => $expiresAt->toISOString()
                ]);
                return null;
            }

            // Cache the retrieved data for future requests
            $this->cacheService->cacheSessionData(session()->getId(), $contextData);

            Log::info('Invitation context retrieved', [
                'token' => substr($token, 0, 8) . '...',
                'stored_at' => $contextData['stored_at']
            ]);

            return $contextData;

        } catch (\Exception $e) {
            Log::error('Failed to retrieve invitation context', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Clear invitation context data from session
     */
    public function clearInvitationContext(string $token): void
    {
        try {
            Session::forget($this->getSessionKey($token));
            Cache::forget($this->getCacheKey($token));
            
            // Also clear from cache service
            $this->cacheService->clearSessionCache(session()->getId());

            Log::info('Invitation context cleared', [
                'token' => substr($token, 0, 8) . '...'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to clear invitation context', [
                'token' => substr($token, 0, 8) . '...',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if invitation context exists in session
     */
    public function hasInvitationContext(string $token): bool
    {
        $contextData = $this->retrieveInvitationContext($token);
        return $contextData !== null;
    }

    /**
     * Clean up expired session data
     */
    public function cleanupExpiredSessions(): int
    {
        $cleanedCount = 0;

        try {
            // Get all cached expiration keys
            $cacheKeys = Cache::get('session_expiry_keys', []);
            
            foreach ($cacheKeys as $cacheKey) {
                $expirationTime = Cache::get($cacheKey);
                
                if ($expirationTime && Carbon::parse($expirationTime)->isPast()) {
                    // Extract token from cache key
                    $token = str_replace(self::CACHE_PREFIX, '', $cacheKey);
                    
                    // Clear the session data
                    $this->clearInvitationContext($token);
                    $cleanedCount++;
                }
            }

            // Also cleanup expired invitation records in database
            $expiredInvitations = ApartmentInvitation::where('session_data', '!=', null)
                ->where('created_at', '<', now()->subHours(self::DEFAULT_LIFETIME_HOURS))
                ->where('status', '!=', ApartmentInvitation::STATUS_USED)
                ->get();

            foreach ($expiredInvitations as $invitation) {
                if ($invitation->session_data) {
                    $invitation->update(['session_data' => null]);
                    $cleanedCount++;
                }
            }

            if ($cleanedCount > 0) {
                Log::info('Session cleanup completed', [
                    'cleaned_sessions' => $cleanedCount
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Session cleanup failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $cleanedCount;
    }

    /**
     * Store application data for unauthenticated users
     */
    public function storeApplicationData(string $token, array $applicationData): void
    {
        $contextData = $this->retrieveInvitationContext($token) ?? [];
        $contextData['data']['application_data'] = $applicationData;
        $this->storeInvitationContext($token, $contextData['data'] ?? []);
    }

    /**
     * Retrieve application data for a token
     */
    public function retrieveApplicationData(string $token): ?array
    {
        $contextData = $this->retrieveInvitationContext($token);
        return $contextData['data']['application_data'] ?? null;
    }

    /**
     * Store registration data during the registration process
     */
    public function storeRegistrationData(string $token, array $registrationData): void
    {
        $contextData = $this->retrieveInvitationContext($token) ?? [];
        $contextData['data']['registration_data'] = $registrationData;
        $this->storeInvitationContext($token, $contextData['data'] ?? []);
    }

    /**
     * Retrieve registration data for a token
     */
    public function retrieveRegistrationData(string $token): ?array
    {
        $contextData = $this->retrieveInvitationContext($token);
        return $contextData['data']['registration_data'] ?? null;
    }

    /**
     * Transfer session data to authenticated user session
     */
    public function transferToAuthenticatedSession(string $token, int $userId): void
    {
        try {
            $contextData = $this->retrieveInvitationContext($token);
            
            if (!$contextData) {
                return;
            }

            // Store the invitation context in the user's authenticated session
            Session::put('authenticated_invitation_context', [
                'token' => $token,
                'user_id' => $userId,
                'transferred_at' => now()->toISOString(),
                'original_data' => $contextData['data']
            ]);

            // Also store in the invitation record for persistence
            $invitation = ApartmentInvitation::where('invitation_token', $token)->first();
            if ($invitation) {
                $sessionData = $invitation->session_data ?? [];
                $sessionData['authenticated_user_id'] = $userId;
                $sessionData['transferred_at'] = now()->toISOString();
                $invitation->update(['session_data' => $sessionData]);
            }

            Log::info('Session data transferred to authenticated user', [
                'token' => substr($token, 0, 8) . '...',
                'user_id' => $userId
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to transfer session data', [
                'token' => substr($token, 0, 8) . '...',
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get session expiration time for a token
     */
    public function getSessionExpiration(string $token): ?\Carbon\Carbon
    {
        $contextData = $this->retrieveInvitationContext($token);
        
        if (!$contextData || !isset($contextData['expires_at'])) {
            return null;
        }

        return Carbon::parse($contextData['expires_at']);
    }

    /**
     * Extend session expiration for a token
     */
    public function extendSessionExpiration(string $token, int $hours = 24): void
    {
        try {
            $contextData = $this->retrieveInvitationContext($token);
            
            if (!$contextData) {
                return;
            }

            $newExpiration = now()->addHours($hours);
            $contextData['expires_at'] = $newExpiration->toISOString();

            // Update session data
            Session::put($this->getSessionKey($token), $contextData);
            
            // Update cache expiration
            Cache::put(
                $this->getCacheKey($token), 
                $newExpiration->toISOString(),
                $newExpiration->addHour() // Cache slightly longer
            );

            Log::info('Session expiration extended', [
                'token' => substr($token, 0, 8) . '...',
                'new_expiration' => $newExpiration->toISOString(),
                'extended_hours' => $hours
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to extend session expiration', [
                'token' => substr($token, 0, 8) . '...',
                'hours' => $hours,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get session key for a token
     */
    private function getSessionKey(string $token): string
    {
        return self::SESSION_PREFIX . $token;
    }

    /**
     * Get cache key for a token
     */
    private function getCacheKey(string $token): string
    {
        return self::CACHE_PREFIX . $token;
    }
}
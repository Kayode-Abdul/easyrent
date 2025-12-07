<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\ApartmentInvitation;
use App\Models\Apartment;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;

class EasyRentCacheService implements EasyRentCacheInterface
{
    /**
     * Cache key prefixes for different data types
     */
    private const APARTMENT_PREFIX = 'apartment_';
    private const INVITATION_PREFIX = 'invitation_';
    private const SESSION_PREFIX = 'session_';
    private const PROPERTY_PREFIX = 'property_';
    private const USER_PREFIX = 'user_';
    private const METRICS_PREFIX = 'metrics_';
    
    /**
     * Default cache TTL in minutes
     */
    private const DEFAULT_TTL = 60; // 1 hour
    private const APARTMENT_TTL = 120; // 2 hours
    private const INVITATION_TTL = 30; // 30 minutes
    private const SESSION_TTL = 1440; // 24 hours
    private const METRICS_TTL = 15; // 15 minutes

    /**
     * Cache apartment data with related information using optimized queries
     */
    public function cacheApartmentData(int $apartmentId): array
    {
        $cacheKey = self::APARTMENT_PREFIX . $apartmentId;
        
        return Cache::remember($cacheKey, self::APARTMENT_TTL, function () use ($apartmentId) {
            // Optimized query with eager loading to reduce N+1 queries
            $apartment = Apartment::with([
                'property' => function($query) {
                    $query->select([
                        'property_id', 'prop_name', 'prop_description', 'prop_address',
                        'prop_state', 'prop_lga', 'prop_type', 'user_id', 'created_at'
                    ]);
                },
                'property.amenities:amenity_id,name',
                'property.user:user_id,first_name,last_name,email,phone',
                'property.reviews' => function($query) {
                    $query->select(['review_id', 'property_id', 'rating', 'comment', 'created_at'])
                          ->latest()
                          ->limit(5);
                },
                'property.attributes' => function($query) {
                    $query->select(['id', 'property_id', 'attribute_name', 'attribute_value']);
                }
            ])
            ->select([
                'apartment_id', 'property_id', 'apartment_type', 'apartment_type_id',
                'amount', 'range_start', 'range_end', 'tenant_id', 'user_id', 'occupied'
            ])
            ->find($apartmentId);
            
            if (!$apartment) {
                return null;
            }
            
            // Build optimized data structure
            $data = [
                'apartment' => [
                    'id' => $apartment->apartment_id,
                    'type' => $apartment->apartment_type,
                    'amount' => $apartment->amount,
                    'range_start' => $apartment->range_start,
                    'range_end' => $apartment->range_end,
                    'occupied' => $apartment->occupied,
                    'bathrooms' => $apartment->bathrooms,
                    'size_sqft' => $apartment->size_sqft,
                ],
                'property' => [
                    'id' => $apartment->property->property_id,
                    'name' => $apartment->property->prop_name,
                    'description' => $apartment->property->prop_description,
                    'address' => $apartment->property->prop_address,
                    'state' => $apartment->property->prop_state,
                    'lga' => $apartment->property->prop_lga,
                    'type' => $apartment->property->prop_type,
                ],
                'landlord' => [
                    'id' => $apartment->property->user->user_id,
                    'name' => $apartment->property->user->first_name . ' ' . $apartment->property->user->last_name,
                    'email' => $apartment->property->user->email,
                    'phone' => $apartment->property->user->phone,
                ],
                'amenities' => $apartment->property->amenities->pluck('name')->toArray(),
                'attributes' => $apartment->property->attributes->mapWithKeys(function($attr) {
                    return [$attr->attribute_name => $attr->attribute_value];
                })->toArray(),
                'recent_reviews' => $apartment->property->reviews->map(function($review) {
                    return [
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'created_at' => $review->created_at->toISOString()
                    ];
                })->toArray(),
                'cached_at' => now()->toISOString(),
                'cache_version' => '2.0' // Version for cache invalidation
            ];
            
            Log::info('Apartment data cached with optimizations', [
                'apartment_id' => $apartmentId,
                'cache_key' => $cacheKey,
                'data_size' => strlen(json_encode($data)),
                'cached_at' => now()
            ]);
            
            return $data;
        });
    }

    /**
     * Get cached apartment data
     */
    public function getCachedApartmentData(int $apartmentId): ?array
    {
        $cacheKey = self::APARTMENT_PREFIX . $apartmentId;
        return Cache::get($cacheKey);
    }

    /**
     * Cache invitation data with session context using optimized queries
     */
    public function cacheInvitationData(string $token): array
    {
        $cacheKey = self::INVITATION_PREFIX . $token;
        
        return Cache::remember($cacheKey, self::INVITATION_TTL, function () use ($token) {
            // Optimized query with selective field loading
            $invitation = ApartmentInvitation::with([
                'apartment' => function($query) {
                    $query->select([
                        'apartment_id', 'property_id', 'apartment_type', 'apartment_type_id',
                        'amount', 'range_start', 'range_end', 'tenant_id', 'user_id', 'occupied'
                    ]);
                },
                'apartment.property' => function($query) {
                    $query->select([
                        'property_id', 'prop_name', 'prop_description', 'prop_address',
                        'prop_state', 'prop_lga', 'prop_type', 'user_id'
                    ]);
                },
                'apartment.property.amenities:amenity_id,name',
                'apartment.property.user:user_id,first_name,last_name,email,phone',
                'landlord:user_id,first_name,last_name,email,phone',
                'tenant:user_id,first_name,last_name,email,phone'
            ])
            ->select([
                'id', 'apartment_id', 'landlord_id', 'invitation_token', 'status',
                'expires_at', 'tenant_user_id', 'access_count', 'last_accessed_at',
                'session_expires_at', 'authentication_required', 'total_amount',
                'lease_duration', 'move_in_date', 'created_at'
            ])
            ->where('invitation_token', $token)
            ->first();
            
            if (!$invitation) {
                return null;
            }
            
            // Get or cache apartment data efficiently
            $apartmentData = $this->getCachedApartmentData($invitation->apartment_id);
            if (!$apartmentData) {
                $apartmentData = $this->cacheApartmentData($invitation->apartment_id);
            }
            
            $data = [
                'invitation' => [
                    'id' => $invitation->id,
                    'apartment_id' => $invitation->apartment_id,
                    'landlord_id' => $invitation->landlord_id,
                    'token' => $invitation->invitation_token,
                    'status' => $invitation->status,
                    'expires_at' => $invitation->expires_at?->toISOString(),
                    'tenant_user_id' => $invitation->tenant_user_id,
                    'access_count' => $invitation->access_count,
                    'last_accessed_at' => $invitation->last_accessed_at?->toISOString(),
                    'session_expires_at' => $invitation->session_expires_at?->toISOString(),
                    'authentication_required' => $invitation->authentication_required,
                    'total_amount' => $invitation->total_amount,
                    'lease_duration' => $invitation->lease_duration,
                    'move_in_date' => $invitation->move_in_date?->toDateString(),
                    'created_at' => $invitation->created_at->toISOString(),
                ],
                'apartment_data' => $apartmentData,
                'landlord' => $invitation->landlord ? [
                    'id' => $invitation->landlord->user_id,
                    'name' => $invitation->landlord->first_name . ' ' . $invitation->landlord->last_name,
                    'email' => $invitation->landlord->email,
                    'phone' => $invitation->landlord->phone,
                ] : null,
                'tenant' => $invitation->tenant ? [
                    'id' => $invitation->tenant->user_id,
                    'name' => $invitation->tenant->first_name . ' ' . $invitation->tenant->last_name,
                    'email' => $invitation->tenant->email,
                    'phone' => $invitation->tenant->phone,
                ] : null,
                'security_validation' => [
                    'is_active' => $invitation->isActive(),
                    'is_expired' => $invitation->isExpired(),
                    'access_count' => $invitation->access_count,
                    'last_accessed' => $invitation->last_accessed_at?->toISOString(),
                    'requires_auth' => $invitation->authentication_required,
                ],
                'cached_at' => now()->toISOString(),
                'cache_version' => '2.0'
            ];
            
            Log::info('Invitation data cached with optimizations', [
                'token' => substr($token, 0, 8) . '...',
                'invitation_id' => $invitation->id,
                'cache_key' => $cacheKey,
                'data_size' => strlen(json_encode($data)),
                'cached_at' => now()
            ]);
            
            return $data;
        });
    }

    /**
     * Get cached invitation data
     */
    public function getCachedInvitationData(string $token): ?array
    {
        $cacheKey = self::INVITATION_PREFIX . $token;
        return Cache::get($cacheKey);
    }

    /**
     * Cache session data for performance
     */
    public function cacheSessionData(string $sessionId, array $data): void
    {
        $cacheKey = self::SESSION_PREFIX . $sessionId;
        
        $sessionData = [
            'data' => $data,
            'cached_at' => now()->toISOString(),
            'expires_at' => now()->addMinutes(self::SESSION_TTL)->toISOString()
        ];
        
        Cache::put($cacheKey, $sessionData, self::SESSION_TTL);
        
        Log::debug('Session data cached', [
            'session_id' => substr($sessionId, 0, 8) . '...',
            'cache_key' => $cacheKey,
            'data_size' => strlen(json_encode($data))
        ]);
    }

    /**
     * Get cached session data
     */
    public function getCachedSessionData(string $sessionId): ?array
    {
        $cacheKey = self::SESSION_PREFIX . $sessionId;
        $cachedData = Cache::get($cacheKey);
        
        if (!$cachedData) {
            return null;
        }
        
        // Check if session data has expired
        if (isset($cachedData['expires_at']) && 
            Carbon::parse($cachedData['expires_at'])->isPast()) {
            $this->clearSessionCache($sessionId);
            return null;
        }
        
        return $cachedData['data'] ?? null;
    }

    /**
     * Clear session cache
     */
    public function clearSessionCache(string $sessionId): void
    {
        $cacheKey = self::SESSION_PREFIX . $sessionId;
        Cache::forget($cacheKey);
        
        Log::debug('Session cache cleared', [
            'session_id' => substr($sessionId, 0, 8) . '...',
            'cache_key' => $cacheKey
        ]);
    }

    /**
     * Cache user data for quick access
     */
    public function cacheUserData(int $userId): array
    {
        $cacheKey = self::USER_PREFIX . $userId;
        
        return Cache::remember($cacheKey, self::DEFAULT_TTL, function () use ($userId) {
            $user = User::with(['roles'])->find($userId);
            
            if (!$user) {
                return null;
            }
            
            $data = [
                'user' => $user->toArray(),
                'roles' => $user->roles->pluck('name')->toArray(),
                'is_marketer' => $user->isMarketer(),
                'qualifies_for_marketer' => $user->qualifiesForMarketerStatus(),
                'cached_at' => now()->toISOString()
            ];
            
            Log::debug('User data cached', [
                'user_id' => $userId,
                'cache_key' => $cacheKey,
                'roles' => $data['roles']
            ]);
            
            return $data;
        });
    }

    /**
     * Get cached user data
     */
    public function getCachedUserData(int $userId): ?array
    {
        $cacheKey = self::USER_PREFIX . $userId;
        return Cache::get($cacheKey);
    }

    /**
     * Cache performance metrics
     */
    public function cachePerformanceMetrics(string $operation, array $metrics): void
    {
        $cacheKey = self::METRICS_PREFIX . $operation . '_' . now()->format('Y-m-d-H');
        
        // Get existing metrics for this hour
        $existingMetrics = Cache::get($cacheKey, [
            'operation' => $operation,
            'hour' => now()->format('Y-m-d-H'),
            'total_requests' => 0,
            'total_execution_time' => 0,
            'total_memory_used' => 0,
            'min_execution_time' => null,
            'max_execution_time' => 0,
            'error_count' => 0,
            'success_count' => 0
        ]);
        
        // Update metrics
        $existingMetrics['total_requests']++;
        $existingMetrics['total_execution_time'] += $metrics['execution_time'] ?? 0;
        $existingMetrics['total_memory_used'] += $metrics['memory_used'] ?? 0;
        
        if ($metrics['execution_time'] ?? 0 > 0) {
            $existingMetrics['min_execution_time'] = $existingMetrics['min_execution_time'] === null 
                ? $metrics['execution_time'] 
                : min($existingMetrics['min_execution_time'], $metrics['execution_time']);
            $existingMetrics['max_execution_time'] = max($existingMetrics['max_execution_time'], $metrics['execution_time']);
        }
        
        if (isset($metrics['success']) && $metrics['success']) {
            $existingMetrics['success_count']++;
        } else {
            $existingMetrics['error_count']++;
        }
        
        // Calculate averages
        $existingMetrics['avg_execution_time'] = $existingMetrics['total_execution_time'] / $existingMetrics['total_requests'];
        $existingMetrics['avg_memory_used'] = $existingMetrics['total_memory_used'] / $existingMetrics['total_requests'];
        $existingMetrics['success_rate'] = $existingMetrics['success_count'] / $existingMetrics['total_requests'];
        
        $existingMetrics['last_updated'] = now()->toISOString();
        
        Cache::put($cacheKey, $existingMetrics, self::METRICS_TTL);
        
        Log::debug('Performance metrics cached', [
            'operation' => $operation,
            'cache_key' => $cacheKey,
            'total_requests' => $existingMetrics['total_requests']
        ]);
    }

    /**
     * Get cached performance metrics
     */
    public function getCachedPerformanceMetrics(string $operation, ?string $hour = null): ?array
    {
        $hour = $hour ?? now()->format('Y-m-d-H');
        $cacheKey = self::METRICS_PREFIX . $operation . '_' . $hour;
        return Cache::get($cacheKey);
    }

    /**
     * Get performance metrics for multiple hours
     */
    public function getPerformanceMetricsRange(string $operation, int $hours = 24): array
    {
        $metrics = [];
        
        for ($i = 0; $i < $hours; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourMetrics = $this->getCachedPerformanceMetrics($operation, $hour);
            
            if ($hourMetrics) {
                $metrics[$hour] = $hourMetrics;
            }
        }
        
        return $metrics;
    }

    /**
     * Invalidate cache for specific data types
     */
    public function invalidateApartmentCache(int $apartmentId): void
    {
        $cacheKey = self::APARTMENT_PREFIX . $apartmentId;
        Cache::forget($cacheKey);
        
        Log::info('Apartment cache invalidated', [
            'apartment_id' => $apartmentId,
            'cache_key' => $cacheKey
        ]);
    }

    /**
     * Invalidate invitation cache
     */
    public function invalidateInvitationCache(string $token): void
    {
        $cacheKey = self::INVITATION_PREFIX . $token;
        Cache::forget($cacheKey);
        
        Log::info('Invitation cache invalidated', [
            'token' => substr($token, 0, 8) . '...',
            'cache_key' => $cacheKey
        ]);
    }

    /**
     * Invalidate user cache
     */
    public function invalidateUserCache(int $userId): void
    {
        $cacheKey = self::USER_PREFIX . $userId;
        Cache::forget($cacheKey);
        
        Log::info('User cache invalidated', [
            'user_id' => $userId,
            'cache_key' => $cacheKey
        ]);
    }

    /**
     * Warm up cache for frequently accessed data
     */
    public function warmUpCache(): void
    {
        Log::info('Starting cache warm-up process');
        
        // Warm up active invitations
        $activeInvitations = ApartmentInvitation::active()
            ->where('access_count', '>', 0)
            ->limit(50)
            ->get();
            
        foreach ($activeInvitations as $invitation) {
            $this->cacheInvitationData($invitation->invitation_token);
            $this->cacheApartmentData($invitation->apartment_id);
        }
        
        Log::info('Cache warm-up completed', [
            'invitations_cached' => $activeInvitations->count()
        ]);
    }

    /**
     * Clean up expired cache entries
     */
    public function cleanupExpiredCache(): int
    {
        $cleanedCount = 0;
        
        // This would typically be handled by the cache driver itself,
        // but we can implement custom cleanup for specific patterns
        
        Log::info('Cache cleanup completed', [
            'cleaned_entries' => $cleanedCount
        ]);
        
        return $cleanedCount;
    }

    /**
     * Cache frequently accessed property data
     */
    public function cachePropertyData(int $propertyId): array
    {
        $cacheKey = self::PROPERTY_PREFIX . $propertyId;
        
        return Cache::remember($cacheKey, self::DEFAULT_TTL, function () use ($propertyId) {
            $property = Property::with([
                'amenities:amenity_id,name',
                'user:user_id,first_name,last_name,email,phone',
                'apartments' => function($query) {
                    $query->select([
                        'apartment_id', 'property_id', 'apartment_type', 'apartment_type_id',
                        'amount', 'range_start', 'range_end', 'tenant_id', 'user_id', 'occupied'
                    ])->where('range_end', '>', now());
                },
                'attributes:id,property_id,attribute_name,attribute_value'
            ])
            ->select([
                'property_id', 'prop_name', 'prop_description', 'prop_address',
                'prop_state', 'prop_lga', 'prop_type', 'user_id', 'created_at'
            ])
            ->find($propertyId);
            
            if (!$property) {
                return null;
            }
            
            $data = [
                'property' => $property->toArray(),
                'available_apartments_count' => $property->apartments->count(),
                'cached_at' => now()->toISOString()
            ];
            
            Log::debug('Property data cached', [
                'property_id' => $propertyId,
                'cache_key' => $cacheKey,
                'apartments_count' => $data['available_apartments_count']
            ]);
            
            return $data;
        });
    }

    /**
     * Cache active invitations for quick lookup
     */
    public function cacheActiveInvitations(int $limit = 100): array
    {
        $cacheKey = 'active_invitations_list';
        
        return Cache::remember($cacheKey, 30, function () use ($limit) { // 30 minutes TTL
            $invitations = ApartmentInvitation::active()
                ->with([
                    'apartment:apartment_id,property_id,apartment_type,apartment_type_id,amount',
                    'apartment.property:property_id,property_id,prop_name,address',
                    'landlord:user_id,first_name,last_name'
                ])
                ->select([
                    'id', 'apartment_id', 'landlord_id', 'invitation_token',
                    'access_count', 'last_accessed_at', 'created_at'
                ])
                ->orderBy('access_count', 'desc')
                ->limit($limit)
                ->get();
                
            $data = [
                'invitations' => $invitations->map(function($invitation) {
                    return [
                        'id' => $invitation->id,
                        'token' => substr($invitation->invitation_token, 0, 8) . '...',
                        'apartment_type' => $invitation->apartment->apartment_type,
                        'property_name' => $invitation->apartment->property->prop_name ?? 'N/A',
                        'landlord_name' => $invitation->landlord->first_name . ' ' . $invitation->landlord->last_name,
                        'access_count' => $invitation->access_count,
                        'last_accessed_at' => $invitation->last_accessed_at?->toISOString(),
                    ];
                })->toArray(),
                'total_count' => $invitations->count(),
                'cached_at' => now()->toISOString()
            ];
            
            Log::debug('Active invitations cached', [
                'count' => $data['total_count'],
                'cache_key' => $cacheKey
            ]);
            
            return $data;
        });
    }

    /**
     * Cache database query results for complex operations
     */
    public function cacheQueryResult(string $queryKey, callable $queryCallback, int $ttl = null): array
    {
        $cacheKey = 'query_' . $queryKey;
        $ttl = $ttl ?? self::DEFAULT_TTL;
        
        return Cache::remember($cacheKey, $ttl, function () use ($queryCallback, $queryKey) {
            $startTime = microtime(true);
            $result = $queryCallback();
            $executionTime = microtime(true) - $startTime;
            
            Log::debug('Query result cached', [
                'query_key' => $queryKey,
                'execution_time' => $executionTime,
                'result_size' => is_array($result) ? count($result) : strlen(json_encode($result))
            ]);
            
            return [
                'data' => $result,
                'execution_time' => $executionTime,
                'cached_at' => now()->toISOString()
            ];
        });
    }

    /**
     * Batch cache multiple apartments for performance
     */
    public function batchCacheApartments(array $apartmentIds): array
    {
        $cached = [];
        $uncached = [];
        
        // Check which apartments are already cached
        foreach ($apartmentIds as $apartmentId) {
            $cachedData = $this->getCachedApartmentData($apartmentId);
            if ($cachedData) {
                $cached[$apartmentId] = $cachedData;
            } else {
                $uncached[] = $apartmentId;
            }
        }
        
        // Batch load uncached apartments
        if (!empty($uncached)) {
            $apartments = Apartment::with([
                'property' => function($query) {
                    $query->select([
                        'property_id', 'prop_name', 'prop_description', 'prop_address',
                        'prop_state', 'prop_lga', 'prop_type', 'user_id'
                    ]);
                },
                'property.amenities:amenity_id,name',
                'property.user:user_id,first_name,last_name,email,phone',
                'property.attributes:id,property_id,attribute_name,attribute_value'
            ])
            ->select([
                'apartment_id', 'property_id', 'apartment_type', 'apartment_type_id',
                'amount', 'range_start', 'range_end', 'tenant_id', 'user_id', 'occupied'
            ])
            ->whereIn('apartment_id', $uncached)
            ->get();
            
            foreach ($apartments as $apartment) {
                $cacheKey = self::APARTMENT_PREFIX . $apartment->apartment_id;
                $data = [
                    'apartment' => [
                        'id' => $apartment->apartment_id,
                        'type' => $apartment->apartment_type,
                        'amount' => $apartment->amount,
                        'range_start' => $apartment->range_start,
                        'range_end' => $apartment->range_end,
                        'occupied' => $apartment->occupied,
                    ],
                    'property' => [
                        'id' => $apartment->property->property_id,
                        'name' => $apartment->property->prop_name,
                        'description' => $apartment->property->prop_description,
                        'address' => $apartment->property->prop_address,
                        'state' => $apartment->property->prop_state,
                        'lga' => $apartment->property->prop_lga,
                        'type' => $apartment->property->prop_type,
                    ],
                    'landlord' => [
                        'id' => $apartment->property->user->user_id,
                        'name' => $apartment->property->user->first_name . ' ' . $apartment->property->user->last_name,
                        'email' => $apartment->property->user->email,
                        'phone' => $apartment->property->user->phone,
                    ],
                    'amenities' => $apartment->property->amenities->pluck('name')->toArray(),
                    'attributes' => $apartment->property->attributes->mapWithKeys(function($attr) {
                        return [$attr->attribute_name => $attr->attribute_value];
                    })->toArray(),
                    'cached_at' => now()->toISOString(),
                    'cache_version' => '2.0'
                ];
                
                Cache::put($cacheKey, $data, self::APARTMENT_TTL);
                $cached[$apartment->apartment_id] = $data;
            }
            
            Log::info('Batch cached apartments', [
                'cached_count' => count($uncached),
                'apartment_ids' => $uncached
            ]);
        }
        
        return $cached;
    }

    /**
     * Get cache statistics with enhanced metrics
     */
    public function getCacheStatistics(): array
    {
        $stats = [
            'cache_driver' => config('cache.default'),
            'cache_prefix' => config('cache.prefix'),
            'statistics_generated_at' => now()->toISOString(),
        ];
        
        // Try to get cache-specific statistics if available
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::getRedis();
                $info = $redis->info();
                
                $stats['redis_stats'] = [
                    'used_memory' => $info['used_memory_human'] ?? 'N/A',
                    'connected_clients' => $info['connected_clients'] ?? 'N/A',
                    'total_commands_processed' => $info['total_commands_processed'] ?? 'N/A',
                    'keyspace_hits' => $info['keyspace_hits'] ?? 'N/A',
                    'keyspace_misses' => $info['keyspace_misses'] ?? 'N/A',
                ];
                
                if (isset($info['keyspace_hits'], $info['keyspace_misses'])) {
                    $total = $info['keyspace_hits'] + $info['keyspace_misses'];
                    $stats['redis_stats']['hit_rate'] = $total > 0 ? 
                        round(($info['keyspace_hits'] / $total) * 100, 2) . '%' : '0%';
                }
            }
        } catch (\Exception $e) {
            $stats['cache_stats_error'] = 'Unable to retrieve cache statistics: ' . $e->getMessage();
        }
        
        // Add EasyRent-specific cache metrics
        $stats['easyrent_cache_metrics'] = [
            'apartment_cache_prefix' => self::APARTMENT_PREFIX,
            'invitation_cache_prefix' => self::INVITATION_PREFIX,
            'session_cache_prefix' => self::SESSION_PREFIX,
            'default_ttl_minutes' => self::DEFAULT_TTL,
            'apartment_ttl_minutes' => self::APARTMENT_TTL,
            'invitation_ttl_minutes' => self::INVITATION_TTL,
            'session_ttl_minutes' => self::SESSION_TTL,
        ];
        
        return $stats;
    }
}
<?php

namespace App\Services\Cache;

interface EasyRentCacheInterface
{
    /**
     * Cache apartment data with related information
     */
    public function cacheApartmentData(int $apartmentId): ?array;

    /**
     * Get cached apartment data
     */
    public function getCachedApartmentData(int $apartmentId): ?array;

    /**
     * Cache invitation data with session context
     */
    public function cacheInvitationData(string $token): ?array;

    /**
     * Get cached invitation data
     */
    public function getCachedInvitationData(string $token): ?array;

    /**
     * Cache session data for performance
     */
    public function cacheSessionData(string $sessionId, array $data): void;

    /**
     * Get cached session data
     */
    public function getCachedSessionData(string $sessionId): ?array;

    /**
     * Clear session cache
     */
    public function clearSessionCache(string $sessionId): void;

    /**
     * Cache user data for quick access
     */
    public function cacheUserData(int $userId): ?array;

    /**
     * Get cached user data
     */
    public function getCachedUserData(int $userId): ?array;

    /**
     * Cache performance metrics
     */
    public function cachePerformanceMetrics(string $operation, array $metrics): void;

    /**
     * Get cached performance metrics
     */
    public function getCachedPerformanceMetrics(string $operation, ?string $hour = null): ?array;

    /**
     * Get performance metrics for multiple hours
     */
    public function getPerformanceMetricsRange(string $operation, int $hours = 24): array;

    /**
     * Invalidate cache for specific data types
     */
    public function invalidateApartmentCache(int $apartmentId): void;

    /**
     * Invalidate invitation cache
     */
    public function invalidateInvitationCache(string $token): void;

    /**
     * Invalidate user cache
     */
    public function invalidateUserCache(int $userId): void;

    /**
     * Warm up cache for frequently accessed data
     */
    public function warmUpCache(): void;

    /**
     * Clean up expired cache entries
     */
    public function cleanupExpiredCache(): int;

    /**
     * Cache frequently accessed property data
     */
    public function cachePropertyData(int $propertyId): ?array;

    /**
     * Cache active invitations for quick lookup
     */
    public function cacheActiveInvitations(int $limit = 100): array;

    /**
     * Cache database query results for complex operations
     */
    public function cacheQueryResult(string $queryKey, callable $queryCallback, ?int $ttl = null): ?array;

    /**
     * Batch cache multiple apartments for performance
     */
    public function batchCacheApartments(array $apartmentIds): array;

    /**
     * Get cache statistics
     */
    public function getCacheStatistics(): array;
}
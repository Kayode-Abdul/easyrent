<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\ApartmentInvitation;
use App\Models\Apartment;
use App\Models\Property;
use App\Models\User;
use App\Services\Cache\EasyRentCacheService;
use App\Services\Monitoring\PerformanceMonitoringService;

class CachePerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected $cacheService;
    protected $performanceMonitor;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cacheService = app(EasyRentCacheService::class);
        $this->performanceMonitor = app(PerformanceMonitoringService::class);
    }

    /** @test */
    public function it_caches_apartment_data_efficiently()
    {
        // Create test data manually
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser' . uniqid(),
            'email' => 'test' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'address' => 'Test Address',
            'state' => 'Test State',
            'lga' => 'Test LGA'
        ]);
        
        $property = Property::create([
            'prop_name' => 'Test Property',
            'prop_description' => 'Test Description',
            'prop_address' => 'Test Address',
            'prop_state' => 'Test State',
            'prop_lga' => 'Test LGA',
            'prop_type' => 'residential',
            'user_id' => $user->user_id
        ]);
        
        $apartment = Apartment::create([
            'property_id' => $property->property_id,
            'apartment_name' => 'Test Apartment',
            'apartment_type' => 'flat',
            'amount' => 100000,
            'duration' => 12,
            'status' => 'available',
            'bedrooms' => 2,
            'bathrooms' => 2
        ]);

        // Clear any existing cache
        Cache::flush();

        // Monitor database queries
        DB::enableQueryLog();

        // First call should hit database
        $startTime = microtime(true);
        $data1 = $this->cacheService->cacheApartmentData($apartment->apartment_id);
        $firstCallTime = microtime(true) - $startTime;
        $firstCallQueries = count(DB::getQueryLog());

        DB::flushQueryLog();

        // Second call should hit cache
        $startTime = microtime(true);
        $data2 = $this->cacheService->getCachedApartmentData($apartment->apartment_id);
        $secondCallTime = microtime(true) - $startTime;
        $secondCallQueries = count(DB::getQueryLog());

        // Assertions
        $this->assertNotNull($data1);
        $this->assertNotNull($data2);
        $this->assertEquals($data1['apartment']['id'], $data2['apartment']['id']);
        
        // Cache should be significantly faster
        $this->assertLessThan($firstCallTime, $secondCallTime);
        
        // Second call should use fewer queries (ideally 0)
        $this->assertLessThan($firstCallQueries, $secondCallQueries);
        
        // Verify cache structure
        $this->assertArrayHasKey('apartment', $data2);
        $this->assertArrayHasKey('property', $data2);
        $this->assertArrayHasKey('landlord', $data2);
        $this->assertArrayHasKey('cached_at', $data2);
        $this->assertArrayHasKey('cache_version', $data2);
    }

    /** @test */
    public function it_caches_invitation_data_with_optimized_queries()
    {
        // Create test data manually
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Landlord',
            'username' => 'testlandlord' . uniqid(),
            'email' => 'landlord' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'address' => 'Test Address',
            'state' => 'Test State',
            'lga' => 'Test LGA'
        ]);
        
        $property = Property::create([
            'prop_name' => 'Test Property',
            'prop_description' => 'Test Description',
            'prop_address' => 'Test Address',
            'prop_state' => 'Test State',
            'prop_lga' => 'Test LGA',
            'prop_type' => 'residential',
            'user_id' => $user->user_id
        ]);
        
        $apartment = Apartment::create([
            'property_id' => $property->property_id,
            'apartment_name' => 'Test Apartment',
            'apartment_type' => 'flat',
            'amount' => 100000,
            'duration' => 12,
            'status' => 'available',
            'bedrooms' => 2,
            'bathrooms' => 2
        ]);
        
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $apartment->apartment_id,
            'landlord_id' => $user->user_id,
            'invitation_token' => 'test_token_' . uniqid(),
            'status' => ApartmentInvitation::STATUS_ACTIVE,
            'expires_at' => now()->addDays(30)
        ]);

        Cache::flush();
        DB::enableQueryLog();

        // Cache invitation data
        $startTime = microtime(true);
        $data = $this->cacheService->cacheInvitationData($invitation->invitation_token);
        $executionTime = microtime(true) - $startTime;
        $queryCount = count(DB::getQueryLog());

        // Verify data structure
        $this->assertNotNull($data);
        $this->assertArrayHasKey('invitation', $data);
        $this->assertArrayHasKey('apartment_data', $data);
        $this->assertArrayHasKey('security_validation', $data);
        $this->assertArrayHasKey('cache_version', $data);

        // Verify performance
        $this->assertLessThan(0.1, $executionTime); // Should complete in under 100ms
        $this->assertLessThan(10, $queryCount); // Should use minimal queries

        // Test cache retrieval
        DB::flushQueryLog();
        $startTime = microtime(true);
        $cachedData = $this->cacheService->getCachedInvitationData($invitation->invitation_token);
        $cacheRetrievalTime = microtime(true) - $startTime;
        $cacheQueries = count(DB::getQueryLog());

        $this->assertNotNull($cachedData);
        $this->assertEquals($data['invitation']['id'], $cachedData['invitation']['id']);
        $this->assertLessThan(0.01, $cacheRetrievalTime); // Cache should be very fast
        $this->assertEquals(0, $cacheQueries); // No database queries for cache hit
    }

    /** @test */
    public function it_batch_caches_apartments_efficiently()
    {
        // Create multiple apartments manually
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'batchuser' . uniqid(),
            'email' => 'batch' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'address' => 'Test Address',
            'state' => 'Test State',
            'lga' => 'Test LGA'
        ]);
        
        $property = Property::create([
            'prop_name' => 'Batch Test Property',
            'prop_description' => 'Test Description',
            'prop_address' => 'Test Address',
            'prop_state' => 'Test State',
            'prop_lga' => 'Test LGA',
            'prop_type' => 'residential',
            'user_id' => $user->user_id
        ]);
        
        $apartments = collect();
        for ($i = 1; $i <= 5; $i++) {
            $apartment = Apartment::create([
                'property_id' => $property->property_id,
                'apartment_name' => "Test Apartment {$i}",
                'apartment_type' => 'flat',
                'amount' => 100000 + ($i * 10000),
                'duration' => 12,
                'status' => 'available',
                'bedrooms' => 2,
                'bathrooms' => 2
            ]);
            $apartments->push($apartment);
        }
        
        $apartmentIds = $apartments->pluck('apartment_id')->toArray();

        Cache::flush();
        DB::enableQueryLog();

        // Batch cache apartments
        $startTime = microtime(true);
        $cachedData = $this->cacheService->batchCacheApartments($apartmentIds);
        $executionTime = microtime(true) - $startTime;
        $queryCount = count(DB::getQueryLog());

        // Verify all apartments were cached
        $this->assertCount(5, $cachedData);
        foreach ($apartmentIds as $apartmentId) {
            $this->assertArrayHasKey($apartmentId, $cachedData);
            $this->assertArrayHasKey('apartment', $cachedData[$apartmentId]);
            $this->assertArrayHasKey('property', $cachedData[$apartmentId]);
        }

        // Verify performance - batch should be more efficient than individual calls
        $this->assertLessThan(0.5, $executionTime); // Should complete in under 500ms
        $this->assertLessThan(15, $queryCount); // Should use efficient queries

        // Test that subsequent individual calls hit cache
        DB::flushQueryLog();
        foreach ($apartmentIds as $apartmentId) {
            $individualData = $this->cacheService->getCachedApartmentData($apartmentId);
            $this->assertNotNull($individualData);
        }
        $individualQueries = count(DB::getQueryLog());
        $this->assertEquals(0, $individualQueries); // All should hit cache
    }

    /** @test */
    public function it_monitors_performance_metrics()
    {
        // Create test data manually
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'perfuser' . uniqid(),
            'email' => 'perf' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'address' => 'Test Address',
            'state' => 'Test State',
            'lga' => 'Test LGA'
        ]);
        
        $property = Property::create([
            'prop_name' => 'Performance Test Property',
            'prop_description' => 'Test Description',
            'prop_address' => 'Test Address',
            'prop_state' => 'Test State',
            'prop_lga' => 'Test LGA',
            'prop_type' => 'residential',
            'user_id' => $user->user_id
        ]);
        
        $apartment = Apartment::create([
            'property_id' => $property->property_id,
            'apartment_name' => 'Performance Test Apartment',
            'apartment_type' => 'flat',
            'amount' => 100000,
            'duration' => 12,
            'status' => 'available',
            'bedrooms' => 2,
            'bathrooms' => 2
        ]);

        Cache::flush();

        // Monitor cache operation performance
        $result = $this->performanceMonitor->monitorCachePerformance('apartment_data', function() use ($apartment) {
            return $this->cacheService->cacheApartmentData($apartment->apartment_id);
        });

        $this->assertNotNull($result);

        // Check that metrics were cached
        $metrics = $this->cacheService->getCachedPerformanceMetrics('cache_apartment_data');
        $this->assertNotNull($metrics);
        $this->assertArrayHasKey('total_requests', $metrics);
        $this->assertArrayHasKey('avg_execution_time', $metrics);
        $this->assertArrayHasKey('success_rate', $metrics);
    }

    /** @test */
    public function it_handles_cache_invalidation_properly()
    {
        // Create test data manually
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'invaliduser' . uniqid(),
            'email' => 'invalid' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'address' => 'Test Address',
            'state' => 'Test State',
            'lga' => 'Test LGA'
        ]);
        
        $property = Property::create([
            'prop_name' => 'Invalidation Test Property',
            'prop_description' => 'Test Description',
            'prop_address' => 'Test Address',
            'prop_state' => 'Test State',
            'prop_lga' => 'Test LGA',
            'prop_type' => 'residential',
            'user_id' => $user->user_id
        ]);
        
        $apartment = Apartment::create([
            'property_id' => $property->property_id,
            'apartment_name' => 'Invalidation Test Apartment',
            'apartment_type' => 'flat',
            'amount' => 100000,
            'duration' => 12,
            'status' => 'available',
            'bedrooms' => 2,
            'bathrooms' => 2
        ]);

        // Cache apartment data
        $originalData = $this->cacheService->cacheApartmentData($apartment->apartment_id);
        $this->assertNotNull($originalData);

        // Verify data is cached
        $cachedData = $this->cacheService->getCachedApartmentData($apartment->apartment_id);
        $this->assertNotNull($cachedData);

        // Invalidate cache
        $this->cacheService->invalidateApartmentCache($apartment->apartment_id);

        // Verify cache is cleared
        $clearedData = $this->cacheService->getCachedApartmentData($apartment->apartment_id);
        $this->assertNull($clearedData);

        // Verify we can cache again
        $newData = $this->cacheService->cacheApartmentData($apartment->apartment_id);
        $this->assertNotNull($newData);
        $this->assertEquals($originalData['apartment']['id'], $newData['apartment']['id']);
    }

    /** @test */
    public function it_optimizes_session_data_caching()
    {
        $sessionId = 'test_session_' . uniqid();
        $sessionData = [
            'invitation_token' => 'test_token_123',
            'user_data' => ['name' => 'Test User'],
            'application_data' => ['duration' => 12]
        ];

        // Cache session data
        $startTime = microtime(true);
        $this->cacheService->cacheSessionData($sessionId, $sessionData);
        $cacheTime = microtime(true) - $startTime;

        // Retrieve session data
        $startTime = microtime(true);
        $retrievedData = $this->cacheService->getCachedSessionData($sessionId);
        $retrievalTime = microtime(true) - $startTime;

        // Verify data integrity
        $this->assertNotNull($retrievedData);
        $this->assertEquals($sessionData['invitation_token'], $retrievedData['invitation_token']);
        $this->assertEquals($sessionData['user_data'], $retrievedData['user_data']);

        // Verify performance
        $this->assertLessThan(0.01, $cacheTime); // Caching should be very fast
        $this->assertLessThan(0.01, $retrievalTime); // Retrieval should be very fast

        // Test cache expiration (session data is stored directly, not wrapped)
        $this->assertArrayHasKey('invitation_token', $retrievedData);
        $this->assertArrayHasKey('user_data', $retrievedData);

        // Clear session cache
        $this->cacheService->clearSessionCache($sessionId);
        $clearedData = $this->cacheService->getCachedSessionData($sessionId);
        $this->assertNull($clearedData);
    }

    /** @test */
    public function it_provides_comprehensive_cache_statistics()
    {
        // Generate some cache activity manually
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'statsuser' . uniqid(),
            'email' => 'stats' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'address' => 'Test Address',
            'state' => 'Test State',
            'lga' => 'Test LGA'
        ]);
        
        $property = Property::create([
            'prop_name' => 'Stats Test Property',
            'prop_description' => 'Test Description',
            'prop_address' => 'Test Address',
            'prop_state' => 'Test State',
            'prop_lga' => 'Test LGA',
            'prop_type' => 'residential',
            'user_id' => $user->user_id
        ]);
        
        $apartment = Apartment::create([
            'property_id' => $property->property_id,
            'apartment_name' => 'Stats Test Apartment',
            'apartment_type' => 'flat',
            'amount' => 100000,
            'duration' => 12,
            'status' => 'available',
            'bedrooms' => 2,
            'bathrooms' => 2
        ]);

        // Cache various data types
        $this->cacheService->cacheApartmentData($apartment->apartment_id);
        $this->cacheService->cacheUserData($user->user_id);
        $this->cacheService->cachePropertyData($property->property_id);

        // Get cache statistics
        $stats = $this->cacheService->getCacheStatistics();

        // Verify statistics structure
        $this->assertArrayHasKey('cache_driver', $stats);
        $this->assertArrayHasKey('statistics_generated_at', $stats);
        $this->assertArrayHasKey('easyrent_cache_metrics', $stats);

        // Verify EasyRent-specific metrics
        $easyRentMetrics = $stats['easyrent_cache_metrics'];
        $this->assertArrayHasKey('apartment_cache_prefix', $easyRentMetrics);
        $this->assertArrayHasKey('invitation_cache_prefix', $easyRentMetrics);
        $this->assertArrayHasKey('session_cache_prefix', $easyRentMetrics);
        $this->assertArrayHasKey('default_ttl_minutes', $easyRentMetrics);
    }
}
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Property;
use App\Models\Referral;
use App\Models\CommissionRate;
use App\Models\CommissionPayment;
use App\Models\ReferralChain;
use App\Services\Commission\MultiTierCommissionCalculator;
use App\Services\Commission\PaymentDistributionService;
use App\Services\Commission\ReferralChainService;
use App\Services\Commission\RegionalRateManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SystemPerformanceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
        $this->setupCommissionRates();
    }

    private function setupRoles()
    {
        Role::create(['id' => 9, 'name' => 'Super Marketer', 'description' => 'Super Marketer Role']);
        Role::create(['id' => 7, 'name' => 'Marketer', 'description' => 'Marketer Role']);
        Role::create(['id' => 3, 'name' => 'Landlord', 'description' => 'Landlord Role']);
        Role::create(['id' => 8, 'name' => 'Regional Manager', 'description' => 'Regional Manager Role']);
    }

    private function setupCommissionRates()
    {
        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 9,
            'commission_percentage' => 0.008,
            'effective_from' => now(),
            'created_by' => 1,
            'is_active' => true
        ]);

        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 7,
            'commission_percentage' => 0.012,
            'effective_from' => now(),
            'created_by' => 1,
            'is_active' => true
        ]);

        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 8,
            'commission_percentage' => 0.005,
            'effective_from' => now(),
            'created_by' => 1,
            'is_active' => true
        ]);
    }

    /** @test */
    public function test_commission_calculation_performance_with_large_dataset()
    {
        $startTime = microtime(true);
        
        // Create 100 referral chains
        $chains = [];
        for ($i = 0; $i < 100; $i++) {
            $superMarketer = User::factory()->create(['region' => 'Lagos']);
            $superMarketer->roles()->attach(9);
            
            $marketer = User::factory()->create(['region' => 'Lagos']);
            $marketer->roles()->attach(7);
            
            $landlord = User::factory()->create(['region' => 'Lagos']);
            $landlord->roles()->attach(3);
            
            $regionalManager = User::factory()->create(['region' => 'Lagos']);
            $regionalManager->roles()->attach(8);
            
            $chains[] = [
                'super_marketer' => $superMarketer,
                'marketer' => $marketer,
                'landlord' => $landlord,
                'regional_manager' => $regionalManager
            ];
        }
        
        $setupTime = microtime(true) - $startTime;
        
        // Test commission calculations
        $calculationStartTime = microtime(true);
        $calculator = new MultiTierCommissionCalculator();
        
        foreach ($chains as $chain) {
            $commissionSplit = $calculator->calculateCommissionSplit(
                2500, // ₦2,500 commission
                [
                    'super_marketer' => $chain['super_marketer'],
                    'marketer' => $chain['marketer'],
                    'regional_manager' => $chain['regional_manager']
                ],
                'Lagos'
            );
            
            $this->assertNotEmpty($commissionSplit);
            $this->assertArrayHasKey('super_marketer', $commissionSplit);
            $this->assertArrayHasKey('marketer', $commissionSplit);
            $this->assertArrayHasKey('regional_manager', $commissionSplit);
        }
        
        $calculationTime = microtime(true) - $calculationStartTime;
        
        // Performance assertions
        $this->assertLessThan(2.0, $calculationTime, 'Commission calculation took too long for 100 chains');
        
        echo "\nPerformance Results:\n";
        echo "Setup Time: " . number_format($setupTime, 3) . " seconds\n";
        echo "Calculation Time: " . number_format($calculationTime, 3) . " seconds\n";
        echo "Average per calculation: " . number_format($calculationTime / 100, 4) . " seconds\n";
    }

    /** @test */
    public function test_payment_distribution_performance()
    {
        $startTime = microtime(true);
        
        // Create test users
        $superMarketer = User::factory()->create(['region' => 'Lagos']);
        $superMarketer->roles()->attach(9);
        
        $marketer = User::factory()->create(['region' => 'Lagos']);
        $marketer->roles()->attach(7);
        
        $regionalManager = User::factory()->create(['region' => 'Lagos']);
        $regionalManager->roles()->attach(8);
        
        $paymentService = new PaymentDistributionService();
        
        // Process 200 payment distributions
        for ($i = 0; $i < 200; $i++) {
            $paymentRecords = $paymentService->distributeMultiTierCommission(
                2500,
                [
                    'super_marketer' => $superMarketer,
                    'marketer' => $marketer,
                    'regional_manager' => $regionalManager
                ],
                'Lagos'
            );
            
            $this->assertCount(3, $paymentRecords);
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should process 200 payments within 3 seconds
        $this->assertLessThan(3.0, $executionTime, 'Payment distribution took too long');
        
        // Verify all payments were created
        $totalPayments = CommissionPayment::count();
        $this->assertEquals(600, $totalPayments); // 200 * 3 payments each
        
        echo "\nPayment Distribution Performance:\n";
        echo "Total Time: " . number_format($executionTime, 3) . " seconds\n";
        echo "Average per distribution: " . number_format($executionTime / 200, 4) . " seconds\n";
    }

    /** @test */
    public function test_database_query_optimization()
    {
        // Create complex referral hierarchy
        $superMarketer = User::factory()->create(['region' => 'Lagos']);
        $superMarketer->roles()->attach(9);
        
        // Create 50 marketers under this super marketer
        $marketers = [];
        for ($i = 0; $i < 50; $i++) {
            $marketer = User::factory()->create(['region' => 'Lagos']);
            $marketer->roles()->attach(7);
            $marketers[] = $marketer;
            
            // Create referral relationship
            Referral::create([
                'referrer_id' => $superMarketer->user_id,
                'referred_id' => $marketer->user_id,
                'referral_code' => 'SM_' . $superMarketer->user_id . '_' . $i,
                'status' => 'active',
                'referral_level' => 1,
                'commission_tier' => 'super_marketer'
            ]);
            
            // Create commission payments
            CommissionPayment::create([
                'user_id' => $marketer->user_id,
                'amount' => 1200,
                'commission_tier' => 'marketer',
                'status' => 'completed',
                'regional_rate_applied' => 0.012
            ]);
        }
        
        // Test query performance
        DB::enableQueryLog();
        $startTime = microtime(true);
        
        // Get super marketer's referred marketers with their performance
        $referredMarketers = $superMarketer->referredMarketers()
            ->with(['commissionPayments' => function($query) {
                $query->where('status', 'completed');
            }])
            ->get();
        
        $queryTime = microtime(true) - $startTime;
        $queries = DB::getQueryLog();
        
        // Performance assertions
        $this->assertLessThan(0.1, $queryTime, 'Query took too long');
        $this->assertLessThan(5, count($queries), 'Too many queries executed (N+1 problem)');
        $this->assertCount(50, $referredMarketers);
        
        echo "\nDatabase Query Performance:\n";
        echo "Query Time: " . number_format($queryTime, 4) . " seconds\n";
        echo "Number of Queries: " . count($queries) . "\n";
        
        DB::disableQueryLog();
    }

    /** @test */
    public function test_referral_chain_validation_performance()
    {
        $referralChainService = new ReferralChainService();
        
        // Create users for testing
        $users = [];
        for ($i = 0; $i < 100; $i++) {
            $user = User::factory()->create(['region' => 'Lagos']);
            $user->roles()->attach(7);
            $users[] = $user;
        }
        
        $startTime = microtime(true);
        
        // Test 1000 referral validations
        for ($i = 0; $i < 1000; $i++) {
            $referrer = $users[array_rand($users)];
            $referred = $users[array_rand($users)];
            
            $isValid = $referralChainService->validateReferralEligibility(
                $referrer->user_id,
                $referred->user_id
            );
            
            // Should be false if same user, true otherwise
            if ($referrer->user_id === $referred->user_id) {
                $this->assertFalse($isValid);
            }
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete 1000 validations within 1 second
        $this->assertLessThan(1.0, $executionTime, 'Referral validation took too long');
        
        echo "\nReferral Validation Performance:\n";
        echo "Total Time: " . number_format($executionTime, 3) . " seconds\n";
        echo "Average per validation: " . number_format($executionTime / 1000, 5) . " seconds\n";
    }

    /** @test */
    public function test_regional_rate_caching_performance()
    {
        $rateManager = new RegionalRateManager();
        
        // Clear cache
        Cache::flush();
        
        // First call (should hit database)
        $startTime = microtime(true);
        $rate1 = $rateManager->getActiveRate('Lagos', 9);
        $firstCallTime = microtime(true) - $startTime;
        
        // Second call (should hit cache)
        $startTime = microtime(true);
        $rate2 = $rateManager->getActiveRate('Lagos', 9);
        $secondCallTime = microtime(true) - $startTime;
        
        // Verify rates are the same
        $this->assertEquals($rate1, $rate2);
        $this->assertEquals(0.008, $rate1);
        
        // Cache should be significantly faster
        $this->assertLessThan($firstCallTime / 2, $secondCallTime, 'Cache not providing performance benefit');
        
        echo "\nRate Caching Performance:\n";
        echo "First Call (DB): " . number_format($firstCallTime, 5) . " seconds\n";
        echo "Second Call (Cache): " . number_format($secondCallTime, 5) . " seconds\n";
        echo "Performance Improvement: " . number_format($firstCallTime / $secondCallTime, 2) . "x\n";
    }

    /** @test */
    public function test_concurrent_commission_calculations()
    {
        // Simulate concurrent commission calculations
        $superMarketer = User::factory()->create(['region' => 'Lagos']);
        $superMarketer->roles()->attach(9);
        
        $marketer = User::factory()->create(['region' => 'Lagos']);
        $marketer->roles()->attach(7);
        
        $regionalManager = User::factory()->create(['region' => 'Lagos']);
        $regionalManager->roles()->attach(8);
        
        $calculator = new MultiTierCommissionCalculator();
        $startTime = microtime(true);
        
        // Simulate 10 concurrent calculations
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = $calculator->calculateCommissionSplit(
                2500,
                [
                    'super_marketer' => $superMarketer,
                    'marketer' => $marketer,
                    'regional_manager' => $regionalManager
                ],
                'Lagos'
            );
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // All results should be identical
        foreach ($results as $result) {
            $this->assertEquals($results[0], $result);
            $this->assertEquals(800, $result['super_marketer']);
            $this->assertEquals(1200, $result['marketer']);
            $this->assertEquals(500, $result['regional_manager']);
        }
        
        // Should complete quickly
        $this->assertLessThan(0.5, $executionTime, 'Concurrent calculations took too long');
        
        echo "\nConcurrent Calculation Performance:\n";
        echo "Total Time: " . number_format($executionTime, 4) . " seconds\n";
        echo "Average per calculation: " . number_format($executionTime / 10, 5) . " seconds\n";
    }

    /** @test */
    public function test_memory_usage_with_large_datasets()
    {
        $initialMemory = memory_get_usage(true);
        
        // Create large dataset
        $users = [];
        $referrals = [];
        
        for ($i = 0; $i < 500; $i++) {
            $user = User::factory()->create(['region' => 'Lagos']);
            $user->roles()->attach(7);
            $users[] = $user;
            
            if ($i > 0) {
                $referral = Referral::create([
                    'referrer_id' => $users[$i - 1]->user_id,
                    'referred_id' => $user->user_id,
                    'referral_code' => 'REF_' . $i,
                    'status' => 'active',
                    'referral_level' => 1,
                    'commission_tier' => 'marketer'
                ]);
                $referrals[] = $referral;
            }
        }
        
        $afterCreationMemory = memory_get_usage(true);
        
        // Process commission calculations
        $calculator = new MultiTierCommissionCalculator();
        
        foreach ($users as $user) {
            $calculator->calculateCommissionSplit(
                2500,
                ['marketer' => $user],
                'Lagos'
            );
        }
        
        $finalMemory = memory_get_usage(true);
        
        $creationMemoryUsage = $afterCreationMemory - $initialMemory;
        $processingMemoryUsage = $finalMemory - $afterCreationMemory;
        
        // Memory usage should be reasonable
        $this->assertLessThan(50 * 1024 * 1024, $creationMemoryUsage, 'Too much memory used for data creation'); // 50MB
        $this->assertLessThan(10 * 1024 * 1024, $processingMemoryUsage, 'Too much memory used for processing'); // 10MB
        
        echo "\nMemory Usage Analysis:\n";
        echo "Initial Memory: " . number_format($initialMemory / 1024 / 1024, 2) . " MB\n";
        echo "After Creation: " . number_format($afterCreationMemory / 1024 / 1024, 2) . " MB\n";
        echo "Final Memory: " . number_format($finalMemory / 1024 / 1024, 2) . " MB\n";
        echo "Creation Usage: " . number_format($creationMemoryUsage / 1024 / 1024, 2) . " MB\n";
        echo "Processing Usage: " . number_format($processingMemoryUsage / 1024 / 1024, 2) . " MB\n";
    }
}
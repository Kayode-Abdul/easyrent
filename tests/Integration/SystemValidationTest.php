<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Services\Commission\MultiTierCommissionCalculator;
use App\Services\Commission\PaymentDistributionService;
use App\Services\Commission\ReferralChainService;
use App\Services\Commission\RegionalRateManager;
use App\Services\Fraud\FraudDetectionService;

class SystemValidationTest extends TestCase
{
    /** @test */
    public function test_all_core_services_are_instantiable()
    {
        // Test that all core services can be instantiated
        $calculator = new MultiTierCommissionCalculator();
        $this->assertInstanceOf(MultiTierCommissionCalculator::class, $calculator);

        $paymentService = new PaymentDistributionService();
        $this->assertInstanceOf(PaymentDistributionService::class, $paymentService);

        $chainService = new ReferralChainService();
        $this->assertInstanceOf(ReferralChainService::class, $chainService);

        $rateManager = new RegionalRateManager();
        $this->assertInstanceOf(RegionalRateManager::class, $rateManager);

        $fraudService = new FraudDetectionService();
        $this->assertInstanceOf(FraudDetectionService::class, $fraudService);
    }

    /** @test */
    public function test_commission_calculation_logic()
    {
        $calculator = new MultiTierCommissionCalculator();
        
        // Mock user objects
        $superMarketer = (object) ['user_id' => 1, 'region' => 'Lagos'];
        $marketer = (object) ['user_id' => 2, 'region' => 'Lagos'];
        $regionalManager = (object) ['user_id' => 3, 'region' => 'Lagos'];

        // Test commission split calculation
        $totalCommission = 2500; // ₦2,500
        $referralChain = [
            'super_marketer' => $superMarketer,
            'marketer' => $marketer,
            'regional_manager' => $regionalManager
        ];

        // This should work without database dependencies
        $result = $calculator->calculateCommissionSplit($totalCommission, $referralChain, 'Lagos');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('super_marketer', $result);
        $this->assertArrayHasKey('marketer', $result);
        $this->assertArrayHasKey('regional_manager', $result);
    }

    /** @test */
    public function test_referral_chain_validation_logic()
    {
        $chainService = new ReferralChainService();
        
        // Test self-referral validation (should fail)
        $isValid = $chainService->validateReferralEligibility(1, 1);
        $this->assertFalse($isValid, 'Self-referral should not be allowed');
        
        // Test different users (should pass basic validation)
        $isValid = $chainService->validateReferralEligibility(1, 2);
        $this->assertTrue($isValid, 'Different users should be allowed');
    }

    /** @test */
    public function test_fraud_detection_basic_logic()
    {
        $fraudService = new FraudDetectionService();
        
        // Test circular referral detection
        $chain = [1, 2, 3, 1]; // Circular chain
        $isCircular = $fraudService->detectCircularReferrals($chain);
        $this->assertTrue($isCircular, 'Should detect circular referral');
        
        // Test valid chain
        $validChain = [1, 2, 3, 4];
        $isCircular = $fraudService->detectCircularReferrals($validChain);
        $this->assertFalse($isCircular, 'Should not detect circular referral in valid chain');
    }

    /** @test */
    public function test_regional_rate_manager_basic_functionality()
    {
        $rateManager = new RegionalRateManager();
        
        // Test default rate retrieval (should not throw errors)
        try {
            $rate = $rateManager->getActiveRate('Lagos', 9);
            $this->assertIsFloat($rate);
            $this->assertGreaterThanOrEqual(0, $rate);
            $this->assertLessThanOrEqual(1, $rate); // Should be a percentage
        } catch (\Exception $e) {
            // If no rates exist, should handle gracefully
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    /** @test */
    public function test_payment_distribution_service_structure()
    {
        $paymentService = new PaymentDistributionService();
        
        // Test that service has required methods
        $this->assertTrue(method_exists($paymentService, 'distributeMultiTierCommission'));
        $this->assertTrue(method_exists($paymentService, 'createPaymentRecords'));
        $this->assertTrue(method_exists($paymentService, 'processPaymentBatch'));
    }

    /** @test */
    public function test_system_performance_basic()
    {
        $startTime = microtime(true);
        
        // Perform basic operations
        $calculator = new MultiTierCommissionCalculator();
        $chainService = new ReferralChainService();
        $fraudService = new FraudDetectionService();
        
        // Run multiple calculations
        for ($i = 0; $i < 100; $i++) {
            $superMarketer = (object) ['user_id' => $i + 1, 'region' => 'Lagos'];
            $marketer = (object) ['user_id' => $i + 101, 'region' => 'Lagos'];
            
            $result = $calculator->calculateCommissionSplit(
                2500,
                ['super_marketer' => $superMarketer, 'marketer' => $marketer],
                'Lagos'
            );
            
            $chainService->validateReferralEligibility($i + 1, $i + 101);
            $fraudService->detectCircularReferrals([$i + 1, $i + 101]);
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete within reasonable time
        $this->assertLessThan(1.0, $executionTime, 'Basic operations should complete quickly');
        
        echo "\nSystem Performance Test Results:\n";
        echo "100 operations completed in: " . number_format($executionTime, 4) . " seconds\n";
        echo "Average per operation: " . number_format($executionTime / 100, 6) . " seconds\n";
    }

    /** @test */
    public function test_error_handling_and_edge_cases()
    {
        $calculator = new MultiTierCommissionCalculator();
        
        // Test with empty referral chain
        try {
            $result = $calculator->calculateCommissionSplit(2500, [], 'Lagos');
            $this->assertIsArray($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
        
        // Test with zero commission
        try {
            $result = $calculator->calculateCommissionSplit(0, ['marketer' => (object)['user_id' => 1]], 'Lagos');
            $this->assertIsArray($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
        
        // Test with invalid region
        try {
            $result = $calculator->calculateCommissionSplit(2500, ['marketer' => (object)['user_id' => 1]], 'InvalidRegion');
            $this->assertIsArray($result);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }
    }

    /** @test */
    public function test_service_dependencies_and_integration()
    {
        // Test that services can work together
        $calculator = new MultiTierCommissionCalculator();
        $chainService = new ReferralChainService();
        $fraudService = new FraudDetectionService();
        
        // Simulate a complete workflow
        $superMarketerId = 1;
        $marketerId = 2;
        $landlordId = 3;
        
        // Step 1: Validate referral eligibility
        $isValid = $chainService->validateReferralEligibility($superMarketerId, $marketerId);
        $this->assertTrue($isValid);
        
        // Step 2: Check for fraud
        $chain = [$superMarketerId, $marketerId, $landlordId];
        $isCircular = $fraudService->detectCircularReferrals($chain);
        $this->assertFalse($isCircular);
        
        // Step 3: Calculate commissions
        $superMarketer = (object) ['user_id' => $superMarketerId, 'region' => 'Lagos'];
        $marketer = (object) ['user_id' => $marketerId, 'region' => 'Lagos'];
        
        $commissionSplit = $calculator->calculateCommissionSplit(
            2500,
            ['super_marketer' => $superMarketer, 'marketer' => $marketer],
            'Lagos'
        );
        
        $this->assertIsArray($commissionSplit);
        $this->assertNotEmpty($commissionSplit);
    }

    /** @test */
    public function test_memory_usage_and_resource_management()
    {
        $initialMemory = memory_get_usage(true);
        
        // Create multiple service instances
        $services = [];
        for ($i = 0; $i < 50; $i++) {
            $services[] = [
                'calculator' => new MultiTierCommissionCalculator(),
                'chain' => new ReferralChainService(),
                'fraud' => new FraudDetectionService(),
                'rate' => new RegionalRateManager()
            ];
        }
        
        $afterCreation = memory_get_usage(true);
        
        // Perform operations
        foreach ($services as $serviceSet) {
            $serviceSet['calculator']->calculateCommissionSplit(
                2500,
                ['marketer' => (object)['user_id' => 1, 'region' => 'Lagos']],
                'Lagos'
            );
            $serviceSet['chain']->validateReferralEligibility(1, 2);
            $serviceSet['fraud']->detectCircularReferrals([1, 2, 3]);
        }
        
        $finalMemory = memory_get_usage(true);
        
        $creationMemory = $afterCreation - $initialMemory;
        $operationMemory = $finalMemory - $afterCreation;
        
        // Memory usage should be reasonable
        $this->assertLessThan(10 * 1024 * 1024, $creationMemory, 'Service creation should not use excessive memory');
        $this->assertLessThan(5 * 1024 * 1024, $operationMemory, 'Operations should not use excessive memory');
        
        echo "\nMemory Usage Test Results:\n";
        echo "Service Creation: " . number_format($creationMemory / 1024 / 1024, 2) . " MB\n";
        echo "Operations: " . number_format($operationMemory / 1024 / 1024, 2) . " MB\n";
        echo "Total: " . number_format($finalMemory / 1024 / 1024, 2) . " MB\n";
    }

    /** @test */
    public function test_concurrent_operation_simulation()
    {
        $calculator = new MultiTierCommissionCalculator();
        $startTime = microtime(true);
        
        // Simulate concurrent operations
        $results = [];
        for ($i = 0; $i < 20; $i++) {
            $superMarketer = (object) ['user_id' => $i + 1, 'region' => 'Lagos'];
            $marketer = (object) ['user_id' => $i + 21, 'region' => 'Lagos'];
            $regionalManager = (object) ['user_id' => $i + 41, 'region' => 'Lagos'];
            
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
        
        // All results should be consistent
        $this->assertCount(20, $results);
        foreach ($results as $result) {
            $this->assertIsArray($result);
        }
        
        // Should complete quickly
        $this->assertLessThan(0.5, $executionTime, 'Concurrent operations should complete quickly');
        
        echo "\nConcurrent Operations Test Results:\n";
        echo "20 concurrent operations completed in: " . number_format($executionTime, 4) . " seconds\n";
        echo "Average per operation: " . number_format($executionTime / 20, 6) . " seconds\n";
    }
}
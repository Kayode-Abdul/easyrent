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
use Illuminate\Foundation\Testing\WithFaker;

class SuperMarketerSystemIntegrationTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $superMarketer;
    protected $marketer;
    protected $landlord;
    protected $regionalManager;
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestData();
    }

    private function setupTestData()
    {
        // Create roles
        Role::create(['id' => 9, 'name' => 'Super Marketer', 'description' => 'Super Marketer Role']);
        Role::create(['id' => 7, 'name' => 'Marketer', 'description' => 'Marketer Role']);
        Role::create(['id' => 3, 'name' => 'Landlord', 'description' => 'Landlord Role']);
        Role::create(['id' => 8, 'name' => 'Regional Manager', 'description' => 'Regional Manager Role']);

        // Create users
        $this->superMarketer = User::factory()->create([
            'email' => 'supermarketer@test.com',
            'region' => 'Lagos'
        ]);
        $this->superMarketer->roles()->attach(9);

        $this->marketer = User::factory()->create([
            'email' => 'marketer@test.com',
            'region' => 'Lagos'
        ]);
        $this->marketer->roles()->attach(7);

        $this->landlord = User::factory()->create([
            'email' => 'landlord@test.com',
            'region' => 'Lagos'
        ]);
        $this->landlord->roles()->attach(3);

        $this->regionalManager = User::factory()->create([
            'email' => 'regional@test.com',
            'region' => 'Lagos'
        ]);
        $this->regionalManager->roles()->attach(8);

        // Create commission rates
        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 9,
            'commission_percentage' => 0.008, // 0.8%
            'effective_from' => now(),
            'created_by' => 1,
            'is_active' => true
        ]);

        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 7,
            'commission_percentage' => 0.012, // 1.2%
            'effective_from' => now(),
            'created_by' => 1,
            'is_active' => true
        ]);

        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 8,
            'commission_percentage' => 0.005, // 0.5%
            'effective_from' => now(),
            'created_by' => 1,
            'is_active' => true
        ]);

        // Create property
        $this->property = Property::factory()->create([
            'user_id' => $this->landlord->user_id,
            'state' => 'Lagos',
            'rent_amount' => 100000
        ]);
    }

    /** @test */
    public function test_complete_referral_and_commission_flow()
    {
        // Step 1: Super Marketer refers Marketer
        $referralChainService = new ReferralChainService();
        
        $superMarketerReferral = Referral::create([
            'referrer_id' => $this->superMarketer->user_id,
            'referred_id' => $this->marketer->user_id,
            'referral_code' => 'SM_' . $this->superMarketer->user_id . '_' . time(),
            'status' => 'active',
            'referral_level' => 1,
            'commission_tier' => 'super_marketer'
        ]);

        // Step 2: Marketer refers Landlord
        $marketerReferral = Referral::create([
            'referrer_id' => $this->marketer->user_id,
            'referred_id' => $this->landlord->user_id,
            'referral_code' => 'M_' . $this->marketer->user_id . '_' . time(),
            'status' => 'active',
            'referral_level' => 2,
            'commission_tier' => 'marketer',
            'parent_referral_id' => $superMarketerReferral->id,
            'property_id' => $this->property->id
        ]);

        // Step 3: Create referral chain
        $referralChain = $referralChainService->createReferralChain(
            $this->superMarketer->user_id,
            $this->marketer->user_id,
            $this->landlord->user_id
        );

        $this->assertNotNull($referralChain);
        $this->assertDatabaseHas('referral_chains', [
            'super_marketer_id' => $this->superMarketer->user_id,
            'marketer_id' => $this->marketer->user_id,
            'landlord_id' => $this->landlord->user_id
        ]);

        // Step 4: Process rent payment and commission calculation
        $rentAmount = 100000;
        $calculator = new MultiTierCommissionCalculator();
        
        $commissionSplit = $calculator->calculateCommissionSplit(
            $rentAmount * 0.025, // 2.5% total commission
            [
                'super_marketer' => $this->superMarketer,
                'marketer' => $this->marketer,
                'regional_manager' => $this->regionalManager
            ],
            'Lagos'
        );

        $this->assertArrayHasKey('super_marketer', $commissionSplit);
        $this->assertArrayHasKey('marketer', $commissionSplit);
        $this->assertArrayHasKey('regional_manager', $commissionSplit);
        $this->assertEquals(800, $commissionSplit['super_marketer']); // 0.8% of 100000
        $this->assertEquals(1200, $commissionSplit['marketer']); // 1.2% of 100000
        $this->assertEquals(500, $commissionSplit['regional_manager']); // 0.5% of 100000

        // Step 5: Distribute payments
        $paymentService = new PaymentDistributionService();
        $paymentRecords = $paymentService->distributeMultiTierCommission(
            $rentAmount * 0.025,
            [
                'super_marketer' => $this->superMarketer,
                'marketer' => $this->marketer,
                'regional_manager' => $this->regionalManager
            ],
            'Lagos'
        );

        $this->assertCount(3, $paymentRecords);
        
        // Verify payments were created
        $this->assertDatabaseHas('commission_payments', [
            'user_id' => $this->superMarketer->user_id,
            'commission_tier' => 'super_marketer',
            'amount' => 800
        ]);

        $this->assertDatabaseHas('commission_payments', [
            'user_id' => $this->marketer->user_id,
            'commission_tier' => 'marketer',
            'amount' => 1200
        ]);

        $this->assertDatabaseHas('commission_payments', [
            'user_id' => $this->regionalManager->user_id,
            'commission_tier' => 'regional_manager',
            'amount' => 500
        ]);
    }

    /** @test */
    public function test_system_handles_missing_referral_tiers()
    {
        // Create direct marketer referral (no super marketer)
        $directReferral = Referral::create([
            'referrer_id' => $this->marketer->user_id,
            'referred_id' => $this->landlord->user_id,
            'referral_code' => 'DIRECT_' . time(),
            'status' => 'active',
            'referral_level' => 1,
            'commission_tier' => 'marketer',
            'property_id' => $this->property->id
        ]);

        $calculator = new MultiTierCommissionCalculator();
        $rentAmount = 100000;
        
        $commissionSplit = $calculator->calculateCommissionSplit(
            $rentAmount * 0.025,
            [
                'marketer' => $this->marketer,
                'regional_manager' => $this->regionalManager
            ],
            'Lagos'
        );

        // Should only have marketer and regional manager
        $this->assertArrayNotHasKey('super_marketer', $commissionSplit);
        $this->assertArrayHasKey('marketer', $commissionSplit);
        $this->assertArrayHasKey('regional_manager', $commissionSplit);
        
        // Company should retain the super marketer's share
        $this->assertEquals(1200, $commissionSplit['marketer']);
        $this->assertEquals(500, $commissionSplit['regional_manager']);
    }

    /** @test */
    public function test_fraud_detection_prevents_circular_referrals()
    {
        $referralChainService = new ReferralChainService();
        
        // Try to create circular referral (marketer refers super marketer)
        $isValid = $referralChainService->validateReferralEligibility(
            $this->marketer->user_id,
            $this->superMarketer->user_id
        );

        $this->assertFalse($isValid);
        
        // Try self-referral
        $isSelfValid = $referralChainService->validateReferralEligibility(
            $this->superMarketer->user_id,
            $this->superMarketer->user_id
        );

        $this->assertFalse($isSelfValid);
    }

    /** @test */
    public function test_regional_rate_management_affects_calculations()
    {
        // Update regional rates
        $rateManager = new RegionalRateManager();
        
        $rateManager->setRegionalRate('Lagos', 9, 0.010); // Increase super marketer rate
        $rateManager->setRegionalRate('Lagos', 7, 0.010); // Decrease marketer rate
        
        $calculator = new MultiTierCommissionCalculator();
        $rentAmount = 100000;
        
        $commissionSplit = $calculator->calculateCommissionSplit(
            $rentAmount * 0.025,
            [
                'super_marketer' => $this->superMarketer,
                'marketer' => $this->marketer,
                'regional_manager' => $this->regionalManager
            ],
            'Lagos'
        );

        // Verify new rates are applied
        $this->assertEquals(1000, $commissionSplit['super_marketer']); // 1.0% of 100000
        $this->assertEquals(1000, $commissionSplit['marketer']); // 1.0% of 100000
        $this->assertEquals(500, $commissionSplit['regional_manager']); // 0.5% unchanged
    }

    /** @test */
    public function test_system_performance_under_load()
    {
        $startTime = microtime(true);
        
        // Create multiple referral chains
        for ($i = 0; $i < 50; $i++) {
            $superMarketer = User::factory()->create(['region' => 'Lagos']);
            $superMarketer->roles()->attach(9);
            
            $marketer = User::factory()->create(['region' => 'Lagos']);
            $marketer->roles()->attach(7);
            
            $landlord = User::factory()->create(['region' => 'Lagos']);
            $landlord->roles()->attach(3);
            
            $property = Property::factory()->create([
                'user_id' => $landlord->user_id,
                'state' => 'Lagos'
            ]);

            // Create referral chain
            $referralChainService = new ReferralChainService();
            $referralChain = $referralChainService->createReferralChain(
                $superMarketer->user_id,
                $marketer->user_id,
                $landlord->user_id
            );

            // Calculate commissions
            $calculator = new MultiTierCommissionCalculator();
            $commissionSplit = $calculator->calculateCommissionSplit(
                100000 * 0.025,
                [
                    'super_marketer' => $superMarketer,
                    'marketer' => $marketer,
                    'regional_manager' => $this->regionalManager
                ],
                'Lagos'
            );

            $this->assertNotEmpty($commissionSplit);
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete within reasonable time (5 seconds for 50 chains)
        $this->assertLessThan(5.0, $executionTime, 'System performance test failed - took too long');
    }

    /** @test */
    public function test_data_consistency_across_transactions()
    {
        $referralChainService = new ReferralChainService();
        $calculator = new MultiTierCommissionCalculator();
        $paymentService = new PaymentDistributionService();

        // Create referral chain
        $referralChain = $referralChainService->createReferralChain(
            $this->superMarketer->user_id,
            $this->marketer->user_id,
            $this->landlord->user_id
        );

        // Process multiple payments for same chain
        for ($i = 0; $i < 5; $i++) {
            $rentAmount = 100000 + ($i * 10000);
            
            $commissionSplit = $calculator->calculateCommissionSplit(
                $rentAmount * 0.025,
                [
                    'super_marketer' => $this->superMarketer,
                    'marketer' => $this->marketer,
                    'regional_manager' => $this->regionalManager
                ],
                'Lagos'
            );

            $paymentRecords = $paymentService->distributeMultiTierCommission(
                $rentAmount * 0.025,
                [
                    'super_marketer' => $this->superMarketer,
                    'marketer' => $this->marketer,
                    'regional_manager' => $this->regionalManager
                ],
                'Lagos'
            );

            $this->assertCount(3, $paymentRecords);
        }

        // Verify total payments match expected amounts
        $totalSuperMarketerPayments = CommissionPayment::where('user_id', $this->superMarketer->user_id)
            ->where('commission_tier', 'super_marketer')
            ->sum('amount');

        $expectedTotal = 0;
        for ($i = 0; $i < 5; $i++) {
            $rentAmount = 100000 + ($i * 10000);
            $expectedTotal += ($rentAmount * 0.008); // 0.8% super marketer rate
        }

        $this->assertEquals($expectedTotal, $totalSuperMarketerPayments);
    }
}
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Commission\MultiTierCommissionCalculator;
use App\Services\Commission\RegionalRateManager;
use App\Models\User;
use App\Models\Role;
use App\Models\CommissionRate;
use App\Models\ReferralChain;
use App\Models\CommissionPayment;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class MultiTierCommissionCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected $calculator;
    protected $mockRateManager;
    protected $superMarketer;
    protected $marketer;
    protected $regionalManager;
    protected $superMarketerRole;
    protected $marketerRole;
    protected $regionalManagerRole;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockRateManager = Mockery::mock(RegionalRateManager::class);
        $this->calculator = new MultiTierCommissionCalculator($this->mockRateManager);
        
        // Create roles if they don't exist
        if (!Role::where('name', 'super_marketer')->exists()) {
            $this->superMarketerRole = Role::create(['name' => 'super_marketer']);
        } else {
            $this->superMarketerRole = Role::where('name', 'super_marketer')->first();
        }
        
        if (!Role::where('name', 'marketer')->exists()) {
            $this->marketerRole = Role::create(['name' => 'marketer']);
        } else {
            $this->marketerRole = Role::where('name', 'marketer')->first();
        }
        
        if (!Role::where('name', 'regional_manager')->exists()) {
            $this->regionalManagerRole = Role::create(['name' => 'regional_manager']);
        } else {
            $this->regionalManagerRole = Role::where('name', 'regional_manager')->first();
        }
        
        // Create users
        $this->superMarketer = User::factory()->create(['user_id' => 1001]);
        $this->marketer = User::factory()->create(['user_id' => 1002]);
        $this->regionalManager = User::factory()->create(['user_id' => 1003]);
        
        // Assign roles
        $this->superMarketer->roles()->attach($this->superMarketerRole->id);
        $this->marketer->roles()->attach($this->marketerRole->id);
        $this->regionalManager->roles()->attach($this->regionalManagerRole->id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_calculates_commission_split_for_complete_hierarchy()
    {
        $totalCommission = 100.0;
        $region = 'Lagos';
        
        $referralChain = [
            ['user_id' => $this->superMarketer->user_id, 'role_id' => $this->superMarketerRole->id],
            ['user_id' => $this->marketer->user_id, 'role_id' => $this->marketerRole->id],
            ['user_id' => $this->regionalManager->user_id, 'role_id' => $this->regionalManagerRole->id]
        ];

        // Mock rate manager responses
        $this->mockRateManager->shouldReceive('getActiveRate')
            ->with($region, $this->superMarketerRole->id)->andReturn(0.8); // Super Marketer
        $this->mockRateManager->shouldReceive('getActiveRate')
            ->with($region, $this->marketerRole->id)->andReturn(0.7); // Marketer
        $this->mockRateManager->shouldReceive('getActiveRate')
            ->with($region, $this->regionalManagerRole->id)->andReturn(1.0); // Regional Manager

        $splits = $this->calculator->calculateCommissionSplit($totalCommission, $referralChain, $region);

        $this->assertCount(4, $splits); // 3 participants + company
        $this->assertEquals(32.0, $splits[0]['amount']); // Super Marketer: 0.8% of 4000
        $this->assertEquals(28.0, $splits[1]['amount']); // Marketer: 0.7% of 4000
        $this->assertEquals(40.0, $splits[2]['amount']); // Regional Manager: 1.0% of 4000
        $this->assertEquals(0.0, $splits[3]['amount']); // Company: remaining (should be 0 for 2.5% total)
    }

    /** @test */
    public function it_handles_missing_super_marketer_tier()
    {
        $totalCommission = 100.0;
        $region = 'Lagos';
        
        $referralChain = [
            ['user_id' => $this->marketer->user_id, 'role_id' => $this->marketerRole->id],
            ['user_id' => $this->regionalManager->user_id, 'role_id' => $this->regionalManagerRole->id]
        ];

        $this->mockRateManager->shouldReceive('getActiveRate')
            ->with($region, $this->marketerRole->id)->andReturn(0.7);
        $this->mockRateManager->shouldReceive('getActiveRate')
            ->with($region, $this->regionalManagerRole->id)->andReturn(1.0);
        $this->mockRateManager->shouldReceive('getActiveRate')
            ->with($region, $this->superMarketerRole->id)->andReturn(0.8); // For company calculation

        $splits = $this->calculator->calculateCommissionSplit($totalCommission, $referralChain, $region);

        $this->assertCount(3, $splits); // 2 participants + company
        $this->assertEquals(28.0, $splits[0]['amount']); // Marketer
        $this->assertEquals(40.0, $splits[1]['amount']); // Regional Manager
        $this->assertEquals(32.0, $splits[2]['amount']); // Company gets Super Marketer's share
    }

    /** @test */
    public function it_validates_commission_total_within_limits()
    {
        $splits = [
            ['amount' => 32.0, 'percentage' => 0.8],
            ['amount' => 28.0, 'percentage' => 0.7],
            ['amount' => 40.0, 'percentage' => 1.0]
        ];

        $isValid = $this->calculator->validateCommissionTotal($splits);

        $this->assertTrue($isValid);
    }

    /** @test */
    public function it_rejects_commission_total_exceeding_limits()
    {
        $splits = [
            ['amount' => 60.0, 'percentage' => 1.5],
            ['amount' => 60.0, 'percentage' => 1.5],
            ['amount' => 60.0, 'percentage' => 1.5]
        ];

        $isValid = $this->calculator->validateCommissionTotal($splits);

        $this->assertFalse($isValid);
    }

    /** @test */
    public function it_gets_commission_breakdown_for_payment()
    {
        // Create a payment record
        $payment = Payment::create([
            'tenant_id' => 2001,
            'landlord_id' => 2002,
            'apartment_id' => 1,
            'amount' => 4000.0,
            'status' => 'success'
        ]);

        // Create commission payments
        CommissionPayment::create([
            'marketer_id' => $this->superMarketer->user_id,
            'commission_tier' => 'super_marketer',
            'total_amount' => 32.0,
            'regional_rate_applied' => 0.8,
            'payment_status' => 'completed',
            'transaction_id' => $payment->id
        ]);

        CommissionPayment::create([
            'marketer_id' => $this->marketer->user_id,
            'commission_tier' => 'marketer',
            'total_amount' => 28.0,
            'regional_rate_applied' => 0.7,
            'payment_status' => 'completed',
            'transaction_id' => $payment->id
        ]);

        $breakdown = $this->calculator->getCommissionBreakdown($payment->id);

        $this->assertCount(2, $breakdown);
        $this->assertEquals(32.0, $breakdown[0]['total_amount']);
        $this->assertEquals('super_marketer', $breakdown[0]['commission_tier']);
        $this->assertEquals(28.0, $breakdown[1]['total_amount']);
        $this->assertEquals('marketer', $breakdown[1]['commission_tier']);
    }

    /** @test */
    public function it_processes_commission_distribution_successfully()
    {
        // Create a payment record
        $payment = Payment::create([
            'tenant_id' => 2001,
            'landlord_id' => 2002,
            'apartment_id' => 1,
            'amount' => 4000.0,
            'status' => 'success'
        ]);

        // Create referral chain
        $chain = ReferralChain::create([
            'super_marketer_id' => $this->superMarketer->user_id,
            'marketer_id' => $this->marketer->user_id,
            'landlord_id' => 2001,
            'chain_hash' => 'test_chain_hash',
            'status' => 'active',
            'region' => 'Lagos'
        ]);

        $rentAmount = 4000.0;

        // Mock rates
        $this->mockRateManager->shouldReceive('getActiveRate')
            ->andReturn(0.8, 0.7, 1.0);

        $breakdown = $this->calculator->calculateChainCommission($chain, $rentAmount);

        $this->assertCount(3, $breakdown); // Adjust based on expected
        $this->assertEquals(32.0, $breakdown[0]['amount']);
    }

    /** @test */
    public function it_gets_tier_commission_rate()
    {
        $this->mockRateManager->shouldReceive('getActiveRate')
            ->with('Lagos', $this->superMarketerRole->id)
            ->andReturn(0.8);

        $rate = $this->calculator->getTierCommissionRate('super_marketer', 'Lagos');

        $this->assertEquals(0.8, $rate);
    }

    /** @test */
    public function it_validates_user_tier_eligibility()
    {
        $isEligible = $this->calculator->validateUserTierEligibility($this->superMarketer->user_id, 'super_marketer');

        $this->assertTrue($isEligible);

        $isEligible = $this->calculator->validateUserTierEligibility($this->marketer->user_id, 'super_marketer');

        $this->assertFalse($isEligible);
    }

    /** @test */
    public function it_throws_exception_for_invalid_chain()
    {
        $this->expectException(Exception::class);

        $invalidChain = []; // Empty chain

        $this->calculator->calculateCommissionSplit(100.0, $invalidChain, 'Lagos');
    }

}
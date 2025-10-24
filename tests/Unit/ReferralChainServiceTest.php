<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Commission\ReferralChainService;
use App\Services\Fraud\FraudDetectionService;
use App\Models\User;
use App\Models\Role;
use App\Models\Referral;
use App\Models\ReferralChain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class ReferralChainServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $chainService;
    protected $mockFraudService;
    protected $superMarketer;
    protected $marketer;
    protected $landlord;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockFraudService = Mockery::mock(FraudDetectionService::class);
        $this->chainService = new ReferralChainService($this->mockFraudService);
        
        // Create roles
        Role::create(['id' => 9, 'name' => 'super_marketer']);
        Role::create(['id' => 3, 'name' => 'marketer']);
        Role::create(['id' => 2, 'name' => 'landlord']);
        
        // Create users
        $this->superMarketer = User::factory()->create(['user_id' => 1001]);
        $this->marketer = User::factory()->create(['user_id' => 1002]);
        $this->landlord = User::factory()->create(['user_id' => 1003]);
        
        // Assign roles
        $this->superMarketer->roles()->attach(9);
        $this->marketer->roles()->attach(3);
        $this->landlord->roles()->attach(2);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_creates_complete_referral_chain_successfully()
    {
        $this->mockFraudService->shouldReceive('detectCircularReferrals')
            ->andReturn(false);
        $this->mockFraudService->shouldReceive('validateReferralAuthenticity')
            ->andReturn(true);

        $chain = $this->chainService->createReferralChain(
            $this->superMarketer->user_id,
            $this->marketer->user_id,
            $this->landlord->user_id
        );

        $this->assertNotNull($chain);
        $this->assertEquals($this->superMarketer->user_id, $chain['super_marketer_id']);
        $this->assertEquals($this->marketer->user_id, $chain['marketer_id']);
        $this->assertEquals($this->landlord->user_id, $chain['landlord_id']);
        $this->assertEquals('active', $chain['status']);
        
        // Verify referral chain record was created
        $chainRecord = ReferralChain::where('chain_hash', $chain['chain_hash'])->first();
        $this->assertNotNull($chainRecord);
    }

    /** @test */
    public function it_creates_partial_chain_without_super_marketer()
    {
        $this->mockFraudService->shouldReceive('detectCircularReferrals')
            ->andReturn(false);
        $this->mockFraudService->shouldReceive('validateReferralAuthenticity')
            ->andReturn(true);

        $chain = $this->chainService->createReferralChain(
            null,
            $this->marketer->user_id,
            $this->landlord->user_id
        );

        $this->assertNotNull($chain);
        $this->assertNull($chain['super_marketer_id']);
        $this->assertEquals($this->marketer->user_id, $chain['marketer_id']);
        $this->assertEquals($this->landlord->user_id, $chain['landlord_id']);
        $this->assertEquals('active', $chain['status']);
    }

    /** @test */
    public function it_prevents_circular_referral_chain_creation()
    {
        $this->mockFraudService->shouldReceive('detectCircularReferrals')
            ->with($this->superMarketer->user_id, $this->marketer->user_id)
            ->andReturn(true);

        $chain = $this->chainService->createReferralChain(
            $this->superMarketer->user_id,
            $this->marketer->user_id,
            $this->landlord->user_id
        );

        $this->assertNull($chain);
    }

    /** @test */
    public function it_gets_referral_hierarchy_correctly()
    {
        // Create referral chain
        $chainRecord = ReferralChain::create([
            'super_marketer_id' => $this->superMarketer->user_id,
            'marketer_id' => $this->marketer->user_id,
            'landlord_id' => $this->landlord->user_id,
            'chain_hash' => 'test_hash_123',
            'status' => 'active'
        ]);

        // Create referral records
        $superMarketerReferral = Referral::create([
            'referrer_id' => $this->superMarketer->user_id,
            'referred_id' => $this->marketer->user_id,
            'referral_status' => 'active',
            'commission_tier' => 'super_marketer',
            'referral_level' => 1
        ]);

        $marketerReferral = Referral::create([
            'referrer_id' => $this->marketer->user_id,
            'referred_id' => $this->landlord->user_id,
            'referral_status' => 'active',
            'commission_tier' => 'marketer',
            'referral_level' => 2,
            'parent_referral_id' => $superMarketerReferral->id
        ]);

        $hierarchy = $this->chainService->getReferralHierarchy($marketerReferral->id);

        $this->assertCount(2, $hierarchy);
        $this->assertEquals($this->superMarketer->user_id, $hierarchy[0]['referrer_id']);
        $this->assertEquals($this->marketer->user_id, $hierarchy[1]['referrer_id']);
        $this->assertEquals(1, $hierarchy[0]['referral_level']);
        $this->assertEquals(2, $hierarchy[1]['referral_level']);
    }

    /** @test */
    public function it_validates_referral_eligibility_successfully()
    {
        $this->mockFraudService->shouldReceive('detectCircularReferrals')
            ->with($this->superMarketer->user_id, $this->marketer->user_id)
            ->andReturn(false);

        $isEligible = $this->chainService->validateReferralEligibility(
            $this->superMarketer->user_id,
            $this->marketer->user_id
        );

        $this->assertTrue($isEligible);
    }

    /** @test */
    public function it_rejects_self_referral()
    {
        $isEligible = $this->chainService->validateReferralEligibility(
            $this->superMarketer->user_id,
            $this->superMarketer->user_id
        );

        $this->assertFalse($isEligible);
    }

    /** @test */
    public function it_rejects_referral_with_circular_dependency()
    {
        $this->mockFraudService->shouldReceive('detectCircularReferrals')
            ->with($this->superMarketer->user_id, $this->marketer->user_id)
            ->andReturn(true);

        $isEligible = $this->chainService->validateReferralEligibility(
            $this->superMarketer->user_id,
            $this->marketer->user_id
        );

        $this->assertFalse($isEligible);
    }

    /** @test */
    public function it_detects_circular_referrals_in_existing_chain()
    {
        // Create existing referral A -> B
        Referral::create([
            'referrer_id' => $this->superMarketer->user_id,
            'referred_id' => $this->marketer->user_id,
            'referral_status' => 'active'
        ]);

        $isCircular = $this->chainService->detectCircularReferrals(
            $this->marketer->user_id,
            $this->superMarketer->user_id
        );

        $this->assertTrue($isCircular);
    }

    /** @test */
    public function it_handles_broken_referral_chains()
    {
        // Create referral with missing parent
        $referral = Referral::create([
            'referrer_id' => $this->marketer->user_id,
            'referred_id' => $this->landlord->user_id,
            'referral_status' => 'active',
            'parent_referral_id' => 999999 // Non-existent parent
        ]);

        $hierarchy = $this->chainService->getReferralHierarchy($referral->id);

        $this->assertCount(1, $hierarchy);
        $this->assertEquals($this->marketer->user_id, $hierarchy[0]['referrer_id']);
    }

    /** @test */
    public function it_generates_unique_chain_hash()
    {
        $this->mockFraudService->shouldReceive('detectCircularReferrals')
            ->andReturn(false);
        $this->mockFraudService->shouldReceive('validateReferralAuthenticity')
            ->andReturn(true);

        $chain1 = $this->chainService->createReferralChain(
            $this->superMarketer->user_id,
            $this->marketer->user_id,
            $this->landlord->user_id
        );

        // Create another user and chain
        $anotherLandlord = User::factory()->create(['user_id' => 1004]);
        $anotherLandlord->roles()->attach(2);

        $chain2 = $this->chainService->createReferralChain(
            $this->superMarketer->user_id,
            $this->marketer->user_id,
            $anotherLandlord->user_id
        );

        $this->assertNotEquals($chain1['chain_hash'], $chain2['chain_hash']);
    }

    /** @test */
    public function it_validates_role_hierarchy_in_chain()
    {
        // Create user with wrong role
        $wrongRoleUser = User::factory()->create(['user_id' => 1005]);
        $wrongRoleUser->roles()->attach(2); // Landlord role instead of marketer

        $this->mockFraudService->shouldReceive('detectCircularReferrals')
            ->andReturn(false);

        $chain = $this->chainService->createReferralChain(
            $this->superMarketer->user_id,
            $wrongRoleUser->user_id, // Wrong role
            $this->landlord->user_id
        );

        $this->assertNull($chain); // Should fail due to role validation
    }

    /** @test */
    public function it_tracks_referral_chain_status()
    {
        $this->mockFraudService->shouldReceive('detectCircularReferrals')
            ->andReturn(false);
        $this->mockFraudService->shouldReceive('validateReferralAuthenticity')
            ->andReturn(true);

        $chain = $this->chainService->createReferralChain(
            $this->superMarketer->user_id,
            $this->marketer->user_id,
            $this->landlord->user_id
        );

        // Update chain status
        $updated = $this->chainService->updateChainStatus($chain['chain_hash'], 'suspended');

        $this->assertTrue($updated);
        
        $chainRecord = ReferralChain::where('chain_hash', $chain['chain_hash'])->first();
        $this->assertEquals('suspended', $chainRecord->status);
    }

    /** @test */
    public function it_gets_chain_performance_metrics()
    {
        // Create chain with some activity
        $chainRecord = ReferralChain::create([
            'super_marketer_id' => $this->superMarketer->user_id,
            'marketer_id' => $this->marketer->user_id,
            'landlord_id' => $this->landlord->user_id,
            'chain_hash' => 'test_hash_123',
            'status' => 'active'
        ]);

        $metrics = $this->chainService->getChainPerformanceMetrics($chainRecord->id);

        $this->assertArrayHasKey('total_referrals', $metrics);
        $this->assertArrayHasKey('successful_conversions', $metrics);
        $this->assertArrayHasKey('total_commission_generated', $metrics);
        $this->assertArrayHasKey('chain_effectiveness_score', $metrics);
    }

    /** @test */
    public function it_breaks_referral_chain_successfully()
    {
        $this->mockFraudService->shouldReceive('detectCircularReferrals')
            ->andReturn(false);
        $this->mockFraudService->shouldReceive('validateReferralAuthenticity')
            ->andReturn(true);
    
        $chain = $this->chainService->createReferralChain(
            $this->superMarketer->user_id,
            $this->marketer->user_id,
            $this->landlord->user_id
        );
    
        $broken = $this->chainService->breakReferralChain($chain['id'], 'Test break');
    
        $this->assertTrue($broken);
        $updatedChain = ReferralChain::find($chain['id']);
        $this->assertEquals('broken', $updatedChain->status);
    
        // Check referrals are cancelled
        $referrals = Referral::whereIn('referrer_id', [$this->superMarketer->user_id, $this->marketer->user_id])->get();
        foreach ($referrals as $referral) {
            $this->assertEquals('cancelled', $referral->referral_status);
        }
    }

    /** @test */
    public function it_gets_user_referral_chains_correctly()
    {
        $this->mockFraudService->shouldReceive('detectCircularReferrals')
            ->andReturn(false);
        $this->mockFraudService->shouldReceive('validateReferralAuthenticity')
            ->andReturn(true);
    
        $chain1 = $this->chainService->createReferralChain(
            $this->superMarketer->user_id,
            $this->marketer->user_id,
            $this->landlord->user_id
        );
    
        $anotherLandlord = User::factory()->create(['user_id' => 1004]);
        $anotherLandlord->roles()->attach(2);
    
        $chain2 = $this->chainService->createReferralChain(
            $this->superMarketer->user_id,
            $this->marketer->user_id,
            $anotherLandlord->user_id
        );
    
        $chains = $this->chainService->getUserReferralChains($this->superMarketer->user_id, 'super_marketer');
    
        $this->assertCount(2, $chains);
        $this->assertEquals($chain1['id'], $chains[0]->id);
        $this->assertEquals($chain2['id'], $chains[1]->id);
    }
}
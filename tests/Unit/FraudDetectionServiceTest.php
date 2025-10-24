<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Fraud\FraudDetectionService;
use App\Models\User;
use App\Models\Referral;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class FraudDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $fraudService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fraudService = new FraudDetectionService();
    }

    /** @test */
    public function it_detects_self_referral_as_invalid()
    {
        // Create a user
        $user = User::factory()->create(['user_id' => 1001]);
        
        // Create a self-referral
        $referral = Referral::create([
            'referrer_id' => $user->user_id,
            'referred_id' => $user->user_id, // Same user
            'referral_code' => 'TEST123',
            'referral_status' => 'pending'
        ]);

        $isValid = $this->fraudService->validateReferralAuthenticity($referral->id);

        $this->assertFalse($isValid);
    }

    /** @test */
    public function it_detects_circular_referrals()
    {
        // Create users
        $userA = User::factory()->create(['user_id' => 1001]);
        $userB = User::factory()->create(['user_id' => 1002]);

        // Create referral A -> B
        Referral::create([
            'referrer_id' => $userA->user_id,
            'referred_id' => $userB->user_id,
            'referral_status' => 'active'
        ]);

        // Test circular referral B -> A
        $isCircular = $this->fraudService->detectCircularReferrals($userB->user_id, $userA->user_id);

        $this->assertTrue($isCircular);
    }

    /** @test */
    public function it_calculates_fraud_risk_score()
    {
        $user = User::factory()->create(['user_id' => 1001]);

        // Create multiple rapid referrals to trigger suspicious pattern
        for ($i = 0; $i < 15; $i++) {
            $referredUser = User::factory()->create(['user_id' => 2000 + $i]);
            Referral::create([
                'referrer_id' => $user->user_id,
                'referred_id' => $referredUser->user_id,
                'referral_status' => 'active',
                'created_at' => now()->subMinutes($i) // Recent referrals
            ]);
        }

        $stats = $this->fraudService->getFraudStatistics($user->user_id);

        $this->assertGreaterThan(0, $stats['fraud_risk_score']);
        $this->assertGreaterThan(0, $stats['suspicious_patterns_count']);
    }

    /** @test */
    public function it_flags_account_for_review()
    {
        $user = User::factory()->create(['user_id' => 1001]);

        $reasons = ['rapid_referral_creation', 'unusual_success_rate'];
        $result = $this->fraudService->flagAccountForReview($user->user_id, $reasons);

        $this->assertTrue($result);
        
        $user->refresh();
        $this->assertTrue($user->isFlaggedForReview());
        $this->assertEquals($reasons, $user->getFlagReasons());
    }

    /** @test */
    public function it_validates_authentic_referral()
    {
        // Create users with different IDs
        $referrer = User::factory()->create(['user_id' => 1001]);
        $referred = User::factory()->create(['user_id' => 1002]);

        // Create roles
        $marketerRole = Role::create(['id' => 3, 'name' => 'marketer']);
        $landlordRole = Role::create(['id' => 2, 'name' => 'landlord']);

        // Assign roles
        $referrer->roles()->attach($marketerRole);
        $referred->roles()->attach($landlordRole);

        // Create valid referral
        $referral = Referral::create([
            'referrer_id' => $referrer->user_id,
            'referred_id' => $referred->user_id,
            'referral_status' => 'active'
        ]);

        $isValid = $this->fraudService->validateReferralAuthenticity($referral->id);

        $this->assertTrue($isValid);
    }
}
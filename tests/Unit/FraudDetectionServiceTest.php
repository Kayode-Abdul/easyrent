<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Fraud\FraudDetectionService;
use App\Models\User;
use App\Models\Referral;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FraudDetectionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $fraudService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fraudService = new FraudDetectionService();
        $this->seedStandardRoles();
    }

    protected function seedStandardRoles()
    {
        // Seed the standard roles with the IDs expected by the system
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Administrator role', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'landlord', 'display_name' => 'Landlord', 'description' => 'Landlord role', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'marketer', 'display_name' => 'Marketer', 'description' => 'Marketer role', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'tenant', 'display_name' => 'Tenant', 'description' => 'Tenant role', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'regional_manager', 'display_name' => 'Regional Manager', 'description' => 'Regional Manager role', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'name' => 'property_manager', 'display_name' => 'Property Manager', 'description' => 'Property Manager role', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'name' => 'super_marketer', 'display_name' => 'Super Marketer', 'description' => 'Super Marketer role', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
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

        // Assign roles using the role_user pivot table
        // The service expects: Role 3 = Marketer, Role 2 = Landlord
        $referrer->roles()->attach(3); // Marketer role
        $referred->roles()->attach(2); // Landlord role

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
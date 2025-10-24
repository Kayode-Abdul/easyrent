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
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;

class UserInterfaceIntegrationTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected $superMarketer;
    protected $marketer;
    protected $landlord;
    protected $regionalManager;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestData();
    }

    private function setupTestData()
    {
        // Create roles
        Role::create(['id' => 1, 'name' => 'Admin', 'description' => 'Administrator']);
        Role::create(['id' => 9, 'name' => 'Super Marketer', 'description' => 'Super Marketer Role']);
        Role::create(['id' => 7, 'name' => 'Marketer', 'description' => 'Marketer Role']);
        Role::create(['id' => 3, 'name' => 'Landlord', 'description' => 'Landlord Role']);
        Role::create(['id' => 8, 'name' => 'Regional Manager', 'description' => 'Regional Manager Role']);

        // Create users
        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->roles()->attach(1);

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
            'commission_percentage' => 0.008,
            'effective_from' => now(),
            'created_by' => $this->admin->user_id,
            'is_active' => true
        ]);

        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 7,
            'commission_percentage' => 0.012,
            'effective_from' => now(),
            'created_by' => $this->admin->user_id,
            'is_active' => true
        ]);

        // Create test data
        $this->createTestReferralsAndPayments();
    }

    private function createTestReferralsAndPayments()
    {
        // Create referrals
        $superMarketerReferral = Referral::create([
            'referrer_id' => $this->superMarketer->user_id,
            'referred_id' => $this->marketer->user_id,
            'referral_code' => 'SM_TEST_001',
            'status' => 'active',
            'referral_level' => 1,
            'commission_tier' => 'super_marketer'
        ]);

        $property = Property::factory()->create([
            'user_id' => $this->landlord->user_id,
            'state' => 'Lagos'
        ]);

        Referral::create([
            'referrer_id' => $this->marketer->user_id,
            'referred_id' => $this->landlord->user_id,
            'referral_code' => 'M_TEST_001',
            'status' => 'active',
            'referral_level' => 2,
            'commission_tier' => 'marketer',
            'parent_referral_id' => $superMarketerReferral->id,
            'property_id' => $property->id
        ]);

        // Create referral chain
        ReferralChain::create([
            'super_marketer_id' => $this->superMarketer->user_id,
            'marketer_id' => $this->marketer->user_id,
            'landlord_id' => $this->landlord->user_id,
            'chain_hash' => hash('sha256', $this->superMarketer->user_id . $this->marketer->user_id . $this->landlord->user_id),
            'status' => 'active'
        ]);

        // Create commission payments
        CommissionPayment::create([
            'user_id' => $this->superMarketer->user_id,
            'amount' => 800,
            'commission_tier' => 'super_marketer',
            'status' => 'completed',
            'regional_rate_applied' => 0.008
        ]);

        CommissionPayment::create([
            'user_id' => $this->marketer->user_id,
            'amount' => 1200,
            'commission_tier' => 'marketer',
            'status' => 'completed',
            'regional_rate_applied' => 0.012
        ]);
    }

    /** @test */
    public function test_super_marketer_dashboard_displays_correctly()
    {
        $response = $this->actingAs($this->superMarketer)
            ->get('/super-marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('super-marketer.dashboard');
        $response->assertViewHas('referredMarketers');
        $response->assertViewHas('totalCommissions');
        $response->assertViewHas('performanceMetrics');
        
        // Check that referred marketers are displayed
        $response->assertSee($this->marketer->name);
        $response->assertSee('Total Commission: ₦800');
    }

    /** @test */
    public function test_super_marketer_referred_marketers_page()
    {
        $response = $this->actingAs($this->superMarketer)
            ->get('/super-marketer/referred-marketers');

        $response->assertStatus(200);
        $response->assertViewIs('super-marketer.referred-marketers');
        $response->assertSee($this->marketer->name);
        $response->assertSee($this->marketer->email);
    }

    /** @test */
    public function test_super_marketer_performance_analytics()
    {
        $response = $this->actingAs($this->superMarketer)
            ->get('/super-marketer/marketer-performance');

        $response->assertStatus(200);
        $response->assertViewIs('super-marketer.marketer-performance');
        $response->assertViewHas('performanceData');
    }

    /** @test */
    public function test_enhanced_marketer_dashboard_shows_hierarchy()
    {
        $response = $this->actingAs($this->marketer)
            ->get('/marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('marketer.dashboard');
        $response->assertViewHas('referringSuperMarketer');
        $response->assertViewHas('commissionBreakdown');
        
        // Should show referring super marketer
        $response->assertSee($this->superMarketer->name);
        $response->assertSee('Referred by Super Marketer');
    }

    /** @test */
    public function test_regional_manager_multi_tier_analytics()
    {
        $response = $this->actingAs($this->regionalManager)
            ->get('/regional-manager/commission-analytics');

        $response->assertStatus(200);
        $response->assertViewIs('regional_manager.commission-analytics');
        $response->assertViewHas('commissionBreakdown');
        $response->assertViewHas('tierPerformance');
        
        // Should show multi-tier breakdown
        $response->assertSee('Super Marketer Commission');
        $response->assertSee('Marketer Commission');
        $response->assertSee('Regional Manager Commission');
    }

    /** @test */
    public function test_admin_regional_commission_management()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/commission-rates');

        $response->assertStatus(200);
        $response->assertViewIs('admin.commission-rates.index');
        $response->assertViewHas('commissionRates');
        
        // Should show existing rates
        $response->assertSee('Lagos');
        $response->assertSee('0.8%'); // Super Marketer rate
        $response->assertSee('1.2%'); // Marketer rate
    }

    /** @test */
    public function test_admin_can_create_new_commission_rate()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/commission-rates/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin.commission-rates.create');
        
        // Test form submission
        $response = $this->actingAs($this->admin)
            ->post('/admin/commission-rates', [
                'region' => 'Abuja',
                'role_id' => 9,
                'commission_percentage' => 0.009,
                'effective_from' => now()->format('Y-m-d H:i:s')
            ]);

        $response->assertRedirect('/admin/commission-rates');
        $this->assertDatabaseHas('commission_rates', [
            'region' => 'Abuja',
            'role_id' => 9,
            'commission_percentage' => 0.009
        ]);
    }

    /** @test */
    public function test_admin_bulk_rate_update()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/commission-rates/bulk-update');

        $response->assertStatus(200);
        $response->assertViewIs('admin.commission-rates.bulk-update');
        
        // Test bulk update submission
        $response = $this->actingAs($this->admin)
            ->post('/admin/commission-rates/bulk-update', [
                'rates' => [
                    [
                        'region' => 'Lagos',
                        'role_id' => 9,
                        'commission_percentage' => 0.010
                    ],
                    [
                        'region' => 'Lagos',
                        'role_id' => 7,
                        'commission_percentage' => 0.010
                    ]
                ]
            ]);

        $response->assertRedirect('/admin/commission-rates');
        
        // Verify rates were updated
        $this->assertDatabaseHas('commission_rates', [
            'region' => 'Lagos',
            'role_id' => 9,
            'commission_percentage' => 0.010,
            'is_active' => true
        ]);
    }

    /** @test */
    public function test_landlord_commission_transparency()
    {
        $property = Property::factory()->create([
            'user_id' => $this->landlord->user_id,
            'state' => 'Lagos'
        ]);

        $response = $this->actingAs($this->landlord)
            ->get("/property/{$property->id}");

        $response->assertStatus(200);
        $response->assertSee('Commission Breakdown');
        
        // Test commission transparency modal
        $response = $this->actingAs($this->landlord)
            ->get("/landlord/commission-transparency/{$property->id}");

        $response->assertStatus(200);
        $response->assertViewIs('landlord.commission-transparency');
        $response->assertViewHas('commissionBreakdown');
    }

    /** @test */
    public function test_system_analytics_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/system-analytics');

        $response->assertStatus(200);
        $response->assertViewIs('admin.system-analytics.dashboard');
        $response->assertViewHas('systemMetrics');
        $response->assertViewHas('commissionHealth');
        
        // Should show system-wide metrics
        $response->assertSee('Total Commission Processed');
        $response->assertSee('Active Referral Chains');
        $response->assertSee('System Performance');
    }

    /** @test */
    public function test_role_switching_works_with_hierarchy()
    {
        // Create user with multiple roles
        $multiRoleUser = User::factory()->create([
            'email' => 'multirole@test.com',
            'region' => 'Lagos'
        ]);
        $multiRoleUser->roles()->attach([7, 9]); // Both Marketer and Super Marketer

        $response = $this->actingAs($multiRoleUser)
            ->get('/role-switcher');

        $response->assertStatus(200);
        $response->assertSee('Super Marketer');
        $response->assertSee('Marketer');
        
        // Test switching to Super Marketer role
        $response = $this->actingAs($multiRoleUser)
            ->post('/switch-role', ['role_id' => 9]);

        $response->assertRedirect('/super-marketer/dashboard');
        
        // Test switching to Marketer role
        $response = $this->actingAs($multiRoleUser)
            ->post('/switch-role', ['role_id' => 7]);

        $response->assertRedirect('/marketer/dashboard');
    }

    /** @test */
    public function test_api_endpoints_work_with_hierarchy()
    {
        // Test Super Marketer API endpoints
        $response = $this->actingAs($this->superMarketer)
            ->getJson('/api/super-marketer/referred-marketers');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'total_referrals',
                    'total_commissions'
                ]
            ]
        ]);

        // Test commission breakdown API
        $response = $this->actingAs($this->marketer)
            ->getJson('/api/marketer/commission-breakdown');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'referring_super_marketer',
                'commission_breakdown',
                'total_earned'
            ]
        ]);
    }

    /** @test */
    public function test_error_handling_in_user_interfaces()
    {
        // Test accessing Super Marketer dashboard without proper role
        $response = $this->actingAs($this->marketer)
            ->get('/super-marketer/dashboard');

        $response->assertStatus(403);
        
        // Test accessing non-existent commission rate
        $response = $this->actingAs($this->admin)
            ->get('/admin/commission-rates/99999');

        $response->assertStatus(404);
        
        // Test invalid bulk update data
        $response = $this->actingAs($this->admin)
            ->post('/admin/commission-rates/bulk-update', [
                'rates' => [
                    [
                        'region' => 'Lagos',
                        'role_id' => 9,
                        'commission_percentage' => 0.050 // Exceeds limit
                    ]
                ]
            ]);

        $response->assertSessionHasErrors();
    }
}
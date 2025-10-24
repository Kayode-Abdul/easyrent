<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Property;
use App\Models\Referral;
use App\Models\ReferralChain;
use App\Models\CommissionPayment;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnhancedMarketerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $superMarketer;
    protected $marketer;
    protected $landlord;
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['id' => 2, 'name' => 'landlord']);
        Role::create(['id' => 3, 'name' => 'marketer']);
        Role::create(['id' => 9, 'name' => 'super_marketer']);
        
        // Create users
        $this->superMarketer = User::factory()->create([
            'user_id' => 1001,
            'name' => 'Super Marketer',
            'email' => 'super@example.com',
            'region' => 'Lagos'
        ]);
        
        $this->marketer = User::factory()->create([
            'user_id' => 1002,
            'name' => 'Marketer',
            'email' => 'marketer@example.com',
            'region' => 'Lagos'
        ]);
        
        $this->landlord = User::factory()->create([
            'user_id' => 1003,
            'name' => 'Landlord',
            'email' => 'landlord@example.com',
            'region' => 'Lagos'
        ]);
        
        // Assign roles
        $this->superMarketer->roles()->attach(9);
        $this->marketer->roles()->attach(3);
        $this->landlord->roles()->attach(2);
        
        $this->setupTestData();
    }

    protected function setupTestData()
    {
        // Create property
        $this->property = Property::create([
            'user_id' => $this->landlord->user_id,
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 4000.0,
            'location' => 'Lagos',
            'property_type' => 'apartment',
            'status' => 'active'
        ]);

        // Create referral chain
        $superMarketerReferral = Referral::create([
            'referrer_id' => $this->superMarketer->user_id,
            'referred_id' => $this->marketer->user_id,
            'referral_status' => 'active',
            'commission_tier' => 'super_marketer',
            'referral_level' => 1,
            'property_id' => $this->property->id
        ]);

        Referral::create([
            'referrer_id' => $this->marketer->user_id,
            'referred_id' => $this->landlord->user_id,
            'referral_status' => 'active',
            'commission_tier' => 'marketer',
            'referral_level' => 2,
            'parent_referral_id' => $superMarketerReferral->id,
            'property_id' => $this->property->id
        ]);

        // Create referral chain record
        ReferralChain::create([
            'super_marketer_id' => $this->superMarketer->user_id,
            'marketer_id' => $this->marketer->user_id,
            'landlord_id' => $this->landlord->user_id,
            'chain_hash' => 'test_hash_123',
            'status' => 'active'
        ]);

        // Create payment and commissions
        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $this->property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        CommissionPayment::create([
            'payment_id' => $payment->id,
            'user_id' => $this->superMarketer->user_id,
            'commission_amount' => 32.0,
            'commission_tier' => 'super_marketer',
            'regional_rate_applied' => 0.8,
            'payment_status' => 'completed'
        ]);

        CommissionPayment::create([
            'payment_id' => $payment->id,
            'user_id' => $this->marketer->user_id,
            'commission_amount' => 28.0,
            'commission_tier' => 'marketer',
            'regional_rate_applied' => 0.7,
            'payment_status' => 'completed'
        ]);
    }

    /** @test */
    public function marketer_can_access_enhanced_dashboard()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('marketer.dashboard');
    }

    /** @test */
    public function dashboard_displays_referring_super_marketer_information()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Super Marketer'); // Super marketer's name
        $response->assertSee('super@example.com'); // Super marketer's email
        $response->assertSee('Referred by'); // Section header
    }

    /** @test */
    public function dashboard_shows_referral_chain_visualization()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('referralChain');
        
        $referralChain = $response->viewData('referralChain');
        $this->assertNotNull($referralChain);
        $this->assertEquals($this->superMarketer->user_id, $referralChain['super_marketer_id']);
        $this->assertEquals($this->marketer->user_id, $referralChain['marketer_id']);
    }

    /** @test */
    public function dashboard_displays_hierarchical_commission_breakdown()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('commissionBreakdown');
        
        $breakdown = $response->viewData('commissionBreakdown');
        $this->assertArrayHasKey('marketer_commission', $breakdown);
        $this->assertArrayHasKey('super_marketer_commission', $breakdown);
        $this->assertEquals(28.0, $breakdown['marketer_commission']);
        $this->assertEquals(32.0, $breakdown['super_marketer_commission']);
    }

    /** @test */
    public function dashboard_shows_tier_information()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Tier 2'); // Marketer tier
        $response->assertSee('Commission Tier');
    }

    /** @test */
    public function marketer_can_view_detailed_commission_breakdown()
    {
        $payment = Payment::first();
        
        $response = $this->actingAs($this->marketer)
                         ->get("/marketer/commission-breakdown/{$payment->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'breakdown',
            'hierarchy',
            'total_commission',
            'marketer_share'
        ]);
    }

    /** @test */
    public function dashboard_displays_performance_comparison_with_hierarchy()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('performanceComparison');
        
        $comparison = $response->viewData('performanceComparison');
        $this->assertArrayHasKey('marketer_performance', $comparison);
        $this->assertArrayHasKey('super_marketer_performance', $comparison);
    }

    /** @test */
    public function marketer_can_view_referral_chain_details()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/referral-chain');

        $response->assertStatus(200);
        $response->assertViewIs('marketer.referral-chain');
        $response->assertViewHas('chainDetails');
    }

    /** @test */
    public function dashboard_shows_commission_history_with_tier_info()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('commissionHistory');
        
        $history = $response->viewData('commissionHistory');
        $this->assertNotEmpty($history);
        
        foreach ($history as $commission) {
            $this->assertArrayHasKey('commission_tier', $commission);
            $this->assertArrayHasKey('regional_rate_applied', $commission);
        }
    }

    /** @test */
    public function marketer_without_super_marketer_sees_direct_marketer_status()
    {
        // Create a marketer without super marketer referrer
        $directMarketer = User::factory()->create([
            'user_id' => 1004,
            'name' => 'Direct Marketer',
            'email' => 'direct@example.com',
            'region' => 'Lagos'
        ]);
        $directMarketer->roles()->attach(3);

        $response = $this->actingAs($directMarketer)
                         ->get('/marketer/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Direct Marketer');
        $response->assertSee('No referring Super Marketer');
    }

    /** @test */
    public function dashboard_displays_earnings_trend_with_hierarchy_context()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/earnings-trend');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'marketer_earnings',
            'super_marketer_earnings',
            'combined_chain_performance',
            'labels'
        ]);
    }

    /** @test */
    public function marketer_can_view_super_marketer_profile()
    {
        $response = $this->actingAs($this->marketer)
                         ->get("/marketer/super-marketer-profile/{$this->superMarketer->user_id}");

        $response->assertStatus(200);
        $response->assertViewIs('marketer.super-marketer-profile');
        $response->assertSee('Super Marketer');
        $response->assertViewHas('superMarketer');
    }

    /** @test */
    public function dashboard_shows_referral_performance_metrics()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('performanceMetrics');
        
        $metrics = $response->viewData('performanceMetrics');
        $this->assertArrayHasKey('total_referrals', $metrics);
        $this->assertArrayHasKey('successful_conversions', $metrics);
        $this->assertArrayHasKey('conversion_rate', $metrics);
        $this->assertArrayHasKey('chain_effectiveness', $metrics);
    }

    /** @test */
    public function marketer_can_export_commission_report_with_hierarchy()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/export/commission-report');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function dashboard_filters_work_with_hierarchy_data()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/dashboard?date_range=last_30_days&include_hierarchy=true');

        $response->assertStatus(200);
        $response->assertViewHas('dateRange', 'last_30_days');
        $response->assertViewHas('includeHierarchy', true);
    }

    /** @test */
    public function marketer_can_view_commission_comparison_chart()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/commission-comparison');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'marketer_data',
            'super_marketer_data',
            'comparison_metrics',
            'chart_config'
        ]);
    }

    /** @test */
    public function dashboard_shows_referral_chain_status()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('chainStatus');
        
        $chainStatus = $response->viewData('chainStatus');
        $this->assertEquals('active', $chainStatus);
    }

    /** @test */
    public function marketer_can_view_hierarchy_analytics()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/hierarchy-analytics');

        $response->assertStatus(200);
        $response->assertViewIs('marketer.hierarchy-analytics');
        $response->assertViewHas('analyticsData');
    }

    /** @test */
    public function dashboard_displays_recent_chain_activities()
    {
        $response = $this->actingAs($this->marketer)
                         ->get('/marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('recentChainActivities');
        
        $activities = $response->viewData('recentChainActivities');
        $this->assertNotEmpty($activities);
    }
}
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
use App\Models\CommissionRate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnhancedRegionalManagerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $regionalManager;
    protected $superMarketer;
    protected $marketer;
    protected $landlord;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['id' => 1, 'name' => 'admin']);
        Role::create(['id' => 2, 'name' => 'landlord']);
        Role::create(['id' => 3, 'name' => 'marketer']);
        Role::create(['id' => 5, 'name' => 'regional_manager']);
        Role::create(['id' => 9, 'name' => 'super_marketer']);
        
        // Create users
        $this->adminUser = User::factory()->create(['user_id' => 1000]);
        $this->regionalManager = User::factory()->create([
            'user_id' => 1001,
            'name' => 'Regional Manager',
            'email' => 'regional@example.com',
            'region' => 'Lagos'
        ]);
        
        $this->superMarketer = User::factory()->create([
            'user_id' => 1002,
            'name' => 'Super Marketer',
            'email' => 'super@example.com',
            'region' => 'Lagos'
        ]);
        
        $this->marketer = User::factory()->create([
            'user_id' => 1003,
            'name' => 'Marketer',
            'email' => 'marketer@example.com',
            'region' => 'Lagos'
        ]);
        
        $this->landlord = User::factory()->create([
            'user_id' => 1004,
            'name' => 'Landlord',
            'email' => 'landlord@example.com',
            'region' => 'Lagos'
        ]);
        
        // Assign roles
        $this->adminUser->roles()->attach(1);
        $this->regionalManager->roles()->attach(5);
        $this->superMarketer->roles()->attach(9);
        $this->marketer->roles()->attach(3);
        $this->landlord->roles()->attach(2);
        
        $this->setupTestData();
    }

    protected function setupTestData()
    {
        // Create commission rates
        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 9,
            'commission_percentage' => 0.8,
            'effective_from' => now()->subDay(),
            'created_by' => $this->adminUser->user_id,
            'is_active' => true
        ]);

        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 3,
            'commission_percentage' => 0.7,
            'effective_from' => now()->subDay(),
            'created_by' => $this->adminUser->user_id,
            'is_active' => true
        ]);

        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 5,
            'commission_percentage' => 1.0,
            'effective_from' => now()->subDay(),
            'created_by' => $this->adminUser->user_id,
            'is_active' => true
        ]);

        // Create properties
        $property1 = Property::create([
            'user_id' => $this->landlord->user_id,
            'title' => 'Test Property 1',
            'description' => 'Test Description',
            'price' => 4000.0,
            'location' => 'Lagos',
            'property_type' => 'apartment',
            'status' => 'active'
        ]);

        $property2 = Property::create([
            'user_id' => $this->landlord->user_id,
            'title' => 'Test Property 2',
            'description' => 'Test Description',
            'price' => 5000.0,
            'location' => 'Lagos',
            'property_type' => 'apartment',
            'status' => 'active'
        ]);

        // Create referral chains
        $this->createReferralChain($property1);
        $this->createReferralChain($property2);
    }

    protected function createReferralChain($property)
    {
        // Create referrals
        $superMarketerReferral = Referral::create([
            'referrer_id' => $this->superMarketer->user_id,
            'referred_id' => $this->marketer->user_id,
            'referral_status' => 'active',
            'commission_tier' => 'super_marketer',
            'referral_level' => 1,
            'property_id' => $property->id
        ]);

        Referral::create([
            'referrer_id' => $this->marketer->user_id,
            'referred_id' => $this->landlord->user_id,
            'referral_status' => 'active',
            'commission_tier' => 'marketer',
            'referral_level' => 2,
            'parent_referral_id' => $superMarketerReferral->id,
            'property_id' => $property->id
        ]);

        // Create referral chain
        ReferralChain::create([
            'super_marketer_id' => $this->superMarketer->user_id,
            'marketer_id' => $this->marketer->user_id,
            'landlord_id' => $this->landlord->user_id,
            'chain_hash' => 'hash_' . $property->id,
            'status' => 'active'
        ]);

        // Create payment and commissions
        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $property->id,
            'amount' => $property->price,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        CommissionPayment::create([
            'payment_id' => $payment->id,
            'user_id' => $this->superMarketer->user_id,
            'commission_amount' => $property->price * 0.008,
            'commission_tier' => 'super_marketer',
            'regional_rate_applied' => 0.8,
            'payment_status' => 'completed'
        ]);

        CommissionPayment::create([
            'payment_id' => $payment->id,
            'user_id' => $this->marketer->user_id,
            'commission_amount' => $property->price * 0.007,
            'commission_tier' => 'marketer',
            'regional_rate_applied' => 0.7,
            'payment_status' => 'completed'
        ]);

        CommissionPayment::create([
            'payment_id' => $payment->id,
            'user_id' => $this->regionalManager->user_id,
            'commission_amount' => $property->price * 0.01,
            'commission_tier' => 'regional_manager',
            'regional_rate_applied' => 1.0,
            'payment_status' => 'completed'
        ]);
    }

    /** @test */
    public function regional_manager_can_access_enhanced_dashboard()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('regional_manager_dashboard');
    }

    /** @test */
    public function dashboard_displays_multi_tier_commission_breakdown()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('commissionBreakdown');
        
        $breakdown = $response->viewData('commissionBreakdown');
        $this->assertArrayHasKey('super_marketer', $breakdown);
        $this->assertArrayHasKey('marketer', $breakdown);
        $this->assertArrayHasKey('regional_manager', $breakdown);
        $this->assertArrayHasKey('company', $breakdown);
    }

    /** @test */
    public function dashboard_shows_referral_chain_effectiveness_metrics()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('chainEffectiveness');
        
        $effectiveness = $response->viewData('chainEffectiveness');
        $this->assertArrayHasKey('total_chains', $effectiveness);
        $this->assertArrayHasKey('active_chains', $effectiveness);
        $this->assertArrayHasKey('conversion_rate', $effectiveness);
        $this->assertArrayHasKey('average_chain_value', $effectiveness);
    }

    /** @test */
    public function regional_manager_can_view_commission_analytics()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/commission-analytics');

        $response->assertStatus(200);
        $response->assertViewIs('regional_manager.commission-analytics');
        $response->assertViewHas('analyticsData');
    }

    /** @test */
    public function commission_analytics_includes_tier_breakdown_charts()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/commission-analytics');

        $response->assertStatus(200);
        
        $analyticsData = $response->viewData('analyticsData');
        $this->assertArrayHasKey('tier_breakdown', $analyticsData);
        $this->assertArrayHasKey('monthly_trends', $analyticsData);
        $this->assertArrayHasKey('performance_comparison', $analyticsData);
    }

    /** @test */
    public function regional_manager_can_view_chain_performance_details()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/chain-performance');

        $response->assertStatus(200);
        $response->assertViewIs('regional_manager.chain-performance');
        $response->assertViewHas('chainData');
    }

    /** @test */
    public function chain_performance_shows_individual_chain_metrics()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/chain-performance');

        $response->assertStatus(200);
        
        $chainData = $response->viewData('chainData');
        $this->assertNotEmpty($chainData);
        
        foreach ($chainData as $chain) {
            $this->assertArrayHasKey('chain_id', $chain);
            $this->assertArrayHasKey('super_marketer', $chain);
            $this->assertArrayHasKey('marketer', $chain);
            $this->assertArrayHasKey('total_commission', $chain);
            $this->assertArrayHasKey('effectiveness_score', $chain);
        }
    }

    /** @test */
    public function regional_manager_can_filter_analytics_by_date_range()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/commission-analytics?date_range=last_30_days');

        $response->assertStatus(200);
        $response->assertViewHas('dateRange', 'last_30_days');
    }

    /** @test */
    public function regional_manager_can_filter_by_commission_tier()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/commission-analytics?tier=super_marketer');

        $response->assertStatus(200);
        $response->assertViewHas('selectedTier', 'super_marketer');
    }

    /** @test */
    public function dashboard_displays_regional_performance_comparison()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('regionalComparison');
        
        $comparison = $response->viewData('regionalComparison');
        $this->assertArrayHasKey('current_region_performance', $comparison);
        $this->assertArrayHasKey('other_regions_average', $comparison);
        $this->assertArrayHasKey('ranking', $comparison);
    }

    /** @test */
    public function regional_manager_can_export_multi_tier_analytics()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/export/multi-tier-analytics');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function regional_manager_can_view_commission_trend_analysis()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/commission-trends');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'trends',
            'tier_performance',
            'growth_metrics',
            'forecasts'
        ]);
    }

    /** @test */
    public function dashboard_shows_top_performing_chains()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('topPerformingChains');
        
        $topChains = $response->viewData('topPerformingChains');
        $this->assertNotEmpty($topChains);
        
        foreach ($topChains as $chain) {
            $this->assertArrayHasKey('total_commission', $chain);
            $this->assertArrayHasKey('conversion_rate', $chain);
        }
    }

    /** @test */
    public function regional_manager_can_view_detailed_chain_analysis()
    {
        $chain = ReferralChain::first();
        
        $response = $this->actingAs($this->regionalManager)
                         ->get("/regional-manager/chain-analysis/{$chain->id}");

        $response->assertStatus(200);
        $response->assertViewIs('regional_manager.chain-analysis');
        $response->assertViewHas('chainDetails');
        $response->assertViewHas('performanceMetrics');
    }

    /** @test */
    public function dashboard_displays_commission_rate_effectiveness()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('rateEffectiveness');
        
        $effectiveness = $response->viewData('rateEffectiveness');
        $this->assertArrayHasKey('current_rates', $effectiveness);
        $this->assertArrayHasKey('performance_impact', $effectiveness);
        $this->assertArrayHasKey('optimization_suggestions', $effectiveness);
    }

    /** @test */
    public function regional_manager_can_view_marketer_hierarchy_overview()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/marketer-hierarchy');

        $response->assertStatus(200);
        $response->assertViewIs('regional_manager.marketer-hierarchy');
        $response->assertViewHas('hierarchyData');
    }

    /** @test */
    public function hierarchy_overview_shows_super_marketer_networks()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/marketer-hierarchy');

        $response->assertStatus(200);
        
        $hierarchyData = $response->viewData('hierarchyData');
        $this->assertArrayHasKey('super_marketers', $hierarchyData);
        $this->assertArrayHasKey('direct_marketers', $hierarchyData);
        $this->assertArrayHasKey('network_statistics', $hierarchyData);
    }

    /** @test */
    public function regional_manager_can_generate_performance_reports()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->post('/regional-manager/generate-report', [
                             'report_type' => 'multi_tier_performance',
                             'date_range' => 'last_quarter',
                             'include_forecasts' => true
                         ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'report_id',
            'download_url',
            'generation_status'
        ]);
    }

    /** @test */
    public function dashboard_shows_real_time_commission_metrics()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/real-time-metrics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'current_month_commission',
            'active_chains_count',
            'conversion_rate',
            'tier_distribution',
            'recent_activities'
        ]);
    }

    /** @test */
    public function regional_manager_can_view_commission_forecasting()
    {
        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/commission-forecasting');

        $response->assertStatus(200);
        $response->assertViewIs('regional_manager.commission-forecasting');
        $response->assertViewHas('forecastData');
    }

    /** @test */
    public function dashboard_pagination_works_for_chain_performance()
    {
        // Create additional chains for pagination testing
        for ($i = 0; $i < 15; $i++) {
            $property = Property::create([
                'user_id' => $this->landlord->user_id,
                'title' => "Property $i",
                'description' => 'Test Description',
                'price' => 3000.0,
                'location' => 'Lagos',
                'property_type' => 'apartment',
                'status' => 'active'
            ]);
            
            $this->createReferralChain($property);
        }

        $response = $this->actingAs($this->regionalManager)
                         ->get('/regional-manager/chain-performance?page=2');

        $response->assertStatus(200);
        $response->assertViewHas('chainData');
    }
}
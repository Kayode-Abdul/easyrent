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
use Illuminate\Support\Facades\Auth;

class SuperMarketerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $superMarketer;
    protected $marketer1;
    protected $marketer2;
    protected $landlord;

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
        
        $this->marketer1 = User::factory()->create([
            'user_id' => 1002,
            'name' => 'Marketer One',
            'email' => 'marketer1@example.com',
            'region' => 'Lagos'
        ]);
        
        $this->marketer2 = User::factory()->create([
            'user_id' => 1003,
            'name' => 'Marketer Two',
            'email' => 'marketer2@example.com',
            'region' => 'Lagos'
        ]);
        
        $this->landlord = User::factory()->create([
            'user_id' => 1004,
            'name' => 'Landlord',
            'email' => 'landlord@example.com',
            'region' => 'Lagos'
        ]);
        
        // Assign roles
        $this->superMarketer->roles()->attach(9);
        $this->marketer1->roles()->attach(3);
        $this->marketer2->roles()->attach(3);
        $this->landlord->roles()->attach(2);
        
        $this->setupTestData();
    }

    protected function setupTestData()
    {
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

        // Create referrals
        Referral::create([
            'referrer_id' => $this->superMarketer->user_id,
            'referred_id' => $this->marketer1->user_id,
            'referral_status' => 'active',
            'commission_tier' => 'super_marketer',
            'referral_level' => 1,
            'property_id' => $property1->id
        ]);

        Referral::create([
            'referrer_id' => $this->superMarketer->user_id,
            'referred_id' => $this->marketer2->user_id,
            'referral_status' => 'active',
            'commission_tier' => 'super_marketer',
            'referral_level' => 1,
            'property_id' => $property2->id
        ]);

        Referral::create([
            'referrer_id' => $this->marketer1->user_id,
            'referred_id' => $this->landlord->user_id,
            'referral_status' => 'active',
            'commission_tier' => 'marketer',
            'referral_level' => 2,
            'property_id' => $property1->id
        ]);

        // Create payments and commissions
        $payment1 = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $property1->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        CommissionPayment::create([
            'payment_id' => $payment1->id,
            'user_id' => $this->superMarketer->user_id,
            'commission_amount' => 32.0,
            'commission_tier' => 'super_marketer',
            'regional_rate_applied' => 0.8,
            'payment_status' => 'completed'
        ]);

        CommissionPayment::create([
            'payment_id' => $payment1->id,
            'user_id' => $this->marketer1->user_id,
            'commission_amount' => 28.0,
            'commission_tier' => 'marketer',
            'regional_rate_applied' => 0.7,
            'payment_status' => 'completed'
        ]);
    }

    /** @test */
    public function super_marketer_can_access_dashboard()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('super-marketer.dashboard');
    }

    /** @test */
    public function non_super_marketer_cannot_access_dashboard()
    {
        $response = $this->actingAs($this->marketer1)
                         ->get('/super-marketer/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function dashboard_displays_referred_marketers()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Marketer One');
        $response->assertSee('Marketer Two');
        $response->assertSee('marketer1@example.com');
        $response->assertSee('marketer2@example.com');
    }

    /** @test */
    public function dashboard_shows_commission_summary()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/dashboard');

        $response->assertStatus(200);
        $response->assertSee('₦32.00'); // Commission amount
        $response->assertSee('Total Commission Earned');
        $response->assertViewHas('totalCommission', 32.0);
    }

    /** @test */
    public function dashboard_displays_referral_performance_metrics()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('referredMarketers');
        $response->assertViewHas('totalReferrals', 2);
        $response->assertSee('2'); // Number of referred marketers
    }

    /** @test */
    public function super_marketer_can_view_referred_marketers_page()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/referred-marketers');

        $response->assertStatus(200);
        $response->assertViewIs('super-marketer.referred-marketers');
        $response->assertSee('Marketer One');
        $response->assertSee('Marketer Two');
    }

    /** @test */
    public function referred_marketers_page_shows_performance_data()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/referred-marketers');

        $response->assertStatus(200);
        $response->assertViewHas('marketers');
        
        $marketers = $response->viewData('marketers');
        $this->assertCount(2, $marketers);
        
        // Check that performance data is included
        foreach ($marketers as $marketer) {
            $this->assertArrayHasKey('total_referrals', $marketer);
            $this->assertArrayHasKey('total_commission', $marketer);
        }
    }

    /** @test */
    public function super_marketer_can_view_marketer_performance_details()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get("/super-marketer/marketer-performance/{$this->marketer1->user_id}");

        $response->assertStatus(200);
        $response->assertViewIs('super-marketer.marketer-performance');
        $response->assertSee('Marketer One');
        $response->assertViewHas('marketer');
        $response->assertViewHas('performanceData');
    }

    /** @test */
    public function super_marketer_can_view_commission_analytics()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/commission-analytics');

        $response->assertStatus(200);
        $response->assertViewIs('super-marketer.commission-analytics');
        $response->assertViewHas('commissionData');
        $response->assertViewHas('chartData');
    }

    /** @test */
    public function commission_analytics_includes_breakdown_data()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/commission-analytics');

        $response->assertStatus(200);
        
        $commissionData = $response->viewData('commissionData');
        $this->assertArrayHasKey('total_earned', $commissionData);
        $this->assertArrayHasKey('monthly_breakdown', $commissionData);
        $this->assertArrayHasKey('by_marketer', $commissionData);
    }

    /** @test */
    public function super_marketer_can_generate_referral_link()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->post('/super-marketer/generate-referral-link', [
                             'campaign_name' => 'Test Campaign'
                         ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'referral_link',
            'campaign_id'
        ]);
    }

    /** @test */
    public function dashboard_filters_work_correctly()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/dashboard?date_range=last_30_days');

        $response->assertStatus(200);
        $response->assertViewHas('dateRange', 'last_30_days');
    }

    /** @test */
    public function super_marketer_can_export_performance_data()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/export/performance');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function dashboard_shows_recent_activities()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('recentActivities');
        
        $activities = $response->viewData('recentActivities');
        $this->assertNotEmpty($activities);
    }

    /** @test */
    public function super_marketer_can_view_commission_breakdown_modal()
    {
        $payment = Payment::first();
        
        $response = $this->actingAs($this->superMarketer)
                         ->get("/super-marketer/commission-breakdown/{$payment->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'breakdown',
            'total_commission',
            'payment_details'
        ]);
    }

    /** @test */
    public function dashboard_pagination_works_for_referred_marketers()
    {
        // Create additional marketers to test pagination
        for ($i = 0; $i < 15; $i++) {
            $marketer = User::factory()->create([
                'user_id' => 2000 + $i,
                'name' => "Marketer $i",
                'email' => "marketer$i@example.com",
                'region' => 'Lagos'
            ]);
            $marketer->roles()->attach(3);
            
            Referral::create([
                'referrer_id' => $this->superMarketer->user_id,
                'referred_id' => $marketer->user_id,
                'referral_status' => 'active',
                'commission_tier' => 'super_marketer',
                'referral_level' => 1
            ]);
        }

        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/referred-marketers?page=2');

        $response->assertStatus(200);
        $response->assertViewHas('marketers');
    }

    /** @test */
    public function super_marketer_can_search_referred_marketers()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/referred-marketers?search=Marketer One');

        $response->assertStatus(200);
        $response->assertSee('Marketer One');
        $response->assertDontSee('Marketer Two');
    }

    /** @test */
    public function dashboard_shows_correct_statistics()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/dashboard');

        $response->assertStatus(200);
        
        // Check statistics
        $response->assertViewHas('stats');
        $stats = $response->viewData('stats');
        
        $this->assertEquals(2, $stats['total_referred_marketers']);
        $this->assertEquals(32.0, $stats['total_commission_earned']);
        $this->assertEquals(1, $stats['active_referral_chains']);
    }

    /** @test */
    public function super_marketer_can_view_earnings_trend()
    {
        $response = $this->actingAs($this->superMarketer)
                         ->get('/super-marketer/earnings-trend');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'labels',
            'data',
            'total'
        ]);
    }
}
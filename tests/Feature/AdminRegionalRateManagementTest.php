<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\CommissionRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class AdminRegionalRateManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $nonAdminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['id' => 1, 'name' => 'admin']);
        Role::create(['id' => 3, 'name' => 'marketer']);
        Role::create(['id' => 5, 'name' => 'regional_manager']);
        Role::create(['id' => 9, 'name' => 'super_marketer']);
        
        // Create users
        $this->adminUser = User::factory()->create([
            'user_id' => 1001,
            'name' => 'Admin User',
            'email' => 'admin@example.com'
        ]);
        
        $this->nonAdminUser = User::factory()->create([
            'user_id' => 1002,
            'name' => 'Regular User',
            'email' => 'user@example.com'
        ]);
        
        // Assign roles
        $this->adminUser->roles()->attach(1);
        $this->nonAdminUser->roles()->attach(3);
        
        $this->setupTestRates();
    }

    protected function setupTestRates()
    {
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
            'region' => 'Abuja',
            'role_id' => 9,
            'commission_percentage' => 0.9,
            'effective_from' => now()->subDay(),
            'created_by' => $this->adminUser->user_id,
            'is_active' => true
        ]);
    }

    /** @test */
    public function admin_can_access_commission_rates_index()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/commission-rates');

        $response->assertStatus(200);
        $response->assertViewIs('admin.commission-rates.index');
        $response->assertSee('Commission Rate Management');
    }

    /** @test */
    public function non_admin_cannot_access_commission_rates()
    {
        $response = $this->actingAs($this->nonAdminUser)
                         ->get('/admin/commission-rates');

        $response->assertStatus(403);
    }

    /** @test */
    public function commission_rates_index_displays_existing_rates()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/commission-rates');

        $response->assertStatus(200);
        $response->assertSee('Lagos');
        $response->assertSee('Abuja');
        $response->assertSee('0.8%');
        $response->assertSee('0.7%');
        $response->assertSee('0.9%');
    }

    /** @test */
    public function admin_can_view_create_commission_rate_form()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/commission-rates/create');

        $response->assertStatus(200);
        $response->assertViewIs('admin.commission-rates.create');
        $response->assertSee('Create Commission Rate');
    }

    /** @test */
    public function admin_can_create_new_commission_rate()
    {
        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/commission-rates', [
                             'region' => 'Port Harcourt',
                             'role_id' => 9,
                             'commission_percentage' => 0.85,
                             'effective_from' => now()->format('Y-m-d H:i:s')
                         ]);

        $response->assertRedirect('/admin/commission-rates');
        $response->assertSessionHas('success', 'Commission rate created successfully.');

        $this->assertDatabaseHas('commission_rates', [
            'region' => 'Port Harcourt',
            'role_id' => 9,
            'commission_percentage' => 0.85,
            'created_by' => $this->adminUser->user_id,
            'is_active' => true
        ]);
    }

    /** @test */
    public function commission_rate_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/commission-rates', [
                             'region' => '',
                             'role_id' => '',
                             'commission_percentage' => ''
                         ]);

        $response->assertSessionHasErrors(['region', 'role_id', 'commission_percentage']);
    }

    /** @test */
    public function commission_rate_creation_validates_percentage_range()
    {
        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/commission-rates', [
                             'region' => 'Test Region',
                             'role_id' => 9,
                             'commission_percentage' => 5.0 // Too high
                         ]);

        $response->assertSessionHasErrors(['commission_percentage']);
    }

    /** @test */
    public function admin_can_view_commission_rate_details()
    {
        $rate = CommissionRate::first();
        
        $response = $this->actingAs($this->adminUser)
                         ->get("/admin/commission-rates/{$rate->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.commission-rates.show');
        $response->assertSee($rate->region);
        $response->assertSee($rate->commission_percentage . '%');
    }

    /** @test */
    public function admin_can_view_edit_commission_rate_form()
    {
        $rate = CommissionRate::first();
        
        $response = $this->actingAs($this->adminUser)
                         ->get("/admin/commission-rates/{$rate->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.commission-rates.edit');
        $response->assertSee('Edit Commission Rate');
    }

    /** @test */
    public function admin_can_update_commission_rate()
    {
        $rate = CommissionRate::first();
        
        $response = $this->actingAs($this->adminUser)
                         ->put("/admin/commission-rates/{$rate->id}", [
                             'region' => $rate->region,
                             'role_id' => $rate->role_id,
                             'commission_percentage' => 1.0,
                             'effective_from' => now()->format('Y-m-d H:i:s')
                         ]);

        $response->assertRedirect('/admin/commission-rates');
        $response->assertSessionHas('success', 'Commission rate updated successfully.');

        // Check that old rate is deactivated and new rate is created
        $this->assertDatabaseHas('commission_rates', [
            'id' => $rate->id,
            'is_active' => false
        ]);

        $this->assertDatabaseHas('commission_rates', [
            'region' => $rate->region,
            'role_id' => $rate->role_id,
            'commission_percentage' => 1.0,
            'is_active' => true
        ]);
    }

    /** @test */
    public function admin_can_access_bulk_update_form()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/commission-rates/bulk-update');

        $response->assertStatus(200);
        $response->assertViewIs('admin.commission-rates.bulk-update');
        $response->assertSee('Bulk Update Commission Rates');
    }

    /** @test */
    public function admin_can_perform_bulk_update()
    {
        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/commission-rates/bulk-update', [
                             'regions' => [
                                 [
                                     'region' => 'Lagos',
                                     'rates' => [
                                         ['role_id' => 9, 'rate' => 0.9],
                                         ['role_id' => 3, 'rate' => 0.8],
                                         ['role_id' => 5, 'rate' => 1.0]
                                     ]
                                 ],
                                 [
                                     'region' => 'Abuja',
                                     'rates' => [
                                         ['role_id' => 9, 'rate' => 1.0],
                                         ['role_id' => 3, 'rate' => 0.9],
                                         ['role_id' => 5, 'rate' => 0.6]
                                     ]
                                 ]
                             ]
                         ]);

        $response->assertRedirect('/admin/commission-rates');
        $response->assertSessionHas('success', 'Bulk update completed successfully.');

        // Verify new rates were created
        $this->assertDatabaseHas('commission_rates', [
            'region' => 'Lagos',
            'role_id' => 9,
            'commission_percentage' => 0.9,
            'is_active' => true
        ]);

        $this->assertDatabaseHas('commission_rates', [
            'region' => 'Abuja',
            'role_id' => 9,
            'commission_percentage' => 1.0,
            'is_active' => true
        ]);
    }

    /** @test */
    public function bulk_update_validates_total_commission_limit()
    {
        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/commission-rates/bulk-update', [
                             'regions' => [
                                 [
                                     'region' => 'Lagos',
                                     'rates' => [
                                         ['role_id' => 9, 'rate' => 1.5],
                                         ['role_id' => 3, 'rate' => 1.5],
                                         ['role_id' => 5, 'rate' => 1.5] // Total: 4.5% > 2.5%
                                     ]
                                 ]
                             ]
                         ]);

        $response->assertSessionHasErrors(['regions.0.rates']);
    }

    /** @test */
    public function admin_can_view_rate_history()
    {
        $rate = CommissionRate::first();
        
        $response = $this->actingAs($this->adminUser)
                         ->get("/admin/commission-rates/{$rate->id}/history");

        $response->assertStatus(200);
        $response->assertViewIs('admin.commission-rates.history');
        $response->assertSee('Rate History');
    }

    /** @test */
    public function admin_can_filter_commission_rates_by_region()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/commission-rates?region=Lagos');

        $response->assertStatus(200);
        $response->assertSee('Lagos');
        $response->assertDontSee('Abuja');
    }

    /** @test */
    public function admin_can_filter_commission_rates_by_role()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/commission-rates?role_id=9');

        $response->assertStatus(200);
        $response->assertViewHas('rates');
        
        $rates = $response->viewData('rates');
        foreach ($rates as $rate) {
            $this->assertEquals(9, $rate->role_id);
        }
    }

    /** @test */
    public function admin_can_export_commission_rates()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/commission-rates/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function admin_can_deactivate_commission_rate()
    {
        $rate = CommissionRate::first();
        
        $response = $this->actingAs($this->adminUser)
                         ->patch("/admin/commission-rates/{$rate->id}/deactivate");

        $response->assertRedirect('/admin/commission-rates');
        $response->assertSessionHas('success', 'Commission rate deactivated successfully.');

        $rate->refresh();
        $this->assertFalse($rate->is_active);
        $this->assertNotNull($rate->effective_until);
    }

    /** @test */
    public function admin_can_view_commission_rate_analytics()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/commission-rates/analytics');

        $response->assertStatus(200);
        $response->assertViewIs('admin.commission-rates.analytics');
        $response->assertViewHas('analyticsData');
    }

    /** @test */
    public function commission_rates_pagination_works()
    {
        // Create additional rates to test pagination
        for ($i = 0; $i < 20; $i++) {
            CommissionRate::create([
                'region' => "Region $i",
                'role_id' => 3,
                'commission_percentage' => 0.5,
                'effective_from' => now(),
                'created_by' => $this->adminUser->user_id,
                'is_active' => true
            ]);
        }

        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/commission-rates?page=2');

        $response->assertStatus(200);
        $response->assertViewHas('rates');
    }

    /** @test */
    public function admin_can_search_commission_rates()
    {
        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/commission-rates?search=Lagos');

        $response->assertStatus(200);
        $response->assertSee('Lagos');
    }

    /** @test */
    public function commission_rate_validation_prevents_duplicate_active_rates()
    {
        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/commission-rates', [
                             'region' => 'Lagos',
                             'role_id' => 9, // Same as existing active rate
                             'commission_percentage' => 0.9,
                             'effective_from' => now()->format('Y-m-d H:i:s')
                         ]);

        $response->assertSessionHasErrors(['role_id']);
    }

    /** @test */
    public function admin_can_preview_bulk_update_changes()
    {
        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/commission-rates/bulk-update/preview', [
                             'regions' => [
                                 [
                                     'region' => 'Lagos',
                                     'rates' => [
                                         ['role_id' => 9, 'rate' => 0.9],
                                         ['role_id' => 3, 'rate' => 0.8]
                                     ]
                                 ]
                             ]
                         ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'preview',
            'validation_results',
            'affected_regions'
        ]);
    }
}
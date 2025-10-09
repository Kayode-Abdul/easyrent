<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Commission\RegionalRateManager;
use App\Models\CommissionRate;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RegionalRateManagerTest extends TestCase
{
    use RefreshDatabase;

    protected $rateManager;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rateManager = new RegionalRateManager();
        
        // Create admin user for rate creation
        $this->adminUser = User::factory()->create(['user_id' => 1001]);
        $adminRole = Role::create(['id' => 1, 'name' => 'admin']);
        $this->adminUser->roles()->attach($adminRole);
        
        // Create other roles
        Role::create(['id' => 3, 'name' => 'marketer']);
        Role::create(['id' => 9, 'name' => 'super_marketer']);
        Role::create(['id' => 5, 'name' => 'regional_manager']);
    }

    /** @test */
    public function it_sets_regional_rate_successfully()
    {
        $result = $this->rateManager->setRegionalRate('Lagos', 3, 1.5, $this->adminUser->user_id);

        $this->assertTrue($result);
        
        $rate = CommissionRate::where('region', 'Lagos')
                             ->where('role_id', 3)
                             ->where('is_active', true)
                             ->first();
        
        $this->assertNotNull($rate);
        $this->assertEquals(1.5, $rate->commission_percentage);
        $this->assertEquals($this->adminUser->user_id, $rate->created_by);
    }

    /** @test */
    public function it_retrieves_active_rate_correctly()
    {
        // Create a rate
        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 3,
            'commission_percentage' => 1.5,
            'effective_from' => now()->subDay(),
            'created_by' => $this->adminUser->user_id,
            'is_active' => true
        ]);

        $rate = $this->rateManager->getActiveRate('Lagos', 3);

        $this->assertEquals(1.5, $rate);
    }

    /** @test */
    public function it_returns_default_rate_when_no_regional_rate_exists()
    {
        $rate = $this->rateManager->getActiveRate('NonExistentRegion', 3);

        $this->assertEquals(0.0, $rate); // Default rate
    }

    /** @test */
    public function it_validates_rate_configuration_successfully()
    {
        $rates = [
            ['region' => 'Lagos', 'role_id' => 9, 'rate' => 0.8], // Super Marketer
            ['region' => 'Lagos', 'role_id' => 3, 'rate' => 0.7], // Marketer
            ['region' => 'Lagos', 'role_id' => 5, 'rate' => 1.0], // Regional Manager
        ];

        $validation = $this->rateManager->validateRateConfiguration($rates);

        $this->assertTrue($validation['is_valid']);
        $this->assertEquals(2.5, $validation['total_percentage']);
        $this->assertEmpty($validation['errors']);
    }

    /** @test */
    public function it_rejects_rate_configuration_exceeding_limit()
    {
        $rates = [
            ['region' => 'Lagos', 'role_id' => 9, 'rate' => 1.5], // Super Marketer
            ['region' => 'Lagos', 'role_id' => 3, 'rate' => 1.5], // Marketer
            ['region' => 'Lagos', 'role_id' => 5, 'rate' => 1.5], // Regional Manager
        ];

        $validation = $this->rateManager->validateRateConfiguration($rates);

        $this->assertFalse($validation['is_valid']);
        $this->assertEquals(4.5, $validation['total_percentage']);
        $this->assertContains('Total commission rate 4.5% exceeds maximum allowed 2.5%', $validation['errors']);
    }

    /** @test */
    public function it_retrieves_historical_rates()
    {
        // Create historical rates
        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 3,
            'commission_percentage' => 1.0,
            'effective_from' => now()->subMonths(2),
            'effective_until' => now()->subMonth(),
            'created_by' => $this->adminUser->user_id,
            'is_active' => false
        ]);

        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 3,
            'commission_percentage' => 1.5,
            'effective_from' => now()->subMonth(),
            'created_by' => $this->adminUser->user_id,
            'is_active' => true
        ]);

        $historicalRates = $this->rateManager->getHistoricalRates('Lagos', 3);

        $this->assertCount(2, $historicalRates);
        $this->assertEquals(1.5, $historicalRates->first()->commission_percentage);
        $this->assertEquals(1.0, $historicalRates->last()->commission_percentage);
    }

    /** @test */
    public function it_performs_bulk_rate_updates()
    {
        $regionRates = [
            [
                'region' => 'Lagos',
                'rates' => [
                    ['role_id' => 9, 'rate' => 0.8],
                    ['role_id' => 3, 'rate' => 0.7],
                    ['role_id' => 5, 'rate' => 1.0]
                ]
            ],
            [
                'region' => 'Abuja',
                'rates' => [
                    ['role_id' => 9, 'rate' => 0.9],
                    ['role_id' => 3, 'rate' => 0.8],
                    ['role_id' => 5, 'rate' => 0.8]
                ]
            ]
        ];

        $result = $this->rateManager->bulkUpdateRates($regionRates, $this->adminUser->user_id);

        $this->assertTrue($result);
        
        // Verify Lagos rates
        $lagosRates = CommissionRate::where('region', 'Lagos')->where('is_active', true)->get();
        $this->assertCount(3, $lagosRates);
        
        // Verify Abuja rates
        $abujaRates = CommissionRate::where('region', 'Abuja')->where('is_active', true)->get();
        $this->assertCount(3, $abujaRates);
    }

    /** @test */
    public function it_deactivates_old_rates_when_setting_new_ones()
    {
        // Create existing rate
        $oldRate = CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 3,
            'commission_percentage' => 1.0,
            'effective_from' => now()->subMonth(),
            'created_by' => $this->adminUser->user_id,
            'is_active' => true
        ]);

        // Set new rate
        $this->rateManager->setRegionalRate('Lagos', 3, 1.5, $this->adminUser->user_id);

        // Check old rate is deactivated
        $oldRate->refresh();
        $this->assertFalse($oldRate->is_active);
        $this->assertNotNull($oldRate->effective_until);

        // Check new rate is active
        $newRate = CommissionRate::where('region', 'Lagos')
                                 ->where('role_id', 3)
                                 ->where('is_active', true)
                                 ->first();
        
        $this->assertNotNull($newRate);
        $this->assertEquals(1.5, $newRate->commission_percentage);
    }

    /** @test */
    public function it_handles_invalid_role_id()
    {
        $result = $this->rateManager->setRegionalRate('Lagos', 999, 1.5, $this->adminUser->user_id);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_handles_negative_commission_rate()
    {
        $result = $this->rateManager->setRegionalRate('Lagos', 3, -1.0, $this->adminUser->user_id);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_gets_all_regional_rates_for_region()
    {
        // Create rates for different roles in same region
        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 9,
            'commission_percentage' => 0.8,
            'effective_from' => now(),
            'created_by' => $this->adminUser->user_id,
            'is_active' => true
        ]);

        CommissionRate::create([
            'region' => 'Lagos',
            'role_id' => 3,
            'commission_percentage' => 0.7,
            'effective_from' => now(),
            'created_by' => $this->adminUser->user_id,
            'is_active' => true
        ]);

        $rates = $this->rateManager->getAllRegionalRates('Lagos');

        $this->assertCount(2, $rates);
        $this->assertEquals(1.5, $rates->sum('commission_percentage')); // 0.8 + 0.7
    }
}
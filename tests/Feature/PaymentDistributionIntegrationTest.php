&lt;?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Commission\PaymentDistributionService;
use App\Services\Commission\MultiTierCommissionCalculator;
use App\Services\Commission\RegionalRateManager;
use App\Services\Commission\ReferralChainService;
use App\Models\User;
use App\Models\Role;
use App\Models\Property;
use App\Models\Payment;
use App\Models\CommissionPayment;
use App\Models\CommissionRate;
use App\Models\ReferralChain;
use App\Models\Referral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentDistributionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $paymentService;
    protected $superMarketer;
    protected $marketer;
    protected $regionalManager;
    protected $landlord;
    protected $property;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->paymentService = app(PaymentDistributionService::class);
        
        // Create roles
        Role::create(['id' => 1, 'name' => 'admin']);
        Role::create(['id' => 2, 'name' => 'landlord']);
        Role::create(['id' => 3, 'name' => 'marketer']);
        Role::create(['id' => 5, 'name' => 'regional_manager']);
        Role::create(['id' => 9, 'name' => 'super_marketer']);
        
        // Create users
        $this->adminUser = User::factory()->create(['user_id' => 1000]);
        $this->superMarketer = User::factory()->create(['user_id' => 1001, 'region' => 'Lagos']);
        $this->marketer = User::factory()->create(['user_id' => 1002, 'region' => 'Lagos']);
        $this->regionalManager = User::factory()->create(['user_id' => 1003, 'region' => 'Lagos']);
        $this->landlord = User::factory()->create(['user_id' => 1004, 'region' => 'Lagos']);
        
        // Assign roles
        $this->adminUser->roles()->attach(1);
        $this->superMarketer->roles()->attach(9);
        $this->marketer->roles()->attach(3);
        $this->regionalManager->roles()->attach(5);
        $this->landlord->roles()->attach(2);
        
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
        
        // Set up commission rates
        $this->setupCommissionRates();
        
        // Create referral chain
        $this->setupReferralChain();
    }

    protected function setupCommissionRates()
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
            'region' => 'Lagos',
            'role_id' => 5,
            'commission_percentage' => 1.0,
            'effective_from' => now()->subDay(),
            'created_by' => $this->adminUser->user_id,
            'is_active' => true
        ]);
    }

    protected function setupReferralChain()
    {
        // Create referral chain
        $chain = ReferralChain::create([
            'super_marketer_id' => $this->superMarketer->user_id,
            'marketer_id' => $this->marketer->user_id,
            'landlord_id' => $this->landlord->user_id,
            'chain_hash' => hash('sha256', $this->superMarketer->user_id . $this->marketer->user_id . $this->landlord->user_id . time()),
            'status' => 'active'
        ]);

        // Create referral records
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
    }

    /** @test */
    public function it_processes_end_to_end_commission_distribution()
    {
        // Create a rent payment
        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $this->property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        // Process commission distribution
        $result = $this->paymentService->distributeMultiTierCommission(
            $payment->amount,
            [
                ['user_id' => $this->superMarketer->user_id, 'role_id' => 9],
                ['user_id' => $this->marketer->user_id, 'role_id' => 3],
                ['user_id' => $this->regionalManager->user_id, 'role_id' => 5]
            ],
            'Lagos',
            $payment->id
        );

        $this->assertTrue($result);

        // Verify commission payments were created
        $commissionPayments = CommissionPayment::where('payment_id', $payment->id)->get();
        $this->assertCount(3, $commissionPayments);

        // Verify amounts
        $superMarketerPayment = $commissionPayments->where('user_id', $this->superMarketer->user_id)->first();
        $this->assertEquals(32.0, $superMarketerPayment->commission_amount); // 0.8% of 4000

        $marketerPayment = $commissionPayments->where('user_id', $this->marketer->user_id)->first();
        $this->assertEquals(28.0, $marketerPayment->commission_amount); // 0.7% of 4000

        $regionalManagerPayment = $commissionPayments->where('user_id', $this->regionalManager->user_id)->first();
        $this->assertEquals(40.0, $regionalManagerPayment->commission_amount); // 1.0% of 4000

        // Verify commission tiers
        $this->assertEquals('super_marketer', $superMarketerPayment->commission_tier);
        $this->assertEquals('marketer', $marketerPayment->commission_tier);
        $this->assertEquals('regional_manager', $regionalManagerPayment->commission_tier);
    }

    /** @test */
    public function it_handles_missing_tier_in_referral_chain()
    {
        // Create payment without super marketer in chain
        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $this->property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        // Process with incomplete chain (no super marketer)
        $result = $this->paymentService->distributeMultiTierCommission(
            $payment->amount,
            [
                ['user_id' => $this->marketer->user_id, 'role_id' => 3],
                ['user_id' => $this->regionalManager->user_id, 'role_id' => 5]
            ],
            'Lagos',
            $payment->id
        );

        $this->assertTrue($result);

        $commissionPayments = CommissionPayment::where('payment_id', $payment->id)->get();
        $this->assertCount(3, $commissionPayments); // 2 participants + company

        // Verify company gets the missing tier's commission
        $companyPayment = $commissionPayments->where('commission_tier', 'company')->first();
        $this->assertNotNull($companyPayment);
        $this->assertEquals(32.0, $companyPayment->commission_amount); // Super marketer's share
    }

    /** @test */
    public function it_maintains_data_consistency_across_transactions()
    {
        DB::beginTransaction();

        try {
            $payment = Payment::create([
                'user_id' => $this->landlord->user_id,
                'property_id' => $this->property->id,
                'amount' => 4000.0,
                'payment_status' => 'completed',
                'payment_type' => 'rent'
            ]);

            $result = $this->paymentService->distributeMultiTierCommission(
                $payment->amount,
                [
                    ['user_id' => $this->superMarketer->user_id, 'role_id' => 9],
                    ['user_id' => $this->marketer->user_id, 'role_id' => 3],
                    ['user_id' => $this->regionalManager->user_id, 'role_id' => 5]
                ],
                'Lagos',
                $payment->id
            );

            $this->assertTrue($result);

            // Verify all records exist within transaction
            $this->assertEquals(1, Payment::where('id', $payment->id)->count());
            $this->assertEquals(3, CommissionPayment::where('payment_id', $payment->id)->count());

            DB::commit();

            // Verify records persist after commit
            $this->assertEquals(1, Payment::where('id', $payment->id)->count());
            $this->assertEquals(3, CommissionPayment::where('payment_id', $payment->id)->count());

        } catch (\Exception $e) {
            DB::rollback();
            $this->fail('Transaction failed: ' . $e->getMessage());
        }
    }

    /** @test */
    public function it_handles_payment_failure_gracefully()
    {
        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $this->property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        // Simulate payment failure by using invalid user ID
        $result = $this->paymentService->distributeMultiTierCommission(
            $payment->amount,
            [
                ['user_id' => 999999, 'role_id' => 9], // Invalid user
                ['user_id' => $this->marketer->user_id, 'role_id' => 3],
                ['user_id' => $this->regionalManager->user_id, 'role_id' => 5]
            ],
            'Lagos',
            $payment->id
        );

        $this->assertFalse($result);

        // Verify no commission payments were created on failure
        $commissionPayments = CommissionPayment::where('payment_id', $payment->id)->count();
        $this->assertEquals(0, $commissionPayments);
    }

    /** @test */
    public function it_processes_bulk_payments_efficiently()
    {
        $payments = [];
        $paymentIds = [];

        // Create multiple payments
        for ($i = 0; $i < 5; $i++) {
            $payment = Payment::create([
                'user_id' => $this->landlord->user_id,
                'property_id' => $this->property->id,
                'amount' => 4000.0,
                'payment_status' => 'completed',
                'payment_type' => 'rent'
            ]);
            
            $payments[] = $payment;
            $paymentIds[] = $payment->id;
        }

        // Process bulk payments
        $results = $this->paymentService->processBulkCommissions($paymentIds, 'Lagos');

        $this->assertCount(5, $results);
        foreach ($results as $result) {
            $this->assertTrue($result['success']);
        }

        // Verify all commission payments were created
        $totalCommissionPayments = CommissionPayment::whereIn('payment_id', $paymentIds)->count();
        $this->assertEquals(15, $totalCommissionPayments); // 5 payments × 3 commission payments each
    }

    /** @test */
    public function it_handles_payment_recovery_after_failure()
    {
        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $this->property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        // Create a failed commission payment
        $failedPayment = CommissionPayment::create([
            'payment_id' => $payment->id,
            'user_id' => $this->superMarketer->user_id,
            'commission_amount' => 32.0,
            'commission_tier' => 'super_marketer',
            'regional_rate_applied' => 0.8,
            'payment_status' => 'failed',
            'failure_reason' => 'Insufficient funds'
        ]);

        // Retry failed payment
        $result = $this->paymentService->retryFailedPayment($failedPayment->id);

        $this->assertTrue($result);

        $failedPayment->refresh();
        $this->assertEquals('completed', $failedPayment->payment_status);
        $this->assertNull($failedPayment->failure_reason);
    }

    /** @test */
    public function it_validates_commission_calculation_accuracy()
    {
        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $this->property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        $result = $this->paymentService->distributeMultiTierCommission(
            $payment->amount,
            [
                ['user_id' => $this->superMarketer->user_id, 'role_id' => 9],
                ['user_id' => $this->marketer->user_id, 'role_id' => 3],
                ['user_id' => $this->regionalManager->user_id, 'role_id' => 5]
            ],
            'Lagos',
            $payment->id
        );

        $this->assertTrue($result);

        // Verify total commission doesn't exceed 2.5%
        $totalCommission = CommissionPayment::where('payment_id', $payment->id)
                                          ->sum('commission_amount');
        
        $maxAllowedCommission = $payment->amount * 0.025; // 2.5%
        $this->assertLessThanOrEqual($maxAllowedCommission, $totalCommission);

        // Verify individual calculations
        $commissionPayments = CommissionPayment::where('payment_id', $payment->id)->get();
        
        foreach ($commissionPayments as $commissionPayment) {
            $expectedAmount = $payment->amount * ($commissionPayment->regional_rate_applied / 100);
            $this->assertEquals($expectedAmount, $commissionPayment->commission_amount, '', 0.01);
        }
    }

    /** @test */
    public function it_handles_regional_rate_changes_during_processing()
    {
        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $this->property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        // Change rates during processing
        CommissionRate::where('region', 'Lagos')->where('role_id', 9)->update([
            'commission_percentage' => 1.0, // Changed from 0.8
            'is_active' => true
        ]);

        $result = $this->paymentService->distributeMultiTierCommission(
            $payment->amount,
            [
                ['user_id' => $this->superMarketer->user_id, 'role_id' => 9],
                ['user_id' => $this->marketer->user_id, 'role_id' => 3],
                ['user_id' => $this->regionalManager->user_id, 'role_id' => 5]
            ],
            'Lagos',
            $payment->id
        );

        $this->assertTrue($result);

        // Verify new rate was applied
        $superMarketerPayment = CommissionPayment::where('payment_id', $payment->id)
                                                ->where('user_id', $this->superMarketer->user_id)
                                                ->first();
        
        $this->assertEquals(40.0, $superMarketerPayment->commission_amount); // 1.0% of 4000
        $this->assertEquals(1.0, $superMarketerPayment->regional_rate_applied);
    }

    /** @test */
    public function it_logs_commission_distribution_activities()
    {
        Log::shouldReceive('info')
           ->with('Commission distribution started', \Mockery::type('array'))
           ->once();
           
        Log::shouldReceive('info')
           ->with('Commission distribution completed', \Mockery::type('array'))
           ->once();

        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $this->property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        $result = $this->paymentService->distributeMultiTierCommission(
            $payment->amount,
            [
                ['user_id' => $this->superMarketer->user_id, 'role_id' => 9],
                ['user_id' => $this->marketer->user_id, 'role_id' => 3],
                ['user_id' => $this->regionalManager->user_id, 'role_id' => 5]
            ],
            'Lagos',
            $payment->id
        );

        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_commission_distribution_with_broken_referral_chain()
    {
        // Create payment
        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $this->property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        // Break the referral chain
        $chain = ReferralChain::first();
        $chain->update(['status' => 'broken']);

        // Attempt distribution
        $result = $this->paymentService->distributeMultiTierCommission(
            $payment->amount,
            [
                ['user_id' => $this->superMarketer->user_id, 'role_id' => 9],
                ['user_id' => $this->marketer->user_id, 'role_id' => 3],
                ['user_id' => $this->regionalManager->user_id, 'role_id' => 5]
            ],
            'Lagos',
            $payment->id
        );

        $this->assertFalse($result);

        // Verify no commissions were distributed
        $commissionPayments = CommissionPayment::where('payment_id', $payment->id)->count();
        $this->assertEquals(0, $commissionPayments);
    }

    /** @test */
    public function it_simulates_full_workflow_from_chain_creation_to_distribution()
    {
        // Create new chain
        $newSuper = User::factory()->create(['user_id' => 2001, 'region' => 'Lagos']);
        $newSuper->roles()->attach(9);
        $newMarketer = User::factory()->create(['user_id' => 2002, 'region' => 'Lagos']);
        $newMarketer->roles()->attach(3);
        $newLandlord = User::factory()->create(['user_id' => 2003, 'region' => 'Lagos']);
        $newLandlord->roles()->attach(2);

        $chainService = app(ReferralChainService::class);
        $chain = $chainService->createReferralChain(
            $newSuper->user_id,
            $newMarketer->user_id,
            $newLandlord->user_id,
            'Lagos'
        );

        // Create property for new landlord
        $newProperty = Property::create([
            'user_id' => $newLandlord->user_id,
            'title' => 'New Property',
            'description' => 'New Desc',
            'price' => 5000.0,
            'location' => 'Lagos',
            'property_type' => 'apartment',
            'status' => 'active'
        ]);

        // Create payment
        $payment = Payment::create([
            'user_id' => $newLandlord->user_id,
            'property_id' => $newProperty->id,
            'amount' => 5000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        // Distribute
        $result = $this->paymentService->distributeMultiTierCommission(
            $payment->amount,
            [
                ['user_id' => $newSuper->user_id, 'role_id' => 9],
                ['user_id' => $newMarketer->user_id, 'role_id' => 3],
                ['user_id' => $this->regionalManager->user_id, 'role_id' => 5]  // Using existing regional
            ],
            'Lagos',
            $payment->id
        );

        $this->assertTrue($result);
        $commissionPayments = CommissionPayment::where('payment_id', $payment->id)->get();
        $this->assertCount(3, $commissionPayments);
    }
}
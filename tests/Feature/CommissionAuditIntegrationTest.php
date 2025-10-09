<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Audit\CommissionAuditService;
use App\Services\Commission\PaymentDistributionService;
use App\Models\User;
use App\Models\Role;
use App\Models\Property;
use App\Models\Payment;
use App\Models\CommissionPayment;
use App\Models\CommissionRate;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class CommissionAuditIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $auditService;
    protected $paymentService;
    protected $superMarketer;
    protected $marketer;
    protected $regionalManager;
    protected $landlord;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->auditService = app(CommissionAuditService::class);
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
        
        $this->setupCommissionRates();
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

    /** @test */
    public function it_audits_commission_calculations_for_accuracy()
    {
        // Create property and payment
        $property = Property::create([
            'user_id' => $this->landlord->user_id,
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 4000.0,
            'location' => 'Lagos',
            'property_type' => 'apartment',
            'status' => 'active'
        ]);

        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        // Create commission payments
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

        // Run audit
        $auditResult = $this->auditService->auditCommissionCalculations($payment->id);

        $this->assertTrue($auditResult['is_valid']);
        $this->assertEmpty($auditResult['discrepancies']);
        $this->assertEquals(60.0, $auditResult['total_commission']);
        $this->assertEquals(1.5, $auditResult['total_percentage']);
    }

    /** @test */
    public function it_detects_commission_calculation_discrepancies()
    {
        $property = Property::create([
            'user_id' => $this->landlord->user_id,
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 4000.0,
            'location' => 'Lagos',
            'property_type' => 'apartment',
            'status' => 'active'
        ]);

        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        // Create commission payment with incorrect amount
        CommissionPayment::create([
            'payment_id' => $payment->id,
            'user_id' => $this->superMarketer->user_id,
            'commission_amount' => 50.0, // Should be 32.0
            'commission_tier' => 'super_marketer',
            'regional_rate_applied' => 0.8,
            'payment_status' => 'completed'
        ]);

        $auditResult = $this->auditService->auditCommissionCalculations($payment->id);

        $this->assertFalse($auditResult['is_valid']);
        $this->assertNotEmpty($auditResult['discrepancies']);
        $this->assertContains('Commission amount mismatch', $auditResult['discrepancies'][0]['type']);
    }

    /** @test */
    public function it_reconciles_commission_payments_with_rates()
    {
        $property = Property::create([
            'user_id' => $this->landlord->user_id,
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 4000.0,
            'location' => 'Lagos',
            'property_type' => 'apartment',
            'status' => 'active'
        ]);

        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        // Create commission payments
        $commissionPayments = [
            CommissionPayment::create([
                'payment_id' => $payment->id,
                'user_id' => $this->superMarketer->user_id,
                'commission_amount' => 32.0,
                'commission_tier' => 'super_marketer',
                'regional_rate_applied' => 0.8,
                'payment_status' => 'completed'
            ]),
            CommissionPayment::create([
                'payment_id' => $payment->id,
                'user_id' => $this->marketer->user_id,
                'commission_amount' => 28.0,
                'commission_tier' => 'marketer',
                'regional_rate_applied' => 0.7,
                'payment_status' => 'completed'
            ])
        ];

        $reconciliationResult = $this->auditService->reconcileCommissionPayments(
            collect($commissionPayments),
            'Lagos'
        );

        $this->assertTrue($reconciliationResult['is_reconciled']);
        $this->assertEmpty($reconciliationResult['mismatches']);
        $this->assertEquals(2, $reconciliationResult['total_payments_checked']);
    }

    /** @test */
    public function it_creates_audit_logs_for_commission_activities()
    {
        $property = Property::create([
            'user_id' => $this->landlord->user_id,
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 4000.0,
            'location' => 'Lagos',
            'property_type' => 'apartment',
            'status' => 'active'
        ]);

        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        // Log commission calculation
        $this->auditService->logCommissionCalculation(
            $payment->id,
            [
                ['user_id' => $this->superMarketer->user_id, 'amount' => 32.0, 'tier' => 'super_marketer'],
                ['user_id' => $this->marketer->user_id, 'amount' => 28.0, 'tier' => 'marketer']
            ],
            'Lagos'
        );

        // Verify audit log was created
        $auditLog = AuditLog::where('auditable_type', 'commission_calculation')
                           ->where('auditable_id', $payment->id)
                           ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals('commission_calculation', $auditLog->event_type);
        $this->assertArrayHasKey('commission_breakdown', $auditLog->audit_data);
        $this->assertArrayHasKey('region', $auditLog->audit_data);
    }

    /** @test */
    public function it_detects_suspicious_commission_patterns()
    {
        // Create multiple payments with unusual patterns
        for ($i = 0; $i < 10; $i++) {
            $property = Property::create([
                'user_id' => $this->landlord->user_id,
                'title' => "Test Property $i",
                'description' => 'Test Description',
                'price' => 4000.0,
                'location' => 'Lagos',
                'property_type' => 'apartment',
                'status' => 'active'
            ]);

            $payment = Payment::create([
                'user_id' => $this->landlord->user_id,
                'property_id' => $property->id,
                'amount' => 4000.0,
                'payment_status' => 'completed',
                'payment_type' => 'rent',
                'created_at' => now()->subMinutes($i)
            ]);

            // Create unusually high commission payments
            CommissionPayment::create([
                'payment_id' => $payment->id,
                'user_id' => $this->superMarketer->user_id,
                'commission_amount' => 100.0, // Unusually high
                'commission_tier' => 'super_marketer',
                'regional_rate_applied' => 2.5, // Unusually high rate
                'payment_status' => 'completed'
            ]);
        }

        $suspiciousPatterns = $this->auditService->detectSuspiciousCommissionPatterns(
            $this->superMarketer->user_id,
            now()->subHour(),
            now()
        );

        $this->assertNotEmpty($suspiciousPatterns);
        $this->assertArrayHasKey('unusual_commission_amounts', $suspiciousPatterns);
        $this->assertArrayHasKey('high_frequency_payments', $suspiciousPatterns);
    }

    /** @test */
    public function it_validates_commission_rate_consistency()
    {
        // Create commission payment with rate that doesn't match current regional rate
        $property = Property::create([
            'user_id' => $this->landlord->user_id,
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 4000.0,
            'location' => 'Lagos',
            'property_type' => 'apartment',
            'status' => 'active'
        ]);

        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        CommissionPayment::create([
            'payment_id' => $payment->id,
            'user_id' => $this->superMarketer->user_id,
            'commission_amount' => 60.0,
            'commission_tier' => 'super_marketer',
            'regional_rate_applied' => 1.5, // Different from current rate (0.8)
            'payment_status' => 'completed'
        ]);

        $consistencyCheck = $this->auditService->validateCommissionRateConsistency($payment->id);

        $this->assertFalse($consistencyCheck['is_consistent']);
        $this->assertNotEmpty($consistencyCheck['rate_mismatches']);
    }

    /** @test */
    public function it_generates_commission_audit_report()
    {
        // Create test data
        $property = Property::create([
            'user_id' => $this->landlord->user_id,
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 4000.0,
            'location' => 'Lagos',
            'property_type' => 'apartment',
            'status' => 'active'
        ]);

        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $property->id,
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

        $auditReport = $this->auditService->generateAuditReport(
            now()->subDay(),
            now(),
            'Lagos'
        );

        $this->assertArrayHasKey('total_payments_audited', $auditReport);
        $this->assertArrayHasKey('total_commission_distributed', $auditReport);
        $this->assertArrayHasKey('discrepancies_found', $auditReport);
        $this->assertArrayHasKey('audit_summary', $auditReport);
        $this->assertEquals(1, $auditReport['total_payments_audited']);
        $this->assertEquals(32.0, $auditReport['total_commission_distributed']);
    }

    /** @test */
    public function it_handles_commission_reversal_audit_trail()
    {
        $property = Property::create([
            'user_id' => $this->landlord->user_id,
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 4000.0,
            'location' => 'Lagos',
            'property_type' => 'apartment',
            'status' => 'active'
        ]);

        $payment = Payment::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => $property->id,
            'amount' => 4000.0,
            'payment_status' => 'completed',
            'payment_type' => 'rent'
        ]);

        $commissionPayment = CommissionPayment::create([
            'payment_id' => $payment->id,
            'user_id' => $this->superMarketer->user_id,
            'commission_amount' => 32.0,
            'commission_tier' => 'super_marketer',
            'regional_rate_applied' => 0.8,
            'payment_status' => 'completed'
        ]);

        // Reverse the commission
        $reversalResult = $this->auditService->reverseCommissionPayment(
            $commissionPayment->id,
            'Fraudulent activity detected',
            $this->adminUser->user_id
        );

        $this->assertTrue($reversalResult);

        // Verify audit trail
        $auditLog = AuditLog::where('event_type', 'commission_reversal')
                           ->where('auditable_id', $commissionPayment->id)
                           ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($this->adminUser->user_id, $auditLog->user_id);
        $this->assertArrayHasKey('reversal_reason', $auditLog->audit_data);
        $this->assertEquals('Fraudulent activity detected', $auditLog->audit_data['reversal_reason']);

        // Verify commission payment status
        $commissionPayment->refresh();
        $this->assertEquals('reversed', $commissionPayment->payment_status);
    }
}
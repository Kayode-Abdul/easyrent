<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Services\Payment\PaymentCalculationService;
use App\Services\Payment\PaymentCalculationServiceInterface;
use App\Models\Apartment;
use App\Models\ProfomaReceipt;
use App\Http\Controllers\ProfomaController;
use App\Http\Controllers\ApartmentInvitationController;
use App\Http\Controllers\PaymentController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

class PaymentCalculationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private PaymentCalculationServiceInterface $calculationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculationService = app(PaymentCalculationServiceInterface::class);
        
        // Create required test data
        $this->createTestData();
    }
    
    private function createTestData(): void
    {
        // Create test user
        \DB::table('users')->insert([
            'user_id' => 1,
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'role' => 1,
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Create test property
        // The apartments.property_id should reference properties.property_id
        \DB::table('properties')->insert([
            'property_id' => 1, // This is what apartments.property_id references
            'prop_type' => 1,
            'address' => 'Test Address',
            'state' => 'Test State',
            'lga' => 'Test LGA',
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Use existing apartment type (seeded by migration)
        // ID 1 is 'Studio' from the seeder
    }

    /**
     * Test complete proforma generation flow with corrected calculations
     */
    public function test_complete_proforma_generation_flow()
    {
        // Create test apartment with total pricing
        $apartment = $this->createTestApartment([
            'amount' => 1500.00,
            'pricing_type' => 'total'
        ]);

        // Create proforma through controller
        $controller = new ProfomaController();
        $request = new Request([
            'apartment_id' => $apartment->id,
            'duration' => 6,
            'tenant_name' => 'Test Tenant',
            'tenant_email' => 'test@example.com'
        ]);

        // Generate proforma
        $response = $controller->store($request);
        
        // Verify proforma was created with correct calculation
        $proforma = ProfomaReceipt::latest()->first();
        $this->assertNotNull($proforma);
        $this->assertEquals(1500.00, $proforma->amount);
        $this->assertEquals('total_price_no_multiplication', $proforma->calculation_method);
        
        // Verify calculation log exists
        $this->assertNotNull($proforma->calculation_log);
        $this->assertIsArray($proforma->calculation_log);
    }

    /**
     * Test EasyRent invitation preview accuracy
     */
    public function test_easyrent_invitation_preview_accuracy()
    {
        // Create test apartment with monthly pricing
        $apartment = $this->createTestApartment([
            'amount' => 500.00,
            'pricing_type' => 'monthly'
        ]);

        // Create invitation controller
        $controller = new ApartmentInvitationController();
        
        // Test invitation preview calculation
        $previewAmount = $apartment->getCalculatedPaymentTotal(6);
        $this->assertEquals(3000.00, $previewAmount);

        // Verify the calculation service is used consistently
        $directCalculation = $this->calculationService->calculatePaymentTotal(
            $apartment->amount,
            6,
            $apartment->pricing_type
        );
        
        $this->assertTrue($directCalculation->isValid);
        $this->assertEquals($previewAmount, $directCalculation->totalAmount);
    }

    /**
     * Test cross-system consistency validation between proforma and invitation
     */
    public function test_cross_system_consistency_validation()
    {
        // Create test apartment
        $apartment = $this->createTestApartment([
            'amount' => 800.00,
            'pricing_type' => 'monthly'
        ]);

        $duration = 12;

        // Calculate through proforma system
        $proformaCalculation = $this->calculationService->calculatePaymentTotal(
            $apartment->amount,
            $duration,
            $apartment->pricing_type
        );

        // Calculate through invitation system
        $invitationAmount = $apartment->getCalculatedPaymentTotal($duration);

        // Calculate through payment system
        $paymentCalculation = $this->calculationService->calculatePaymentTotal(
            $apartment->amount,
            $duration,
            $apartment->pricing_type
        );

        // All systems should produce identical results
        $this->assertTrue($proformaCalculation->isValid);
        $this->assertTrue($paymentCalculation->isValid);
        
        $this->assertEquals($proformaCalculation->totalAmount, $invitationAmount);
        $this->assertEquals($proformaCalculation->totalAmount, $paymentCalculation->totalAmount);
        $this->assertEquals($invitationAmount, $paymentCalculation->totalAmount);
        
        // Expected result: 800 * 12 = 9600
        $this->assertEquals(9600.00, $proformaCalculation->totalAmount);
    }

    /**
     * Test calculation consistency across different pricing types
     */
    public function test_calculation_consistency_across_pricing_types()
    {
        // Test total pricing
        $totalApartment = $this->createTestApartment([
            'amount' => 1200.00,
            'pricing_type' => 'total'
        ]);

        // Test monthly pricing
        $monthlyApartment = $this->createTestApartment([
            'amount' => 200.00,
            'pricing_type' => 'monthly'
        ]);

        $duration = 6;

        // Calculate for total pricing
        $totalResult = $this->calculationService->calculatePaymentTotal(
            $totalApartment->amount,
            $duration,
            $totalApartment->pricing_type
        );

        // Calculate for monthly pricing
        $monthlyResult = $this->calculationService->calculatePaymentTotal(
            $monthlyApartment->amount,
            $duration,
            $monthlyApartment->pricing_type
        );

        $this->assertTrue($totalResult->isValid);
        $this->assertTrue($monthlyResult->isValid);

        // Total pricing should not multiply by duration
        $this->assertEquals(1200.00, $totalResult->totalAmount);
        
        // Monthly pricing should multiply by duration
        $this->assertEquals(1200.00, $monthlyResult->totalAmount); // 200 * 6 = 1200

        // Different calculation methods should be recorded
        $this->assertEquals('total_price_no_multiplication', $totalResult->calculationMethod);
        $this->assertEquals('monthly_price_with_duration_multiplication', $monthlyResult->calculationMethod);
    }

    /**
     * Test audit logging across all calculation scenarios
     */
    public function test_audit_logging_across_scenarios()
    {
        $apartment = $this->createTestApartment([
            'amount' => 1000.00,
            'pricing_type' => 'total'
        ]);

        // Perform calculation
        $result = $this->calculationService->calculatePaymentTotal(
            $apartment->amount,
            6,
            $apartment->pricing_type
        );

        $this->assertTrue($result->isValid);
        
        // Verify calculation steps are logged
        $this->assertNotEmpty($result->calculationSteps);
        
        // Verify required audit information is present
        $hasInputValidation = false;
        $hasFinalResult = false;
        
        foreach ($result->calculationSteps as $step) {
            if ($step['step'] === 'input_validation') {
                $hasInputValidation = true;
                $this->assertArrayHasKey('apartment_price', $step);
                $this->assertArrayHasKey('rental_duration', $step);
                $this->assertArrayHasKey('pricing_type', $step);
            }
            
            if ($step['step'] === 'final_result') {
                $hasFinalResult = true;
                $this->assertArrayHasKey('total_amount', $step);
                $this->assertArrayHasKey('calculation_method', $step);
            }
        }
        
        $this->assertTrue($hasInputValidation, 'Input validation step should be logged');
        $this->assertTrue($hasFinalResult, 'Final result step should be logged');
    }

    /**
     * Test error handling integration across systems
     */
    public function test_error_handling_integration()
    {
        // Test with invalid apartment data
        $apartment = $this->createTestApartment([
            'amount' => -100.00, // Invalid negative amount
            'pricing_type' => 'total'
        ]);

        // Calculation should fail gracefully
        $result = $this->calculationService->calculatePaymentTotal(
            $apartment->amount,
            6,
            $apartment->pricing_type
        );

        $this->assertFalse($result->isValid);
        $this->assertNotNull($result->errorMessage);
        $this->assertStringContains('negative', $result->errorMessage);
    }

    /**
     * Test calculation with additional charges integration
     */
    public function test_calculation_with_additional_charges_integration()
    {
        $apartment = $this->createTestApartment([
            'amount' => 1000.00,
            'pricing_type' => 'total'
        ]);

        $additionalCharges = [
            'service_fee' => 100.00,
            'cleaning_fee' => 50.00,
            'security_deposit' => 200.00
        ];

        $result = $this->calculationService->calculatePaymentTotalWithCharges(
            $apartment->amount,
            6,
            $apartment->pricing_type,
            $additionalCharges
        );

        $this->assertTrue($result->isValid);
        $this->assertEquals(1350.00, $result->totalAmount); // 1000 + 100 + 50 + 200
        $this->assertStringContains('with_additional_charges', $result->calculationMethod);
        
        // Verify additional charges are logged in calculation steps
        $hasChargeSteps = false;
        foreach ($result->calculationSteps as $step) {
            if ($step['step'] === 'additional_charge') {
                $hasChargeSteps = true;
                $this->assertArrayHasKey('charge_name', $step);
                $this->assertArrayHasKey('charge_amount', $step);
            }
        }
        $this->assertTrue($hasChargeSteps, 'Additional charge steps should be logged');
    }

    /**
     * Test backward compatibility with existing data
     */
    public function test_backward_compatibility_with_existing_data()
    {
        // Create apartment without pricing_type (simulating existing data)
        $apartment = $this->createTestApartment([
            'amount' => 1500.00,
            'pricing_type' => null // Simulating legacy data
        ]);

        // Should default to 'total' pricing type
        $pricingType = $apartment->getPricingType();
        $this->assertEquals('total', $pricingType);

        // Calculation should work with fallback
        $result = $this->calculationService->calculatePaymentTotal(
            $apartment->amount,
            6,
            $pricingType
        );

        $this->assertTrue($result->isValid);
        $this->assertEquals(1500.00, $result->totalAmount);
    }

    /**
     * Test performance with multiple concurrent calculations
     */
    public function test_performance_with_multiple_calculations()
    {
        $apartments = [];
        
        // Create multiple test apartments
        for ($i = 0; $i < 10; $i++) {
            $apartments[] = $this->createTestApartment([
                'amount' => rand(500, 2000),
                'pricing_type' => ['total', 'monthly'][rand(0, 1)]
            ]);
        }

        $startTime = microtime(true);
        
        // Perform multiple calculations
        $results = [];
        foreach ($apartments as $apartment) {
            $results[] = $this->calculationService->calculatePaymentTotal(
                $apartment->amount,
                rand(1, 12),
                $apartment->pricing_type
            );
        }
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // All calculations should be valid
        foreach ($results as $result) {
            $this->assertTrue($result->isValid);
        }

        // Performance should be reasonable (less than 1 second for 10 calculations)
        $this->assertLessThan(1000, $executionTime, 'Multiple calculations should complete within 1 second');
    }

    /**
     * Test data integrity across calculation updates
     */
    public function test_data_integrity_across_calculation_updates()
    {
        // Create apartment and proforma
        $apartment = $this->createTestApartment([
            'amount' => 1000.00,
            'pricing_type' => 'total'
        ]);

        $proforma = ProfomaReceipt::create([
            'apartment_id' => $apartment->id,
            'amount' => 1000.00,
            'duration' => 6,
            'calculation_method' => 'total_price_no_multiplication',
            'calculation_log' => [
                ['step' => 'initial_calculation', 'amount' => 1000.00]
            ]
        ]);

        // Update apartment pricing type
        $apartment->update(['pricing_type' => 'monthly']);

        // Existing proforma should remain unchanged
        $proforma->refresh();
        $this->assertEquals(1000.00, $proforma->amount);
        $this->assertEquals('total_price_no_multiplication', $proforma->calculation_method);

        // New calculations should use updated pricing type
        $newResult = $this->calculationService->calculatePaymentTotal(
            $apartment->amount,
            6,
            $apartment->pricing_type
        );

        $this->assertTrue($newResult->isValid);
        $this->assertEquals(6000.00, $newResult->totalAmount); // 1000 * 6
        $this->assertEquals('monthly_price_with_duration_multiplication', $newResult->calculationMethod);
    }

    /**
     * Helper method to create test apartment
     */
    private function createTestApartment(array $attributes = []): Apartment
    {
        $defaultAttributes = [
            'name' => 'Test Apartment',
            'amount' => 1000.00,
            'pricing_type' => 'total',
            'price_configuration' => null,
            'property_id' => 1,
            'apartment_type_id' => 1,
            'user_id' => 1, // Required field for apartment owner
            'apartment_id' => rand(1000, 9999), // Unique apartment identifier
            'bedrooms' => 2,
            'bathrooms' => 1,
            'size' => 100,
            'description' => 'Test apartment for integration testing'
        ];

        return Apartment::create(array_merge($defaultAttributes, $attributes));
    }
}
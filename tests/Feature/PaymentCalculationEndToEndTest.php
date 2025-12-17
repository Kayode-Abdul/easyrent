<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Apartment;
use App\Models\ProfomaReceipt;
use App\Models\ApartmentInvitation;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\PaymentCalculationServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;

class PaymentCalculationEndToEndTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private PaymentCalculationServiceInterface $calculationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculationService = app(PaymentCalculationServiceInterface::class);
        
        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
    }

    /**
     * Test complete proforma and invitation consistency end-to-end
     */
    public function test_complete_proforma_and_invitation_consistency_end_to_end()
    {
        // Step 1: Create apartment with total pricing
        $apartment = $this->createTestApartment([
            'amount' => 1500.00,
            'pricing_type' => 'total'
        ]);

        // Step 2: Generate proforma via HTTP request
        $proformaResponse = $this->actingAs($this->user)
            ->post('/proforma/store', [
                'apartment_id' => $apartment->id,
                'duration' => 6,
                'tenant_name' => 'John Doe',
                'tenant_email' => 'john@example.com'
            ]);

        $this->assertEquals(200, $proformaResponse->status());
        
        // Verify proforma was created correctly
        $proforma = ProfomaReceipt::where('apartment_id', $apartment->id)->latest()->first();
        $this->assertNotNull($proforma);
        $this->assertEquals(1500.00, $proforma->amount);
        $this->assertEquals('total_price_no_multiplication', $proforma->calculation_method);

        // Step 3: Create apartment invitation
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $apartment->id,
            'token' => 'test-token-' . uniqid(),
            'email' => 'john@example.com',
            'expires_at' => now()->addDays(7),
            'duration' => 6
        ]);

        // Step 4: Access invitation via HTTP request
        $invitationResponse = $this->get('/apartment/invite/' . $invitation->token);
        $this->assertEquals(200, $invitationResponse->status());

        // Step 5: Verify invitation shows correct payment amount
        $invitationAmount = $apartment->getCalculatedPaymentTotal(6);
        $this->assertEquals(1500.00, $invitationAmount);

        // Step 6: Verify consistency between proforma and invitation
        $this->assertEquals($proforma->amount, $invitationAmount);

        // Step 7: Process payment via invitation
        $paymentResponse = $this->actingAs($this->user)
            ->post('/apartment/invite/' . $invitation->token . '/payment', [
                'amount' => $invitationAmount,
                'payment_method' => 'paystack'
            ]);

        $this->assertEquals(200, $paymentResponse->status());

        // Step 8: Verify payment record consistency
        $payment = Payment::where('apartment_id', $apartment->id)->latest()->first();
        $this->assertNotNull($payment);
        $this->assertEquals($invitationAmount, $payment->amount);
        $this->assertEquals($proforma->amount, $payment->amount);
    }

    /**
     * Test monthly pricing end-to-end flow
     */
    public function test_monthly_pricing_end_to_end_flow()
    {
        // Step 1: Create apartment with monthly pricing
        $apartment = $this->createTestApartment([
            'amount' => 400.00,
            'pricing_type' => 'monthly'
        ]);

        $duration = 12;
        $expectedTotal = 4800.00; // 400 * 12

        // Step 2: Generate proforma
        $proformaResponse = $this->actingAs($this->user)
            ->post('/proforma/store', [
                'apartment_id' => $apartment->id,
                'duration' => $duration,
                'tenant_name' => 'Jane Smith',
                'tenant_email' => 'jane@example.com'
            ]);

        $this->assertEquals(200, $proformaResponse->status());

        // Verify proforma calculation
        $proforma = ProfomaReceipt::where('apartment_id', $apartment->id)->latest()->first();
        $this->assertNotNull($proforma);
        $this->assertEquals($expectedTotal, $proforma->amount);
        $this->assertEquals('monthly_price_with_duration_multiplication', $proforma->calculation_method);

        // Step 3: Create and access invitation
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $apartment->id,
            'token' => 'monthly-token-' . uniqid(),
            'email' => 'jane@example.com',
            'expires_at' => now()->addDays(7),
            'duration' => $duration
        ]);

        $invitationResponse = $this->get('/apartment/invite/' . $invitation->token);
        $this->assertEquals(200, $invitationResponse->status());

        // Step 4: Verify invitation calculation matches proforma
        $invitationAmount = $apartment->getCalculatedPaymentTotal($duration);
        $this->assertEquals($expectedTotal, $invitationAmount);
        $this->assertEquals($proforma->amount, $invitationAmount);

        // Step 5: Verify calculation service consistency
        $directCalculation = $this->calculationService->calculatePaymentTotal(
            $apartment->amount,
            $duration,
            $apartment->pricing_type
        );

        $this->assertTrue($directCalculation->isValid);
        $this->assertEquals($expectedTotal, $directCalculation->totalAmount);
        $this->assertEquals($proforma->amount, $directCalculation->totalAmount);
        $this->assertEquals($invitationAmount, $directCalculation->totalAmount);
    }

    /**
     * Test error handling end-to-end
     */
    public function test_error_handling_end_to_end()
    {
        // Step 1: Create apartment with invalid pricing configuration
        $apartment = $this->createTestApartment([
            'amount' => -100.00, // Invalid negative amount
            'pricing_type' => 'total'
        ]);

        // Step 2: Attempt to generate proforma - should handle error gracefully
        $proformaResponse = $this->actingAs($this->user)
            ->post('/proforma/store', [
                'apartment_id' => $apartment->id,
                'duration' => 6,
                'tenant_name' => 'Error Test',
                'tenant_email' => 'error@example.com'
            ]);

        // Should either redirect with error or return error response
        $this->assertTrue(in_array($proformaResponse->status(), [302, 400, 422]));

        // Step 3: Verify no invalid proforma was created
        $proforma = ProfomaReceipt::where('apartment_id', $apartment->id)->first();
        if ($proforma) {
            // If proforma was created, it should not have negative amount
            $this->assertGreaterThanOrEqual(0, $proforma->amount);
        }

        // Step 4: Test direct calculation service error handling
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
     * Test calculation with additional charges end-to-end
     */
    public function test_calculation_with_additional_charges_end_to_end()
    {
        // Step 1: Create apartment
        $apartment = $this->createTestApartment([
            'amount' => 1000.00,
            'pricing_type' => 'total'
        ]);

        // Step 2: Generate proforma with additional charges
        $additionalCharges = [
            'service_fee' => 100.00,
            'cleaning_fee' => 50.00,
            'security_deposit' => 200.00
        ];

        $proformaResponse = $this->actingAs($this->user)
            ->post('/proforma/store', [
                'apartment_id' => $apartment->id,
                'duration' => 6,
                'tenant_name' => 'Charges Test',
                'tenant_email' => 'charges@example.com',
                'additional_charges' => $additionalCharges
            ]);

        $this->assertEquals(200, $proformaResponse->status());

        // Step 3: Verify proforma includes additional charges
        $proforma = ProfomaReceipt::where('apartment_id', $apartment->id)->latest()->first();
        $this->assertNotNull($proforma);
        
        $expectedTotal = 1350.00; // 1000 + 100 + 50 + 200
        $this->assertEquals($expectedTotal, $proforma->amount);

        // Step 4: Test calculation service with charges
        $result = $this->calculationService->calculatePaymentTotalWithCharges(
            $apartment->amount,
            6,
            $apartment->pricing_type,
            $additionalCharges
        );

        $this->assertTrue($result->isValid);
        $this->assertEquals($expectedTotal, $result->totalAmount);
        $this->assertEquals($proforma->amount, $result->totalAmount);

        // Step 5: Verify calculation steps include charge details
        $hasChargeSteps = false;
        foreach ($result->calculationSteps as $step) {
            if ($step['step'] === 'additional_charge') {
                $hasChargeSteps = true;
                break;
            }
        }
        $this->assertTrue($hasChargeSteps, 'Calculation steps should include additional charges');
    }

    /**
     * Test pricing type change impact end-to-end
     */
    public function test_pricing_type_change_impact_end_to_end()
    {
        // Step 1: Create apartment with total pricing
        $apartment = $this->createTestApartment([
            'amount' => 1200.00,
            'pricing_type' => 'total'
        ]);

        // Step 2: Generate initial proforma
        $proformaResponse1 = $this->actingAs($this->user)
            ->post('/proforma/store', [
                'apartment_id' => $apartment->id,
                'duration' => 6,
                'tenant_name' => 'Initial Test',
                'tenant_email' => 'initial@example.com'
            ]);

        $this->assertEquals(200, $proformaResponse1->status());

        $proforma1 = ProfomaReceipt::where('apartment_id', $apartment->id)->latest()->first();
        $this->assertEquals(1200.00, $proforma1->amount);
        $this->assertEquals('total_price_no_multiplication', $proforma1->calculation_method);

        // Step 3: Change apartment pricing type
        $apartment->update(['pricing_type' => 'monthly']);

        // Step 4: Generate new proforma with updated pricing
        $proformaResponse2 = $this->actingAs($this->user)
            ->post('/proforma/store', [
                'apartment_id' => $apartment->id,
                'duration' => 6,
                'tenant_name' => 'Updated Test',
                'tenant_email' => 'updated@example.com'
            ]);

        $this->assertEquals(200, $proformaResponse2->status());

        $proforma2 = ProfomaReceipt::where('apartment_id', $apartment->id)->latest()->first();
        $this->assertEquals(7200.00, $proforma2->amount); // 1200 * 6
        $this->assertEquals('monthly_price_with_duration_multiplication', $proforma2->calculation_method);

        // Step 5: Verify old proforma remains unchanged (backward compatibility)
        $proforma1->refresh();
        $this->assertEquals(1200.00, $proforma1->amount);
        $this->assertEquals('total_price_no_multiplication', $proforma1->calculation_method);

        // Step 6: Verify new calculations use updated pricing type
        $newCalculation = $this->calculationService->calculatePaymentTotal(
            $apartment->amount,
            6,
            $apartment->pricing_type
        );

        $this->assertTrue($newCalculation->isValid);
        $this->assertEquals(7200.00, $newCalculation->totalAmount);
        $this->assertEquals($proforma2->amount, $newCalculation->totalAmount);
    }

    /**
     * Test audit trail end-to-end
     */
    public function test_audit_trail_end_to_end()
    {
        // Step 1: Create apartment
        $apartment = $this->createTestApartment([
            'amount' => 800.00,
            'pricing_type' => 'monthly'
        ]);

        // Step 2: Generate proforma
        $proformaResponse = $this->actingAs($this->user)
            ->post('/proforma/store', [
                'apartment_id' => $apartment->id,
                'duration' => 9,
                'tenant_name' => 'Audit Test',
                'tenant_email' => 'audit@example.com'
            ]);

        $this->assertEquals(200, $proformaResponse->status());

        // Step 3: Verify proforma has audit information
        $proforma = ProfomaReceipt::where('apartment_id', $apartment->id)->latest()->first();
        $this->assertNotNull($proforma);
        $this->assertNotNull($proforma->calculation_method);
        $this->assertNotNull($proforma->calculation_log);
        $this->assertIsArray($proforma->calculation_log);

        // Step 4: Verify calculation service provides audit trail
        $result = $this->calculationService->calculatePaymentTotal(
            $apartment->amount,
            9,
            $apartment->pricing_type
        );

        $this->assertTrue($result->isValid);
        $this->assertNotEmpty($result->calculationSteps);

        // Step 5: Verify audit trail contains required information
        $hasInputValidation = false;
        $hasCalculationStep = false;
        $hasFinalResult = false;

        foreach ($result->calculationSteps as $step) {
            if ($step['step'] === 'input_validation') {
                $hasInputValidation = true;
                $this->assertArrayHasKey('apartment_price', $step);
                $this->assertArrayHasKey('rental_duration', $step);
                $this->assertArrayHasKey('pricing_type', $step);
            }

            if ($step['step'] === 'monthly_pricing_calculation') {
                $hasCalculationStep = true;
                $this->assertArrayHasKey('apartment_price', $step);
                $this->assertArrayHasKey('rental_duration', $step);
                $this->assertArrayHasKey('multiplication_result', $step);
            }

            if ($step['step'] === 'final_result') {
                $hasFinalResult = true;
                $this->assertArrayHasKey('total_amount', $step);
                $this->assertArrayHasKey('calculation_method', $step);
            }
        }

        $this->assertTrue($hasInputValidation, 'Audit trail should include input validation');
        $this->assertTrue($hasCalculationStep, 'Audit trail should include calculation step');
        $this->assertTrue($hasFinalResult, 'Audit trail should include final result');

        // Step 6: Verify calculation consistency across audit trail
        $expectedAmount = 7200.00; // 800 * 9
        $this->assertEquals($expectedAmount, $result->totalAmount);
        $this->assertEquals($expectedAmount, $proforma->amount);
    }

    /**
     * Test user workflow from apartment viewing to payment
     */
    public function test_user_workflow_apartment_viewing_to_payment()
    {
        // Step 1: User views apartment listing
        $apartment = $this->createTestApartment([
            'amount' => 1100.00,
            'pricing_type' => 'total'
        ]);

        $listingResponse = $this->get('/apartment/' . $apartment->id);
        $this->assertEquals(200, $listingResponse->status());

        // Step 2: User requests proforma
        $proformaResponse = $this->actingAs($this->user)
            ->post('/proforma/store', [
                'apartment_id' => $apartment->id,
                'duration' => 8,
                'tenant_name' => $this->user->name,
                'tenant_email' => $this->user->email
            ]);

        $this->assertEquals(200, $proformaResponse->status());

        // Step 3: Landlord sends invitation
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $apartment->id,
            'token' => 'workflow-token-' . uniqid(),
            'email' => $this->user->email,
            'expires_at' => now()->addDays(7),
            'duration' => 8
        ]);

        // Step 4: User accesses invitation
        $invitationResponse = $this->get('/apartment/invite/' . $invitation->token);
        $this->assertEquals(200, $invitationResponse->status());

        // Step 5: User proceeds with payment
        $paymentResponse = $this->actingAs($this->user)
            ->post('/apartment/invite/' . $invitation->token . '/payment', [
                'amount' => 1100.00,
                'payment_method' => 'paystack'
            ]);

        $this->assertEquals(200, $paymentResponse->status());

        // Step 6: Verify complete workflow consistency
        $proforma = ProfomaReceipt::where('apartment_id', $apartment->id)->latest()->first();
        $payment = Payment::where('apartment_id', $apartment->id)->latest()->first();
        $invitationAmount = $apartment->getCalculatedPaymentTotal(8);

        $this->assertEquals(1100.00, $proforma->amount);
        $this->assertEquals(1100.00, $payment->amount);
        $this->assertEquals(1100.00, $invitationAmount);

        // All amounts should be consistent
        $this->assertEquals($proforma->amount, $payment->amount);
        $this->assertEquals($proforma->amount, $invitationAmount);
        $this->assertEquals($payment->amount, $invitationAmount);
    }

    /**
     * Helper method to create test apartment
     */
    private function createTestApartment(array $attributes = []): Apartment
    {
        $defaultAttributes = [
            'name' => 'Test Apartment ' . $this->faker->word,
            'amount' => 1000.00,
            'pricing_type' => 'total',
            'price_configuration' => null,
            'property_id' => 1,
            'apartment_type_id' => 1,
            'bedrooms' => $this->faker->numberBetween(1, 4),
            'bathrooms' => $this->faker->numberBetween(1, 3),
            'size' => $this->faker->numberBetween(50, 200),
            'description' => $this->faker->sentence
        ];

        return Apartment::create(array_merge($defaultAttributes, $attributes));
    }
}
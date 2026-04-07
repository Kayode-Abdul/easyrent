<?php

namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Models\User;
use App\Models\Property;
use App\Models\Apartment;
use App\Models\ApartmentInvitation;
use App\Models\Payment;
use App\Services\Session\SessionManager;
use App\Services\Email\EmailNotificationService;
use App\Services\Payment\PaymentIntegrationService;
use App\Services\Marketer\MarketerQualificationService;
use App\Mail\TenantApplicationMail;
use App\Mail\PaymentConfirmationMail;
use App\Mail\ApartmentAssignedMail;
use App\Mail\WelcomeToEasyRentMail;

/**
 * **Feature: easyrent-link-authentication, Integration Test: Complete System Flow**
 * 
 * This integration test validates the complete EasyRent Link Authentication System
 * by testing end-to-end flows including invitation access, authentication,
 * registration, payment processing, and marketer qualification.
 */
class EasyRentLinkAuthenticationIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $landlord;
    protected $property;
    protected $apartment;
    protected $sessionManager;
    protected $emailService;
    protected $paymentService;
    protected $marketerService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test landlord and property
        $this->landlord = User::factory()->create([
            'email' => 'landlord@test.com',
            'registration_source' => 'direct'
        ]);
        
        $this->property = Property::create([
            'user_id' => $this->landlord->user_id,
            'property_id' => 'PROP-' . uniqid(),
            'prop_type' => Property::TYPE_FLAT,
            'address' => '123 Test Street',
            'state' => 'Lagos',
            'lga' => 'Lagos Island',
            'no_of_apartment' => 10,
            'status' => 'approved'
        ]);
        
        $this->apartment = Apartment::create([
            'property_id' => $this->property->property_id,
            'apartment_id' => 'APT-' . uniqid(),
            'apartment_type' => 'Standard',
            'user_id' => $this->landlord->user_id,
            'range_start' => now(),
            'range_end' => now()->addYear(),
            'amount' => 500000,
            'occupied' => 0
        ]);

        // Initialize services
        $this->sessionManager = app(SessionManager::class);
        $this->emailService = app(EmailNotificationService::class);
        $this->paymentService = app(PaymentIntegrationService::class);
        $this->marketerService = app(MarketerQualificationService::class);

        // Fake mail and queue for testing
        Mail::fake();
        Queue::fake();
    }

    /** @test */
    public function test_invitation_creation_and_validation()
    {
        // Test invitation creation
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $this->apartment->apartment_id,
            'landlord_id' => $this->landlord->user_id,
            'invitation_token' => 'test-token-' . uniqid(),
            'expires_at' => now()->addDays(7),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        $this->assertNotNull($invitation);
        $this->assertEquals(ApartmentInvitation::STATUS_ACTIVE, $invitation->status);
        $this->assertTrue($invitation->isActive());
        $this->assertFalse($invitation->isExpired());

        // Test token validation
        $this->assertTrue($invitation->validateTokenSecurity());
        
        // Test access recording
        $invitation->recordAccess('127.0.0.1', 'Test User Agent');
        $this->assertEquals(1, $invitation->fresh()->access_count);
        
        // Test rate limiting
        $this->assertTrue($invitation->checkRateLimit('127.0.0.1'));
        
        // Test session data storage
        $sessionData = [
            'application_data' => [
                'duration' => 12,
                'move_in_date' => now()->addDays(30)->format('Y-m-d')
            ]
        ];
        
        $invitation->storeSessionData($sessionData);
        $retrievedData = $invitation->getSessionData();
        
        $this->assertNotNull($retrievedData);
        $this->assertEquals(12, $retrievedData['data']['application_data']['duration']);
    }

    /** @test */
    public function test_session_management_service()
    {
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $this->apartment->apartment_id,
            'landlord_id' => $this->landlord->user_id,
            'invitation_token' => 'session-test-' . uniqid(),
            'expires_at' => now()->addDays(7),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        // Test session storage
        $sessionData = [
            'invitation_token' => $invitation->invitation_token,
            'application_data' => [
                'duration' => 12,
                'move_in_date' => now()->addDays(30)->format('Y-m-d')
            ]
        ];

        $this->sessionManager->storeInvitationContext($invitation->invitation_token, $sessionData);
        
        // Test session retrieval
        $retrievedData = $this->sessionManager->retrieveInvitationContext($invitation->invitation_token);
        $this->assertNotNull($retrievedData);
        $this->assertEquals($sessionData, $retrievedData);
        
        // Test session existence check
        $this->assertTrue($this->sessionManager->hasInvitationContext($invitation->invitation_token));
        
        // Test session cleanup
        $this->sessionManager->clearInvitationContext($invitation->invitation_token);
        $this->assertNull($this->sessionManager->retrieveInvitationContext($invitation->invitation_token));
        $this->assertFalse($this->sessionManager->hasInvitationContext($invitation->invitation_token));
    }

    /** @test */
    public function test_marketer_qualification_service()
    {
        // Create potential marketer
        $potentialMarketer = User::factory()->create([
            'email' => 'marketer@test.com'
        ]);

        // Create referred landlord
        $referredLandlord = User::factory()->create([
            'email' => 'referred.landlord@test.com',
            'referred_by' => $potentialMarketer->id,
            'registration_source' => 'referral'
        ]);

        // Create property for referred landlord
        $referredProperty = Property::create([
            'user_id' => $referredLandlord->user_id,
            'property_id' => 'PROP-REF-' . uniqid(),
            'prop_type' => Property::TYPE_FLAT,
            'address' => '456 Referred Street',
            'state' => 'Lagos',
            'lga' => 'Lagos Island',
            'no_of_apartment' => 5,
            'status' => 'approved'
        ]);

        $referredApartment = Apartment::create([
            'property_id' => $referredProperty->property_id,
            'apartment_id' => 'APT-REF-' . uniqid(),
            'apartment_type' => 'Standard',
            'user_id' => $referredLandlord->user_id,
            'range_start' => now(),
            'range_end' => now()->addYear(),
            'amount' => 300000,
            'occupied' => 0
        ]);

        // Create tenant and payment
        $tenant = User::factory()->create([
            'email' => 'tenant@test.com'
        ]);

        $payment = Payment::create([
            'user_id' => $tenant->id,
            'apartment_id' => $referredApartment->apartment_id,
            'amount' => $referredApartment->amount,
            'status' => 'completed',
            'reference' => 'PAY-' . uniqid()
        ]);

        // Test marketer qualification logic
        $qualifies = $potentialMarketer->qualifiesForMarketerStatus();
        $this->assertTrue($qualifies);

        // Test marketer service evaluation
        $this->marketerService->evaluateAndPromoteQualifiedUsers();
        
        $potentialMarketer->refresh();
        $this->assertTrue($potentialMarketer->hasRole('marketer'));
    }

    /** @test */
    public function test_payment_integration_service()
    {
        $user = User::factory()->create();
        
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $this->apartment->apartment_id,
            'landlord_id' => $this->landlord->user_id,
            'invitation_token' => 'payment-test-' . uniqid(),
            'expires_at' => now()->addDays(7),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        // Test payment creation
        $paymentData = [
            'user_id' => $user->id,
            'apartment_id' => $this->apartment->apartment_id,
            'amount' => $this->apartment->amount,
            'duration' => 12,
            'move_in_date' => now()->addDays(30)
        ];

        $payment = $this->paymentService->createPayment($paymentData);
        $this->assertNotNull($payment);
        $this->assertEquals($this->apartment->amount, $payment->amount);
        $this->assertEquals('pending', $payment->status);

        // Test payment completion
        $this->paymentService->completePayment($payment, $invitation);
        
        $payment->refresh();
        $this->assertEquals('completed', $payment->status);
        
        // Test apartment assignment
        $this->apartment->refresh();
        $this->assertEquals(1, $this->apartment->occupied);
        
        // Test session cleanup
        $invitation->refresh();
        $this->assertEquals(ApartmentInvitation::STATUS_USED, $invitation->status);
    }

    /** @test */
    public function test_security_measures_and_validation()
    {
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $this->apartment->apartment_id,
            'landlord_id' => $this->landlord->id,
            'invitation_token' => 'security-test-' . uniqid(),
            'expires_at' => now()->addDays(7),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        // Test rate limiting
        $ipAddress = '127.0.0.1';
        
        // Should allow initial access
        $this->assertTrue($invitation->checkRateLimit($ipAddress));
        
        // Simulate multiple rapid accesses
        for ($i = 0; $i < ApartmentInvitation::MAX_ACCESS_ATTEMPTS + 5; $i++) {
            $invitation->increment('rate_limit_count');
        }
        
        // Should now be rate limited
        $this->assertFalse($invitation->fresh()->checkRateLimit($ipAddress));

        // Test token security validation
        $this->assertTrue($invitation->validateTokenSecurity());
        
        // Test expiration handling
        $expiredInvitation = ApartmentInvitation::create([
            'apartment_id' => $this->apartment->apartment_id,
            'landlord_id' => $this->landlord->id,
            'invitation_token' => 'expired-test-' . uniqid(),
            'expires_at' => now()->subDays(1),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        $this->assertTrue($expiredInvitation->isExpired());
        $this->assertFalse($expiredInvitation->isActive());
        
        // Test security validation
        $securityIssues = $invitation->performSecurityValidation($ipAddress);
        $this->assertContains('rate_limit_exceeded', $securityIssues);
    }

    /** @test */
    public function test_invitation_session_lifecycle()
    {
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $this->apartment->apartment_id,
            'landlord_id' => $this->landlord->id,
            'invitation_token' => 'lifecycle-test-' . uniqid(),
            'expires_at' => now()->addDays(7),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        // Test session data storage
        $sessionData = [
            'application_data' => [
                'duration' => 12,
                'move_in_date' => now()->addDays(30)->format('Y-m-d')
            ]
        ];

        $invitation->storeSessionData($sessionData);
        $retrievedData = $invitation->getSessionData();
        
        $this->assertNotNull($retrievedData);
        $this->assertEquals(12, $retrievedData['data']['application_data']['duration']);

        // Test session expiration
        $expiredInvitation = ApartmentInvitation::create([
            'apartment_id' => $this->apartment->apartment_id,
            'landlord_id' => $this->landlord->id,
            'invitation_token' => 'expired-session-' . uniqid(),
            'expires_at' => now()->addDays(7),
            'session_expires_at' => now()->subHours(1),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        $this->assertTrue($expiredInvitation->isSessionExpired());
        
        // Test cleanup
        $cleanedCount = ApartmentInvitation::cleanupExpiredSessions();
        $this->assertGreaterThanOrEqual(0, $cleanedCount);

        // Test session clearing
        $invitation->clearSessionData();
        $this->assertNull($invitation->fresh()->getSessionData());
    }

    /** @test */
    public function test_email_notification_service()
    {
        $user = User::factory()->create();
        
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $this->apartment->apartment_id,
            'landlord_id' => $this->landlord->id,
            'invitation_token' => 'email-test-' . uniqid(),
            'expires_at' => now()->addDays(7),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'apartment_id' => $this->apartment->apartment_id,
            'amount' => $this->apartment->amount,
            'status' => 'completed',
            'reference' => 'PAY-' . uniqid()
        ]);

        // Test application notification
        $result = $this->emailService->sendApplicationNotification($invitation, $payment);
        $this->assertTrue($result);
        
        Mail::assertSent(TenantApplicationMail::class);

        // Test payment confirmation
        $result = $this->emailService->sendPaymentConfirmation($invitation, $payment);
        $this->assertTrue($result);
        
        Mail::assertSent(PaymentConfirmationMail::class);

        // Test welcome email for new user
        $newUser = User::factory()->create([
            'registration_source' => 'easyrent_invitation'
        ]);
        
        $result = $this->emailService->sendWelcomeEmail($newUser, $invitation);
        $this->assertTrue($result);
        
        Mail::assertSent(WelcomeToEasyRentMail::class);

        // Test assignment confirmation
        $result = $this->emailService->sendAssignmentConfirmation($invitation, $payment);
        $this->assertTrue($result);
        
        Mail::assertSent(ApartmentAssignedMail::class);
    }

    /** @test */
    public function test_error_handling_and_validation()
    {
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $this->apartment->apartment_id,
            'landlord_id' => $this->landlord->id,
            'invitation_token' => 'error-test-' . uniqid(),
            'expires_at' => now()->addDays(7),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        // Test invalid session data handling
        $invalidSessionData = [
            'invalid_field' => 'invalid_value'
        ];
        
        try {
            $invitation->storeSessionData($invalidSessionData);
            $retrievedData = $invitation->getSessionData();
            $this->assertNotNull($retrievedData);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Exception::class, $e);
        }

        // Test expired invitation handling
        $expiredInvitation = ApartmentInvitation::create([
            'apartment_id' => $this->apartment->apartment_id,
            'landlord_id' => $this->landlord->id,
            'invitation_token' => 'expired-error-' . uniqid(),
            'expires_at' => now()->subDays(1),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        $this->assertTrue($expiredInvitation->checkAndHandleExpiration());
        $this->assertEquals(ApartmentInvitation::STATUS_EXPIRED, $expiredInvitation->fresh()->status);

        // Test security breach handling
        $invitation->invalidateForSecurity('Test security breach');
        $this->assertEquals(ApartmentInvitation::STATUS_CANCELLED, $invitation->fresh()->status);
    }

    /** @test */
    public function test_system_performance_and_scalability()
    {
        $startTime = microtime(true);
        
        // Create multiple invitations
        $invitations = [];
        for ($i = 0; $i < 10; $i++) {
            $invitations[] = ApartmentInvitation::create([
                'apartment_id' => $this->apartment->apartment_id,
                'landlord_id' => $this->landlord->id,
                'invitation_token' => 'perf-test-' . $i . '-' . uniqid(),
                'expires_at' => now()->addDays(7),
                'status' => ApartmentInvitation::STATUS_ACTIVE
            ]);
        }

        // Test batch operations
        foreach ($invitations as $invitation) {
            $invitation->recordAccess('127.0.0.1', 'Test User Agent');
            $invitation->performSecurityValidation('127.0.0.1');
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete within reasonable time
        $this->assertLessThan(2.0, $executionTime, 'Multiple invitation operations should complete quickly');

        // Test memory usage
        $memoryUsage = memory_get_peak_usage(true);
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsage, 'Memory usage should be reasonable');
        
        // Test batch cleanup
        $cleanedCount = ApartmentInvitation::cleanupExpiredSessions();
        $this->assertGreaterThanOrEqual(0, $cleanedCount);
    }

    /** @test */
    public function test_data_consistency_and_integrity()
    {
        $user = User::factory()->create([
            'registration_source' => 'easyrent_invitation'
        ]);
        
        $invitation = ApartmentInvitation::create([
            'apartment_id' => $this->apartment->apartment_id,
            'landlord_id' => $this->landlord->id,
            'invitation_token' => 'consistency-test-' . uniqid(),
            'expires_at' => now()->addDays(7),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        // Create payment
        $payment = Payment::create([
            'user_id' => $user->id,
            'apartment_id' => $this->apartment->apartment_id,
            'amount' => $this->apartment->amount,
            'status' => 'completed',
            'reference' => 'PAY-' . uniqid()
        ]);

        // Complete the flow using services
        $this->paymentService->completePayment($payment, $invitation);

        // Verify data consistency
        $payment->refresh();
        $this->apartment->refresh();
        $invitation->refresh();

        // Payment should be completed
        $this->assertEquals('completed', $payment->status);
        $this->assertEquals($user->id, $payment->user_id);
        $this->assertEquals($this->apartment->amount, $payment->amount);

        // Apartment should be occupied
        $this->assertEquals(1, $this->apartment->occupied);

        // Invitation should be used
        $this->assertEquals(ApartmentInvitation::STATUS_USED, $invitation->status);

        // User should have correct registration source
        $this->assertEquals('easyrent_invitation', $user->registration_source);
        
        // Test invitation statistics
        $stats = $invitation->getInvitationStats();
        $this->assertIsArray($stats);
        $this->assertEquals(ApartmentInvitation::STATUS_USED, $stats['status']);
    }
}
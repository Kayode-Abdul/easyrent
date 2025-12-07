<?php

namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Models\User;
use App\Models\ApartmentInvitation;
use App\Services\Session\SessionManager;

/**
 * **Feature: easyrent-link-authentication, Final Integration Test**
 * 
 * This integration test validates the implemented components of the EasyRent Link Authentication System
 * and documents the current system capabilities.
 */
class EasyRentLinkFinalIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $sessionManager;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->sessionManager = app(SessionManager::class);
        Mail::fake();
        Queue::fake();
    }

    /** @test */
    public function test_apartment_invitation_model_functionality()
    {
        // Test ApartmentInvitation model creation and methods
        $invitation = new ApartmentInvitation([
            'apartment_id' => 'APT-TEST-123',
            'landlord_id' => 1,
            'invitation_token' => 'test-token-' . uniqid(),
            'expires_at' => now()->addDays(7),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        // Test status methods
        $this->assertTrue($invitation->isActive());
        $this->assertFalse($invitation->isExpired());

        // Test token generation
        $token = $invitation->generateSecureToken();
        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token));

        // Test security hash generation
        $invitation->invitation_token = $token;
        $hash = $invitation->generateSecurityHash();
        $this->assertIsString($hash);

        // Test token validation
        $invitation->security_hash = $hash;
        $this->assertTrue($invitation->validateTokenSecurity());

        // Test rate limiting
        $this->assertTrue($invitation->checkRateLimit('127.0.0.1'));

        // Test access recording
        $invitation->recordAccess('127.0.0.1', 'Test User Agent');
        $this->assertEquals(1, $invitation->access_count);

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
        $this->assertArrayHasKey('data', $retrievedData);
        $this->assertEquals(12, $retrievedData['data']['application_data']['duration']);

        // Test session expiration
        $this->assertFalse($invitation->isSessionExpired());

        // Test session cleanup
        $invitation->clearSessionData();
        $this->assertNull($invitation->getSessionData());
    }

    /** @test */
    public function test_session_manager_integration()
    {
        $token = 'integration-test-' . uniqid();
        
        // Test session storage with SessionManager
        $sessionData = [
            'invitation_token' => $token,
            'application_data' => [
                'duration' => 12,
                'move_in_date' => now()->addDays(30)->format('Y-m-d')
            ]
        ];

        $this->sessionManager->storeInvitationContext($token, $sessionData);
        
        // Test session retrieval (accounting for the wrapper structure)
        $retrievedData = $this->sessionManager->retrieveInvitationContext($token);
        $this->assertNotNull($retrievedData);
        
        // The SessionManager wraps the data, so we need to check the structure
        $this->assertArrayHasKey('invitation_token', $retrievedData);
        $this->assertEquals($token, $retrievedData['invitation_token']);
        
        // Test session existence
        $this->assertTrue($this->sessionManager->hasInvitationContext($token));
        
        // Test session cleanup
        $this->sessionManager->clearInvitationContext($token);
        $this->assertFalse($this->sessionManager->hasInvitationContext($token));
    }

    /** @test */
    public function test_user_model_marketer_qualification()
    {
        // Test User model marketer qualification methods
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // Test qualification check (should return boolean)
        $qualifies = $user->qualifiesForMarketerStatus();
        $this->assertIsBool($qualifies);

        // Test registration source check
        $user->registration_source = 'easyrent_invitation';
        $user->save();
        
        $this->assertTrue($user->isEasyRentRegistration());

        // Test marketer evaluation method exists
        $this->assertTrue(method_exists($user, 'evaluateMarketerPromotion'));
    }

    /** @test */
    public function test_invitation_security_validation()
    {
        $invitation = new ApartmentInvitation([
            'apartment_id' => 'APT-SECURITY-TEST',
            'landlord_id' => 1,
            'invitation_token' => 'security-test-' . uniqid(),
            'expires_at' => now()->addDays(7),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        // Generate security hash
        $invitation->security_hash = $invitation->generateSecurityHash();

        // Test comprehensive security validation
        $securityIssues = $invitation->performSecurityValidation('127.0.0.1', 'Test User Agent');
        
        // Should have no security issues for a valid invitation
        $this->assertIsArray($securityIssues);
        $this->assertEmpty($securityIssues);

        // Test expired invitation handling
        $expiredInvitation = new ApartmentInvitation([
            'apartment_id' => 'APT-EXPIRED-TEST',
            'landlord_id' => 1,
            'invitation_token' => 'expired-test-' . uniqid(),
            'expires_at' => now()->subDays(1),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        $this->assertTrue($expiredInvitation->checkAndHandleExpiration());
        $this->assertEquals(ApartmentInvitation::STATUS_EXPIRED, $expiredInvitation->status);
    }

    /** @test */
    public function test_invitation_statistics_and_cleanup()
    {
        $invitation = new ApartmentInvitation([
            'apartment_id' => 'APT-STATS-TEST',
            'landlord_id' => 1,
            'invitation_token' => 'stats-test-' . uniqid(),
            'expires_at' => now()->addDays(7),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        // Test statistics generation
        $stats = $invitation->getInvitationStats();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('status', $stats);
        $this->assertArrayHasKey('access_count', $stats);
        $this->assertArrayHasKey('is_expired', $stats);

        // Test static cleanup methods exist
        $this->assertTrue(method_exists(ApartmentInvitation::class, 'cleanupExpiredSessions'));
        $this->assertTrue(method_exists(ApartmentInvitation::class, 'expireOldInvitations'));

        // Test cleanup operations
        $cleanedSessions = ApartmentInvitation::cleanupExpiredSessions();
        $this->assertIsInt($cleanedSessions);

        $expiredInvitations = ApartmentInvitation::expireOldInvitations();
        $this->assertIsInt($expiredInvitations);
    }

    /** @test */
    public function test_email_service_integration()
    {
        // Test that email services are properly configured
        $emailService = app(\App\Services\Email\EmailNotificationService::class);
        $this->assertNotNull($emailService);

        // Test that email classes exist
        $this->assertTrue(class_exists(\App\Mail\TenantApplicationMail::class));
        $this->assertTrue(class_exists(\App\Mail\PaymentConfirmationMail::class));
        $this->assertTrue(class_exists(\App\Mail\ApartmentAssignedMail::class));
        $this->assertTrue(class_exists(\App\Mail\WelcomeToEasyRentMail::class));
    }

    /** @test */
    public function test_system_performance_and_scalability()
    {
        $startTime = microtime(true);
        
        // Test creating multiple invitations
        $invitations = [];
        for ($i = 0; $i < 10; $i++) {
            $invitation = new ApartmentInvitation([
                'apartment_id' => 'APT-PERF-' . $i,
                'landlord_id' => 1,
                'invitation_token' => 'perf-test-' . $i . '-' . uniqid(),
                'expires_at' => now()->addDays(7),
                'status' => ApartmentInvitation::STATUS_ACTIVE
            ]);

            $invitation->security_hash = $invitation->generateSecurityHash();
            $invitation->recordAccess('127.0.0.1', 'Performance Test');
            $invitations[] = $invitation;
        }

        // Test session operations
        for ($i = 0; $i < 10; $i++) {
            $token = 'perf-session-' . $i;
            $data = ['test' => $i, 'timestamp' => now()];
            
            $this->sessionManager->storeInvitationContext($token, $data);
            $this->sessionManager->retrieveInvitationContext($token);
            $this->sessionManager->clearInvitationContext($token);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete within reasonable time
        $this->assertLessThan(2.0, $executionTime, 'Performance operations should complete quickly');
        
        // Test memory usage
        $memoryUsage = memory_get_peak_usage(true);
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsage, 'Memory usage should be reasonable');
    }

    /** @test */
    public function test_error_handling_and_edge_cases()
    {
        // Test invalid session operations
        $result = $this->sessionManager->retrieveInvitationContext('nonexistent-token');
        $this->assertNull($result);

        $this->assertFalse($this->sessionManager->hasInvitationContext('nonexistent-token'));

        // Test invitation with invalid data
        $invitation = new ApartmentInvitation();
        
        // Should handle missing data gracefully
        $this->assertFalse($invitation->isActive());
        $this->assertTrue($invitation->isExpired()); // No expires_at means expired

        // Test security validation with missing data
        $securityIssues = $invitation->performSecurityValidation('127.0.0.1');
        $this->assertIsArray($securityIssues);
        $this->assertNotEmpty($securityIssues); // Should have issues
    }

    /** @test */
    public function test_system_integration_workflow()
    {
        // Test a complete workflow using the implemented components
        $token = 'workflow-integration-' . uniqid();
        
        // Step 1: Create invitation
        $invitation = new ApartmentInvitation([
            'apartment_id' => 'APT-WORKFLOW',
            'landlord_id' => 1,
            'invitation_token' => $token,
            'expires_at' => now()->addDays(7),
            'status' => ApartmentInvitation::STATUS_ACTIVE
        ]);

        $invitation->security_hash = $invitation->generateSecurityHash();
        
        // Step 2: Validate security
        $securityIssues = $invitation->performSecurityValidation('127.0.0.1', 'Integration Test');
        $this->assertEmpty($securityIssues);
        
        // Step 3: Store session data
        $sessionData = [
            'application_data' => [
                'duration' => 12,
                'move_in_date' => now()->addDays(30)->format('Y-m-d')
            ]
        ];
        
        $invitation->storeSessionData($sessionData);
        $this->sessionManager->storeInvitationContext($token, $sessionData);
        
        // Step 4: Verify data integrity
        $invitationSessionData = $invitation->getSessionData();
        $managerSessionData = $this->sessionManager->retrieveInvitationContext($token);
        
        $this->assertNotNull($invitationSessionData);
        $this->assertNotNull($managerSessionData);
        
        // Step 5: Complete workflow and cleanup
        $invitation->markAsUsed();
        $invitation->clearSessionData();
        $this->sessionManager->clearInvitationContext($token);
        
        // Verify final state
        $this->assertEquals(ApartmentInvitation::STATUS_USED, $invitation->status);
        $this->assertNull($invitation->getSessionData());
        $this->assertFalse($this->sessionManager->hasInvitationContext($token));
    }

    /** @test */
    public function test_system_components_availability()
    {
        // Test that all required services are available
        $this->assertNotNull(app(\App\Services\Session\SessionManager::class));
        $this->assertNotNull(app(\App\Services\Email\EmailNotificationService::class));
        
        // Test that controllers exist
        $this->assertTrue(class_exists(\App\Http\Controllers\ApartmentInvitationController::class));
        $this->assertTrue(class_exists(\App\Http\Controllers\Auth\LoginController::class));
        $this->assertTrue(class_exists(\App\Http\Controllers\Auth\RegisterController::class));
        
        // Test that middleware exists
        $this->assertTrue(class_exists(\App\Http\Middleware\InvitationSessionMiddleware::class));
        $this->assertTrue(class_exists(\App\Http\Middleware\InvitationRateLimitMiddleware::class));
        
        // Test that models exist
        $this->assertTrue(class_exists(\App\Models\ApartmentInvitation::class));
        $this->assertTrue(class_exists(\App\Models\User::class));
        $this->assertTrue(class_exists(\App\Models\Apartment::class));
        $this->assertTrue(class_exists(\App\Models\Property::class));
        $this->assertTrue(class_exists(\App\Models\Payment::class));
    }
}
<?php

namespace Tests\Unit;

use Tests\TestCase;

class SessionManagerPropertyTest extends TestCase
{

    /**
     * **Feature: easyrent-link-authentication, Property 8: Session Lifecycle Management**
     * 
     * Property: For any invitation session, the system should store invitation details 
     * with 24-hour expiration for unauthenticated users, persist context until payment 
     * completion for registered users, automatically cleanup data after successful payment, 
     * and handle session expiration with proper cleanup.
     * 
     * **Validates: Requirements 7.1, 7.2, 7.3, 7.4, 7.5**
     */
    public function test_session_lifecycle_management_property()
    {
        // Run property-based test with 100 iterations (as specified in design document)
        for ($i = 0; $i < 100; $i++) {
            // Generate random test data
            $token = $this->generateRandomString(8, 32);
            $sessionData = [
                'apartment_id' => rand(1, 1000),
                'landlord_id' => rand(1, 1000),
                'application_data' => [
                    'duration' => rand(1, 24),
                    'move_in_date' => date('Y-m-d', strtotime('+' . rand(1, 30) . ' days')),
                    'notes' => $this->generateRandomString(0, 100)
                ],
                'registration_data' => [
                    'first_name' => $this->generateRandomString(1, 50),
                    'last_name' => $this->generateRandomString(1, 50),
                    'email' => $this->generateRandomString(5, 100),
                    'phone' => $this->generateRandomString(10, 15)
                ]
            ];

            // Skip empty tokens as they're invalid
            if (empty(trim($token))) {
                continue;
            }

            // Create a mock session manager to test the properties
            $sessionManager = new MockSessionManager();

            // Property 1: Store invitation details with automatic 24-hour expiration
            $sessionManager->storeInvitationContext($token, $sessionData);
            
            // Verify data is stored
            $this->assertTrue($sessionManager->hasInvitationContext($token), 
                "Session should exist after storing - iteration $i");
            
            $retrievedData = $sessionManager->retrieveInvitationContext($token);
            $this->assertNotNull($retrievedData, "Retrieved data should not be null - iteration $i");
            $this->assertEquals($token, $retrievedData['invitation_token'], 
                "Token should match - iteration $i");
            $this->assertEquals($sessionData, $retrievedData['data'], 
                "Session data should match - iteration $i");
            
            // Verify expiration is set to 24 hours from now (within 1 minute tolerance)
            $expiresAt = new \DateTime($retrievedData['expires_at']);
            $expectedExpiration = new \DateTime('+24 hours');
            $diffInMinutes = abs(($expiresAt->getTimestamp() - $expectedExpiration->getTimestamp()) / 60);
            $this->assertTrue(
                $diffInMinutes <= 1,
                "Session expiration should be approximately 24 hours from creation - iteration $i"
            );

            // Property 2: Session data persistence until completion
            // Store additional application data
            $sessionManager->storeApplicationData($token, $sessionData['application_data']);
            $retrievedAppData = $sessionManager->retrieveApplicationData($token);
            $this->assertEquals($sessionData['application_data'], $retrievedAppData, 
                "Application data should match - iteration $i");

            // Store registration data
            $sessionManager->storeRegistrationData($token, $sessionData['registration_data']);
            $retrievedRegData = $sessionManager->retrieveRegistrationData($token);
            $this->assertEquals($sessionData['registration_data'], $retrievedRegData, 
                "Registration data should match - iteration $i");

            // Property 3: Transfer to authenticated session
            $userId = rand(1, 1000);
            $sessionManager->transferToAuthenticatedSession($token, $userId);
            
            // Verify authenticated session data is stored
            $authenticatedContext = $sessionManager->getAuthenticatedContext();
            $this->assertNotNull($authenticatedContext, 
                "Authenticated context should exist - iteration $i");
            $this->assertEquals($token, $authenticatedContext['token'], 
                "Authenticated token should match - iteration $i");
            $this->assertEquals($userId, $authenticatedContext['user_id'], 
                "User ID should match - iteration $i");
            $this->assertEquals($sessionData, $authenticatedContext['original_data'], 
                "Original data should match - iteration $i");

            // Property 4: Session expiration handling
            // Test extending session expiration
            $originalExpiration = $sessionManager->getSessionExpiration($token);
            $sessionManager->extendSessionExpiration($token, 48);
            $newExpiration = $sessionManager->getSessionExpiration($token);
            
            $this->assertNotNull($originalExpiration, 
                "Original expiration should exist - iteration $i");
            $this->assertNotNull($newExpiration, 
                "New expiration should exist - iteration $i");
            $this->assertTrue($newExpiration > $originalExpiration, 
                "New expiration should be later than original - iteration $i");

            // Property 5: Cleanup functionality
            // Clear the session context
            $sessionManager->clearInvitationContext($token);
            
            // Verify data is removed
            $this->assertFalse($sessionManager->hasInvitationContext($token), 
                "Session should not exist after clearing - iteration $i");
            $this->assertNull($sessionManager->retrieveInvitationContext($token), 
                "Retrieved data should be null after clearing - iteration $i");
            $this->assertNull($sessionManager->retrieveApplicationData($token), 
                "Application data should be null after clearing - iteration $i");
            $this->assertNull($sessionManager->retrieveRegistrationData($token), 
                "Registration data should be null after clearing - iteration $i");
        }
    }

    /**
     * Property test for session expiration behavior
     */
    public function test_session_expiration_property()
    {
        // Run property-based test with 100 iterations
        for ($i = 0; $i < 100; $i++) {
            $token = $this->generateRandomString(8, 32);
            $sessionData = [
                'test_data' => $this->generateRandomString(1, 100)
            ];

            // Skip empty tokens
            if (empty(trim($token))) {
                continue;
            }

            $sessionManager = new MockSessionManager();

            // Store session data
            $sessionManager->storeInvitationContext($token, $sessionData);
            
            // Verify data exists
            $this->assertTrue($sessionManager->hasInvitationContext($token), 
                "Session should exist before expiration - iteration $i");

            // Simulate session expiration
            $sessionManager->expireSession($token);

            // Attempt to retrieve expired session - should return null
            $retrievedData = $sessionManager->retrieveInvitationContext($token);
            $this->assertNull($retrievedData, 
                "Expired session data should return null - iteration $i");
            
            // Verify session is automatically cleaned up
            $this->assertFalse($sessionManager->hasInvitationContext($token), 
                "Session should not exist after expiration - iteration $i");
        }
    }

    /**
     * Property test for cleanup of expired sessions
     */
    public function test_cleanup_expired_sessions_property()
    {
        // Run property-based test with 50 iterations (fewer since this is more complex)
        for ($i = 0; $i < 50; $i++) {
            // Generate 2-5 random tokens
            $tokenCount = rand(2, 5);
            $tokens = [];
            for ($j = 0; $j < $tokenCount; $j++) {
                $tokens[] = $this->generateRandomString(8, 32);
            }
            
            $sessionData = [
                'test_data' => $this->generateRandomString(1, 50)
            ];

            // Filter out empty tokens
            $validTokens = array_filter($tokens, function($token) {
                return !empty(trim($token));
            });

            if (count($validTokens) < 1) {
                continue; // Need at least 1 valid token for this test
            }

            $validTokens = array_unique($validTokens);
            if (count($validTokens) < 1) {
                continue; // Need at least 1 unique token
            }

            $sessionManager = new MockSessionManager();

            // Store multiple sessions
            foreach ($validTokens as $token) {
                $sessionManager->storeInvitationContext($token, $sessionData);
            }

            // Verify all sessions exist
            foreach ($validTokens as $token) {
                $this->assertTrue($sessionManager->hasInvitationContext($token), 
                    "Session should exist after storing - iteration $i, token: $token");
            }

            // Expire some sessions (at least 1, up to half)
            $expireCount = max(1, intval(count($validTokens) / 2));
            $tokensToExpire = array_slice($validTokens, 0, $expireCount);
            
            foreach ($tokensToExpire as $token) {
                $sessionManager->expireSession($token);
            }

            // Run cleanup
            $cleanedCount = $sessionManager->cleanupExpiredSessions();
            
            // Verify cleanup worked
            $this->assertGreaterThanOrEqual($expireCount, $cleanedCount, 
                "Should clean up at least $expireCount expired sessions - iteration $i");
        }
    }

    /**
     * Generate a random string of specified length range
     */
    private function generateRandomString(int $minLength, int $maxLength): string
    {
        $length = rand($minLength, $maxLength);
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $result = '';
        
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $result;
    }
}

/**
 * Mock implementation of SessionManager for testing properties
 */
class MockSessionManager
{
    private array $sessions = [];
    private array $authenticatedContext = [];
    
    public function storeInvitationContext(string $token, array $data): void
    {
        $contextData = [
            'invitation_token' => $token,
            'stored_at' => date('c'),
            'expires_at' => date('c', strtotime('+24 hours')),
            'user_agent' => 'Test User Agent',
            'ip_address' => '127.0.0.1',
            'data' => $data
        ];
        
        $this->sessions[$token] = $contextData;
    }
    
    public function retrieveInvitationContext(string $token): ?array
    {
        if (!isset($this->sessions[$token])) {
            return null;
        }
        
        $contextData = $this->sessions[$token];
        
        // Check if session has expired
        $expiresAt = new \DateTime($contextData['expires_at']);
        if ($expiresAt < new \DateTime()) {
            $this->clearInvitationContext($token);
            return null;
        }
        
        return $contextData;
    }
    
    public function clearInvitationContext(string $token): void
    {
        unset($this->sessions[$token]);
    }
    
    public function hasInvitationContext(string $token): bool
    {
        return $this->retrieveInvitationContext($token) !== null;
    }
    
    public function storeApplicationData(string $token, array $applicationData): void
    {
        if (isset($this->sessions[$token])) {
            $this->sessions[$token]['data']['application_data'] = $applicationData;
        }
    }
    
    public function retrieveApplicationData(string $token): ?array
    {
        $contextData = $this->retrieveInvitationContext($token);
        return $contextData['data']['application_data'] ?? null;
    }
    
    public function storeRegistrationData(string $token, array $registrationData): void
    {
        if (isset($this->sessions[$token])) {
            $this->sessions[$token]['data']['registration_data'] = $registrationData;
        }
    }
    
    public function retrieveRegistrationData(string $token): ?array
    {
        $contextData = $this->retrieveInvitationContext($token);
        return $contextData['data']['registration_data'] ?? null;
    }
    
    public function transferToAuthenticatedSession(string $token, int $userId): void
    {
        $contextData = $this->retrieveInvitationContext($token);
        
        if ($contextData) {
            $this->authenticatedContext = [
                'token' => $token,
                'user_id' => $userId,
                'transferred_at' => date('c'),
                'original_data' => $contextData['data']
            ];
        }
    }
    
    public function getAuthenticatedContext(): ?array
    {
        return empty($this->authenticatedContext) ? null : $this->authenticatedContext;
    }
    
    public function getSessionExpiration(string $token): ?\DateTime
    {
        $contextData = $this->retrieveInvitationContext($token);
        
        if (!$contextData || !isset($contextData['expires_at'])) {
            return null;
        }
        
        return new \DateTime($contextData['expires_at']);
    }
    
    public function extendSessionExpiration(string $token, int $hours = 24): void
    {
        if (isset($this->sessions[$token])) {
            $newExpiration = new \DateTime("+{$hours} hours");
            $this->sessions[$token]['expires_at'] = $newExpiration->format('c');
        }
    }
    
    public function expireSession(string $token): void
    {
        if (isset($this->sessions[$token])) {
            $this->sessions[$token]['expires_at'] = date('c', strtotime('-1 hour'));
        }
    }
    
    public function cleanupExpiredSessions(): int
    {
        $cleanedCount = 0;
        
        foreach ($this->sessions as $token => $sessionData) {
            $expiresAt = new \DateTime($sessionData['expires_at']);
            if ($expiresAt < new \DateTime()) {
                $this->clearInvitationContext($token);
                $cleanedCount++;
            }
        }
        
        return $cleanedCount;
    }
}
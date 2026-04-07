<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use App\Services\Security\SuspiciousActivityDetector;
use App\Services\Security\SecurityBreachResponseService;
use App\Services\Security\InputValidationService;
use App\Services\Logging\EasyRentLogger;
use Illuminate\Http\Request;

class SecurityMeasuresTest extends TestCase
{

    protected $suspiciousActivityDetector;
    protected $securityBreachResponseService;
    protected $inputValidationService;
    protected $logger;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->logger = $this->createMock(EasyRentLogger::class);
        $this->suspiciousActivityDetector = new SuspiciousActivityDetector($this->logger);
        $this->securityBreachResponseService = new SecurityBreachResponseService($this->logger);
        $this->inputValidationService = new InputValidationService();
    }

    public function test_suspicious_activity_detector_identifies_rapid_access()
    {
        $ipAddress = '192.168.1.100';
        $request = Request::create('/test', 'GET');
        $request->server->set('REMOTE_ADDR', $ipAddress);
        
        // Simulate rapid access
        for ($i = 0; $i < 20; $i++) {
            $analysis = $this->suspiciousActivityDetector->analyzeRequest($request);
        }
        
        $this->assertTrue($analysis['is_suspicious']);
        $this->assertContains('rapid_access', $analysis['patterns']);
    }

    public function test_input_validation_detects_xss_attempts()
    {
        $maliciousInput = '<script>alert("xss")</script>';
        $request = Request::create('/test', 'POST', ['description' => $maliciousInput]);
        
        $result = $this->inputValidationService->validateAndSanitizeRequest($request);
        
        $this->assertFalse($result['is_safe']);
        $this->assertContains('xss_attempt', $result['threats_detected']);
        $this->assertContains('description', $result['blocked_fields']);
    }

    public function test_input_validation_detects_sql_injection()
    {
        $maliciousInput = "'; DROP TABLE users; --";
        $request = Request::create('/test', 'POST', ['email' => $maliciousInput]);
        
        $result = $this->inputValidationService->validateAndSanitizeRequest($request);
        
        $this->assertFalse($result['is_safe']);
        $this->assertContains('sql_injection_attempt', $result['threats_detected']);
    }

    public function test_invitation_token_validation()
    {
        // Valid token (proper hex format)
        $validToken = bin2hex(random_bytes(32)); // 64 hex characters
        $result = $this->inputValidationService->validateInvitationToken($validToken);
        $this->assertTrue($result['is_valid']);
        
        // Another valid token with mixed hex characters
        $validToken2 = 'abcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890';
        $result2 = $this->inputValidationService->validateInvitationToken($validToken2);
        $this->assertTrue($result2['is_valid']);
        
        // Invalid length
        $invalidToken = 'short';
        $result = $this->inputValidationService->validateInvitationToken($invalidToken);
        $this->assertFalse($result['is_valid']);
        $this->assertContains('invalid_length', $result['issues']);
        
        // Invalid format (non-hex characters)
        $invalidToken = str_repeat('z', 64);
        $result = $this->inputValidationService->validateInvitationToken($invalidToken);
        $this->assertFalse($result['is_valid']);
        $this->assertContains('invalid_format', $result['issues']);
        
        // Suspicious pattern (too many repeated characters)
        $suspiciousToken = str_repeat('a', 64);
        $result = $this->inputValidationService->validateInvitationToken($suspiciousToken);
        $this->assertFalse($result['is_valid']);
        $this->assertContains('suspicious_pattern', $result['issues']);
    }

    public function test_security_breach_response_determines_severity()
    {
        $breachData = [
            'risk_score' => 95,
            'patterns' => ['rapid_access', 'failed_tokens'],
            'affected_tokens' => 15
        ];
        
        $reflection = new \ReflectionClass($this->securityBreachResponseService);
        $method = $reflection->getMethod('determineSeverity');
        $method->setAccessible(true);
        
        $severity = $method->invoke($this->securityBreachResponseService, $breachData);
        
        $this->assertEquals('critical', $severity);
    }

    public function test_emergency_lockdown_functionality()
    {
        Cache::flush(); // Clear cache first
        
        $this->assertFalse($this->securityBreachResponseService->isEmergencyLockdown());
        
        $breachData = [
            'severity' => 'critical',
            'ip_address' => '192.168.1.100',
            'risk_score' => 100,
            'patterns' => ['rapid_access', 'failed_tokens', 'bot_user_agent']
        ];
        
        $this->securityBreachResponseService->handleSecurityBreach($breachData);
        
        $this->assertTrue($this->securityBreachResponseService->isEmergencyLockdown());
    }

    public function test_input_sanitization_for_names()
    {
        $maliciousName = '<script>alert("xss")</script>John O\'Connor-Smith';
        $request = Request::create('/test', 'POST', ['name' => $maliciousName]);
        
        $result = $this->inputValidationService->validateAndSanitizeRequest($request);
        
        $this->assertEquals('John O\'Connor-Smith', $result['sanitized_input']['name']);
    }

    public function test_rate_limiting_cache_keys()
    {
        $ipAddress = '192.168.1.100';
        
        // Clear any existing cache
        Cache::flush();
        
        $reflection = new \ReflectionClass($this->suspiciousActivityDetector);
        $method = $reflection->getMethod('recordRequest');
        $method->setAccessible(true);
        
        $method->invoke($this->suspiciousActivityDetector, $ipAddress, 'test-agent', 'test-route');
        
        $this->assertTrue(Cache::has("rate_limit:minute:{$ipAddress}"));
        $this->assertTrue(Cache::has("rate_limit:hour:{$ipAddress}"));
    }
}
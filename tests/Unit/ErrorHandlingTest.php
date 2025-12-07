<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\Request;
use App\Services\ErrorHandling\EasyRentErrorHandler;
use App\Services\ErrorHandling\ErrorRecoveryService;
use App\Services\Monitoring\ErrorMonitoringService;
use App\Services\Logging\EasyRentLogger;
use App\Services\Session\SessionManagerInterface;
use App\Services\Email\EmailNotificationInterface;
use Exception;

class ErrorHandlingTest extends TestCase
{
    protected $errorHandler;
    protected $recoveryService;
    protected $monitoringService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock dependencies
        $logger = $this->createMock(EasyRentLogger::class);
        $sessionManager = $this->createMock(SessionManagerInterface::class);
        $emailService = $this->createMock(EmailNotificationInterface::class);
        
        $this->errorHandler = new EasyRentErrorHandler($logger);
        $this->recoveryService = new ErrorRecoveryService($sessionManager, $emailService);
        $this->monitoringService = new ErrorMonitoringService($logger);
    }

    public function test_authentication_error_handling()
    {
        $request = Request::create('/test', 'GET');
        $request->setLaravelSession($this->app['session.store']);
        $exception = new Exception('Session expired');
        
        $result = $this->errorHandler->handleAuthenticationError($exception, $request);
        
        $this->assertArrayHasKey('error_type', $result);
        $this->assertArrayHasKey('user_message', $result);
        $this->assertArrayHasKey('recovery_options', $result);
        $this->assertEquals('session_timeout', $result['error_type']);
        $this->assertTrue($result['preserve_session']);
    }

    public function test_payment_error_handling()
    {
        $request = Request::create('/payment', 'POST');
        $exception = new Exception('Payment gateway timeout');
        $context = ['payment_reference' => 'test_ref_123', 'amount' => 50000];
        
        $result = $this->errorHandler->handlePaymentError($exception, $request, $context);
        
        $this->assertArrayHasKey('error_type', $result);
        $this->assertArrayHasKey('user_message', $result);
        $this->assertArrayHasKey('retry_config', $result);
        $this->assertEquals('gateway_timeout', $result['error_type']);
        $this->assertTrue($result['preserve_state']);
    }

    public function test_session_error_handling()
    {
        $request = Request::create('/test', 'GET');
        $request->setLaravelSession($this->app['session.store']);
        $exception = new Exception('Session corrupted');
        
        $result = $this->errorHandler->handleSessionError($exception, $request);
        
        $this->assertArrayHasKey('error_type', $result);
        $this->assertArrayHasKey('user_message', $result);
        $this->assertArrayHasKey('recovery_strategy', $result);
        $this->assertEquals('session_corrupted', $result['error_type']);
        $this->assertTrue($result['requires_fresh_start']);
    }

    public function test_system_error_handling()
    {
        $request = Request::create('/test', 'GET');
        $exception = new Exception('Database connection failed');
        
        $result = $this->errorHandler->handleSystemError($exception, $request);
        
        $this->assertArrayHasKey('error_type', $result);
        $this->assertArrayHasKey('user_message', $result);
        $this->assertArrayHasKey('degradation_strategy', $result);
        $this->assertEquals('database_error', $result['error_type']);
        $this->assertArrayHasKey('estimated_recovery_time', $result);
    }

    public function test_user_friendly_messages()
    {
        $message = $this->errorHandler->getUserFriendlyMessage('payment', ['error_type' => 'insufficient_funds']);
        
        $this->assertStringContainsString('insufficient funds', $message);
        $this->assertStringNotContainsString('Exception', $message);
        $this->assertStringNotContainsString('Error:', $message);
    }

    public function test_recovery_options_generation()
    {
        $options = $this->errorHandler->getRecoveryOptions('payment', ['error_type' => 'gateway_timeout']);
        
        $this->assertArrayHasKey('retry_payment', $options);
        $this->assertArrayHasKey('preserve_application_state', $options);
        $this->assertTrue($options['retry_payment']);
    }

    public function test_error_monitoring_tracks_errors()
    {
        $request = Request::create('/test', 'GET');
        $request->setLaravelSession($this->app['session.store']);
        $exception = new Exception('Test error');
        
        // This should not throw an exception
        $this->monitoringService->trackError($exception, $request, 'system');
        
        // Verify health status - single error might cause degraded status
        $healthStatus = $this->monitoringService->checkSystemHealth();
        $this->assertContains($healthStatus['status'], ['healthy', 'degraded']);
        $this->assertArrayHasKey('metrics', $healthStatus);
    }

    public function test_requires_immediate_attention_detection()
    {
        $criticalException = new Exception('Database connection failed');
        $normalException = new Exception('Validation failed');
        
        $this->assertTrue($this->errorHandler->requiresImmediateAttention($criticalException));
        $this->assertFalse($this->errorHandler->requiresImmediateAttention($normalException));
    }
}
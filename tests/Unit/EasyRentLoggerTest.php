<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Logging\EasyRentLogger;
use App\Models\ApartmentInvitation;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery;

class EasyRentLoggerTest extends TestCase
{
    protected $logger;
    protected $mockRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new EasyRentLogger();
        
        // Create a mock request
        $this->mockRequest = Mockery::mock(Request::class);
        $this->mockRequest->shouldReceive('ip')->andReturn('127.0.0.1');
        $this->mockRequest->shouldReceive('userAgent')->andReturn('Test User Agent');
        $this->mockRequest->shouldReceive('session->getId')->andReturn('test-session-id');
        $this->mockRequest->shouldReceive('header')->with('referer')->andReturn('http://test.com');
        $this->mockRequest->shouldReceive('fullUrl')->andReturn('http://test.com/test');
        $this->mockRequest->shouldReceive('method')->andReturn('GET');
    }

    public function test_logs_invitation_access()
    {
        Log::shouldReceive('channel')
            ->with('easyrent_invitations')
            ->once()
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->with('Invitation accessed', Mockery::type('array'))
            ->once();

        $invitation = new ApartmentInvitation([
            'id' => 1,
            'token' => 'test-token',
            'apartment_id' => 1,
        ]);

        $user = new User(['user_id' => 1]);

        $this->logger->logInvitationAccess($invitation, $this->mockRequest, $user);
        
        $this->assertTrue(true); // Assert that no exceptions were thrown
    }

    public function test_logs_authentication_event()
    {
        Log::shouldReceive('channel')
            ->with('easyrent_auth')
            ->once()
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->with('Authentication event: login_attempt', Mockery::type('array'))
            ->once();

        $user = new User(['user_id' => 1]);

        $this->logger->logAuthenticationEvent('login_attempt', $this->mockRequest, $user, [
            'email' => 'test@example.com',
            'successful' => true,
        ]);
        
        $this->assertTrue(true);
    }

    public function test_logs_payment_transaction()
    {
        Log::shouldReceive('channel')
            ->with('easyrent_payments')
            ->once()
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->with('Payment transaction: payment_initiated', Mockery::type('array'))
            ->once();

        $payment = new Payment([
            'id' => 1,
            'user_id' => 1,
            'apartment_id' => 1,
            'amount' => 100000,
            'status' => 'pending',
            'payment_method' => 'paystack',
            'transaction_reference' => 'test-ref',
        ]);

        $this->logger->logPaymentTransaction('payment_initiated', $payment, $this->mockRequest, [
            'invitation_id' => 1,
        ]);
        
        $this->assertTrue(true);
    }

    public function test_logs_error_with_context()
    {
        Log::shouldReceive('channel')
            ->with('easyrent_errors')
            ->once()
            ->andReturnSelf();
        
        Log::shouldReceive('error')
            ->with('Test error message', Mockery::type('array'))
            ->once();

        $exception = new \Exception('Test exception message');

        $this->logger->logError('Test error message', $exception, $this->mockRequest, [
            'additional_context' => 'test',
        ]);
        
        $this->assertTrue(true);
    }

    public function test_logs_performance_metric()
    {
        Log::shouldReceive('channel')
            ->with('easyrent_performance')
            ->once()
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->with('Performance metric: test_operation', Mockery::type('array'))
            ->once();

        $this->logger->logPerformanceMetric('test_operation', 0.5, $this->mockRequest, [
            'additional_data' => 'test',
        ]);
        
        $this->assertTrue(true);
    }

    public function test_logs_security_event()
    {
        Log::shouldReceive('channel')
            ->with('easyrent_security')
            ->once()
            ->andReturnSelf();
        
        Log::shouldReceive('warning')
            ->with('Security event: suspicious_activity', Mockery::type('array'))
            ->once();

        $this->logger->logSecurityEvent('suspicious_activity', $this->mockRequest, [
            'reason' => 'Multiple failed attempts',
        ]);
        
        $this->assertTrue(true);
    }

    public function test_logs_email_event()
    {
        Log::shouldReceive('channel')
            ->with('easyrent_emails')
            ->once()
            ->andReturnSelf();
        
        Log::shouldReceive('info')
            ->with('Email event: email_sent', Mockery::type('array'))
            ->once();

        $this->logger->logEmailEvent('email_sent', 'welcome_email', ['test@example.com'], [
            'template' => 'welcome',
        ]);
        
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
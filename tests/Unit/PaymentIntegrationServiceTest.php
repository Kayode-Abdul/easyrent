<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Payment\PaymentIntegrationService;
use App\Services\Session\SessionManagerInterface;
use App\Services\Marketer\MarketerQualificationService;
use App\Models\Payment;
use App\Models\ApartmentInvitation;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PaymentIntegrationServiceTest extends TestCase
{
    protected $paymentIntegrationService;
    protected $sessionManager;
    protected $marketerQualificationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->sessionManager = $this->createMock(SessionManagerInterface::class);
        $this->marketerQualificationService = $this->createMock(MarketerQualificationService::class);
        $this->paymentIntegrationService = new PaymentIntegrationService(
            $this->sessionManager,
            $this->marketerQualificationService
        );
        
        Mail::fake();
    }

    /** @test */
    public function it_can_generate_transaction_id()
    {
        // Create mock objects
        $tenant = new User(['user_id' => 12345]);
        $invitation = new ApartmentInvitation(['invitation_token' => 'test_token_123']);
        
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->paymentIntegrationService);
        $method = $reflection->getMethod('generateTransactionId');
        $method->setAccessible(true);

        $transactionId = $method->invoke($this->paymentIntegrationService, $invitation, $tenant);
        
        $this->assertStringStartsWith('ER-INV-', $transactionId);
        $this->assertGreaterThan(15, strlen($transactionId)); // Should be reasonably long
    }

    /** @test */
    public function it_generates_unique_transaction_ids()
    {
        // Create mock objects
        $tenant = new User(['user_id' => 12345]);
        $invitation = new ApartmentInvitation(['invitation_token' => 'test_token_123']);
        
        // Use reflection to test private method
        $reflection = new \ReflectionClass($this->paymentIntegrationService);
        $method = $reflection->getMethod('generateTransactionId');
        $method->setAccessible(true);

        $transactionId1 = $method->invoke($this->paymentIntegrationService, $invitation, $tenant);
        sleep(1); // Ensure different timestamp
        $transactionId2 = $method->invoke($this->paymentIntegrationService, $invitation, $tenant);

        $this->assertNotEquals($transactionId1, $transactionId2);
        $this->assertStringStartsWith('ER-INV-', $transactionId1);
        $this->assertStringStartsWith('ER-INV-', $transactionId2);
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(PaymentIntegrationService::class, $this->paymentIntegrationService);
    }
}
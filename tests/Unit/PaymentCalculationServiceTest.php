<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Payment\PaymentCalculationService;
use App\Services\Payment\PaymentCalculationServiceInterface;
use App\Services\Payment\PaymentCalculationResult;
use App\Services\Security\PaymentCalculationSecurityService;
use App\Services\Monitoring\PaymentCalculationMonitoringService;
use App\Services\Audit\PaymentCalculationAuditLogger;
use App\Services\Cache\PaymentCalculationCacheService;
use Mockery;

class PaymentCalculationServiceTest extends TestCase
{
    private PaymentCalculationService $service;
    private $mockSecurityService;
    private $mockMonitoringService;
    private $mockAuditLogger;
    private $mockCacheService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mocks for dependencies
        $this->mockSecurityService = Mockery::mock(PaymentCalculationSecurityService::class);
        $this->mockMonitoringService = Mockery::mock(PaymentCalculationMonitoringService::class);
        $this->mockAuditLogger = Mockery::mock(PaymentCalculationAuditLogger::class);
        $this->mockCacheService = Mockery::mock(PaymentCalculationCacheService::class);
        
        // Set up default mock behaviors
        $this->mockMonitoringService->shouldReceive('recordCalculationPerformance')->andReturn(true);
        $this->mockMonitoringService->shouldReceive('recordCalculationAccuracy')->andReturn(true);
        $this->mockMonitoringService->shouldReceive('recordCalculationError')->andReturn(true);
        $this->mockAuditLogger->shouldReceive('logCalculationAudit')->andReturn(true);
        $this->mockCacheService->shouldReceive('getCachedCalculationResult')->andReturn(null);
        $this->mockCacheService->shouldReceive('cacheCalculationResult')->andReturn(true);
        
        $this->service = new PaymentCalculationService(
            $this->mockSecurityService,
            $this->mockMonitoringService,
            $this->mockAuditLogger,
            $this->mockCacheService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test basic total pricing calculation
     */
    public function test_total_pricing_calculation()
    {
        $result = $this->service->calculatePaymentTotal(1000.00, 6, 'total');
        
        $this->assertTrue($result->isValid);
        $this->assertEquals(1000.00, $result->totalAmount);
        $this->assertEquals('total_price_no_multiplication', $result->calculationMethod);
        $this->assertNull($result->errorMessage);
    }

    /**
     * Test basic monthly pricing calculation
     */
    public function test_monthly_pricing_calculation()
    {
        $result = $this->service->calculatePaymentTotal(500.00, 6, 'monthly');
        
        $this->assertTrue($result->isValid);
        $this->assertEquals(3000.00, $result->totalAmount);
        $this->assertEquals('monthly_price_with_duration_multiplication', $result->calculationMethod);
        $this->assertNull($result->errorMessage);
    }

    /**
     * Test negative apartment price validation
     */
    public function test_negative_apartment_price_validation()
    {
        $result = $this->service->calculatePaymentTotal(-100.00, 6, 'total');
        
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('cannot be negative', $result->errorMessage);
    }

    /**
     * Test zero rental duration validation
     */
    public function test_zero_rental_duration_validation()
    {
        $result = $this->service->calculatePaymentTotal(1000.00, 0, 'total');
        
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('must be positive', $result->errorMessage);
    }

    /**
     * Test invalid pricing type validation
     */
    public function test_invalid_pricing_type_validation()
    {
        $result = $this->service->calculatePaymentTotal(1000.00, 6, 'invalid');
        
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('Invalid pricing type', $result->errorMessage);
    }

    /**
     * Test maximum apartment price validation
     */
    public function test_maximum_apartment_price_validation()
    {
        $result = $this->service->calculatePaymentTotal(10000000000.00, 6, 'total');
        
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('exceeds maximum allowed value', $result->errorMessage);
    }

    /**
     * Test maximum rental duration validation
     */
    public function test_maximum_rental_duration_validation()
    {
        $result = $this->service->calculatePaymentTotal(1000.00, 150, 'total');
        
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('exceeds maximum allowed value', $result->errorMessage);
    }

    /**
     * Test overflow protection in monthly calculations
     */
    public function test_overflow_protection_monthly_calculation()
    {
        $result = $this->service->calculatePaymentTotal(999999999.99, 120, 'monthly');
        
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('exceed maximum result limit', $result->errorMessage);
    }

    /**
     * Test precision handling for currency calculations
     */
    public function test_precision_handling()
    {
        $result = $this->service->calculatePaymentTotal(100.333333, 3, 'monthly');
        
        $this->assertTrue($result->isValid);
        $this->assertEquals(301.00, $result->totalAmount); // Should be rounded to 2 decimal places
    }

    /**
     * Test zero apartment price (promotional offers)
     */
    public function test_zero_apartment_price()
    {
        $result = $this->service->calculatePaymentTotal(0.00, 6, 'total');
        
        $this->assertTrue($result->isValid);
        $this->assertEquals(0.00, $result->totalAmount);
    }

    /**
     * Test fallback logic for invalid pricing type
     */
    public function test_fallback_logic_invalid_pricing_type()
    {
        $result = $this->service->calculatePaymentTotal(1000.00, 6, '');
        
        // Empty string should be invalid, not fallback to total
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('Invalid pricing type', $result->errorMessage);
    }

    /**
     * Test calculation steps logging
     */
    public function test_calculation_steps_logging()
    {
        $result = $this->service->calculatePaymentTotal(500.00, 6, 'monthly');
        
        $this->assertTrue($result->isValid);
        $this->assertNotEmpty($result->calculationSteps);
        $this->assertArrayHasKey('step', $result->calculationSteps[0]);
    }

    /**
     * Test pricing configuration validation - valid config
     */
    public function test_pricing_configuration_validation_valid()
    {
        $config = [
            'pricing_type' => 'total',
            'base_price' => 1000.00,
            'duration_multiplier' => 1.0
        ];
        
        $isValid = $this->service->validatePricingConfiguration($config);
        
        $this->assertTrue($isValid);
    }

    /**
     * Test pricing configuration validation - missing pricing type
     */
    public function test_pricing_configuration_validation_missing_pricing_type()
    {
        $config = [
            'base_price' => 1000.00
        ];
        
        $isValid = $this->service->validatePricingConfiguration($config);
        
        $this->assertFalse($isValid);
    }

    /**
     * Test pricing configuration validation - invalid pricing type
     */
    public function test_pricing_configuration_validation_invalid_pricing_type()
    {
        $config = [
            'pricing_type' => 'invalid'
        ];
        
        $isValid = $this->service->validatePricingConfiguration($config);
        
        $this->assertFalse($isValid);
    }

    /**
     * Test calculation with additional charges
     */
    public function test_calculation_with_additional_charges()
    {
        $additionalCharges = [
            'service_fee' => 50.00,
            'cleaning_fee' => 25.00
        ];
        
        $result = $this->service->calculatePaymentTotalWithCharges(
            1000.00, 
            6, 
            'total', 
            $additionalCharges
        );
        
        $this->assertTrue($result->isValid);
        $this->assertEquals(1075.00, $result->totalAmount); // 1000 + 50 + 25
        $this->assertStringContainsString('with_additional_charges', $result->calculationMethod);
    }

    /**
     * Test supported pricing types
     */
    public function test_supported_pricing_types()
    {
        $supportedTypes = $this->service->getSupportedPricingTypes();
        
        $this->assertArrayHasKey('total', $supportedTypes);
        $this->assertArrayHasKey('monthly', $supportedTypes);
    }

    /**
     * Test validation limits
     */
    public function test_validation_limits()
    {
        $limits = $this->service->getValidationLimits();
        
        $this->assertArrayHasKey('max_rental_duration', $limits);
        $this->assertArrayHasKey('max_apartment_price', $limits);
        $this->assertArrayHasKey('min_apartment_price', $limits);
    }

    /**
     * Test secure calculation with valid inputs
     */
    public function test_secure_calculation_valid_inputs()
    {
        $inputs = [
            'apartment_price' => 1000.00,
            'rental_duration' => 6,
            'pricing_type' => 'total'
        ];
        
        $this->mockSecurityService->shouldReceive('sanitizeCalculationInputs')
            ->once()
            ->with($inputs)
            ->andReturn([
                'is_valid' => true,
                'validation_errors' => [],
                'security_issues' => [],
                'sanitized_inputs' => $inputs
            ]);
        
        $result = $this->service->calculatePaymentTotalSecure($inputs);
        
        $this->assertTrue($result->isValid);
        $this->assertEquals(1000.00, $result->totalAmount);
    }

    /**
     * Test secure calculation with validation errors
     */
    public function test_secure_calculation_validation_errors()
    {
        $inputs = [
            'apartment_price' => -100.00,
            'rental_duration' => 6,
            'pricing_type' => 'total'
        ];
        
        $this->mockSecurityService->shouldReceive('sanitizeCalculationInputs')
            ->once()
            ->with($inputs)
            ->andReturn([
                'is_valid' => false,
                'validation_errors' => ['Negative price not allowed'],
                'security_issues' => [],
                'sanitized_inputs' => []
            ]);
        
        $result = $this->service->calculatePaymentTotalSecure($inputs);
        
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('Input validation failed', $result->errorMessage);
    }

    /**
     * Test secure calculation with security issues
     */
    public function test_secure_calculation_security_issues()
    {
        $inputs = [
            'apartment_price' => 1000.00,
            'rental_duration' => 6,
            'pricing_type' => 'malicious_input'
        ];
        
        $this->mockSecurityService->shouldReceive('sanitizeCalculationInputs')
            ->once()
            ->with($inputs)
            ->andReturn([
                'is_valid' => true,
                'validation_errors' => [],
                'security_issues' => ['Suspicious input detected'],
                'sanitized_inputs' => $inputs
            ]);
        
        $result = $this->service->calculatePaymentTotalSecure($inputs);
        
        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('Security validation failed', $result->errorMessage);
    }

    /**
     * Test calculation steps logging functionality
     */
    public function test_log_calculation_steps()
    {
        $result = PaymentCalculationResult::success(
            1000.00,
            'test_method',
            [['step' => 'test_step']]
        );
        
        // This should not throw an exception
        $this->service->logCalculationSteps($result);
        
        $this->assertTrue(true); // If we get here, the method executed successfully
    }

    /**
     * Test edge case: minimum valid price
     */
    public function test_minimum_valid_price()
    {
        $result = $this->service->calculatePaymentTotal(0.01, 1, 'total');
        
        $this->assertTrue($result->isValid);
        $this->assertEquals(0.01, $result->totalAmount);
    }

    /**
     * Test edge case: maximum valid duration
     */
    public function test_maximum_valid_duration()
    {
        $result = $this->service->calculatePaymentTotal(100.00, 120, 'monthly');
        
        $this->assertTrue($result->isValid);
        $this->assertEquals(12000.00, $result->totalAmount);
    }

    /**
     * Test case sensitivity in pricing type
     */
    public function test_pricing_type_case_sensitivity()
    {
        $result1 = $this->service->calculatePaymentTotal(1000.00, 6, 'TOTAL');
        $result2 = $this->service->calculatePaymentTotal(1000.00, 6, 'Total');
        $result3 = $this->service->calculatePaymentTotal(1000.00, 6, 'total');
        
        $this->assertTrue($result1->isValid);
        $this->assertTrue($result2->isValid);
        $this->assertTrue($result3->isValid);
        
        $this->assertEquals($result1->totalAmount, $result2->totalAmount);
        $this->assertEquals($result2->totalAmount, $result3->totalAmount);
    }

    /**
     * Test whitespace handling in pricing type
     */
    public function test_pricing_type_whitespace_handling()
    {
        $result = $this->service->calculatePaymentTotal(1000.00, 6, ' total ');
        
        $this->assertTrue($result->isValid);
        $this->assertEquals(1000.00, $result->totalAmount);
    }
}
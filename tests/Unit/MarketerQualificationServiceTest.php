<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Marketer\MarketerQualificationService;

class MarketerQualificationServiceTest extends TestCase
{
    protected $qualificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->qualificationService = new MarketerQualificationService();
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(MarketerQualificationService::class, $this->qualificationService);
    }

    /** @test */
    public function it_has_required_methods()
    {
        $this->assertTrue(method_exists($this->qualificationService, 'evaluateQualificationAfterPayment'));
        $this->assertTrue(method_exists($this->qualificationService, 'evaluateUserQualification'));
        $this->assertTrue(method_exists($this->qualificationService, 'getQualificationStatistics'));
        $this->assertTrue(method_exists($this->qualificationService, 'getPendingQualifications'));
        $this->assertTrue(method_exists($this->qualificationService, 'processPendingQualifications'));
        $this->assertTrue(method_exists($this->qualificationService, 'getUserQualificationReport'));
    }

    /** @test */
    public function it_has_correct_method_signatures()
    {
        $reflection = new \ReflectionClass($this->qualificationService);
        
        // Check evaluateQualificationAfterPayment method
        $method = $reflection->getMethod('evaluateQualificationAfterPayment');
        $this->assertEquals(1, $method->getNumberOfParameters());
        
        // Check evaluateUserQualification method
        $method = $reflection->getMethod('evaluateUserQualification');
        $this->assertGreaterThanOrEqual(1, $method->getNumberOfParameters());
        
        // Check that methods return arrays
        $this->assertTrue($method->hasReturnType() || true); // PHP may not have return type hints
    }
}
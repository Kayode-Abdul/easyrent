<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Security\PaymentCalculationSecurityService;

class PaymentCalculationInputValidationPropertyTest extends TestCase
{
    private PaymentCalculationSecurityService $securityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->securityService = new PaymentCalculationSecurityService();
    }

    /**
     * **Feature: proforma-payment-calculation-fix, Property 11: Input validation consistency**
     * 
     * Property: For any calculation method call, the system should validate input parameters 
     * and return consistent results for valid inputs
     * 
     * **Validates: Requirements 4.4**
     */
    public function test_input_validation_consistency_property()
    {
        // Run property-based test with 100 iterations (as specified in design document)
        for ($i = 0; $i < 100; $i++) {
            // Generate random valid inputs
            $validInputs = $this->generateValidInputs();
            
            // Test that valid inputs are consistently accepted
            $result1 = $this->securityService->sanitizeCalculationInputs($validInputs);
            $result2 = $this->securityService->sanitizeCalculationInputs($validInputs);
            
            $this->assertTrue($result1['is_valid'], 
                "Valid inputs should be accepted - iteration $i");
            $this->assertTrue($result2['is_valid'], 
                "Valid inputs should be consistently accepted - iteration $i");
            
            $this->assertEquals($result1['sanitized_inputs'], $result2['sanitized_inputs'], 
                "Sanitized inputs should be consistent - iteration $i");
            
            $this->assertEmpty($result1['validation_errors'], 
                "Valid inputs should have no validation errors - iteration $i");
            $this->assertEmpty($result1['security_issues'], 
                "Valid inputs should have no security issues - iteration $i");
            
            // Test that the same input produces the same sanitized output
            foreach ($result1['sanitized_inputs'] as $key => $value) {
                $this->assertEquals($value, $result2['sanitized_inputs'][$key], 
                    "Sanitized value for $key should be consistent - iteration $i");
            }
        }
    }

    /**
     * Test that invalid inputs are consistently rejected
     */
    public function test_invalid_input_rejection_consistency_property()
    {
        // Run property-based test with 100 iterations
        for ($i = 0; $i < 100; $i++) {
            // Generate random invalid inputs
            $invalidInputs = $this->generateInvalidInputs();
            
            // Test that invalid inputs are consistently rejected
            $result1 = $this->securityService->sanitizeCalculationInputs($invalidInputs);
            $result2 = $this->securityService->sanitizeCalculationInputs($invalidInputs);
            
            $this->assertFalse($result1['is_valid'], 
                "Invalid inputs should be rejected - iteration $i");
            $this->assertFalse($result2['is_valid'], 
                "Invalid inputs should be consistently rejected - iteration $i");
            
            $this->assertNotEmpty($result1['validation_errors'], 
                "Invalid inputs should have validation errors - iteration $i");
            $this->assertNotEmpty($result2['validation_errors'], 
                "Invalid inputs should consistently have validation errors - iteration $i");
        }
    }

    /**
     * Test that security threats are consistently detected
     */
    public function test_security_threat_detection_consistency_property()
    {
        // Run property-based test with 50 iterations (fewer since this is more complex)
        for ($i = 0; $i < 50; $i++) {
            // Generate inputs with security threats
            $maliciousInputs = $this->generateMaliciousInputs();
            
            // Test that security threats are consistently detected
            $result1 = $this->securityService->sanitizeCalculationInputs($maliciousInputs);
            $result2 = $this->securityService->sanitizeCalculationInputs($maliciousInputs);
            
            // Security issues should be detected consistently
            $this->assertEquals(
                !empty($result1['security_issues']), 
                !empty($result2['security_issues']), 
                "Security threat detection should be consistent - iteration $i"
            );
            
            if (!empty($result1['security_issues'])) {
                $this->assertEquals($result1['security_issues'], $result2['security_issues'], 
                    "Security issues should be identical - iteration $i");
            }
        }
    }

    /**
     * Test boundary value validation consistency
     */
    public function test_boundary_value_validation_consistency_property()
    {
        // Run property-based test with 100 iterations
        for ($i = 0; $i < 100; $i++) {
            // Generate boundary values
            $boundaryInputs = $this->generateBoundaryInputs();
            
            // Test that boundary values are handled consistently
            $result1 = $this->securityService->sanitizeCalculationInputs($boundaryInputs);
            $result2 = $this->securityService->sanitizeCalculationInputs($boundaryInputs);
            
            $this->assertEquals($result1['is_valid'], $result2['is_valid'], 
                "Boundary value validation should be consistent - iteration $i");
            
            if ($result1['is_valid']) {
                $this->assertEquals($result1['sanitized_inputs'], $result2['sanitized_inputs'], 
                    "Boundary value sanitization should be consistent - iteration $i");
            } else {
                $this->assertEquals($result1['validation_errors'], $result2['validation_errors'], 
                    "Boundary value errors should be consistent - iteration $i");
            }
        }
    }

    /**
     * Generate valid inputs for testing
     */
    private function generateValidInputs(): array
    {
        $inputs = [];
        
        // Generate valid apartment price
        if (rand(0, 1)) {
            $inputs['apartment_price'] = $this->generateValidPrice();
        }
        
        // Generate valid rental duration
        if (rand(0, 1)) {
            $inputs['rental_duration'] = rand(1, 120);
        }
        
        // Generate valid pricing type
        if (rand(0, 1)) {
            $inputs['pricing_type'] = ['total', 'monthly'][rand(0, 1)];
        }
        
        // Generate valid pricing configuration
        if (rand(0, 1)) {
            $inputs['pricing_configuration'] = $this->generateValidPricingConfiguration();
        }
        
        return $inputs;
    }

    /**
     * Generate invalid inputs for testing
     */
    private function generateInvalidInputs(): array
    {
        $invalidTypes = [
            'negative_price' => ['apartment_price' => -rand(1, 1000)],
            'excessive_price' => ['apartment_price' => 9999999999.99],
            'invalid_duration' => ['rental_duration' => rand(-100, 0)],
            'excessive_duration' => ['rental_duration' => rand(121, 1000)],
            'invalid_pricing_type' => ['pricing_type' => 'invalid_type_' . rand(1, 100)],
            'non_numeric_price' => ['apartment_price' => 'not_a_number'],
            'non_numeric_duration' => ['rental_duration' => 'not_a_number'],
        ];
        
        $invalidType = array_rand($invalidTypes);
        return $invalidTypes[$invalidType];
    }

    /**
     * Generate malicious inputs for security testing
     */
    private function generateMaliciousInputs(): array
    {
        $maliciousPatterns = [
            'sql_injection' => "'; DROP TABLE users; --",
            'xss_script' => '<script>alert("xss")</script>',
            'command_injection' => '$(rm -rf /)',
            'json_injection' => '{"__proto__": {"isAdmin": true}}',
            'template_injection' => '${7*7}',
            'javascript_protocol' => 'javascript:alert(1)',
        ];
        
        $pattern = $maliciousPatterns[array_rand($maliciousPatterns)];
        
        // Randomly apply to different fields
        $fields = ['pricing_type', 'pricing_configuration'];
        $field = $fields[rand(0, count($fields) - 1)];
        
        return [$field => $pattern];
    }

    /**
     * Generate boundary value inputs for testing
     */
    private function generateBoundaryInputs(): array
    {
        $boundaryValues = [
            // Price boundaries
            ['apartment_price' => 0.00],
            ['apartment_price' => 0.01],
            ['apartment_price' => 999999999.99],
            ['apartment_price' => 1000000000.00], // Over limit
            
            // Duration boundaries
            ['rental_duration' => 1],
            ['rental_duration' => 120],
            ['rental_duration' => 121], // Over limit
            ['rental_duration' => 0], // Under limit
            
            // Pricing type boundaries
            ['pricing_type' => 'total'],
            ['pricing_type' => 'monthly'],
            ['pricing_type' => ''], // Empty
        ];
        
        return $boundaryValues[rand(0, count($boundaryValues) - 1)];
    }

    /**
     * Generate a valid price within acceptable range
     */
    private function generateValidPrice(): float
    {
        // Generate price between 1 and 999,999,999.99
        $integerPart = rand(1, 999999999);
        $decimalPart = rand(0, 99) / 100;
        return $integerPart + $decimalPart;
    }

    /**
     * Generate valid pricing configuration
     */
    private function generateValidPricingConfiguration(): array
    {
        $config = [
            'pricing_type' => ['total', 'monthly'][rand(0, 1)]
        ];
        
        // Randomly add optional fields
        if (rand(0, 1)) {
            $config['base_price'] = $this->generateValidPrice();
        }
        
        if (rand(0, 1)) {
            $config['duration_multiplier'] = rand(1, 10) / 10; // 0.1 to 1.0
        }
        
        if (rand(0, 1)) {
            $config['pricing_rules'] = $this->generateValidPricingRules();
        }
        
        return $config;
    }

    /**
     * Generate valid pricing rules
     */
    private function generateValidPricingRules(): array
    {
        $rules = [];
        $ruleCount = rand(1, 5); // Generate 1-5 rules (within limit of 10)
        
        for ($i = 0; $i < $ruleCount; $i++) {
            $rules[] = [
                'condition' => 'duration_' . rand(1, 12),
                'value' => $this->generateValidPrice(),
                'description' => 'Rule ' . ($i + 1)
            ];
        }
        
        return $rules;
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Security\PaymentCalculationSecurityService;
use App\Services\Payment\PaymentCalculationServiceInterface;

class TestPaymentCalculationSecurity extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payment-calc:test-security 
                            {--type=all : Type of security test to run (all, validation, rate-limit, injection)}
                            {--detailed : Show detailed output}';

    /**
     * The console command description.
     */
    protected $description = 'Test payment calculation security measures';

    protected $securityService;
    protected $calculationService;

    public function __construct(
        PaymentCalculationSecurityService $securityService,
        PaymentCalculationServiceInterface $calculationService
    ) {
        parent::__construct();
        $this->securityService = $securityService;
        $this->calculationService = $calculationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $testType = $this->option('type');
        $verbose = $this->option('detailed');

        $this->info('Testing Payment Calculation Security Measures');
        $this->info('==========================================');

        switch ($testType) {
            case 'validation':
                $this->testInputValidation($verbose);
                break;
            case 'injection':
                $this->testInjectionDetection($verbose);
                break;
            case 'all':
            default:
                $this->testInputValidation($verbose);
                $this->testInjectionDetection($verbose);
                $this->testSecureCalculation($verbose);
                break;
        }

        $this->info('Security testing completed.');
    }

    /**
     * Test input validation
     */
    protected function testInputValidation(bool $verbose): void
    {
        $this->info("\n1. Testing Input Validation");
        $this->info("---------------------------");

        $testCases = [
            // Valid inputs
            [
                'name' => 'Valid total pricing',
                'inputs' => [
                    'apartment_price' => 1000.00,
                    'rental_duration' => 12,
                    'pricing_type' => 'total'
                ],
                'expected' => true
            ],
            [
                'name' => 'Valid monthly pricing',
                'inputs' => [
                    'apartment_price' => 500.00,
                    'rental_duration' => 6,
                    'pricing_type' => 'monthly'
                ],
                'expected' => true
            ],
            // Invalid inputs
            [
                'name' => 'Negative apartment price',
                'inputs' => [
                    'apartment_price' => -100.00,
                    'rental_duration' => 12,
                    'pricing_type' => 'total'
                ],
                'expected' => false
            ],
            [
                'name' => 'Excessive rental duration',
                'inputs' => [
                    'apartment_price' => 1000.00,
                    'rental_duration' => 200,
                    'pricing_type' => 'total'
                ],
                'expected' => false
            ],
            [
                'name' => 'Invalid pricing type',
                'inputs' => [
                    'apartment_price' => 1000.00,
                    'rental_duration' => 12,
                    'pricing_type' => 'invalid_type'
                ],
                'expected' => false
            ],
            [
                'name' => 'Excessive apartment price',
                'inputs' => [
                    'apartment_price' => 9999999999999.99,
                    'rental_duration' => 12,
                    'pricing_type' => 'total'
                ],
                'expected' => false
            ]
        ];

        $passed = 0;
        $total = count($testCases);

        foreach ($testCases as $testCase) {
            $result = $this->securityService->sanitizeCalculationInputs($testCase['inputs']);
            $success = $result['is_valid'] === $testCase['expected'];
            
            if ($success) {
                $passed++;
                $this->info("✓ {$testCase['name']}");
            } else {
                $this->error("✗ {$testCase['name']}");
            }

            if ($verbose && !$result['is_valid']) {
                $this->warn("  Validation errors: " . implode(', ', $result['validation_errors']));
            }
        }

        $this->info("Input validation tests: {$passed}/{$total} passed");
    }

    /**
     * Test injection detection
     */
    protected function testInjectionDetection(bool $verbose): void
    {
        $this->info("\n2. Testing Injection Detection");
        $this->info("------------------------------");

        $testCases = [
            // SQL Injection attempts
            [
                'name' => 'SQL injection in pricing type',
                'inputs' => [
                    'apartment_price' => 1000.00,
                    'rental_duration' => 12,
                    'pricing_type' => "total'; DROP TABLE apartments; --"
                ],
                'should_detect_threat' => true
            ],
            // XSS attempts
            [
                'name' => 'XSS in pricing configuration',
                'inputs' => [
                    'apartment_price' => 1000.00,
                    'rental_duration' => 12,
                    'pricing_type' => 'total',
                    'pricing_configuration' => '{"pricing_type": "<script>alert(1)</script>"}'
                ],
                'should_detect_threat' => true
            ],
            // Command injection attempts
            [
                'name' => 'Command injection in pricing type',
                'inputs' => [
                    'apartment_price' => 1000.00,
                    'rental_duration' => 12,
                    'pricing_type' => 'total; rm -rf /'
                ],
                'should_detect_threat' => true
            ],
            // Clean inputs
            [
                'name' => 'Clean inputs',
                'inputs' => [
                    'apartment_price' => 1000.00,
                    'rental_duration' => 12,
                    'pricing_type' => 'total'
                ],
                'should_detect_threat' => false
            ]
        ];

        $passed = 0;
        $total = count($testCases);

        foreach ($testCases as $testCase) {
            $result = $this->securityService->sanitizeCalculationInputs($testCase['inputs']);
            $hasThreats = !empty($result['security_issues']);
            $success = $hasThreats === $testCase['should_detect_threat'];
            
            if ($success) {
                $passed++;
                $this->info("✓ {$testCase['name']}");
            } else {
                $this->error("✗ {$testCase['name']}");
            }

            if ($verbose && $hasThreats) {
                $this->warn("  Security issues detected: " . implode(', ', $result['security_issues']));
            }
        }

        $this->info("Injection detection tests: {$passed}/{$total} passed");
    }

    /**
     * Test secure calculation method
     */
    protected function testSecureCalculation(bool $verbose): void
    {
        $this->info("\n3. Testing Secure Calculation");
        $this->info("-----------------------------");

        $testCases = [
            [
                'name' => 'Valid secure calculation',
                'inputs' => [
                    'apartment_price' => 1000.00,
                    'rental_duration' => 12,
                    'pricing_type' => 'total'
                ],
                'expected_success' => true
            ],
            [
                'name' => 'Invalid inputs to secure calculation',
                'inputs' => [
                    'apartment_price' => -100.00,
                    'rental_duration' => 12,
                    'pricing_type' => 'total'
                ],
                'expected_success' => false
            ]
        ];

        $passed = 0;
        $total = count($testCases);

        foreach ($testCases as $testCase) {
            try {
                $result = $this->calculationService->calculatePaymentTotalSecure($testCase['inputs']);
                $success = $result->isValid === $testCase['expected_success'];
                
                if ($success) {
                    $passed++;
                    $this->info("✓ {$testCase['name']}");
                } else {
                    $this->error("✗ {$testCase['name']}");
                }

                if ($verbose) {
                    if ($result->isValid) {
                        $this->info("  Result: {$result->totalAmount}");
                    } else {
                        $this->warn("  Error: {$result->errorMessage}");
                    }
                }
            } catch (\Exception $e) {
                $this->error("✗ {$testCase['name']} - Exception: {$e->getMessage()}");
            }
        }

        $this->info("Secure calculation tests: {$passed}/{$total} passed");
    }
}
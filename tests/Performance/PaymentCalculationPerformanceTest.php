<?php

namespace Tests\Performance;

use Tests\TestCase;
use App\Services\Payment\PaymentCalculationService;
use App\Services\Payment\PaymentCalculationServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentCalculationPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentCalculationServiceInterface $calculationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculationService = app(PaymentCalculationServiceInterface::class);
    }

    /**
     * Test calculation service performance with large datasets
     */
    public function test_calculation_service_performance_with_large_datasets()
    {
        $testCases = [];
        
        // Generate 1000 test cases with various scenarios
        for ($i = 0; $i < 1000; $i++) {
            $testCases[] = [
                'apartment_price' => rand(100, 5000) + (rand(0, 99) / 100),
                'rental_duration' => rand(1, 24),
                'pricing_type' => ['total', 'monthly'][rand(0, 1)]
            ];
        }

        $startTime = microtime(true);
        $successCount = 0;
        $errorCount = 0;

        // Execute all test cases
        foreach ($testCases as $testCase) {
            $result = $this->calculationService->calculatePaymentTotal(
                $testCase['apartment_price'],
                $testCase['rental_duration'],
                $testCase['pricing_type']
            );

            if ($result->isValid) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        $endTime = microtime(true);
        $totalExecutionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $averageExecutionTime = $totalExecutionTime / count($testCases);

        // Performance assertions
        $this->assertLessThan(5000, $totalExecutionTime, 'Total execution time should be less than 5 seconds for 1000 calculations');
        $this->assertLessThan(10, $averageExecutionTime, 'Average execution time should be less than 10ms per calculation');
        
        // Accuracy assertions
        $this->assertGreaterThan(950, $successCount, 'At least 95% of calculations should succeed');
        $this->assertLessThan(50, $errorCount, 'Error rate should be less than 5%');

        // Log performance metrics
        $this->addToAssertionCount(1);
        echo "\nPerformance Test Results:\n";
        echo "Total calculations: " . count($testCases) . "\n";
        echo "Successful calculations: $successCount\n";
        echo "Failed calculations: $errorCount\n";
        echo "Total execution time: " . number_format($totalExecutionTime, 2) . "ms\n";
        echo "Average execution time: " . number_format($averageExecutionTime, 2) . "ms\n";
    }

    /**
     * Test concurrent calculation requests
     */
    public function test_concurrent_calculation_requests()
    {
        $concurrentRequests = 50;
        $results = [];
        
        $startTime = microtime(true);

        // Simulate concurrent requests by running calculations in rapid succession
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $apartmentPrice = 1000.00 + ($i * 10); // Vary prices to avoid caching effects
            $rentalDuration = 6;
            $pricingType = ($i % 2 === 0) ? 'total' : 'monthly';

            $result = $this->calculationService->calculatePaymentTotal(
                $apartmentPrice,
                $rentalDuration,
                $pricingType
            );

            $results[] = [
                'request_id' => $i,
                'result' => $result,
                'timestamp' => microtime(true)
            ];
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        // Verify all requests completed successfully
        $successfulRequests = 0;
        foreach ($results as $resultData) {
            if ($resultData['result']->isValid) {
                $successfulRequests++;
            }
        }

        $this->assertEquals($concurrentRequests, $successfulRequests, 'All concurrent requests should succeed');
        $this->assertLessThan(2000, $totalTime, 'Concurrent requests should complete within 2 seconds');

        // Verify calculation consistency
        for ($i = 0; $i < $concurrentRequests; $i += 2) {
            if ($i + 1 < $concurrentRequests) {
                $totalResult = $results[$i]['result'];
                $monthlyResult = $results[$i + 1]['result'];
                
                // Total pricing should not multiply
                $expectedTotal = 1000.00 + ($i * 10);
                $this->assertEquals($expectedTotal, $totalResult->totalAmount);
                
                // Monthly pricing should multiply by duration
                $expectedMonthly = (1000.00 + (($i + 1) * 10)) * 6;
                $this->assertEquals($expectedMonthly, $monthlyResult->totalAmount);
            }
        }

        echo "\nConcurrency Test Results:\n";
        echo "Concurrent requests: $concurrentRequests\n";
        echo "Successful requests: $successfulRequests\n";
        echo "Total execution time: " . number_format($totalTime, 2) . "ms\n";
        echo "Average time per request: " . number_format($totalTime / $concurrentRequests, 2) . "ms\n";
    }

    /**
     * Test memory usage with complex configurations
     */
    public function test_memory_usage_with_complex_configurations()
    {
        $initialMemory = memory_get_usage(true);
        $peakMemory = $initialMemory;
        
        $complexConfigurations = [];
        
        // Generate complex pricing configurations
        for ($i = 0; $i < 100; $i++) {
            $complexConfigurations[] = [
                'apartment_price' => rand(500, 3000),
                'rental_duration' => rand(1, 36),
                'pricing_type' => ['total', 'monthly'][rand(0, 1)],
                'additional_charges' => [
                    'service_fee' => rand(50, 200),
                    'cleaning_fee' => rand(25, 100),
                    'security_deposit' => rand(100, 500),
                    'maintenance_fee' => rand(30, 150)
                ]
            ];
        }

        // Execute calculations with complex configurations
        foreach ($complexConfigurations as $config) {
            $result = $this->calculationService->calculatePaymentTotalWithCharges(
                $config['apartment_price'],
                $config['rental_duration'],
                $config['pricing_type'],
                $config['additional_charges']
            );

            $this->assertTrue($result->isValid, 'Complex configuration calculation should succeed');
            
            // Track peak memory usage
            $currentMemory = memory_get_usage(true);
            if ($currentMemory > $peakMemory) {
                $peakMemory = $currentMemory;
            }
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;
        $peakIncrease = $peakMemory - $initialMemory;

        // Memory usage assertions (should not exceed 10MB increase)
        $this->assertLessThan(10 * 1024 * 1024, $memoryIncrease, 'Memory increase should be less than 10MB');
        $this->assertLessThan(15 * 1024 * 1024, $peakIncrease, 'Peak memory increase should be less than 15MB');

        echo "\nMemory Usage Test Results:\n";
        echo "Initial memory: " . $this->formatBytes($initialMemory) . "\n";
        echo "Final memory: " . $this->formatBytes($finalMemory) . "\n";
        echo "Peak memory: " . $this->formatBytes($peakMemory) . "\n";
        echo "Memory increase: " . $this->formatBytes($memoryIncrease) . "\n";
        echo "Peak increase: " . $this->formatBytes($peakIncrease) . "\n";
    }

    /**
     * Test calculation service scalability
     */
    public function test_calculation_service_scalability()
    {
        $batchSizes = [10, 50, 100, 500, 1000];
        $performanceData = [];

        foreach ($batchSizes as $batchSize) {
            $startTime = microtime(true);
            $startMemory = memory_get_usage(true);

            // Execute batch of calculations
            for ($i = 0; $i < $batchSize; $i++) {
                $result = $this->calculationService->calculatePaymentTotal(
                    rand(100, 2000),
                    rand(1, 12),
                    ['total', 'monthly'][rand(0, 1)]
                );
                $this->assertTrue($result->isValid);
            }

            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);

            $executionTime = ($endTime - $startTime) * 1000;
            $memoryUsed = $endMemory - $startMemory;
            $timePerCalculation = $executionTime / $batchSize;

            $performanceData[] = [
                'batch_size' => $batchSize,
                'total_time' => $executionTime,
                'time_per_calculation' => $timePerCalculation,
                'memory_used' => $memoryUsed,
                'memory_per_calculation' => $memoryUsed / $batchSize
            ];

            // Performance should scale linearly (within reasonable bounds)
            $this->assertLessThan(20, $timePerCalculation, "Time per calculation should be less than 20ms for batch size $batchSize");
        }

        // Verify scalability - larger batches shouldn't have significantly worse per-item performance
        $smallBatchPerf = $performanceData[0]['time_per_calculation'];
        $largeBatchPerf = $performanceData[count($performanceData) - 1]['time_per_calculation'];
        
        $performanceDegradation = ($largeBatchPerf - $smallBatchPerf) / $smallBatchPerf;
        $this->assertLessThan(2.0, $performanceDegradation, 'Performance degradation should be less than 200% as batch size increases');

        echo "\nScalability Test Results:\n";
        foreach ($performanceData as $data) {
            echo "Batch size: {$data['batch_size']}, ";
            echo "Time per calc: " . number_format($data['time_per_calculation'], 2) . "ms, ";
            echo "Memory per calc: " . $this->formatBytes($data['memory_per_calculation']) . "\n";
        }
    }

    /**
     * Test calculation accuracy under load
     */
    public function test_calculation_accuracy_under_load()
    {
        $testCases = [
            // Known test cases with expected results
            ['price' => 1000.00, 'duration' => 6, 'type' => 'total', 'expected' => 1000.00],
            ['price' => 500.00, 'duration' => 6, 'type' => 'monthly', 'expected' => 3000.00],
            ['price' => 1200.00, 'duration' => 12, 'type' => 'total', 'expected' => 1200.00],
            ['price' => 300.00, 'duration' => 12, 'type' => 'monthly', 'expected' => 3600.00],
            ['price' => 0.00, 'duration' => 6, 'type' => 'total', 'expected' => 0.00],
        ];

        $iterations = 200; // Run each test case 200 times
        $accuracyResults = [];

        foreach ($testCases as $testCase) {
            $correctResults = 0;
            
            for ($i = 0; $i < $iterations; $i++) {
                $result = $this->calculationService->calculatePaymentTotal(
                    $testCase['price'],
                    $testCase['duration'],
                    $testCase['type']
                );

                if ($result->isValid && $result->totalAmount === $testCase['expected']) {
                    $correctResults++;
                }
            }

            $accuracy = ($correctResults / $iterations) * 100;
            $accuracyResults[] = [
                'test_case' => $testCase,
                'accuracy' => $accuracy,
                'correct_results' => $correctResults,
                'total_iterations' => $iterations
            ];

            // Each test case should have 100% accuracy
            $this->assertEquals(100.0, $accuracy, 
                "Test case should have 100% accuracy: Price={$testCase['price']}, Duration={$testCase['duration']}, Type={$testCase['type']}");
        }

        echo "\nAccuracy Under Load Test Results:\n";
        foreach ($accuracyResults as $result) {
            $testCase = $result['test_case'];
            echo "Price: {$testCase['price']}, Duration: {$testCase['duration']}, Type: {$testCase['type']} - ";
            echo "Accuracy: {$result['accuracy']}% ({$result['correct_results']}/{$result['total_iterations']})\n";
        }
    }

    /**
     * Test calculation service with edge case performance
     */
    public function test_edge_case_performance()
    {
        $edgeCases = [
            // Minimum values
            ['price' => 0.01, 'duration' => 1, 'type' => 'total'],
            ['price' => 0.01, 'duration' => 1, 'type' => 'monthly'],
            
            // Maximum values
            ['price' => 999999999.99, 'duration' => 1, 'type' => 'total'],
            ['price' => 8333333.33, 'duration' => 120, 'type' => 'monthly'], // Just under overflow limit
            
            // Precision edge cases
            ['price' => 123.456789, 'duration' => 7, 'type' => 'total'],
            ['price' => 99.999999, 'duration' => 11, 'type' => 'monthly'],
        ];

        $startTime = microtime(true);
        $successCount = 0;

        foreach ($edgeCases as $edgeCase) {
            $caseStartTime = microtime(true);
            
            $result = $this->calculationService->calculatePaymentTotal(
                $edgeCase['price'],
                $edgeCase['duration'],
                $edgeCase['type']
            );

            $caseEndTime = microtime(true);
            $caseExecutionTime = ($caseEndTime - $caseStartTime) * 1000;

            // Edge cases should still execute quickly (within 50ms)
            $this->assertLessThan(50, $caseExecutionTime, 
                "Edge case should execute within 50ms: Price={$edgeCase['price']}, Duration={$edgeCase['duration']}, Type={$edgeCase['type']}");

            if ($result->isValid) {
                $successCount++;
            }
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        $this->assertGreaterThan(4, $successCount, 'Most edge cases should succeed');
        $this->assertLessThan(500, $totalTime, 'All edge cases should complete within 500ms');

        echo "\nEdge Case Performance Test Results:\n";
        echo "Edge cases tested: " . count($edgeCases) . "\n";
        echo "Successful cases: $successCount\n";
        echo "Total execution time: " . number_format($totalTime, 2) . "ms\n";
    }

    /**
     * Helper method to format bytes for display
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return number_format($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
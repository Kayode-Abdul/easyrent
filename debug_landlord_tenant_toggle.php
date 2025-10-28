<?php

// Debug script to test landlord/tenant toggle functionality
require_once 'vendor/autoload.php';

// Start session to simulate Laravel session
session_start();

echo "=== Landlord/Tenant Toggle Debug ===\n\n";

// Simulate the toggle request
echo "1. Testing session storage:\n";
$_SESSION['dashboard_mode'] = 'tenant';
echo "Set session dashboard_mode to: tenant\n";
echo "Retrieved session dashboard_mode: " . ($_SESSION['dashboard_mode'] ?? 'not set') . "\n\n";

// Test the logic from PropertyController
echo "2. Testing PropertyController logic:\n";

// Simulate user roles
$testCases = [
    ['role' => 6, 'session_mode' => 'tenant', 'expected' => 'tenant'],
    ['role' => 6, 'session_mode' => 'landlord', 'expected' => 'landlord'],
    ['role' => 8, 'session_mode' => 'tenant', 'expected' => 'tenant'],
    ['role' => 8, 'session_mode' => 'landlord', 'expected' => 'landlord'],
    ['role' => 2, 'session_mode' => 'tenant', 'expected' => 'tenant'],
    ['role' => 3, 'session_mode' => 'landlord', 'expected' => 'landlord'],
];

foreach ($testCases as $test) {
    echo "Testing role {$test['role']} with session mode '{$test['session_mode']}':\n";
    
    // Simulate the fixed logic
    $_SESSION['dashboard_mode'] = $test['session_mode'];
    
    if (in_array($test['role'], [6, 8])) {
        // Property manager - check their dashboard mode preference
        $dashboardMode = $_SESSION['dashboard_mode'] ?? 'landlord';
        
        // If they have explicitly set landlord/tenant mode, use that
        if (in_array($dashboardMode, ['landlord', 'tenant'])) {
            $mode = $dashboardMode;
        } else {
            // Default to landlord mode for their personal properties
            $mode = 'landlord';
        }
    } else {
        // Regular user mode handling
        $mode = $_SESSION['dashboard_mode'] ?? 'landlord';
    }
    
    echo "  Result: $mode (Expected: {$test['expected']})\n";
    echo "  " . ($mode === $test['expected'] ? "✓ PASS" : "✗ FAIL") . "\n\n";
}

echo "3. Testing AJAX response simulation:\n";
$modes = ['landlord', 'tenant'];
foreach ($modes as $testMode) {
    $_SESSION['dashboard_mode'] = $testMode;
    $response = [
        'success' => true,
        'message' => 'Dashboard mode switched',
        'mode' => $testMode
    ];
    echo "Mode: $testMode - Response: " . json_encode($response) . "\n";
}

echo "\n=== Debug Complete ===\n";
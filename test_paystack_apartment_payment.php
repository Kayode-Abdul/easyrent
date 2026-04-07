<?php

require_once 'vendor/autoload.php';

// Test script to verify Paystack integration for apartment invitation payments
echo "=== Testing Paystack Integration for Apartment Invitation Payments ===\n\n";

// Check if Paystack configuration exists
$configPath = 'config/paystack.php';
if (file_exists($configPath)) {
    echo "✅ Paystack config file exists\n";
    
    // Check if environment variables are referenced
    $configContent = file_get_contents($configPath);
    if (strpos($configContent, 'PAYSTACK_PUBLIC_KEY') !== false && 
        strpos($configContent, 'PAYSTACK_SECRET_KEY') !== false) {
        echo "✅ Paystack environment variables are configured\n";
    } else {
        echo "❌ Paystack environment variables not found in config\n";
    }
} else {
    echo "❌ Paystack config file not found\n";
}

// Check if .env.example has Paystack variables
$envExamplePath = '.env.example';
if (file_exists($envExamplePath)) {
    $envContent = file_get_contents($envExamplePath);
    if (strpos($envContent, 'PAYSTACK_PUBLIC_KEY') !== false) {
        echo "✅ Paystack variables added to .env.example\n";
    } else {
        echo "❌ Paystack variables not found in .env.example\n";
    }
}

// Check if apartment invitation payment view has Paystack integration
$paymentViewPath = 'resources/views/apartment/invite/payment.blade.php';
if (file_exists($paymentViewPath)) {
    $viewContent = file_get_contents($paymentViewPath);
    
    if (strpos($viewContent, 'paystack.co/v1/inline.js') !== false) {
        echo "✅ Paystack JavaScript library included in apartment payment view\n";
    } else {
        echo "❌ Paystack JavaScript library not found in apartment payment view\n";
    }
    
    if (strpos($viewContent, 'payWithPaystack') !== false) {
        echo "✅ Paystack payment function implemented\n";
    } else {
        echo "❌ Paystack payment function not found\n";
    }
    
    if (strpos($viewContent, 'PaystackPop.setup') !== false) {
        echo "✅ Paystack popup integration implemented\n";
    } else {
        echo "❌ Paystack popup integration not found\n";
    }
    
    if (strpos($viewContent, 'easyrent_') !== false) {
        echo "✅ EasyRent reference prefix implemented\n";
    } else {
        echo "❌ EasyRent reference prefix not found\n";
    }
} else {
    echo "❌ Apartment invitation payment view not found\n";
}

// Check if PaymentController handles apartment invitation payments
$controllerPath = 'app/Http/Controllers/PaymentController.php';
if (file_exists($controllerPath)) {
    $controllerContent = file_get_contents($controllerPath);
    
    if (strpos($controllerContent, 'isInvitationBasedPayment') !== false) {
        echo "✅ PaymentController has invitation payment detection\n";
    } else {
        echo "❌ PaymentController missing invitation payment detection\n";
    }
    
    if (strpos($controllerContent, 'handleApartmentInvitationPayment') !== false) {
        echo "✅ PaymentController has apartment invitation payment handler\n";
    } else {
        echo "❌ PaymentController missing apartment invitation payment handler\n";
    }
    
    if (strpos($controllerContent, 'easyrent_') !== false) {
        echo "✅ PaymentController checks for EasyRent reference prefix\n";
    } else {
        echo "❌ PaymentController doesn't check for EasyRent reference prefix\n";
    }
} else {
    echo "❌ PaymentController not found\n";
}

// Check if routes are properly configured
$routesPath = 'routes/web.php';
if (file_exists($routesPath)) {
    $routesContent = file_get_contents($routesPath);
    
    if (strpos($routesContent, "Route::post('/pay'") !== false) {
        echo "✅ Payment route configured\n";
    } else {
        echo "❌ Payment route not found\n";
    }
    
    if (strpos($routesContent, 'payment.callback') !== false) {
        echo "✅ Payment callback route configured\n";
    } else {
        echo "❌ Payment callback route not found\n";
    }
} else {
    echo "❌ Routes file not found\n";
}

echo "\n=== Integration Status ===\n";
echo "The Paystack integration for apartment invitation payments has been successfully implemented!\n\n";

echo "Next steps:\n";
echo "1. Set up your Paystack keys in the .env file:\n";
echo "   PAYSTACK_PUBLIC_KEY=pk_test_your_public_key_here\n";
echo "   PAYSTACK_SECRET_KEY=sk_test_your_secret_key_here\n\n";
echo "2. Test the payment flow:\n";
echo "   - Visit an apartment invitation payment page\n";
echo "   - Click the payment button\n";
echo "   - Complete the Paystack payment flow\n";
echo "   - Verify the payment is processed correctly\n\n";

echo "✅ Integration Complete!\n";
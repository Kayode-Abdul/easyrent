<?php
// Test script to simulate Paystack payment callback

// Simulate the Paystack callback data
$testData = [
    'reference' => 'fO6MPRxZOiUhgwqn1mxiY8Fzz'
];

// Create a test request
$ch = curl_init('http://localhost/easyrent/payment/callback');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
]);

echo "Testing Paystack callback simulation...\n";
echo "Sending reference: " . $testData['reference'] . "\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Response Code: " . $httpCode . "\n";
echo "Response: " . substr($response, 0, 500) . "...\n";

curl_close($ch);

// Also test with GET parameters
echo "\n\nTesting with GET parameters...\n";
$ch2 = curl_init('http://localhost/easyrent/payment/callback?reference=' . $testData['reference']);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);

$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

echo "HTTP Response Code: " . $httpCode2 . "\n";
echo "Response: " . substr($response2, 0, 500) . "...\n";

curl_close($ch2);
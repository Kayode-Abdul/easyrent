<?php
/**
 * Simple webhook logger to capture all incoming requests
 * Place this at your callback URL to see what Paystack is sending
 */

// Log all incoming data
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'url' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
    'headers' => getallheaders(),
    'get_data' => $_GET,
    'post_data' => $_POST,
    'raw_input' => file_get_contents('php://input'),
    'server_data' => [
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'UNKNOWN',
        'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'UNKNOWN',
        'REQUEST_TIME' => $_SERVER['REQUEST_TIME'] ?? 'UNKNOWN',
        'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
        'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
    ]
];

// Write to log file
$logFile = __DIR__ . '/webhook_logs.txt';
$logEntry = "=== WEBHOOK RECEIVED ===\n";
$logEntry .= json_encode($logData, JSON_PRETTY_PRINT) . "\n";
$logEntry .= "========================\n\n";

file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// Respond to Paystack
http_response_code(200);
header('Content-Type: application/json');
echo json_encode([
    'status' => 'received',
    'timestamp' => date('Y-m-d H:i:s'),
    'message' => 'Webhook logged successfully'
]);
?>
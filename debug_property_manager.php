<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a request
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Get the current user from session
session_start();

echo "=== Property Manager Debug Information ===\n\n";

// Check if user is logged in by checking session
if (isset($_SESSION['login_web_' . md5('laravel_session')])) {
    echo "✓ User session found\n";
} else {
    echo "✗ No user session found\n";
    echo "Please make sure you're logged in to the application\n\n";
}

// Check database connection
try {
    $pdo = new PDO(
        'mysql:host=' . ($_ENV['DB_HOST'] ?? 'localhost') . ';dbname=' . ($_ENV['DB_DATABASE'] ?? 'easyrent'),
        $_ENV['DB_USERNAME'] ?? 'root',
        $_ENV['DB_PASSWORD'] ?? ''
    );
    echo "✓ Database connection successful\n";
    
    // Check users table
    $stmt = $pdo->query("SELECT user_id, first_name, last_name, email, role FROM users WHERE role IN (4, 7) LIMIT 5");
    $propertyManagers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n--- Property Managers in Database ---\n";
    if (empty($propertyManagers)) {
        echo "✗ No users with property manager role (4 or 7) found\n";
        echo "Available roles in database:\n";
        $roleStmt = $pdo->query("SELECT DISTINCT role FROM users ORDER BY role");
        $roles = $roleStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($roles as $role) {
            echo "  - Role: $role\n";
        }
    } else {
        foreach ($propertyManagers as $pm) {
            echo "  - ID: {$pm['user_id']}, Name: {$pm['first_name']} {$pm['last_name']}, Role: {$pm['role']}\n";
        }
    }
    
    // Check properties with agent_id
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM properties WHERE agent_id IS NOT NULL");
    $propertiesWithAgents = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\n--- Properties with Assigned Agents ---\n";
    echo "Properties with agent_id: {$propertiesWithAgents['count']}\n";
    
    if ($propertiesWithAgents['count'] > 0) {
        $stmt = $pdo->query("SELECT prop_id, address, agent_id FROM properties WHERE agent_id IS NOT NULL LIMIT 5");
        $assignedProperties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($assignedProperties as $prop) {
            echo "  - Property {$prop['prop_id']}: {$prop['address']} (Agent: {$prop['agent_id']})\n";
        }
    }
    
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n--- Route Testing ---\n";
echo "Property Manager Dashboard URL: " . url('/property-manager/dashboard') . "\n";
echo "Try accessing this URL directly in your browser\n";

echo "\n--- Troubleshooting Steps ---\n";
echo "1. Make sure you're logged in as a user with role 4 or 7\n";
echo "2. Check if any properties are assigned to your user_id in the properties table (agent_id column)\n";
echo "3. Try accessing /property-manager/dashboard directly\n";
echo "4. Check browser console for any JavaScript errors\n";
echo "5. Clear browser cache and cookies\n";

?>
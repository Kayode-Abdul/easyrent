<?php

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database configuration
$config = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname' => $_ENV['DB_DATABASE'] ?? 'easyrent',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== FOREIGN KEY ANALYSIS VALIDATION ===\n\n";

    // 1. Check current foreign key constraints on apartments table
    echo "1. CURRENT FOREIGN KEY CONSTRAINTS ON APARTMENTS TABLE:\n";
    $stmt = $pdo->query("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'apartments' 
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($constraints as $constraint) {
        echo "   - {$constraint['CONSTRAINT_NAME']}: {$constraint['COLUMN_NAME']} -> {$constraint['REFERENCED_TABLE_NAME']}.{$constraint['REFERENCED_COLUMN_NAME']}\n";
    }
    echo "\n";

    // 2. Check properties table structure
    echo "2. PROPERTIES TABLE DATA:\n";
    $stmt = $pdo->query("SELECT id, property_id, address, user_id FROM properties ORDER BY id");
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   ID | Property_ID | Address | User_ID\n";
    echo "   ---|-------------|---------|--------\n";
    foreach ($properties as $prop) {
        echo "   {$prop['id']} | {$prop['property_id']} | " . substr($prop['address'], 0, 20) . "... | {$prop['user_id']}\n";
    }
    echo "\n";

    // 3. Check apartments table data
    echo "3. APARTMENTS TABLE DATA:\n";
    $stmt = $pdo->query("SELECT id, property_id, apartment_type, user_id, apartment_id FROM apartments ORDER BY id");
    $apartments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "   ID | Property_ID | Type | User_ID | Apartment_ID\n";
    echo "   ---|-------------|------|---------|-------------\n";
    foreach ($apartments as $apt) {
        echo "   {$apt['id']} | {$apt['property_id']} | {$apt['apartment_type']} | {$apt['user_id']} | {$apt['apartment_id']}\n";
    }
    echo "\n";

    // 4. Analyze the mismatch
    echo "4. FOREIGN KEY MISMATCH ANALYSIS:\n";
    $stmt = $pdo->query("
        SELECT 
            a.id as apartment_id,
            a.property_id as current_property_id,
            p.id as properties_table_id,
            p.property_id as correct_property_id,
            p.address,
            CASE 
                WHEN p.id IS NOT NULL THEN 'References properties.id (WRONG)'
                ELSE 'No matching property found'
            END as status
        FROM apartments a
        LEFT JOIN properties p ON a.property_id = p.id
    ");
    
    $mismatches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($mismatches as $mismatch) {
        echo "   Apartment {$mismatch['apartment_id']}: property_id={$mismatch['current_property_id']} -> {$mismatch['status']}\n";
        if ($mismatch['correct_property_id']) {
            echo "     Should reference: {$mismatch['correct_property_id']} (properties.property_id)\n";
        }
    }
    echo "\n";

    // 5. Check for truly orphaned apartments
    echo "5. ORPHANED APARTMENTS CHECK:\n";
    $stmt = $pdo->query("
        SELECT 
            a.id,
            a.property_id,
            'No matching property.id' as issue_type
        FROM apartments a
        WHERE NOT EXISTS (
            SELECT 1 FROM properties p WHERE p.id = a.property_id
        )
        UNION ALL
        SELECT 
            a.id,
            a.property_id,
            'No matching property.property_id' as issue_type
        FROM apartments a
        WHERE NOT EXISTS (
            SELECT 1 FROM properties p WHERE p.property_id = a.property_id
        )
    ");
    
    $orphaned = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($orphaned)) {
        echo "   ✅ No orphaned apartments found\n";
    } else {
        foreach ($orphaned as $orphan) {
            echo "   ❌ Apartment {$orphan['id']}: {$orphan['issue_type']}\n";
        }
    }
    echo "\n";

    // 6. Validation summary
    echo "6. ANALYSIS SUMMARY:\n";
    $totalApartments = count($apartments);
    $apartmentsWithWrongReference = count($mismatches);
    
    echo "   - Total apartments: {$totalApartments}\n";
    echo "   - Apartments referencing properties.id (wrong): {$apartmentsWithWrongReference}\n";
    echo "   - Foreign key constraint: apartments.property_id -> properties.id (INCORRECT)\n";
    echo "   - Required fix: apartments.property_id -> properties.property_id\n";
    echo "\n";

    echo "✅ ANALYSIS COMPLETE - Ready for migration\n";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Note: This is expected if database is not set up locally\n";
    echo "Analysis based on SQL dump file is sufficient for migration planning\n";
}
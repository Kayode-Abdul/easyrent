<?php

/**
 * Test script for orphaned apartment record handling
 * This script tests the orphaned record identification and cleanup logic
 * before running the actual migration.
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Orphaned Apartment Records Analysis ===\n\n";

try {
    // Step 1: Identify apartments currently referencing properties.id (should reference properties.property_id)
    echo "1. Checking apartments referencing wrong field (properties.id instead of properties.property_id)...\n";
    
    $apartmentsReferencingWrongField = DB::select('
        SELECT a.id, a.property_id, a.apartment_id, p.id as prop_table_id, p.property_id as correct_property_id
        FROM apartments a
        INNER JOIN properties p ON a.property_id = p.id
        WHERE p.property_id IS NOT NULL
    ');

    echo "Found " . count($apartmentsReferencingWrongField) . " apartments referencing properties.id instead of properties.property_id\n";
    
    foreach ($apartmentsReferencingWrongField as $apt) {
        echo "  - Apartment ID: {$apt->id}, Current property_id: {$apt->property_id}, Should be: {$apt->correct_property_id}\n";
    }

    // Step 2: Identify truly orphaned apartments (no matching property at all)
    echo "\n2. Checking for truly orphaned apartments (no matching property)...\n";
    
    $trulyOrphanedApartments = DB::select('
        SELECT a.id, a.property_id, a.apartment_id
        FROM apartments a
        WHERE NOT EXISTS (
            SELECT 1 FROM properties p WHERE p.id = a.property_id
        )
        AND NOT EXISTS (
            SELECT 1 FROM properties p WHERE p.property_id = a.property_id
        )
    ');

    echo "Found " . count($trulyOrphanedApartments) . " truly orphaned apartments with no matching property\n";
    
    foreach ($trulyOrphanedApartments as $apt) {
        echo "  - Apartment ID: {$apt->id}, Invalid property_id: {$apt->property_id}\n";
    }

    // Step 3: Check current foreign key constraints
    echo "\n3. Checking current foreign key constraints on apartments table...\n";
    
    $foreignKeys = DB::select("
        SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'apartments' 
        AND COLUMN_NAME = 'property_id' 
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    if (empty($foreignKeys)) {
        echo "No foreign key constraints found on apartments.property_id\n";
    } else {
        foreach ($foreignKeys as $fk) {
            echo "  - Constraint: {$fk->CONSTRAINT_NAME}, References: {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
        }
    }

    // Step 4: Simulate cleanup actions (without actually executing them)
    echo "\n4. Simulating cleanup actions...\n";
    
    if (!empty($apartmentsReferencingWrongField)) {
        echo "Would fix " . count($apartmentsReferencingWrongField) . " apartments with wrong field references:\n";
        foreach ($apartmentsReferencingWrongField as $apt) {
            echo "  - UPDATE apartments SET property_id = '{$apt->correct_property_id}' WHERE id = {$apt->id}\n";
        }
    }
    
    if (!empty($trulyOrphanedApartments)) {
        echo "Would backup and delete " . count($trulyOrphanedApartments) . " truly orphaned apartments:\n";
        foreach ($trulyOrphanedApartments as $apt) {
            echo "  - BACKUP and DELETE apartment ID {$apt->id} (property_id: {$apt->property_id})\n";
        }
    }

    // Step 5: Check audit_logs table exists
    echo "\n5. Checking audit_logs table for logging capability...\n";
    
    try {
        $auditTableExists = DB::select("SHOW TABLES LIKE 'audit_logs'");
        if (!empty($auditTableExists)) {
            echo "audit_logs table exists - logging will work\n";
        } else {
            echo "WARNING: audit_logs table does not exist - logging may fail\n";
        }
    } catch (Exception $e) {
        echo "WARNING: Could not check audit_logs table: " . $e->getMessage() . "\n";
    }

    echo "\n=== Analysis Complete ===\n";
    echo "Summary:\n";
    echo "- Apartments with wrong field reference: " . count($apartmentsReferencingWrongField) . "\n";
    echo "- Truly orphaned apartments: " . count($trulyOrphanedApartments) . "\n";
    echo "- Total issues to resolve: " . (count($apartmentsReferencingWrongField) + count($trulyOrphanedApartments)) . "\n";
    
    if (count($apartmentsReferencingWrongField) + count($trulyOrphanedApartments) == 0) {
        echo "\nNo orphaned records found - migration should proceed smoothly!\n";
    } else {
        echo "\nOrphaned records found - migration will handle these automatically.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
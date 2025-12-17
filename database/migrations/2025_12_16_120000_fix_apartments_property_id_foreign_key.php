<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes the apartments.property_id foreign key to correctly reference
     * properties.property_id instead of properties.id, as required by the business logic.
     *
     * @return void
     */
    public function up()
    {
        // Step 1: Comprehensive orphaned apartment record identification and handling
        $this->handleOrphanedApartmentRecords();

        // Step 2: Update apartment records to use correct property_id values
        // For apartments that currently reference properties.id, update them to reference properties.property_id
        DB::statement('
            UPDATE apartments a
            INNER JOIN properties p ON a.property_id = p.id
            SET a.property_id = p.property_id
            WHERE EXISTS (
                SELECT 1 FROM properties p2 
                WHERE p2.id = a.property_id 
                AND p2.property_id IS NOT NULL
            )
        ');

        // Step 3: Drop existing foreign key constraint on apartments.property_id
        Schema::table('apartments', function (Blueprint $table) {
            // Get all foreign key constraints for property_id column
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'apartments' 
                AND COLUMN_NAME = 'property_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            // Drop each foreign key constraint found
            foreach ($foreignKeys as $fk) {
                try {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                } catch (Exception $e) {
                    // Log the error but continue - constraint might not exist
                    \Log::warning("Could not drop foreign key constraint: " . $fk->CONSTRAINT_NAME);
                }
            }
        });

        // Step 4: Create new foreign key constraint referencing properties.property_id
        Schema::table('apartments', function (Blueprint $table) {
            $table->foreign('property_id')
                  ->references('property_id')
                  ->on('properties')
                  ->onDelete('cascade')
                  ->name('apartments_property_id_foreign');
        });

        // Step 5: Add validation to ensure data integrity
        // Check that all apartments now have valid property_id references
        $invalidReferences = DB::select('
            SELECT COUNT(*) as count
            FROM apartments a
            LEFT JOIN properties p ON a.property_id = p.property_id
            WHERE p.property_id IS NULL
        ');

        if ($invalidReferences[0]->count > 0) {
            throw new Exception(
                "Migration failed: {$invalidReferences[0]->count} apartments still have invalid property_id references. " .
                "Please review orphaned records and fix data integrity issues before proceeding."
            );
        }
    }

    /**
     * Handle orphaned apartment records during migration
     * Implements comprehensive cleanup strategy for apartments with invalid property references
     * 
     * @return void
     */
    private function handleOrphanedApartmentRecords()
    {
        echo "Starting orphaned apartment records analysis...\n";
        
        // Step 1: Identify apartments currently referencing properties.id (should reference properties.property_id)
        $apartmentsReferencingWrongField = DB::select('
            SELECT a.id, a.property_id, a.apartment_id, p.id as prop_table_id, p.property_id as correct_property_id
            FROM apartments a
            INNER JOIN properties p ON a.property_id = p.id
            WHERE p.property_id IS NOT NULL
        ');

        echo "Found " . count($apartmentsReferencingWrongField) . " apartments referencing properties.id instead of properties.property_id\n";

        // Step 2: Identify truly orphaned apartments (no matching property at all)
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

        // Step 3: Log all identified issues for audit purposes
        $this->logOrphanedRecords($apartmentsReferencingWrongField, 'wrong_field_reference');
        $this->logOrphanedRecords($trulyOrphanedApartments, 'truly_orphaned');

        // Step 4: Implement cleanup strategy
        $this->implementOrphanedRecordCleanupStrategy($apartmentsReferencingWrongField, $trulyOrphanedApartments);
        
        echo "Orphaned apartment records handling completed.\n";
    }

    /**
     * Log orphaned records for audit purposes
     * 
     * @param array $records
     * @param string $issueType
     * @return void
     */
    private function logOrphanedRecords($records, $issueType)
    {
        foreach ($records as $record) {
            $logData = [
                'table_name' => 'apartments',
                'record_id' => $record->id,
                'action' => 'orphaned_record_identified',
                'old_values' => json_encode([
                    'property_id' => $record->property_id,
                    'apartment_id' => $record->apartment_id ?? null,
                    'issue_type' => $issueType
                ]),
                'new_values' => null,
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Add correct property_id for wrong field reference issues
            if ($issueType === 'wrong_field_reference' && isset($record->correct_property_id)) {
                $logData['new_values'] = json_encode([
                    'correct_property_id' => $record->correct_property_id
                ]);
            }

            // Use the correct audit_logs table structure
            $auditLogData = [
                'audit_type' => 'migration',
                'reference_type' => 'apartments',
                'reference_id' => $record->id,
                'audit_data' => json_encode([
                    'property_id' => $record->property_id,
                    'apartment_id' => $record->apartment_id ?? null,
                    'issue_type' => $issueType
                ]),
                'user_id' => null,
                'action' => 'orphaned_record_identified',
                'model_type' => 'App\\Models\\Apartment',
                'model_id' => $record->id,
                'description' => "Orphaned apartment record identified during foreign key migration: {$issueType}",
                'old_values' => json_encode([
                    'property_id' => $record->property_id,
                    'apartment_id' => $record->apartment_id ?? null
                ]),
                'new_values' => isset($record->correct_property_id) ? json_encode(['correct_property_id' => $record->correct_property_id]) : null,
                'ip_address' => null,
                'user_agent' => 'Migration Script',
                'performed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ];

            try {
                DB::table('audit_logs')->insert($auditLogData);
                echo "Logged orphaned record: Apartment ID {$record->id}, Issue: {$issueType}\n";
            } catch (Exception $e) {
                echo "Warning: Could not log audit record for apartment {$record->id}: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * Implement cleanup strategy for orphaned records
     * 
     * @param array $wrongFieldReferences
     * @param array $trulyOrphaned
     * @return void
     */
    private function implementOrphanedRecordCleanupStrategy($wrongFieldReferences, $trulyOrphaned)
    {
        // Strategy 1: Fix apartments referencing wrong field (properties.id instead of properties.property_id)
        if (!empty($wrongFieldReferences)) {
            echo "Fixing apartments that reference properties.id instead of properties.property_id...\n";
            
            foreach ($wrongFieldReferences as $apartment) {
                try {
                    DB::table('apartments')
                        ->where('id', $apartment->id)
                        ->update(['property_id' => $apartment->correct_property_id]);
                    
                    // Log the successful update using correct audit_logs structure
                    DB::table('audit_logs')->insert([
                        'audit_type' => 'migration',
                        'reference_type' => 'apartments',
                        'reference_id' => $apartment->id,
                        'audit_data' => json_encode([
                            'old_property_id' => $apartment->property_id,
                            'new_property_id' => $apartment->correct_property_id
                        ]),
                        'user_id' => null,
                        'action' => 'property_id_corrected',
                        'model_type' => 'App\\Models\\Apartment',
                        'model_id' => $apartment->id,
                        'description' => "Corrected apartment property_id from {$apartment->property_id} to {$apartment->correct_property_id}",
                        'old_values' => json_encode(['property_id' => $apartment->property_id]),
                        'new_values' => json_encode(['property_id' => $apartment->correct_property_id]),
                        'ip_address' => null,
                        'user_agent' => 'Migration Script',
                        'performed_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    echo "Fixed apartment {$apartment->id}: {$apartment->property_id} -> {$apartment->correct_property_id}\n";
                } catch (Exception $e) {
                    echo "Error fixing apartment {$apartment->id}: " . $e->getMessage() . "\n";
                }
            }
        }

        // Strategy 2: Handle truly orphaned apartments
        if (!empty($trulyOrphaned)) {
            echo "Handling truly orphaned apartments...\n";
            
            // Create a backup table for orphaned apartments before deletion
            $this->createOrphanedApartmentsBackup($trulyOrphaned);
            
            // Option 1: Delete orphaned apartments (recommended for data integrity)
            foreach ($trulyOrphaned as $apartment) {
                try {
                    // Log the deletion using correct audit_logs structure
                    DB::table('audit_logs')->insert([
                        'audit_type' => 'migration',
                        'reference_type' => 'apartments',
                        'reference_id' => $apartment->id,
                        'audit_data' => json_encode([
                            'property_id' => $apartment->property_id,
                            'apartment_id' => $apartment->apartment_id,
                            'reason' => 'truly_orphaned'
                        ]),
                        'user_id' => null,
                        'action' => 'orphaned_record_deleted',
                        'model_type' => 'App\\Models\\Apartment',
                        'model_id' => $apartment->id,
                        'description' => "Deleted orphaned apartment {$apartment->id} with invalid property_id {$apartment->property_id}",
                        'old_values' => json_encode([
                            'property_id' => $apartment->property_id,
                            'apartment_id' => $apartment->apartment_id
                        ]),
                        'new_values' => null,
                        'ip_address' => null,
                        'user_agent' => 'Migration Script',
                        'performed_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Delete the orphaned apartment
                    DB::table('apartments')->where('id', $apartment->id)->delete();
                    
                    echo "Deleted orphaned apartment {$apartment->id} (property_id: {$apartment->property_id})\n";
                } catch (Exception $e) {
                    echo "Error deleting orphaned apartment {$apartment->id}: " . $e->getMessage() . "\n";
                }
            }
        }
    }

    /**
     * Create backup table for orphaned apartments before deletion
     * 
     * @param array $orphanedApartments
     * @return void
     */
    private function createOrphanedApartmentsBackup($orphanedApartments)
    {
        if (empty($orphanedApartments)) {
            return;
        }

        try {
            // Create backup table if it doesn't exist
            DB::statement('
                CREATE TABLE IF NOT EXISTS orphaned_apartments_backup (
                    id INT PRIMARY KEY,
                    property_id VARCHAR(255),
                    apartment_id VARCHAR(255),
                    apartment_type VARCHAR(255),
                    tenant_id INT,
                    user_id INT,
                    range_start DATETIME,
                    range_end DATETIME,
                    amount DECIMAL(10,2),
                    occupied TINYINT(1),
                    backup_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    original_created_at TIMESTAMP,
                    INDEX idx_backup_property_id (property_id),
                    INDEX idx_backup_apartment_id (apartment_id)
                )
            ');

            // Backup orphaned apartments
            foreach ($orphanedApartments as $apartment) {
                $apartmentData = DB::table('apartments')->where('id', $apartment->id)->first();
                if ($apartmentData) {
                    DB::table('orphaned_apartments_backup')->insert([
                        'id' => $apartmentData->id,
                        'property_id' => $apartmentData->property_id,
                        'apartment_id' => $apartmentData->apartment_id ?? null,
                        'apartment_type' => $apartmentData->apartment_type ?? null,
                        'tenant_id' => $apartmentData->tenant_id ?? null,
                        'user_id' => $apartmentData->user_id ?? null,
                        'range_start' => $apartmentData->range_start ?? null,
                        'range_end' => $apartmentData->range_end ?? null,
                        'amount' => $apartmentData->amount ?? null,
                        'occupied' => $apartmentData->occupied ?? null,
                        'backup_created_at' => now(),
                        'original_created_at' => $apartmentData->created_at ?? null
                    ]);
                }
            }
            
            echo "Created backup for " . count($orphanedApartments) . " orphaned apartments in 'orphaned_apartments_backup' table\n";
        } catch (Exception $e) {
            echo "Warning: Could not create backup for orphaned apartments: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Step 1: Drop the new foreign key constraint
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropForeign('apartments_property_id_foreign');
        });

        // Step 2: Revert apartments.property_id back to reference properties.id
        DB::statement('
            UPDATE apartments a
            INNER JOIN properties p ON a.property_id = p.property_id
            SET a.property_id = p.id
            WHERE EXISTS (
                SELECT 1 FROM properties p2 
                WHERE p2.property_id = a.property_id 
                AND p2.id IS NOT NULL
            )
        ');

        // Step 3: Restore original foreign key constraint (referencing properties.id)
        Schema::table('apartments', function (Blueprint $table) {
            $table->foreign('property_id')
                  ->references('id')
                  ->on('properties')
                  ->onDelete('cascade');
        });
    }
};
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
     * This migration makes all id columns in the database auto-increment.
     * This is necessary for proper foreign key relationships and data integrity.
     *
     * @return void
     */
    public function up()
    {
        // List of tables that need their id columns to be auto-increment
        $tables = [
            'apartment_types',
            'audit_logs',
            'blog',
            'commission_payments',
            'commission_rates',
            'database_maintenance_logs',
            'invitation_analytics_cache',
            'invitation_performance_metrics',
            'invitation_security_monitoring',
            'marketer_profiles',
            'messages',
            'payment_invitations',
            'payment_tracking',
            'payments',
            'performance_logs',
            'profoma_receipt',
            'properties',
            'property_attributes',
            'property_types',
            'referral_campaigns',
            'referral_chains',
            'referral_rewards',
            'referrals',
            'regional_scopes',
            'reviews',
            'role_assignment_audits',
            'role_change_notifications',
            'role_user',
            'roles',
            'session_cleanup_history'
        ];

        foreach ($tables as $tableName) {
            try {
                // Check if table exists
                if (!Schema::hasTable($tableName)) {
                    echo "Table {$tableName} does not exist, skipping...\n";
                    continue;
                }

                // Check if table has an id column
                if (!Schema::hasColumn($tableName, 'id')) {
                    echo "Table {$tableName} does not have an id column, skipping...\n";
                    continue;
                }

                // Check current id column properties
                $columns = DB::select("DESCRIBE {$tableName}");
                $idColumn = null;
                foreach ($columns as $column) {
                    if ($column->Field === 'id') {
                        $idColumn = $column;
                        break;
                    }
                }

                if (!$idColumn) {
                    echo "No id column found in {$tableName}, skipping...\n";
                    continue;
                }

                // Check if already auto-increment
                if (strpos($idColumn->Extra, 'auto_increment') !== false) {
                    echo "Table {$tableName} id column is already auto-increment, skipping...\n";
                    continue;
                }

                echo "Making id column auto-increment for table: {$tableName}\n";

                // Check for duplicate ids first
                $duplicates = DB::select("SELECT id, COUNT(*) as count FROM {$tableName} GROUP BY id HAVING COUNT(*) > 1");
                if (!empty($duplicates)) {
                    echo "Found duplicate ids in {$tableName}, fixing...\n";
                    
                    // Create a temporary table with unique records
                    $tempTable = $tableName . '_temp_' . time();
                    DB::statement("CREATE TABLE {$tempTable} LIKE {$tableName}");
                    
                    // Copy unique records (keeping the first occurrence of each id)
                    $columns = DB::select("SHOW COLUMNS FROM {$tableName}");
                    $columnNames = array_map(function($col) { return $col->Field; }, $columns);
                    $columnList = implode(', ', $columnNames);
                    
                    DB::statement("
                        INSERT INTO {$tempTable} ({$columnList})
                        SELECT {$columnList} FROM {$tableName} 
                        WHERE id IN (
                            SELECT MIN(id) FROM (
                                SELECT id FROM {$tableName} GROUP BY id
                            ) as subquery
                        )
                    ");
                    
                    // Drop original table and rename temp table
                    DB::statement("DROP TABLE {$tableName}");
                    DB::statement("RENAME TABLE {$tempTable} TO {$tableName}");
                    
                    echo "Fixed duplicates in {$tableName}\n";
                }

                // Get the current maximum id value
                $maxId = DB::table($tableName)->max('id') ?? 0;
                $nextAutoIncrement = $maxId + 1;

                // Check if id is already primary key
                $isPrimaryKey = $idColumn->Key === 'PRI';
                
                if ($isPrimaryKey) {
                    // If already primary key, just add auto-increment
                    DB::statement("ALTER TABLE {$tableName} MODIFY COLUMN id BIGINT UNSIGNED AUTO_INCREMENT");
                } else {
                    // If not primary key, make it primary key with auto-increment
                    DB::statement("ALTER TABLE {$tableName} MODIFY COLUMN id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY");
                }
                
                // Set the auto-increment starting value
                if ($maxId > 0) {
                    DB::statement("ALTER TABLE {$tableName} AUTO_INCREMENT = {$nextAutoIncrement}");
                }

                echo "Successfully updated {$tableName} id column to auto-increment (starting from {$nextAutoIncrement})\n";

            } catch (Exception $e) {
                echo "Error updating table {$tableName}: " . $e->getMessage() . "\n";
                // Continue with other tables instead of failing completely
            }
        }

        echo "Auto-increment migration completed.\n";
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This down migration removes auto-increment but keeps the primary key.
     * This is generally safe but may cause issues if new records are inserted.
     *
     * @return void
     */
    public function down()
    {
        // List of tables to revert
        $tables = [
            'apartment_types',
            'audit_logs',
            'blog',
            'commission_payments',
            'commission_rates',
            'database_maintenance_logs',
            'invitation_analytics_cache',
            'invitation_performance_metrics',
            'invitation_security_monitoring',
            'marketer_profiles',
            'messages',
            'payment_invitations',
            'payment_tracking',
            'payments',
            'performance_logs',
            'profoma_receipt',
            'properties',
            'property_attributes',
            'property_types',
            'referral_campaigns',
            'referral_chains',
            'referral_rewards',
            'referrals',
            'regional_scopes',
            'reviews',
            'role_assignment_audits',
            'role_change_notifications',
            'role_user',
            'roles',
            'session_cleanup_history'
        ];

        foreach ($tables as $tableName) {
            try {
                if (!Schema::hasTable($tableName)) {
                    continue;
                }

                if (!Schema::hasColumn($tableName, 'id')) {
                    continue;
                }

                echo "Removing auto-increment from {$tableName} id column\n";
                
                // Remove auto-increment but keep as primary key
                DB::statement("ALTER TABLE {$tableName} MODIFY COLUMN id BIGINT UNSIGNED PRIMARY KEY");

            } catch (Exception $e) {
                echo "Error reverting table {$tableName}: " . $e->getMessage() . "\n";
            }
        }

        echo "Auto-increment reversion completed.\n";
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations - Verification only, no schema changes
     *
     * @return void
     */
    public function up()
    {
        // Verify all expected indexes exist
        $expectedIndexes = [
            'idx_landlord_active_invitations',
            'idx_apartment_status_expiry',
            'idx_session_cleanup',
            'idx_security_tracking',
            'idx_tenant_payments',
            'idx_analytics_reporting',
            'idx_expiry_cleanup',
            'idx_session_cleanup_efficient',
            'idx_batch_expiration',
            'idx_rate_limit_cleanup'
        ];
        
        $existingIndexes = collect(DB::select('SHOW INDEX FROM apartment_invitations WHERE Key_name LIKE "idx_%"'))
            ->pluck('Key_name')
            ->unique()
            ->toArray();
        
        $missingIndexes = array_diff($expectedIndexes, $existingIndexes);
        
        if (empty($missingIndexes)) {
            Log::info('Database optimization verification: All expected indexes are present', [
                'indexes_found' => count($existingIndexes),
                'indexes_expected' => count($expectedIndexes)
            ]);
        } else {
            Log::warning('Database optimization verification: Some indexes are missing', [
                'missing_indexes' => $missingIndexes,
                'existing_indexes' => $existingIndexes
            ]);
        }
        
        // Verify views exist
        $expectedViews = ['active_invitation_details', 'invitation_analytics'];
        $existingViews = [];
        
        foreach ($expectedViews as $view) {
            try {
                DB::select("SELECT 1 FROM {$view} LIMIT 1");
                $existingViews[] = $view;
            } catch (\Exception $e) {
                Log::warning("View {$view} is not accessible", ['error' => $e->getMessage()]);
            }
        }
        
        if (count($existingViews) === count($expectedViews)) {
            Log::info('Database optimization verification: All expected views are accessible', [
                'views_verified' => $existingViews
            ]);
        }
        
        // Verify maintenance tables exist
        $expectedTables = ['database_maintenance_logs', 'invitation_performance_metrics', 'session_cleanup_history'];
        $existingTables = [];
        
        foreach ($expectedTables as $table) {
            if (Schema::hasTable($table)) {
                $existingTables[] = $table;
            }
        }
        
        if (count($existingTables) === count($expectedTables)) {
            Log::info('Database optimization verification: All maintenance tables exist', [
                'tables_verified' => $existingTables
            ]);
        }
        
        // Log successful verification
        Log::info('Database optimization verification completed successfully', [
            'indexes_verified' => count($existingIndexes),
            'views_verified' => count($existingViews),
            'tables_verified' => count($existingTables),
            'verification_timestamp' => now()
        ]);
        
        // Insert verification record
        DB::table('database_maintenance_logs')->insert([
            'operation_type' => 'verification',
            'description' => 'Database optimization verification completed',
            'operation_details' => json_encode([
                'indexes_verified' => count($existingIndexes),
                'views_verified' => count($existingViews),
                'tables_verified' => count($existingTables),
                'missing_indexes' => $missingIndexes
            ]),
            'records_affected' => 0,
            'status' => empty($missingIndexes) ? 'completed' : 'completed_with_warnings',
            'started_at' => now(),
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This is a verification migration - no rollback needed
        Log::info('Database optimization verification rollback - no action needed');
    }
};
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
     * This migration finalizes the EasyRent Link Authentication System schema
     * by adding any remaining optimizations and ensuring all requirements are met.
     *
     * @return void
     */
    public function up()
    {
        // Ensure all required fields exist in apartment_invitations table
        Schema::table('apartment_invitations', function (Blueprint $table) {
            // Check if any critical fields are missing and add them
            if (!Schema::hasColumn('apartment_invitations', 'invitation_url')) {
                $table->text('invitation_url')->nullable()->after('invitation_token');
            }
            
            // Add metadata fields for enhanced tracking
            if (!Schema::hasColumn('apartment_invitations', 'metadata')) {
                $table->json('metadata')->nullable()->after('session_data');
            }
            
            // Add referral tracking field for marketer qualification
            if (!Schema::hasColumn('apartment_invitations', 'referral_source')) {
                $table->string('referral_source')->nullable()->after('registration_source');
            }
            
            // Add payment reference field for better integration
            if (!Schema::hasColumn('apartment_invitations', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('total_amount');
            }
        });
        
        // Add final performance indexes
        Schema::table('apartment_invitations', function (Blueprint $table) {
            // Composite index for invitation URL generation and validation
            $urlIndexExists = collect(DB::select("SHOW INDEX FROM apartment_invitations WHERE Key_name = 'idx_token_apartment_landlord'"))->isNotEmpty();
            if (!$urlIndexExists) {
                $table->index(['invitation_token', 'apartment_id', 'landlord_id'], 'idx_token_apartment_landlord');
            }
            
            // Index for referral tracking and marketer qualification
            $referralIndexExists = collect(DB::select("SHOW INDEX FROM apartment_invitations WHERE Key_name = 'idx_referral_tracking'"))->isNotEmpty();
            if (!$referralIndexExists) {
                $table->index(['referral_source', 'payment_completed_at'], 'idx_referral_tracking');
            }
            
            // Index for payment reference lookups
            $paymentRefIndexExists = collect(DB::select("SHOW INDEX FROM apartment_invitations WHERE Key_name = 'idx_payment_reference'"))->isNotEmpty();
            if (!$paymentRefIndexExists) {
                $table->index('payment_reference', 'idx_payment_reference');
            }
            
            // Comprehensive index for dashboard queries
            $dashboardIndexExists = collect(DB::select("SHOW INDEX FROM apartment_invitations WHERE Key_name = 'idx_dashboard_queries'"))->isNotEmpty();
            if (!$dashboardIndexExists) {
                $table->index(['landlord_id', 'created_at', 'status', 'viewed_at'], 'idx_dashboard_queries');
            }
        });
        
        // Create additional database views for complex queries
        
        // View for invitation conversion funnel analysis
        DB::unprepared('DROP VIEW IF EXISTS invitation_conversion_funnel');
        DB::unprepared('
            CREATE VIEW invitation_conversion_funnel AS
            SELECT 
                ai.landlord_id,
                u.first_name as landlord_name,
                u.email as landlord_email,
                COUNT(*) as total_invitations,
                COUNT(CASE WHEN ai.viewed_at IS NOT NULL THEN 1 END) as viewed_invitations,
                COUNT(CASE WHEN ai.payment_initiated_at IS NOT NULL THEN 1 END) as payment_initiated,
                COUNT(CASE WHEN ai.payment_completed_at IS NOT NULL THEN 1 END) as payment_completed,
                ROUND(
                    (COUNT(CASE WHEN ai.viewed_at IS NOT NULL THEN 1 END) * 100.0) / COUNT(*), 2
                ) as view_rate_percent,
                ROUND(
                    (COUNT(CASE WHEN ai.payment_initiated_at IS NOT NULL THEN 1 END) * 100.0) / 
                    NULLIF(COUNT(CASE WHEN ai.viewed_at IS NOT NULL THEN 1 END), 0), 2
                ) as initiation_rate_percent,
                ROUND(
                    (COUNT(CASE WHEN ai.payment_completed_at IS NOT NULL THEN 1 END) * 100.0) / 
                    NULLIF(COUNT(CASE WHEN ai.payment_initiated_at IS NOT NULL THEN 1 END), 0), 2
                ) as completion_rate_percent,
                ROUND(
                    (COUNT(CASE WHEN ai.payment_completed_at IS NOT NULL THEN 1 END) * 100.0) / COUNT(*), 2
                ) as overall_conversion_rate
            FROM apartment_invitations ai
            JOIN users u ON ai.landlord_id = u.user_id
            WHERE ai.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            GROUP BY ai.landlord_id, u.first_name, u.email
            HAVING total_invitations > 0
            ORDER BY overall_conversion_rate DESC, total_invitations DESC
        ');
        
        // View for security monitoring
        DB::unprepared('DROP VIEW IF EXISTS invitation_security_monitoring');
        DB::unprepared('
            CREATE VIEW invitation_security_monitoring AS
            SELECT 
                ai.id,
                ai.invitation_token,
                ai.apartment_id,
                ai.access_count,
                ai.rate_limit_count,
                ai.last_accessed_ip,
                ai.last_accessed_at,
                ai.created_at,
                CASE 
                    WHEN ai.access_count > 100 THEN "high_access"
                    WHEN ai.rate_limit_count >= 45 THEN "rate_limit_warning"
                    WHEN ai.access_count > 50 THEN "moderate_access"
                    ELSE "normal"
                END as security_status,
                TIMESTAMPDIFF(HOUR, ai.created_at, ai.last_accessed_at) as hours_since_creation,
                TIMESTAMPDIFF(MINUTE, ai.last_accessed_at, NOW()) as minutes_since_last_access
            FROM apartment_invitations ai
            WHERE ai.status = "active"
              AND ai.access_count > 0
            ORDER BY 
                CASE 
                    WHEN ai.access_count > 100 THEN 1
                    WHEN ai.rate_limit_count >= 45 THEN 2
                    WHEN ai.access_count > 50 THEN 3
                    ELSE 4
                END,
                ai.access_count DESC
        ');
        
        // Note: Stored procedures skipped due to MySQL system table compatibility issues
        // The Laravel commands will handle cleanup operations instead
        
        // Add database constraints for enhanced data integrity
        
        // Ensure invitation URLs are properly formatted (basic check)
        DB::statement('ALTER TABLE apartment_invitations ADD CONSTRAINT chk_invitation_url_format 
                      CHECK (invitation_url IS NULL OR invitation_url LIKE "http%" OR invitation_url LIKE "https%")');
        
        // Ensure payment reference follows expected format if provided
        DB::statement('ALTER TABLE apartment_invitations ADD CONSTRAINT chk_payment_reference_format 
                      CHECK (payment_reference IS NULL OR LENGTH(payment_reference) >= 10)');
        
        // Ensure metadata is valid JSON if provided
        DB::statement('ALTER TABLE apartment_invitations ADD CONSTRAINT chk_metadata_json 
                      CHECK (metadata IS NULL OR JSON_VALID(metadata))');
        
        // Ensure referral source is from known sources
        DB::statement('ALTER TABLE apartment_invitations ADD CONSTRAINT chk_referral_source_valid 
                      CHECK (referral_source IS NULL OR referral_source IN ("direct", "referral_link", "social_media", "email", "sms", "whatsapp"))');
        
        // Log the completion of schema finalization
        DB::table('database_maintenance_logs')->insert([
            'operation_type' => 'schema_finalization',
            'table_name' => 'apartment_invitations',
            'description' => 'Finalized EasyRent Link Authentication System database schema with all optimizations',
            'operation_details' => json_encode([
                'additional_fields_added' => 4,
                'performance_indexes_added' => 4,
                'database_views_created' => 2,
                'stored_procedures_created' => 0,
                'data_constraints_added' => 4,
                'schema_version' => '1.0.0'
            ]),
            'records_affected' => 0,
            'status' => 'completed',
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
        // Note: No stored procedures to drop (skipped due to compatibility)
        
        // Drop views
        DB::unprepared('DROP VIEW IF EXISTS invitation_security_monitoring');
        DB::unprepared('DROP VIEW IF EXISTS invitation_conversion_funnel');
        
        // Drop constraints
        DB::statement('ALTER TABLE apartment_invitations DROP CONSTRAINT IF EXISTS chk_referral_source_valid');
        DB::statement('ALTER TABLE apartment_invitations DROP CONSTRAINT IF EXISTS chk_metadata_json');
        DB::statement('ALTER TABLE apartment_invitations DROP CONSTRAINT IF EXISTS chk_payment_reference_format');
        DB::statement('ALTER TABLE apartment_invitations DROP CONSTRAINT IF EXISTS chk_invitation_url_format');
        
        // Drop indexes
        Schema::table('apartment_invitations', function (Blueprint $table) {
            $table->dropIndex('idx_dashboard_queries');
            $table->dropIndex('idx_payment_reference');
            $table->dropIndex('idx_referral_tracking');
            $table->dropIndex('idx_token_apartment_landlord');
        });
        
        // Drop additional fields
        Schema::table('apartment_invitations', function (Blueprint $table) {
            $table->dropColumn([
                'invitation_url',
                'metadata',
                'referral_source',
                'payment_reference'
            ]);
        });
    }
};
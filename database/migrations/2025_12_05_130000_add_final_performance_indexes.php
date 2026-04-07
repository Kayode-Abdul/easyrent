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
     * This migration adds final performance indexes for the EasyRent Link Authentication System
     * to ensure optimal query performance across all use cases.
     *
     * @return void
     */
    public function up()
    {
        // Add performance indexes to related tables that work with apartment invitations
        
        // Properties table already has the required indexes (idx_state_prop_type and idx_status_created)
        // No additional indexes needed for properties table
        
        // Optimize referrals table for marketer qualification queries
        Schema::table('referrals', function (Blueprint $table) {
            $referrerStatusExists = collect(DB::select("SHOW INDEX FROM referrals WHERE Key_name = 'idx_referrer_referral_status'"))->isNotEmpty();
            if (!$referrerStatusExists) {
                $table->index(['referrer_id', 'referral_status'], 'idx_referrer_referral_status');
            }
            
            $referredCreatedExists = collect(DB::select("SHOW INDEX FROM referrals WHERE Key_name = 'idx_referred_created'"))->isNotEmpty();
            if (!$referredCreatedExists) {
                $table->index(['referred_id', 'created_at'], 'idx_referred_created');
            }
        });
        
        // Optimize activity_logs table for security monitoring
        Schema::table('activity_logs', function (Blueprint $table) {
            $actionIpExists = collect(DB::select("SHOW INDEX FROM activity_logs WHERE Key_name = 'idx_action_ip_created'"))->isNotEmpty();
            if (!$actionIpExists) {
                $table->index(['action', 'ip_address', 'created_at'], 'idx_action_ip_created');
            }
            
            $userActionExists = collect(DB::select("SHOW INDEX FROM activity_logs WHERE Key_name = 'idx_user_action_created'"))->isNotEmpty();
            if (!$userActionExists) {
                $table->index(['user_id', 'action', 'created_at'], 'idx_user_action_created');
            }
        });
        
        // Add final composite indexes to apartment_invitations for complex queries
        Schema::table('apartment_invitations', function (Blueprint $table) {
            // Index for landlord dashboard with filtering
            $landlordDashboardExists = collect(DB::select("SHOW INDEX FROM apartment_invitations WHERE Key_name = 'idx_landlord_dashboard_full'"))->isNotEmpty();
            if (!$landlordDashboardExists) {
                $table->index(['landlord_id', 'status', 'created_at', 'expires_at'], 'idx_landlord_dashboard_full');
            }
            
            // Index for payment processing queries
            $paymentProcessingExists = collect(DB::select("SHOW INDEX FROM apartment_invitations WHERE Key_name = 'idx_payment_processing'"))->isNotEmpty();
            if (!$paymentProcessingExists) {
                $table->index(['apartment_id', 'tenant_user_id', 'payment_completed_at'], 'idx_payment_processing');
            }
            
            // Index for security monitoring and analytics
            $securityAnalyticsExists = collect(DB::select("SHOW INDEX FROM apartment_invitations WHERE Key_name = 'idx_security_analytics'"))->isNotEmpty();
            if (!$securityAnalyticsExists) {
                $table->index(['last_accessed_ip', 'access_count', 'created_at'], 'idx_security_analytics');
            }
            
            // Index for session management queries
            $sessionManagementExists = collect(DB::select("SHOW INDEX FROM apartment_invitations WHERE Key_name = 'idx_session_management'"))->isNotEmpty();
            if (!$sessionManagementExists) {
                $table->index(['session_expires_at', 'authentication_required', 'status'], 'idx_session_management');
            }
        });
        
        // Create additional database views for common query patterns
        
        // View for landlord invitation dashboard
        DB::unprepared('DROP VIEW IF EXISTS landlord_invitation_dashboard');
        DB::unprepared('
            CREATE VIEW landlord_invitation_dashboard AS
            SELECT 
                ai.landlord_id,
                u.first_name as landlord_name,
                u.email as landlord_email,
                COUNT(*) as total_invitations,
                COUNT(CASE WHEN ai.status = "active" THEN 1 END) as active_invitations,
                COUNT(CASE WHEN ai.status = "used" THEN 1 END) as used_invitations,
                COUNT(CASE WHEN ai.status = "expired" THEN 1 END) as expired_invitations,
                COUNT(CASE WHEN ai.viewed_at IS NOT NULL THEN 1 END) as viewed_invitations,
                COUNT(CASE WHEN ai.payment_completed_at IS NOT NULL THEN 1 END) as completed_payments,
                SUM(ai.total_amount) as total_revenue,
                AVG(ai.access_count) as avg_access_count,
                MAX(ai.created_at) as last_invitation_created,
                MAX(ai.last_accessed_at) as last_activity
            FROM apartment_invitations ai
            JOIN users u ON ai.landlord_id = u.user_id
            WHERE ai.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            GROUP BY ai.landlord_id, u.first_name, u.email
            ORDER BY total_invitations DESC, last_activity DESC
        ');
        
        // View for system performance monitoring
        DB::unprepared('DROP VIEW IF EXISTS system_performance_overview');
        DB::unprepared('
            CREATE VIEW system_performance_overview AS
            SELECT 
                DATE(ai.created_at) as date,
                COUNT(*) as daily_invitations,
                COUNT(CASE WHEN ai.viewed_at IS NOT NULL THEN 1 END) as daily_views,
                COUNT(CASE WHEN ai.payment_completed_at IS NOT NULL THEN 1 END) as daily_completions,
                AVG(ai.access_count) as avg_daily_access,
                COUNT(CASE WHEN ai.access_count > 50 THEN 1 END) as high_access_invitations,
                COUNT(CASE WHEN ai.rate_limit_count > 0 THEN 1 END) as rate_limited_invitations,
                COUNT(DISTINCT ai.last_accessed_ip) as unique_ips,
                AVG(TIMESTAMPDIFF(MINUTE, ai.created_at, ai.viewed_at)) as avg_time_to_view_minutes,
                AVG(TIMESTAMPDIFF(MINUTE, ai.viewed_at, ai.payment_completed_at)) as avg_time_to_payment_minutes
            FROM apartment_invitations ai
            WHERE ai.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(ai.created_at)
            ORDER BY date DESC
        ');
        
        // Create materialized view simulation for heavy analytics queries
        // (MySQL doesn't support materialized views, so we'll create a table that gets refreshed)
        if (!Schema::hasTable('invitation_analytics_cache')) {
            Schema::create('invitation_analytics_cache', function (Blueprint $table) {
                $table->id();
                $table->date('cache_date');
                $table->string('metric_type', 50); // daily, weekly, monthly
                $table->json('analytics_data');
                $table->timestamp('last_updated');
                $table->timestamps();
                
                $table->unique(['cache_date', 'metric_type']);
                $table->index('cache_date');
                $table->index('metric_type');
                $table->index('last_updated');
            });
        }
        
        // Note: Database functions skipped due to MySQL system table compatibility issues
        // The application will handle these calculations in PHP instead
        
        // Log the completion of final optimizations
        DB::table('database_maintenance_logs')->insert([
            'operation_type' => 'final_optimization',
            'table_name' => 'multiple_tables',
            'description' => 'Applied final performance optimizations for EasyRent Link Authentication System',
            'operation_details' => json_encode([
                'performance_indexes_added' => 12,
                'database_views_created' => 2,
                'database_functions_created' => 0,
                'analytics_cache_table_created' => true,
                'optimization_level' => 'production_ready'
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
        // Note: No database functions to drop (skipped due to compatibility)
        
        // Drop analytics cache table
        Schema::dropIfExists('invitation_analytics_cache');
        
        // Drop views
        DB::unprepared('DROP VIEW IF EXISTS system_performance_overview');
        DB::unprepared('DROP VIEW IF EXISTS landlord_invitation_dashboard');
        
        // Drop indexes from apartment_invitations
        Schema::table('apartment_invitations', function (Blueprint $table) {
            $table->dropIndex('idx_session_management');
            $table->dropIndex('idx_security_analytics');
            $table->dropIndex('idx_payment_processing');
            $table->dropIndex('idx_landlord_dashboard_full');
        });
        
        // Drop indexes from activity_logs
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('idx_user_action_created');
            $table->dropIndex('idx_action_ip_created');
        });
        
        // Drop indexes from referrals
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropIndex('idx_referred_created');
            $table->dropIndex('idx_referrer_referral_status');
        });
        
        // No indexes to drop from properties table (they were pre-existing)
    }
};
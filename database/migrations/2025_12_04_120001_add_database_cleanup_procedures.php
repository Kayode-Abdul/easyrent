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
     * @return void
     */
    public function up()
    {
        // Skip stored procedures due to MySQL compatibility issues
        // Instead, create optimized views and indexes for cleanup operations
        
        // Create view for active invitations with apartment details (for performance)
        DB::unprepared('DROP VIEW IF EXISTS active_invitation_details');
        DB::unprepared('
            CREATE VIEW active_invitation_details AS
            SELECT 
                ai.id,
                ai.invitation_token,
                ai.apartment_id,
                ai.landlord_id,
                ai.status,
                ai.expires_at,
                ai.access_count,
                ai.last_accessed_at,
                ai.session_expires_at,
                ai.created_at,
                a.amount as apartment_amount,
                a.occupied as apartment_occupied,
                p.prop_type as property_type,
                p.address as property_address,
                p.state as property_state,
                u.first_name as landlord_first_name,
                u.last_name as landlord_last_name,
                u.email as landlord_email
            FROM apartment_invitations ai
            JOIN apartments a ON ai.apartment_id = a.id
            JOIN properties p ON a.property_id = p.prop_id  
            JOIN users u ON ai.landlord_id = u.user_id
            WHERE ai.status = "active" 
              AND (ai.expires_at IS NULL OR ai.expires_at > NOW())
        ');
        
        // Create view for invitation analytics
        DB::unprepared('DROP VIEW IF EXISTS invitation_analytics');
        DB::unprepared('
            CREATE VIEW invitation_analytics AS
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_created,
                COUNT(CASE WHEN status = "used" THEN 1 END) as total_used,
                COUNT(CASE WHEN status = "expired" THEN 1 END) as total_expired,
                COUNT(CASE WHEN viewed_at IS NOT NULL THEN 1 END) as total_viewed,
                COUNT(CASE WHEN payment_initiated_at IS NOT NULL THEN 1 END) as total_payment_initiated,
                COUNT(CASE WHEN payment_completed_at IS NOT NULL THEN 1 END) as total_payment_completed,
                AVG(access_count) as avg_access_count,
                MAX(access_count) as max_access_count
            FROM apartment_invitations
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ');
        
        // Create additional indexes for cleanup operations
        Schema::table('apartment_invitations', function (Blueprint $table) {
            // Index for efficient session cleanup queries (exclude JSON column)
            $table->index('session_expires_at', 'idx_session_cleanup_efficient');
            
            // Index for batch expiration operations
            $table->index(['status', 'expires_at', 'updated_at'], 'idx_batch_expiration');
            
            // Index for rate limit cleanup
            $table->index(['rate_limit_reset_at', 'rate_limit_count'], 'idx_rate_limit_cleanup');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop additional indexes
        Schema::table('apartment_invitations', function (Blueprint $table) {
            $table->dropIndex('idx_session_cleanup_efficient');
            $table->dropIndex('idx_batch_expiration');
            $table->dropIndex('idx_rate_limit_cleanup');
        });
        
        // Drop views
        DB::unprepared('DROP VIEW IF EXISTS invitation_analytics');
        DB::unprepared('DROP VIEW IF EXISTS active_invitation_details');
    }
};
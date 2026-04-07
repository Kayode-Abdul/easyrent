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
        Schema::table('apartment_invitations', function (Blueprint $table) {
            // Composite indexes for performance optimization
            
            // Index for active invitations by landlord (common query pattern)
            $table->index(['landlord_id', 'status', 'expires_at'], 'idx_landlord_active_invitations');
            
            // Index for apartment availability queries
            $table->index(['apartment_id', 'status', 'expires_at'], 'idx_apartment_status_expiry');
            
            // Index for session cleanup operations
            $table->index(['session_expires_at', 'status'], 'idx_session_cleanup');
            
            // Index for security and rate limiting queries
            $table->index(['last_accessed_ip', 'last_accessed_at'], 'idx_security_tracking');
            
            // Index for payment tracking queries
            $table->index(['tenant_user_id', 'payment_completed_at'], 'idx_tenant_payments');
            
            // Index for invitation analytics and reporting
            $table->index(['created_at', 'status', 'landlord_id'], 'idx_analytics_reporting');
            
            // Index for expired invitation cleanup
            $table->index(['status', 'expires_at', 'updated_at'], 'idx_expiry_cleanup');
        });
        
        // Add database-level constraints for data integrity
        DB::statement('ALTER TABLE apartment_invitations ADD CONSTRAINT chk_valid_lease_duration CHECK (lease_duration IS NULL OR lease_duration > 0)');
        DB::statement('ALTER TABLE apartment_invitations ADD CONSTRAINT chk_valid_total_amount CHECK (total_amount IS NULL OR total_amount >= 0)');
        DB::statement('ALTER TABLE apartment_invitations ADD CONSTRAINT chk_valid_access_count CHECK (access_count >= 0)');
        DB::statement('ALTER TABLE apartment_invitations ADD CONSTRAINT chk_valid_rate_limit_count CHECK (rate_limit_count >= 0)');
        
        // Add check constraint to ensure session expiration is reasonable
        DB::statement('ALTER TABLE apartment_invitations ADD CONSTRAINT chk_session_expiry_reasonable CHECK (session_expires_at IS NULL OR session_expires_at > created_at)');
        
        // Add check constraint for payment flow integrity
        DB::statement('ALTER TABLE apartment_invitations ADD CONSTRAINT chk_payment_flow_integrity CHECK (
            (payment_initiated_at IS NULL OR payment_initiated_at >= viewed_at) AND
            (payment_completed_at IS NULL OR payment_completed_at >= payment_initiated_at)
        )');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('apartment_invitations', function (Blueprint $table) {
            // Drop composite indexes
            $table->dropIndex('idx_landlord_active_invitations');
            $table->dropIndex('idx_apartment_status_expiry');
            $table->dropIndex('idx_session_cleanup');
            $table->dropIndex('idx_security_tracking');
            $table->dropIndex('idx_tenant_payments');
            $table->dropIndex('idx_analytics_reporting');
            $table->dropIndex('idx_expiry_cleanup');
        });
        
        // Drop database constraints
        DB::statement('ALTER TABLE apartment_invitations DROP CONSTRAINT IF EXISTS chk_valid_lease_duration');
        DB::statement('ALTER TABLE apartment_invitations DROP CONSTRAINT IF EXISTS chk_valid_total_amount');
        DB::statement('ALTER TABLE apartment_invitations DROP CONSTRAINT IF EXISTS chk_valid_access_count');
        DB::statement('ALTER TABLE apartment_invitations DROP CONSTRAINT IF EXISTS chk_valid_rate_limit_count');
        DB::statement('ALTER TABLE apartment_invitations DROP CONSTRAINT IF EXISTS chk_session_expiry_reasonable');
        DB::statement('ALTER TABLE apartment_invitations DROP CONSTRAINT IF EXISTS chk_payment_flow_integrity');
    }
};
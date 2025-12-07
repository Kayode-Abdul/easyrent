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
        // First, let's check if we need to update any existing foreign key constraints
        // and add missing ones for better referential integrity
        
        Schema::table('apartment_invitations', function (Blueprint $table) {
            // Add foreign key for tenant_user_id if it doesn't exist with proper constraint name
            // This ensures data integrity when users are deleted
            
            // Note: We'll use a more descriptive constraint name
            // The existing foreign key might need to be recreated with better options
        });
        
        // Add indexes for related tables that are frequently joined with apartment_invitations
        
        // Optimize apartments table for invitation queries
        Schema::table('apartments', function (Blueprint $table) {
            // Check if index exists before adding
            $indexExists = collect(DB::select("SHOW INDEX FROM apartments WHERE Key_name = 'idx_property_occupied'"))->isNotEmpty();
            
            if (!$indexExists) {
                $table->index(['property_id', 'occupied'], 'idx_property_occupied');
            }
            
            // Index for amount-based queries (price filtering)
            $amountIndexExists = collect(DB::select("SHOW INDEX FROM apartments WHERE Key_name = 'idx_amount_occupied'"))->isNotEmpty();
            
            if (!$amountIndexExists) {
                $table->index(['amount', 'occupied'], 'idx_amount_occupied');
            }
        });
        
        // Optimize users table for landlord and tenant queries
        Schema::table('users', function (Blueprint $table) {
            // Index for registration source queries (EasyRent registrations)
            $regSourceExists = collect(DB::select("SHOW INDEX FROM users WHERE Key_name = 'idx_registration_source'"))->isNotEmpty();
            
            if (!$regSourceExists) {
                $table->index('registration_source', 'idx_registration_source');
            }
            
            // Index for referred_by queries (marketer qualification)
            $referredByExists = collect(DB::select("SHOW INDEX FROM users WHERE Key_name = 'idx_referred_by'"))->isNotEmpty();
            
            if (!$referredByExists) {
                $table->index('referred_by', 'idx_referred_by');
            }
        });
        
        // Optimize payments table for apartment-related queries
        Schema::table('payments', function (Blueprint $table) {
            // Index for apartment_id and status combination (frequently queried together)
            $aptStatusExists = collect(DB::select("SHOW INDEX FROM payments WHERE Key_name = 'idx_apartment_status'"))->isNotEmpty();
            
            if (!$aptStatusExists) {
                $table->index(['apartment_id', 'status'], 'idx_apartment_status');
            }
            
            // Index for payment date and status queries
            $dateStatusExists = collect(DB::select("SHOW INDEX FROM payments WHERE Key_name = 'idx_paid_at_status'"))->isNotEmpty();
            
            if (!$dateStatusExists) {
                $table->index(['paid_at', 'status'], 'idx_paid_at_status');
            }
        });
        
        // Add database triggers for automatic cleanup and maintenance
        
        // Trigger to automatically clean up session data when invitation expires
        DB::unprepared('
            CREATE TRIGGER cleanup_expired_invitation_session
            AFTER UPDATE ON apartment_invitations
            FOR EACH ROW
            BEGIN
                -- If invitation status changed to expired, clean up session data
                IF NEW.status = "expired" AND OLD.status != "expired" THEN
                    UPDATE apartment_invitations 
                    SET session_data = NULL, 
                        session_expires_at = NULL,
                        updated_at = NOW()
                    WHERE id = NEW.id AND session_data IS NOT NULL;
                END IF;
                
                -- If invitation is used (payment completed), clean up session data
                IF NEW.status = "used" AND OLD.status != "used" THEN
                    UPDATE apartment_invitations 
                    SET session_data = NULL, 
                        session_expires_at = NULL,
                        updated_at = NOW()
                    WHERE id = NEW.id AND session_data IS NOT NULL;
                END IF;
            END
        ');
        
        // Trigger to automatically update apartment occupancy when invitation is used
        DB::unprepared('
            CREATE TRIGGER update_apartment_occupancy
            AFTER UPDATE ON apartment_invitations
            FOR EACH ROW
            BEGIN
                -- When payment is completed, mark apartment as occupied
                IF NEW.payment_completed_at IS NOT NULL AND OLD.payment_completed_at IS NULL THEN
                    UPDATE apartments 
                    SET occupied = 1, 
                        updated_at = NOW()
                    WHERE id = NEW.apartment_id;
                END IF;
            END
        ');
        
        // Trigger for security logging of suspicious activities
        DB::unprepared('
            CREATE TRIGGER log_suspicious_invitation_activity
            AFTER UPDATE ON apartment_invitations
            FOR EACH ROW
            BEGIN
                -- Log when rate limit is exceeded
                IF NEW.rate_limit_count >= 50 AND OLD.rate_limit_count < 50 THEN
                    INSERT INTO activity_logs (user_id, action, description, ip_address, created_at, updated_at)
                    VALUES (NULL, "security_alert", 
                           CONCAT("Rate limit exceeded for invitation ID: ", NEW.id, " from IP: ", NEW.last_accessed_ip),
                           NEW.last_accessed_ip, NOW(), NOW());
                END IF;
                
                -- Log when access count is unusually high
                IF NEW.access_count >= 100 AND OLD.access_count < 100 THEN
                    INSERT INTO activity_logs (user_id, action, description, ip_address, created_at, updated_at)
                    VALUES (NULL, "security_alert", 
                           CONCAT("High access count (", NEW.access_count, ") for invitation ID: ", NEW.id),
                           NEW.last_accessed_ip, NOW(), NOW());
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop triggers
        DB::unprepared('DROP TRIGGER IF EXISTS log_suspicious_invitation_activity');
        DB::unprepared('DROP TRIGGER IF EXISTS update_apartment_occupancy');
        DB::unprepared('DROP TRIGGER IF EXISTS cleanup_expired_invitation_session');
        
        // Drop indexes from related tables
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_apartment_status');
            $table->dropIndex('idx_paid_at_status');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_registration_source');
            $table->dropIndex('idx_referred_by');
        });
        
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropIndex('idx_property_occupied');
            $table->dropIndex('idx_amount_occupied');
        });
    }
};
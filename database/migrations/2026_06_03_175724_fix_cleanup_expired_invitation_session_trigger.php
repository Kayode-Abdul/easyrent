<?php

use Illuminate\Database\Migrations\Migration;
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
        // Drop the problematic AFTER UPDATE trigger
        DB::unprepared('DROP TRIGGER IF EXISTS cleanup_expired_invitation_session');
        
        // Recreate it as a BEFORE UPDATE trigger to prevent recursive trigger error (1442)
        DB::unprepared('
            CREATE TRIGGER cleanup_expired_invitation_session
            BEFORE UPDATE ON apartment_invitations
            FOR EACH ROW
            BEGIN
                -- If invitation status changed to expired, clean up session data
                IF NEW.status = "expired" AND OLD.status != "expired" THEN
                    SET NEW.session_data = NULL;
                    SET NEW.session_expires_at = NULL;
                    SET NEW.updated_at = NOW();
                END IF;
                
                -- If invitation is used (payment completed), clean up session data
                IF NEW.status = "used" AND OLD.status != "used" THEN
                    SET NEW.session_data = NULL;
                    SET NEW.session_expires_at = NULL;
                    SET NEW.updated_at = NOW();
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
        DB::unprepared('DROP TRIGGER IF EXISTS cleanup_expired_invitation_session');
        
        // Restore the original problematic trigger
        DB::unprepared('
            CREATE TRIGGER cleanup_expired_invitation_session
            AFTER UPDATE ON apartment_invitations
            FOR EACH ROW
            BEGIN
                IF NEW.status = "expired" AND OLD.status != "expired" THEN
                    UPDATE apartment_invitations 
                    SET session_data = NULL, 
                        session_expires_at = NULL,
                        updated_at = NOW()
                    WHERE id = NEW.id AND session_data IS NOT NULL;
                END IF;
                
                IF NEW.status = "used" AND OLD.status != "used" THEN
                    UPDATE apartment_invitations 
                    SET session_data = NULL, 
                        session_expires_at = NULL,
                        updated_at = NOW()
                    WHERE id = NEW.id AND session_data IS NOT NULL;
                END IF;
            END
        ');
    }
};

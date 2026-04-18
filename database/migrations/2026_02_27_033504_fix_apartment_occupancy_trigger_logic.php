<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop the old trigger and create a fixed one
        DB::unprepared('DROP TRIGGER IF EXISTS update_apartment_occupancy');

        DB::unprepared('
            CREATE TRIGGER update_apartment_occupancy
            AFTER UPDATE ON apartment_invitations
            FOR EACH ROW
            BEGIN
                -- When payment is completed AND tenant is assigned, mark apartment as occupied
                -- This prevents apartments from appearing as rented without user details (guest flow)
                IF NEW.payment_completed_at IS NOT NULL AND OLD.payment_completed_at IS NULL AND NEW.tenant_user_id IS NOT NULL THEN
                    UPDATE apartments 
                    SET occupied = 1, 
                        tenant_id = NEW.tenant_user_id,
                        updated_at = NOW()
                    WHERE apartment_id = NEW.apartment_id;
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
        DB::unprepared('DROP TRIGGER IF EXISTS update_apartment_occupancy');

        // Revert to original (buggy but original) trigger if needed for rollback
        DB::unprepared('
            CREATE TRIGGER update_apartment_occupancy
            AFTER UPDATE ON apartment_invitations
            FOR EACH ROW
            BEGIN
                IF NEW.payment_completed_at IS NOT NULL AND OLD.payment_completed_at IS NULL THEN
                    UPDATE apartments 
                    SET occupied = 1, 
                        updated_at = NOW()
                    WHERE id = NEW.apartment_id;
                END IF;
            END
        ');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add Phase 1 features to benefactors table
        Schema::table('benefactors', function (Blueprint $table) {
            $table->enum('relationship_type', [
                'employer', 
                'parent', 
                'guardian', 
                'sponsor', 
                'organization',
                'other'
            ])->default('other')->after('phone');
            $table->boolean('is_registered')->default(false)->after('type');
        });

        // Add Phase 1 features to payment_invitations table
        Schema::table('payment_invitations', function (Blueprint $table) {
            $table->enum('approval_status', [
                'pending_approval',
                'approved', 
                'declined'
            ])->default('pending_approval')->after('status');
            $table->timestamp('approved_at')->nullable()->after('accepted_at');
            $table->timestamp('declined_at')->nullable()->after('approved_at');
            $table->text('decline_reason')->nullable()->after('declined_at');
        });

        // Add Phase 1 features to benefactor_payments table
        Schema::table('benefactor_payments', function (Blueprint $table) {
            $table->boolean('is_paused')->default(false)->after('status');
            $table->timestamp('paused_at')->nullable()->after('cancelled_at');
            $table->text('pause_reason')->nullable()->after('paused_at');
            $table->integer('payment_day_of_month')->nullable()->after('next_payment_date')
                ->comment('Day of month for recurring payments (1-31)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('benefactors', function (Blueprint $table) {
            $table->dropColumn(['relationship_type', 'is_registered']);
        });

        Schema::table('payment_invitations', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'approved_at', 'declined_at', 'decline_reason']);
        });

        Schema::table('benefactor_payments', function (Blueprint $table) {
            $table->dropColumn(['is_paused', 'paused_at', 'pause_reason', 'payment_day_of_month']);
        });
    }
};

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
        Schema::table('apartment_invitations', function (Blueprint $table) {
            $table->boolean('authentication_required')->default(false)->after('session_data');
            $table->string('registration_source')->nullable()->after('authentication_required');
            $table->timestamp('session_expires_at')->nullable()->after('registration_source');
            
            // Add indexes for performance
            $table->index('authentication_required');
            $table->index('registration_source');
            $table->index('session_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('apartment_invitations', function (Blueprint $table) {
            $table->dropIndex(['authentication_required']);
            $table->dropIndex(['registration_source']);
            $table->dropIndex(['session_expires_at']);
            
            $table->dropColumn([
                'authentication_required',
                'registration_source', 
                'session_expires_at'
            ]);
        });
    }
};

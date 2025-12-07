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
            // Access tracking fields
            $table->integer('access_count')->default(0)->after('session_expires_at');
            $table->timestamp('last_accessed_at')->nullable()->after('access_count');
            $table->string('last_accessed_ip', 45)->nullable()->after('last_accessed_at'); // IPv6 support
            
            // Security fields
            $table->string('security_hash')->nullable()->after('last_accessed_ip');
            
            // Rate limiting fields
            $table->integer('rate_limit_count')->default(0)->after('security_hash');
            $table->timestamp('rate_limit_reset_at')->nullable()->after('rate_limit_count');
            
            // Add indexes for performance
            $table->index('access_count');
            $table->index('last_accessed_at');
            $table->index('last_accessed_ip');
            $table->index('rate_limit_count');
            $table->index('rate_limit_reset_at');
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
            // Drop indexes first
            $table->dropIndex(['access_count']);
            $table->dropIndex(['last_accessed_at']);
            $table->dropIndex(['last_accessed_ip']);
            $table->dropIndex(['rate_limit_count']);
            $table->dropIndex(['rate_limit_reset_at']);
            
            // Drop columns
            $table->dropColumn([
                'access_count',
                'last_accessed_at',
                'last_accessed_ip',
                'security_hash',
                'rate_limit_count',
                'rate_limit_reset_at'
            ]);
        });
    }
};
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
        // We will not drop the role column automatically to maintain backward compatibility.
        // If you truly want to remove it, set USERS_DROP_ROLE_COLUMN=true in .env
        if (env('USERS_DROP_ROLE_COLUMN', false)) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'role')) {
                    $table->dropColumn('role');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No-op. If role was dropped under the flag, restoring it would need defaults and data migration.
    }
};

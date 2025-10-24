<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add legacy role integer if missing (for backward compatibility)
            if (!Schema::hasColumn('users', 'role')) {
                $table->unsignedTinyInteger('role')->default(3)->after('email');
            }
            // Add region if missing
            if (!Schema::hasColumn('users', 'region')) {
                $table->string('region')->nullable()->after('lga');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'region')) {
                $table->dropColumn('region');
            }
            // Do not drop role by default here to avoid breaking legacy paths
        });
    }
};
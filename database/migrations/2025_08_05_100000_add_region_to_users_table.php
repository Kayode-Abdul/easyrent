<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'region')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('region')->nullable()->after('lga');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'region')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('region');
            });
        }
    }
};

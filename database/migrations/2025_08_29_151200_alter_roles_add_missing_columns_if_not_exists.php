<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterRolesAddMissingColumnsIfNotExists extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (!Schema::hasColumn('roles', 'name')) {
                    $table->string('name')->unique()->after('id');
                }
                if (!Schema::hasColumn('roles', 'display_name')) {
                    $table->string('display_name')->nullable()->after('name');
                }
                if (!Schema::hasColumn('roles', 'description')) {
                    $table->text('description')->nullable()->after('display_name');
                }
                if (!Schema::hasColumn('roles', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('description');
                }
                if (!Schema::hasColumn('roles', 'permissions')) {
                    $table->json('permissions')->nullable()->after('is_active');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (Schema::hasColumn('roles', 'permissions')) {
                    $table->dropColumn('permissions');
                }
                if (Schema::hasColumn('roles', 'is_active')) {
                    $table->dropColumn('is_active');
                }
                if (Schema::hasColumn('roles', 'description')) {
                    $table->dropColumn('description');
                }
                if (Schema::hasColumn('roles', 'display_name')) {
                    $table->dropColumn('display_name');
                }
                if (Schema::hasColumn('roles', 'name')) {
                    $table->dropColumn('name');
                }
            });
        }
    }
}

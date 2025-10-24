<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('role_user')) {
            return;
        }

        Schema::table('role_user', function (Blueprint $table) {
            if (!Schema::hasColumn('role_user', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('role_user', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('user_id');
            }
        });

        // Add indexes/constraints in a safe way
        try {
            // Unique pair to prevent duplicates
            DB::statement('ALTER TABLE role_user ADD UNIQUE role_user_user_id_role_id_unique (user_id, role_id)');
        } catch (\Throwable $e) {
            // ignore if exists
        }

        // Add foreign keys if not present
        try {
            DB::statement('ALTER TABLE role_user ADD CONSTRAINT role_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE role_user ADD CONSTRAINT role_user_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE');
        } catch (\Throwable $e) {}

        // Ensure not null after adding and backfilling if necessary
        Schema::table('role_user', function (Blueprint $table) {
            if (Schema::hasColumn('role_user', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            }
            if (Schema::hasColumn('role_user', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable(false)->change();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('role_user')) {
            return;
        }

        // Drop constraints if they exist
        try {
            DB::statement('ALTER TABLE role_user DROP FOREIGN KEY role_user_user_id_foreign');
        } catch (\Throwable $e) {}
        try {
            DB::statement('ALTER TABLE role_user DROP FOREIGN KEY role_user_role_id_foreign');
        } catch (\Throwable $e) {}
        try {
            DB::statement('ALTER TABLE role_user DROP INDEX role_user_user_id_role_id_unique');
        } catch (\Throwable $e) {}
    }
};

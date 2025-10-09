<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('regional_scopes')) {
            Schema::create('regional_scopes', function (Blueprint $table) {
                $table->id();
                // Link to users.user_id (custom PK)
                $table->unsignedBigInteger('user_id');
                $table->string('scope_type', 20)->default('state'); // state | lga | city
                $table->string('scope_value', 100);
                $table->timestamps();

                $table->index(['user_id', 'scope_type']);
                $table->unique(['user_id', 'scope_type', 'scope_value'], 'regional_scope_unique');
            });
        }

        // Add FK separately to avoid issues if users table uses non-standard PK name
        try {
            Schema::table('regional_scopes', function (Blueprint $table) {
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // Ignore FK issues on legacy DBs; unique index still ensures integrity
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('regional_scopes');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_invitations', function (Blueprint $table) {
            // Make benefactor_email nullable to support link sharing without email
            $table->string('benefactor_email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_invitations', function (Blueprint $table) {
            // Revert benefactor_email to not nullable
            $table->string('benefactor_email')->nullable(false)->change();
        });
    }
};
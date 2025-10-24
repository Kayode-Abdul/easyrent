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
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('audit_type')->nullable()->after('id');
            $table->string('reference_type')->nullable()->after('audit_type');
            $table->bigInteger('reference_id')->nullable()->after('reference_type');
            $table->json('audit_data')->nullable()->after('reference_id');
            
            $table->index('audit_type');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['audit_type', 'reference_type', 'reference_id', 'audit_data']);
        });
    }
};

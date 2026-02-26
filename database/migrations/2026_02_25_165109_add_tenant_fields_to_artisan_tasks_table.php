<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('artisan_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('landlord_id');
            $table->foreign('tenant_id')->references('user_id')->on('users')->onDelete('set null');
            $table->boolean('request_setoff')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('artisan_tasks', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'request_setoff']);
        });
    }
};
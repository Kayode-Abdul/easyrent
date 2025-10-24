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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('flagged_for_review')->default(false)->after('email_verified_at');
            $table->json('flag_reasons')->nullable()->after('flagged_for_review');
            $table->timestamp('flagged_at')->nullable()->after('flag_reasons');
            
            $table->index('flagged_for_review');
            $table->index('flagged_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['flagged_for_review', 'flag_reasons', 'flagged_at']);
        });
    }
};

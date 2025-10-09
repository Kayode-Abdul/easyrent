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
        Schema::table('referrals', function (Blueprint $table) {
            $table->boolean('is_flagged')->default(false);
            $table->json('flag_reasons')->nullable();
            $table->timestamp('flagged_at')->nullable();
            
            $table->index('is_flagged');
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
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropColumn(['is_flagged', 'flag_reasons', 'flagged_at']);
        });
    }
};

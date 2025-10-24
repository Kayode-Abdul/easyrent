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
        Schema::create('payment_tracking', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('payment_id')->unsigned();
            $table->string('status', 50);
            $table->json('metadata')->nullable();
            $table->timestamp('tracked_at');
            $table->timestamps();
            
            $table->index(['payment_id', 'status']);
            $table->index('tracked_at');
            $table->foreign('payment_id')->references('id')->on('commission_payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_tracking');
    }
};

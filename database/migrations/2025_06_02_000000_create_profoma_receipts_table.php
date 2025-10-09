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
        Schema::create('profoma_receipt', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // The owner/landlord
            $table->unsignedBigInteger('tenant_id'); // The tenant (users.user_id)
            $table->unsignedTinyInteger('status')->default(0); // 0, 1, or 2 only
            $table->string('transaction_id')->unique();
            $table->unsignedBigInteger('apartment_id');
            $table->timestamps();

            // Foreign keys (optional, but recommended)
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('tenant_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profoma_receipt');
    }
};

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
        Schema::create('marketer_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('business_name')->nullable();
            $table->string('business_type', 100)->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->decimal('preferred_commission_rate', 5, 2)->nullable();
            $table->text('marketing_channels')->nullable();
            $table->json('target_regions')->nullable();
            $table->enum('kyc_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('kyc_documents')->nullable();
            $table->text('bio')->nullable();
            $table->string('website')->nullable();
            $table->string('social_media_handles')->nullable();
            $table->integer('total_referrals')->default(0);
            $table->decimal('total_commission_earned', 10, 2)->default(0.00);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('kyc_status');
            $table->index('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marketer_profiles');
    }
};

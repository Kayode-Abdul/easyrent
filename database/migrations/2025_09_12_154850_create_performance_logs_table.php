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
        Schema::create('performance_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('method', 10);
            $table->string('url', 500);
            $table->string('route_name')->nullable();
            $table->string('controller_action')->nullable();
            $table->integer('status_code');
            $table->decimal('execution_time', 8, 2);
            $table->bigInteger('memory_usage');
            $table->integer('query_count');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index('execution_time');
            $table->index('created_at');
            $table->index('status_code');
            // Foreign key constraint removed for now due to data type mismatch
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('performance_logs');
    }
};

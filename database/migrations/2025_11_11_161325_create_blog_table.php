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
        Schema::create('blog', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->string('topic_url')->unique();
            $table->text('content');
            $table->text('excerpt')->nullable();
            $table->string('cover_photo')->nullable();
            $table->string('author')->default('Admin');
            $table->boolean('published')->default(true);
            $table->timestamp('date')->useCurrent();
            $table->string('hide')->nullable(); // For soft delete compatibility
            $table->timestamps();
            
            $table->index(['published', 'date']);
            $table->index('topic_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blog');
    }
};

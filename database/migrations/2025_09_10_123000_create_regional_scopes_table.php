<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if(!Schema::hasTable('regional_scopes')) {
            Schema::create('regional_scopes', function(Blueprint $table){
                $table->id();
                $table->unsignedBigInteger('user_id'); // regional manager user_id
                $table->string('state');
                $table->string('lga')->nullable();
                $table->timestamps();
                $table->unique(['user_id','state','lga']);
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            });
        }
    }
    public function down(): void
    {
        Schema::dropIfExists('regional_scopes');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('regional_scopes')) {
            Schema::create('regional_scopes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id'); // references users.user_id
                $table->enum('scope_type', ['state','lga'])->nullable();
                $table->string('scope_value')->nullable(); // e.g. Lagos or Lagos::Ikeja for lga
                $table->timestamps();

                $table->unique(['user_id','scope_type','scope_value']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('regional_scopes');
    }
};

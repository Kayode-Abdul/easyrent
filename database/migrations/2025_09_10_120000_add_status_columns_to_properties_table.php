<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('properties', function(Blueprint $table){
            if(!Schema::hasColumn('properties','status')) {
                $table->string('status',20)->default('pending')->after('agent_id');
            }
            if(!Schema::hasColumn('properties','approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('status');
            }
            if(!Schema::hasColumn('properties','rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function(Blueprint $table){
            if(Schema::hasColumn('properties','rejected_at')) $table->dropColumn('rejected_at');
            if(Schema::hasColumn('properties','approved_at')) $table->dropColumn('approved_at');
            if(Schema::hasColumn('properties','status')) $table->dropColumn('status');
        });
    }
};

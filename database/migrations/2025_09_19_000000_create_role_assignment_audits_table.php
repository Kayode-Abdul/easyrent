<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_assignment_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable(); // admin performing action
            $table->unsignedBigInteger('user_id'); // target user
            $table->unsignedBigInteger('role_id')->nullable(); // modern role id if applicable
            $table->string('legacy_role')->nullable(); // legacy numeric or label
            $table->string('action'); // assigned / rejected / blocked
            $table->text('reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['user_id']);
            $table->index(['role_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('role_assignment_audits');
    }
};

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
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'status')) {
                $table->string('status')->default('available')->after('lga');
            }
            if (!Schema::hasColumn('properties', 'suspension_reason')) {
                $table->text('suspension_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('properties', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('suspension_reason');
            }
            if (!Schema::hasColumn('properties', 'suspended_by')) {
                $table->unsignedBigInteger('suspended_by')->nullable()->after('suspended_at');
            }
            if (!Schema::hasColumn('properties', 'reactivated_at')) {
                $table->timestamp('reactivated_at')->nullable()->after('suspended_by');
            }
            if (!Schema::hasColumn('properties', 'reactivated_by')) {
                $table->unsignedBigInteger('reactivated_by')->nullable()->after('reactivated_at');
            }
        });

        // Add foreign keys separately to avoid issues
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'suspended_by') && !$this->foreignKeyExists('properties', 'properties_suspended_by_foreign')) {
                $table->foreign('suspended_by')->references('user_id')->on('users')->onDelete('set null');
            }
            if (Schema::hasColumn('properties', 'reactivated_by') && !$this->foreignKeyExists('properties', 'properties_reactivated_by_foreign')) {
                $table->foreign('reactivated_by')->references('user_id')->on('users')->onDelete('set null');
            }
        });
    }

    private function foreignKeyExists($table, $name)
    {
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        $foreignKeys = $dbSchemaManager->listTableForeignKeys($table);
        
        foreach ($foreignKeys as $foreignKey) {
            if ($foreignKey->getName() === $name) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['suspended_by']);
            $table->dropForeign(['reactivated_by']);
            $table->dropColumn([
                'status',
                'suspension_reason',
                'suspended_at',
                'suspended_by',
                'reactivated_at',
                'reactivated_by'
            ]);
        });
    }
};

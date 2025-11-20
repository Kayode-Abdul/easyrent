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
        // Drop the foreign key constraints if they exist
        // Since user_id is the actual identifier (not id), we can't use foreign keys
        // We'll rely on application-level integrity instead
        
        try {
            Schema::table('payment_invitations', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
            });
        } catch (\Exception $e) {
            // Foreign key doesn't exist, that's fine
        }

        try {
            Schema::table('benefactor_payments', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
            });
        } catch (\Exception $e) {
            // Foreign key doesn't exist, that's fine
        }

        // Add indexes for performance (without foreign key constraints)
        Schema::table('payment_invitations', function (Blueprint $table) {
            if (!$this->hasIndex('payment_invitations', 'payment_invitations_tenant_id_index')) {
                $table->index('tenant_id');
            }
        });

        Schema::table('benefactor_payments', function (Blueprint $table) {
            if (!$this->hasIndex('benefactor_payments', 'benefactor_payments_tenant_id_index')) {
                $table->index('tenant_id');
            }
        });
    }

    /**
     * Check if an index exists
     */
    private function hasIndex($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEXES FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove indexes
        Schema::table('payment_invitations', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });

        Schema::table('benefactor_payments', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
        });

        // Restore original foreign keys
        Schema::table('payment_invitations', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('benefactor_payments', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};

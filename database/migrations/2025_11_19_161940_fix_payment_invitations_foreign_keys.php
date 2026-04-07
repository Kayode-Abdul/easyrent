<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $driver = DB::getDriverName();
        
        // Drop the foreign key constraints if they exist (MySQL/PostgreSQL only)
        // Since user_id is the actual identifier (not id), we can't use foreign keys
        // We'll rely on application-level integrity instead
        
        if ($driver !== 'sqlite') {
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
        }

        // Add indexes for performance (without foreign key constraints)
        if (Schema::hasTable('payment_invitations')) {
            Schema::table('payment_invitations', function (Blueprint $table) {
                if (!$this->hasIndex('payment_invitations', 'payment_invitations_tenant_id_index')) {
                    $table->index('tenant_id');
                }
            });
        }

        if (Schema::hasTable('benefactor_payments')) {
            Schema::table('benefactor_payments', function (Blueprint $table) {
                if (!$this->hasIndex('benefactor_payments', 'benefactor_payments_tenant_id_index')) {
                    $table->index('tenant_id');
                }
            });
        }
    }

    /**
     * Check if an index exists (database-agnostic)
     */
    private function hasIndex($table, $indexName)
    {
        $driver = DB::getDriverName();
        
        try {
            if ($driver === 'sqlite') {
                // SQLite: Check sqlite_master table
                $indexes = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name = ? AND tbl_name = ?", [$indexName, $table]);
                return count($indexes) > 0;
            } else {
                // MySQL/PostgreSQL: Use SHOW INDEXES
                $indexes = DB::select("SHOW INDEXES FROM {$table} WHERE Key_name = ?", [$indexName]);
                return count($indexes) > 0;
            }
        } catch (\Exception $e) {
            // If query fails, assume index doesn't exist
            return false;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $driver = DB::getDriverName();
        
        // Remove indexes
        try {
            if (Schema::hasTable('payment_invitations')) {
                Schema::table('payment_invitations', function (Blueprint $table) {
                    $table->dropIndex(['tenant_id']);
                });
            }
        } catch (\Exception $e) {
            // Index doesn't exist, that's fine
        }

        try {
            if (Schema::hasTable('benefactor_payments')) {
                Schema::table('benefactor_payments', function (Blueprint $table) {
                    $table->dropIndex(['tenant_id']);
                });
            }
        } catch (\Exception $e) {
            // Index doesn't exist, that's fine
        }

        // Restore original foreign keys (MySQL/PostgreSQL only)
        if ($driver !== 'sqlite') {
            try {
                Schema::table('payment_invitations', function (Blueprint $table) {
                    $table->foreign('tenant_id')->references('id')->on('users')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Foreign key creation failed, that's fine for rollback
            }

            try {
                Schema::table('benefactor_payments', function (Blueprint $table) {
                    $table->foreign('tenant_id')->references('id')->on('users')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Foreign key creation failed, that's fine for rollback
            }
        }
    }
};

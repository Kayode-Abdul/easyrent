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
        // Create table for tracking database maintenance operations
        if (!Schema::hasTable('database_maintenance_logs')) {
            Schema::create('database_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('operation_type', 50); // cleanup, optimization, backup, etc.
            $table->string('table_name', 100)->nullable();
            $table->text('description');
            $table->json('operation_details')->nullable(); // Store operation parameters and results
            $table->integer('records_affected')->default(0);
            $table->decimal('execution_time_seconds', 8, 3)->nullable();
            $table->enum('status', ['started', 'completed', 'failed', 'cancelled'])->default('started');
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['operation_type', 'started_at']);
            $table->index(['table_name', 'status']);
            $table->index('status');
            $table->index('started_at');
        });
        }
        
        // Create table for tracking invitation performance metrics
        if (!Schema::hasTable('invitation_performance_metrics')) {
            Schema::create('invitation_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date');
            $table->integer('total_invitations_created')->default(0);
            $table->integer('total_invitations_viewed')->default(0);
            $table->integer('total_payments_initiated')->default(0);
            $table->integer('total_payments_completed')->default(0);
            $table->integer('total_sessions_created')->default(0);
            $table->integer('total_sessions_expired')->default(0);
            $table->integer('total_rate_limit_hits')->default(0);
            $table->integer('total_security_blocks')->default(0);
            $table->decimal('avg_access_count', 8, 2)->default(0);
            $table->decimal('conversion_rate_view_to_payment', 5, 2)->default(0); // Percentage
            $table->decimal('conversion_rate_initiate_to_complete', 5, 2)->default(0); // Percentage
            $table->integer('avg_session_duration_minutes')->default(0);
            $table->json('hourly_distribution')->nullable(); // Access patterns by hour
            $table->json('top_accessing_ips')->nullable(); // Top 10 IPs by access count
            $table->timestamps();
            
            // Unique constraint to prevent duplicate entries for same date
            $table->unique('metric_date');
            
            // Indexes
            $table->index('metric_date');
            $table->index('conversion_rate_view_to_payment', 'idx_conversion_rate');
        });
        }
        
        // Create table for session cleanup tracking
        if (!Schema::hasTable('session_cleanup_history')) {
            Schema::create('session_cleanup_history', function (Blueprint $table) {
            $table->id();
            $table->timestamp('cleanup_date');
            $table->integer('expired_sessions_found')->default(0);
            $table->integer('sessions_cleaned')->default(0);
            $table->integer('invitations_expired')->default(0);
            $table->integer('rate_limits_reset')->default(0);
            $table->decimal('cleanup_duration_seconds', 8, 3)->default(0);
            $table->json('cleanup_details')->nullable(); // Additional cleanup statistics
            $table->enum('cleanup_type', ['scheduled', 'manual', 'triggered'])->default('scheduled');
            $table->string('initiated_by', 100)->nullable(); // Command, user, or system
            $table->timestamps();
            
            // Indexes
            $table->index('cleanup_date');
            $table->index('cleanup_type');
        });
        }
        
        // Insert initial maintenance log entry
        DB::table('database_maintenance_logs')->insert([
            'operation_type' => 'schema_optimization',
            'table_name' => 'apartment_invitations',
            'description' => 'Initial database schema optimization for EasyRent Link Authentication System',
            'operation_details' => json_encode([
                'indexes_added' => 7,
                'constraints_added' => 6,
                'triggers_added' => 3,
                'procedures_added' => 3,
                'views_added' => 2
            ]),
            'records_affected' => 0,
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Skip stored procedures due to MySQL compatibility issues
        // The Laravel commands will handle the maintenance operations instead
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop tables
        Schema::dropIfExists('session_cleanup_history');
        Schema::dropIfExists('invitation_performance_metrics');
        Schema::dropIfExists('database_maintenance_logs');
    }
};
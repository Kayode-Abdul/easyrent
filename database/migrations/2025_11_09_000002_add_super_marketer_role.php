<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First ensure the roles table has the necessary columns
        if (!Schema::hasColumn('roles', 'is_active')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('description');
            });
        }

        if (!Schema::hasColumn('roles', 'permissions')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->json('permissions')->nullable()->after('is_active');
            });
        }

        // Insert the Super Marketer role with ID 9
        $now = now();
        
        // Check if role with ID 9 already exists
        $existingRole = DB::table('roles')->where('id', 9)->first();
        
        if (!$existingRole) {
            DB::table('roles')->insert([
                'id' => 9,
                'name' => 'super_marketer',
                'display_name' => 'Super Marketer',
                'description' => 'Top-tier marketer who can refer other marketers',
                'is_active' => true,
                'permissions' => json_encode([
                    'refer_marketers',
                    'view_referral_analytics',
                    'manage_referral_campaigns',
                    'view_commission_breakdown'
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            // Update existing role if it exists but has different data
            DB::table('roles')->where('id', 9)->update([
                'name' => 'super_marketer',
                'display_name' => 'Super Marketer',
                'description' => 'Top-tier marketer who can refer other marketers',
                'is_active' => true,
                'permissions' => json_encode([
                    'refer_marketers',
                    'view_referral_analytics',
                    'manage_referral_campaigns',
                    'view_commission_breakdown'
                ]),
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the Super Marketer role
        DB::table('roles')->where('id', 9)->delete();
        
        // Note: We don't remove the added columns as they might be used by other roles
    }
};
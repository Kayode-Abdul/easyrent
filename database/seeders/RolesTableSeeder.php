<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('roles')) {
            $this->command?->warn('roles table does not exist; skipping RolesTableSeeder');
            return;
        }

        $now = now();
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Full system access', 'is_active' => true, 'permissions' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'regional_manager', 'display_name' => 'Regional Manager', 'description' => 'Manages region-specific operations', 'is_active' => true, 'permissions' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'marketer', 'display_name' => 'Marketer', 'description' => 'Handles marketing tasks', 'is_active' => true, 'permissions' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'landlord', 'display_name' => 'Landlord', 'description' => 'Property owner', 'is_active' => true, 'permissions' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'tenant', 'display_name' => 'Tenant', 'description' => 'Rents properties', 'is_active' => true, 'permissions' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'property_manager', 'display_name' => 'Property Manager', 'description' => 'Manages properties', 'is_active' => true, 'permissions' => null, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'super_marketer', 'display_name' => 'Super Marketer', 'description' => 'Top-tier marketer who can refer other marketers', 'is_active' => true, 'permissions' => json_encode(['refer_marketers', 'view_referral_analytics', 'manage_referral_campaigns', 'view_commission_breakdown']), 'created_at' => $now, 'updated_at' => $now],
        ];

        // Use upsert to avoid duplicate inserts on subsequent runs
        DB::table('roles')->upsert($roles, ['name'], ['display_name', 'description', 'is_active', 'permissions', 'updated_at']);

        $this->command?->info('Roles table seeded or updated.');
    }
}

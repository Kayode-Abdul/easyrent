<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $roles = [
            [
                'id' => 1,
                'name' => 'tenant',
                'display_name' => 'Tenant',
                'description' => 'Rents properties',
                'is_active' => 1,
                'permissions' => null,
                'created_at' => '2025-08-29 15:26:53',
                'updated_at' => '2025-08-29 15:26:53'
            ],
            [
                'id' => 2,
                'name' => 'landlord',
                'display_name' => 'Landlord',
                'description' => 'Property owner',
                'is_active' => 1,
                'permissions' => null,
                'created_at' => '2025-08-29 15:26:53',
                'updated_at' => '2025-08-29 15:26:53'
            ],
            [
                'id' => 3,
                'name' => 'marketer',
                'display_name' => 'Marketer',
                'description' => 'Handles marketing tasks',
                'is_active' => 1,
                'permissions' => null,
                'created_at' => '2025-08-29 15:26:53',
                'updated_at' => '2025-08-29 15:26:53'
            ],
            [
                'id' => 4,
                'name' => 'super_marketer',
                'display_name' => 'Super Marketer',
                'description' => 'Top-tier marketer who can refer other marketers',
                'is_active' => 1,
                'permissions' => json_encode([
                    'refer_marketers',
                    'view_referral_analytics',
                    'manage_referral_campaigns',
                    'view_commission_breakdown'
                ]),
                'created_at' => '2025-09-12 08:34:40',
                'updated_at' => '2025-09-12 08:34:40'
            ],
            [
                'id' => 5,
                'name' => 'Artisan',
                'display_name' => 'Artisan',
                'description' => null,
                'is_active' => 1,
                'permissions' => null,
                'created_at' => '2025-08-05 17:31:30',
                'updated_at' => '2025-08-05 17:31:30'
            ],
            [
                'id' => 6,
                'name' => 'property_manager',
                'display_name' => 'Property Manager',
                'description' => 'Manages properties',
                'is_active' => 1,
                'permissions' => null,
                'created_at' => '2025-08-29 15:26:53',
                'updated_at' => '2025-08-29 15:26:53'
            ],
            [
                'id' => 7,
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access',
                'is_active' => 1,
                'permissions' => null,
                'created_at' => '2025-08-29 15:26:53',
                'updated_at' => '2025-08-29 15:26:53'
            ],
            [
                'id' => 8,
                'name' => 'Verified_Property_Manager',
                'display_name' => 'Verified Property Manager',
                'description' => 'Recognised by the company',
                'is_active' => 1,
                'permissions' => null,
                'created_at' => '2025-08-13 12:21:42',
                'updated_at' => '2025-08-13 12:21:42'
            ],
            [
                'id' => 9,
                'name' => 'regional_manager',
                'display_name' => 'Regional Manager',
                'description' => 'Manages region-specific operations',
                'is_active' => 1,
                'permissions' => null,
                'created_at' => '2025-08-29 15:26:53',
                'updated_at' => '2025-08-29 15:26:53'
            ]
        ];

        // Process each role
        foreach ($roles as $role) {
            // Check if role exists by name
            $existingByName = DB::table('roles')->where('name', $role['name'])->first();
            
            if ($existingByName) {
                // Update existing role by name
                DB::table('roles')->where('name', $role['name'])->update([
                    'display_name' => $role['display_name'],
                    'description' => $role['description'],
                    'is_active' => $role['is_active'],
                    'permissions' => $role['permissions'],
                    'updated_at' => $now
                ]);
                
                $this->command->info("Updated role: {$role['name']} (ID: {$existingByName->id})");
            } else {
                // Check if the ID is taken by another role
                $existingById = DB::table('roles')->where('id', $role['id'])->first();
                
                if ($existingById) {
                    // ID is taken, insert without specifying ID
                    $roleData = $role;
                    unset($roleData['id']);
                    DB::table('roles')->insert($roleData);
                    $this->command->info("Inserted role: {$role['name']} (new ID assigned)");
                } else {
                    // Insert with specified ID
                    DB::table('roles')->insert($role);
                    $this->command->info("Inserted role: {$role['name']} (ID: {$role['id']})");
                }
            }
        }

        $this->command->info('Roles seeded successfully!');
    }
}

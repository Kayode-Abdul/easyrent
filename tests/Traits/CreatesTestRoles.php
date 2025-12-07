<?php

namespace Tests\Traits;

use App\Models\Role;

trait CreatesTestRoles
{
    /**
     * Create or retrieve test roles
     */
    protected function createTestRoles(): void
    {
        Role::firstOrCreate(['id' => 1], ['name' => 'admin', 'description' => 'Administrator']);
        Role::firstOrCreate(['id' => 2], ['name' => 'landlord', 'description' => 'Landlord']);
        Role::firstOrCreate(['id' => 3], ['name' => 'marketer', 'description' => 'Marketer']);
        Role::firstOrCreate(['id' => 5], ['name' => 'regional_manager', 'description' => 'Regional Manager']);
        Role::firstOrCreate(['id' => 7], ['name' => 'Marketer', 'description' => 'Marketer Role']);
        Role::firstOrCreate(['id' => 8], ['name' => 'Regional Manager', 'description' => 'Regional Manager Role']);
        Role::firstOrCreate(['id' => 9], ['name' => 'super_marketer', 'description' => 'Super Marketer']);
    }
}

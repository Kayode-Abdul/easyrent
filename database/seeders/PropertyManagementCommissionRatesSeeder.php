<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CommissionRate;
use Carbon\Carbon;

class PropertyManagementCommissionRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = ['default', 'lagos', 'abuja', 'kano', 'port_harcourt', 'ibadan'];
        
        foreach ($regions as $region) {
            // Unmanaged Properties - Without Super Marketer
            CommissionRate::create([
                'region' => $region,
                'property_management_status' => 'unmanaged',
                'hierarchy_status' => 'without_super_marketer',
                'super_marketer_rate' => null,
                'marketer_rate' => 1.500,
                'regional_manager_rate' => 0.250,
                'company_rate' => 3.250,
                'total_commission_rate' => 5.000,
                'description' => 'Unmanaged properties without Super Marketer',
                'role_id' => 1, // Default role
                'commission_percentage' => 5.0000,
                'effective_from' => Carbon::now(),
                'created_by' => 1, // Admin user
                'is_active' => true,
                'last_updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // Unmanaged Properties - With Super Marketer
            CommissionRate::create([
                'region' => $region,
                'property_management_status' => 'unmanaged',
                'hierarchy_status' => 'with_super_marketer',
                'super_marketer_rate' => 0.500,
                'marketer_rate' => 1.000,
                'regional_manager_rate' => 0.250,
                'company_rate' => 3.250,
                'total_commission_rate' => 5.000,
                'description' => 'Unmanaged properties with Super Marketer',
                'role_id' => 1, // Default role
                'commission_percentage' => 5.0000,
                'effective_from' => Carbon::now(),
                'created_by' => 1, // Admin user
                'is_active' => true,
                'last_updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // Managed Properties - Without Super Marketer
            CommissionRate::create([
                'region' => $region,
                'property_management_status' => 'managed',
                'hierarchy_status' => 'without_super_marketer',
                'super_marketer_rate' => null,
                'marketer_rate' => 0.750,
                'regional_manager_rate' => 0.100,
                'company_rate' => 1.650,
                'total_commission_rate' => 2.500,
                'description' => 'Managed properties without Super Marketer',
                'role_id' => 1, // Default role
                'commission_percentage' => 2.5000,
                'effective_from' => Carbon::now(),
                'created_by' => 1, // Admin user
                'is_active' => true,
                'last_updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // Managed Properties - With Super Marketer
            CommissionRate::create([
                'region' => $region,
                'property_management_status' => 'managed',
                'hierarchy_status' => 'with_super_marketer',
                'super_marketer_rate' => 0.250,
                'marketer_rate' => 0.500,
                'regional_manager_rate' => 0.100,
                'company_rate' => 1.650,
                'total_commission_rate' => 2.500,
                'description' => 'Managed properties with Super Marketer',
                'role_id' => 1, // Default role
                'commission_percentage' => 2.5000,
                'effective_from' => Carbon::now(),
                'created_by' => 1, // Admin user
                'is_active' => true,
                'last_updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommissionRatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Get any existing user or use a default ID
        $anyUser = DB::table('users')->select('user_id')->first();
        $createdBy = $anyUser ? $anyUser->user_id : 1;

        $commissionRates = [
            // Default region
            [
                'id' => 3,
                'region' => 'default',
                'property_management_status' => 'unmanaged',
                'hierarchy_status' => 'without_super_marketer',
                'super_marketer_rate' => null,
                'marketer_rate' => 1.500,
                'regional_manager_rate' => 0.250,
                'company_rate' => 3.250,
                'total_commission_rate' => 5.000,
                'description' => 'Unmanaged properties without Super Marketer',
                'last_updated_at' => '2025-09-15 15:44:14',
                'updated_by' => null,
                'role_id' => 1,
                'commission_percentage' => 5.0000,
                'effective_from' => '2025-09-15 15:44:14',
                'effective_until' => null,
                'created_by' => $createdBy,
                'is_active' => 1,
                'created_at' => '2025-09-15 15:44:14',
                'updated_at' => '2025-09-15 15:44:14'
            ],
            [
                'id' => 4,
                'region' => 'default',
                'property_management_status' => 'unmanaged',
                'hierarchy_status' => 'with_super_marketer',
                'super_marketer_rate' => 0.500,
                'marketer_rate' => 1.000,
                'regional_manager_rate' => 0.250,
                'company_rate' => 3.250,
                'total_commission_rate' => 5.000,
                'description' => 'Unmanaged properties with Super Marketer',
                'last_updated_at' => '2025-09-15 15:44:14',
                'updated_by' => null,
                'role_id' => 1,
                'commission_percentage' => 5.0000,
                'effective_from' => '2025-09-15 15:44:14',
                'effective_until' => null,
                'created_by' => $createdBy,
                'is_active' => 1,
                'created_at' => '2025-09-15 15:44:14',
                'updated_at' => '2025-09-15 15:44:14'
            ],
            [
                'id' => 5,
                'region' => 'default',
                'property_management_status' => 'managed',
                'hierarchy_status' => 'without_super_marketer',
                'super_marketer_rate' => null,
                'marketer_rate' => 0.750,
                'regional_manager_rate' => 0.100,
                'company_rate' => 1.650,
                'total_commission_rate' => 2.500,
                'description' => 'Managed properties without Super Marketer',
                'last_updated_at' => '2025-09-15 15:44:14',
                'updated_by' => null,
                'role_id' => 1,
                'commission_percentage' => 2.5000,
                'effective_from' => '2025-09-15 15:44:14',
                'effective_until' => null,
                'created_by' => $createdBy,
                'is_active' => 1,
                'created_at' => '2025-09-15 15:44:14',
                'updated_at' => '2025-09-15 15:44:14'
            ],
            [
                'id' => 6,
                'region' => 'default',
                'property_management_status' => 'managed',
                'hierarchy_status' => 'with_super_marketer',
                'super_marketer_rate' => 0.250,
                'marketer_rate' => 0.500,
                'regional_manager_rate' => 0.100,
                'company_rate' => 1.650,
                'total_commission_rate' => 2.500,
                'description' => 'Managed properties with Super Marketer',
                'last_updated_at' => '2025-09-15 15:44:14',
                'updated_by' => null,
                'role_id' => 1,
                'commission_percentage' => 2.5000,
                'effective_from' => '2025-09-15 15:44:14',
                'effective_until' => null,
                'created_by' => $createdBy,
                'is_active' => 1,
                'created_at' => '2025-09-15 15:44:14',
                'updated_at' => '2025-09-15 15:44:14'
            ],
        ];

        // Add rates for other regions (lagos, abuja, kano, port_harcourt, ibadan)
        $regions = ['lagos', 'abuja', 'kano', 'port_harcourt', 'ibadan'];
        $baseId = 7;

        foreach ($regions as $region) {
            // Unmanaged without super marketer
            $commissionRates[] = [
                'id' => $baseId++,
                'region' => $region,
                'property_management_status' => 'unmanaged',
                'hierarchy_status' => 'without_super_marketer',
                'super_marketer_rate' => null,
                'marketer_rate' => 1.500,
                'regional_manager_rate' => 0.250,
                'company_rate' => 3.250,
                'total_commission_rate' => 5.000,
                'description' => 'Unmanaged properties without Super Marketer',
                'last_updated_at' => '2025-09-15 15:44:14',
                'updated_by' => null,
                'role_id' => 1,
                'commission_percentage' => 5.0000,
                'effective_from' => '2025-09-15 15:44:14',
                'effective_until' => null,
                'created_by' => $createdBy,
                'is_active' => 1,
                'created_at' => '2025-09-15 15:44:14',
                'updated_at' => '2025-09-15 15:44:14'
            ];

            // Unmanaged with super marketer
            $commissionRates[] = [
                'id' => $baseId++,
                'region' => $region,
                'property_management_status' => 'unmanaged',
                'hierarchy_status' => 'with_super_marketer',
                'super_marketer_rate' => 0.500,
                'marketer_rate' => 1.000,
                'regional_manager_rate' => 0.250,
                'company_rate' => 3.250,
                'total_commission_rate' => 5.000,
                'description' => 'Unmanaged properties with Super Marketer',
                'last_updated_at' => '2025-09-15 15:44:14',
                'updated_by' => null,
                'role_id' => 1,
                'commission_percentage' => 5.0000,
                'effective_from' => '2025-09-15 15:44:14',
                'effective_until' => null,
                'created_by' => $createdBy,
                'is_active' => 1,
                'created_at' => '2025-09-15 15:44:14',
                'updated_at' => '2025-09-15 15:44:14'
            ];

            // Managed without super marketer
            $commissionRates[] = [
                'id' => $baseId++,
                'region' => $region,
                'property_management_status' => 'managed',
                'hierarchy_status' => 'without_super_marketer',
                'super_marketer_rate' => null,
                'marketer_rate' => 0.750,
                'regional_manager_rate' => 0.100,
                'company_rate' => 1.650,
                'total_commission_rate' => 2.500,
                'description' => 'Managed properties without Super Marketer',
                'last_updated_at' => '2025-09-15 15:44:14',
                'updated_by' => null,
                'role_id' => 1,
                'commission_percentage' => 2.5000,
                'effective_from' => '2025-09-15 15:44:14',
                'effective_until' => null,
                'created_by' => $createdBy,
                'is_active' => 1,
                'created_at' => '2025-09-15 15:44:14',
                'updated_at' => '2025-09-15 15:44:14'
            ];

            // Managed with super marketer
            $commissionRates[] = [
                'id' => $baseId++,
                'region' => $region,
                'property_management_status' => 'managed',
                'hierarchy_status' => 'with_super_marketer',
                'super_marketer_rate' => 0.250,
                'marketer_rate' => 0.500,
                'regional_manager_rate' => 0.100,
                'company_rate' => 1.650,
                'total_commission_rate' => 2.500,
                'description' => 'Managed properties with Super Marketer',
                'last_updated_at' => '2025-09-15 15:44:14',
                'updated_by' => null,
                'role_id' => 1,
                'commission_percentage' => 2.5000,
                'effective_from' => '2025-09-15 15:44:14',
                'effective_until' => null,
                'created_by' => $createdBy,
                'is_active' => 1,
                'created_at' => '2025-09-15 15:44:14',
                'updated_at' => '2025-09-15 15:44:14'
            ];
        }

        // Insert or update commission rates
        foreach ($commissionRates as $rate) {
            $existing = DB::table('commission_rates')
                ->where('region', $rate['region'])
                ->where('property_management_status', $rate['property_management_status'])
                ->where('hierarchy_status', $rate['hierarchy_status'])
                ->first();

            if ($existing) {
                DB::table('commission_rates')
                    ->where('id', $existing->id)
                    ->update([
                        'super_marketer_rate' => $rate['super_marketer_rate'],
                        'marketer_rate' => $rate['marketer_rate'],
                        'regional_manager_rate' => $rate['regional_manager_rate'],
                        'company_rate' => $rate['company_rate'],
                        'total_commission_rate' => $rate['total_commission_rate'],
                        'description' => $rate['description'],
                        'commission_percentage' => $rate['commission_percentage'],
                        'updated_at' => $now
                    ]);
                $this->command->info("Updated commission rate: {$rate['region']} - {$rate['property_management_status']} - {$rate['hierarchy_status']}");
            } else {
                DB::table('commission_rates')->insert($rate);
                $this->command->info("Inserted commission rate: {$rate['region']} - {$rate['property_management_status']} - {$rate['hierarchy_status']}");
            }
        }

        $this->command->info('Commission rates seeded successfully!');
    }
}

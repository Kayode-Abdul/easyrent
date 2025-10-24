<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SuperMarketerSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Default commission rates for different regions and roles
        $defaultRates = [
            // Lagos State rates
            ['region' => 'Lagos', 'role_id' => 9, 'commission_percentage' => 0.0075, 'created_by' => 1], // Super Marketer: 0.75%
            ['region' => 'Lagos', 'role_id' => 3, 'commission_percentage' => 0.0100, 'created_by' => 1], // Marketer: 1.0%
            ['region' => 'Lagos', 'role_id' => 2, 'commission_percentage' => 0.0075, 'created_by' => 1], // Regional Manager: 0.75%
            
            // Abuja rates
            ['region' => 'Abuja', 'role_id' => 9, 'commission_percentage' => 0.0080, 'created_by' => 1], // Super Marketer: 0.8%
            ['region' => 'Abuja', 'role_id' => 3, 'commission_percentage' => 0.0090, 'created_by' => 1], // Marketer: 0.9%
            ['region' => 'Abuja', 'role_id' => 2, 'commission_percentage' => 0.0080, 'created_by' => 1], // Regional Manager: 0.8%
            
            // Port Harcourt rates
            ['region' => 'Port Harcourt', 'role_id' => 9, 'commission_percentage' => 0.0070, 'created_by' => 1], // Super Marketer: 0.7%
            ['region' => 'Port Harcourt', 'role_id' => 3, 'commission_percentage' => 0.0110, 'created_by' => 1], // Marketer: 1.1%
            ['region' => 'Port Harcourt', 'role_id' => 2, 'commission_percentage' => 0.0070, 'created_by' => 1], // Regional Manager: 0.7%
            
            // Default rates for other regions
            ['region' => 'Default', 'role_id' => 9, 'commission_percentage' => 0.0075, 'created_by' => 1], // Super Marketer: 0.75%
            ['region' => 'Default', 'role_id' => 3, 'commission_percentage' => 0.0100, 'created_by' => 1], // Marketer: 1.0%
            ['region' => 'Default', 'role_id' => 2, 'commission_percentage' => 0.0075, 'created_by' => 1], // Regional Manager: 0.75%
        ];
        
        // Insert commission rates
        foreach ($defaultRates as $rate) {
            DB::table('commission_rates')->updateOrInsert(
                [
                    'region' => $rate['region'],
                    'role_id' => $rate['role_id']
                ],
                [
                    'commission_percentage' => $rate['commission_percentage'],
                    'effective_from' => $now,
                    'effective_until' => null,
                    'created_by' => $rate['created_by'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
        
        $this->command->info('Super Marketer system commission rates seeded successfully.');
    }
}
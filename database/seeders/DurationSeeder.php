<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Duration;

class DurationSeeder extends Seeder
{
    public function run(): void
    {
        $durations = [
            [
                'code' => 'hourly',
                'name' => 'Hourly',
                'description' => 'Per hour rental',
                'duration_months' => 0.04, // ~1.2 hours per month
                'is_active' => true,
                'sort_order' => 1,
                'display_format' => 'per hour',
                'calculation_rules' => [
                    'multiplier' => 1,
                    'base_type' => 'hourly'
                ]
            ],
            [
                'code' => 'daily',
                'name' => 'Daily',
                'description' => 'Per day rental',
                'duration_months' => 0.03, // ~1 day
                'is_active' => true,
                'sort_order' => 2,
                'display_format' => 'per day',
                'calculation_rules' => [
                    'multiplier' => 1,
                    'base_type' => 'daily'
                ]
            ],
            [
                'code' => 'weekly',
                'name' => 'Weekly',
                'description' => 'Per week rental',
                'duration_months' => 0.25, // 1 week
                'is_active' => true,
                'sort_order' => 3,
                'display_format' => 'per week',
                'calculation_rules' => [
                    'multiplier' => 7,
                    'base_type' => 'daily'
                ]
            ],
            [
                'code' => 'monthly',
                'name' => 'Monthly',
                'description' => 'Per month rental',
                'duration_months' => 1,
                'is_active' => true,
                'sort_order' => 4,
                'display_format' => 'per month',
                'calculation_rules' => [
                    'multiplier' => 1,
                    'base_type' => 'monthly'
                ]
            ],
            [
                'code' => 'quarterly',
                'name' => 'Quarterly',
                'description' => 'Per quarter (3 months) rental',
                'duration_months' => 3,
                'is_active' => true,
                'sort_order' => 5,
                'display_format' => 'per quarter',
                'calculation_rules' => [
                    'multiplier' => 3,
                    'base_type' => 'monthly'
                ]
            ],
            [
                'code' => 'semi_annually',
                'name' => 'Semi-Annual',
                'description' => 'Per 6 months rental',
                'duration_months' => 6,
                'is_active' => true,
                'sort_order' => 6,
                'display_format' => 'per 6 months',
                'calculation_rules' => [
                    'multiplier' => 6,
                    'base_type' => 'monthly'
                ]
            ],
            [
                'code' => 'annually',
                'name' => 'Annual',
                'description' => 'Per year rental',
                'duration_months' => 12,
                'is_active' => true,
                'sort_order' => 7,
                'display_format' => 'per year',
                'calculation_rules' => [
                    'multiplier' => 12,
                    'base_type' => 'monthly'
                ]
            ],
            [
                'code' => 'bi_annually',
                'name' => 'Bi-Annual',
                'description' => 'Per 24 months rental',
                'duration_months' => 24,
                'is_active' => true,
                'sort_order' => 8,
                'display_format' => 'per 24 months',
                'calculation_rules' => [
                    'multiplier' => 24,
                    'base_type' => 'monthly'
                ]
            ]
        ];

        foreach ($durations as $duration) {
            Duration::updateOrCreate(
                ['code' => $duration['code']],
                $duration
            );
        }

        $this->command->info('Durations table seeded successfully!');
    }
}

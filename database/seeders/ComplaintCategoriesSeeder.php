<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ComplaintCategory;

class ComplaintCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electrical Issues',
                'description' => 'Power outages, faulty wiring, electrical appliance problems',
                'icon' => 'nc-icon nc-bulb-63',
                'priority_level' => 'high',
                'estimated_resolution_hours' => 12
            ],
            [
                'name' => 'Plumbing Problems',
                'description' => 'Leaks, blocked drains, water pressure issues, toilet problems',
                'icon' => 'nc-icon nc-tap-01',
                'priority_level' => 'high',
                'estimated_resolution_hours' => 8
            ],
            [
                'name' => 'Heating/Cooling',
                'description' => 'Air conditioning, heating system, ventilation issues',
                'icon' => 'nc-icon nc-air-baloon',
                'priority_level' => 'medium',
                'estimated_resolution_hours' => 24
            ],
            [
                'name' => 'Security Concerns',
                'description' => 'Broken locks, security system issues, safety concerns',
                'icon' => 'nc-icon nc-lock-circle-open',
                'priority_level' => 'urgent',
                'estimated_resolution_hours' => 4
            ],
            [
                'name' => 'Noise Complaints',
                'description' => 'Excessive noise from neighbors or external sources',
                'icon' => 'nc-icon nc-sound-wave',
                'priority_level' => 'medium',
                'estimated_resolution_hours' => 48
            ],
            [
                'name' => 'Maintenance Request',
                'description' => 'General maintenance, repairs, and upkeep requests',
                'icon' => 'nc-icon nc-settings-tool-66',
                'priority_level' => 'low',
                'estimated_resolution_hours' => 72
            ],
            [
                'name' => 'Pest Control',
                'description' => 'Insects, rodents, or other pest-related issues',
                'icon' => 'nc-icon nc-bug-2',
                'priority_level' => 'medium',
                'estimated_resolution_hours' => 24
            ],
            [
                'name' => 'Appliance Issues',
                'description' => 'Refrigerator, washing machine, oven, and other appliance problems',
                'icon' => 'nc-icon nc-tv-2',
                'priority_level' => 'medium',
                'estimated_resolution_hours' => 48
            ],
            [
                'name' => 'Structural Problems',
                'description' => 'Cracks, leaks, foundation issues, structural damage',
                'icon' => 'nc-icon nc-istanbul',
                'priority_level' => 'urgent',
                'estimated_resolution_hours' => 6
            ],
            [
                'name' => 'Cleanliness Issues',
                'description' => 'Common area cleanliness, garbage collection, sanitation',
                'icon' => 'nc-icon nc-basket',
                'priority_level' => 'low',
                'estimated_resolution_hours' => 24
            ],
            [
                'name' => 'Internet/Utilities',
                'description' => 'Internet connectivity, cable TV, utility service issues',
                'icon' => 'nc-icon nc-wifi-router',
                'priority_level' => 'medium',
                'estimated_resolution_hours' => 48
            ],
            [
                'name' => 'Other',
                'description' => 'Any other issues not covered by the above categories',
                'icon' => 'nc-icon nc-chat-33',
                'priority_level' => 'low',
                'estimated_resolution_hours' => 48
            ]
        ];

        foreach ($categories as $category) {
            ComplaintCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
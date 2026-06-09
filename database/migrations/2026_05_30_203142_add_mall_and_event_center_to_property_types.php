<?php

use Illuminate\Database\Migrations\Migration;
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
        // Insert Mall and Event Center into property_types
        $newTypes = [
            ['name' => 'Mall', 'category' => 'commercial', 'description' => 'Shopping mall and retail complex'],
            ['name' => 'Event Center', 'category' => 'commercial', 'description' => 'Event and conference center'],
        ];

        foreach ($newTypes as $type) {
            // Only insert if not already present
            if (!DB::table('property_types')->where('name', $type['name'])->exists()) {
                $nextId = (DB::table('property_types')->max('id') ?? 0) + 1;
                DB::table('property_types')->insert([
                    'id' => $nextId,
                    'name' => $type['name'],
                    'category' => $type['category'],
                    'description' => $type['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('property_types')->whereIn('name', ['Mall', 'Event Center'])->delete();
    }
};

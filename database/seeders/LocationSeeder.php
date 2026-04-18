<?php

namespace Database\Seeders;

use App\Models\Lga;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LocationSeeder extends Seeder
{
    public function run()
    {
        $json = File::get(resource_path('countries.json'));
        $countries = json_decode($json, true);

        foreach ($countries as $countryData) {
            $countryName = $countryData['name'];
            if (isset($countryData['states'])) {
                foreach ($countryData['states'] as $stateData) {
                    $state = State::updateOrCreate(
                        ['name' => $stateData['name'], 'country_name' => $countryName]
                    );

                    if (isset($stateData['cities']) && is_array($stateData['cities'])) {
                        foreach ($stateData['cities'] as $lgaName) {
                            Lga::updateOrCreate(
                                ['name' => $lgaName, 'state_id' => $state->id]
                            );
                        }
                    }
                }
            }
        }
    }
}

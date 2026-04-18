<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sync Properties
        $properties = DB::table('properties')->get();
        foreach ($properties as $property) {
            $stateId = $this->getStateId($property->state, $property->country ?? 'Nigeria');
            $lgaId = $this->getLgaId($property->lga, $stateId);
            
            DB::table('properties')->where('id', $property->id)->update([
                'state_id' => $stateId,
                'lga_id' => $lgaId,
                'country_name' => $property->country ?? 'Nigeria'
            ]);
        }

        // Sync Users
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $stateId = $this->getStateId($user->state, $user->country ?? 'Nigeria');
            $lgaId = $this->getLgaId($user->lga, $stateId);
            
            DB::table('users')->where('id', $user->id)->update([
                'state_id' => $stateId,
                'lga_id' => $lgaId,
                'country_name' => $user->country ?? 'Nigeria'
            ]);
        }

        // Sync Marketer Profiles
        $marketers = DB::table('marketer_profiles')->get();
        foreach ($marketers as $marketer) {
            // Marketer profiles may not have state/city directly, but let's check
            // If they don't, we might need to skip or use a default
        }

        // Sync Regional Scopes
        $scopes = DB::table('regional_scopes')->get();
        foreach ($scopes as $scope) {
            if ($scope->scope_type === 'state') {
                $stateId = $this->getStateId($scope->scope_value, 'Nigeria');
                DB::table('regional_scopes')->where('id', $scope->id)->update([
                    'state_id' => $stateId,
                    'country_name' => 'Nigeria'
                ]);
            } elseif ($scope->scope_type === 'lga') {
                $parts = explode('::', $scope->scope_value);
                if (count($parts) === 2) {
                    $stateName = $parts[0];
                    $lgaName = $parts[1];
                    $stateId = $this->getStateId($stateName, 'Nigeria');
                    $lgaId = $this->getLgaId($lgaName, $stateId);
                    
                    DB::table('regional_scopes')->where('id', $scope->id)->update([
                        'state_id' => $stateId,
                        'lga_id' => $lgaId,
                        'country_name' => 'Nigeria'
                    ]);
                }
            }
        }
    }

    private function getStateId($name, $country)
    {
        if (!$name) return null;
        return DB::table('states')
            ->where('name', $name)
            ->where('country_name', $country)
            ->value('id');
    }

    private function getLgaId($name, $stateId)
    {
        if (!$name || !$stateId) return null;
        return DB::table('lgas')
            ->where('name', $name)
            ->where('state_id', $stateId)
            ->value('id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('properties')->update(['state_id' => null, 'lga_id' => null, 'country_name' => null]);
        DB::table('users')->update(['state_id' => null, 'lga_id' => null, 'country_name' => null]);
        DB::table('regional_scopes')->update(['state_id' => null, 'lga_id' => null, 'country_name' => null]);
    }
};

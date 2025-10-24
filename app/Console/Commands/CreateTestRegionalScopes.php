<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\RegionalScope;

class CreateTestRegionalScopes extends Command
{
    protected $signature = 'test:create-regional-scopes {user_id?}';
    protected $description = 'Create test regional scopes for a regional manager';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if (!$userId) {
            // Find a regional manager user
            $regionalManagerRole = Role::where('name', 'Regional Manager')->orWhere('id', 8)->first();
            
            if (!$regionalManagerRole) {
                $this->error('Regional Manager role not found');
                return 1;
            }
            
            $user = User::whereHas('roles', function($q) use ($regionalManagerRole) {
                $q->where('role_id', $regionalManagerRole->id);
            })->first();
            
            if (!$user) {
                $this->error('No Regional Manager users found');
                return 1;
            }
            
            $userId = $user->user_id;
        } else {
            $user = User::where('user_id', $userId)->first();
            if (!$user) {
                $this->error("User with ID {$userId} not found");
                return 1;
            }
        }
        
        $this->info("Creating test regional scopes for user: {$user->first_name} {$user->last_name} (ID: {$userId})");
        
        // Create test scopes
        $scopes = [
            ['scope_type' => 'state', 'scope_value' => 'Lagos'],
            ['scope_type' => 'lga', 'scope_value' => 'Lagos::Ikeja'],
            ['scope_type' => 'lga', 'scope_value' => 'Lagos::Victoria Island'],
            ['scope_type' => 'state', 'scope_value' => 'Abuja'],
        ];
        
        foreach ($scopes as $scopeData) {
            RegionalScope::updateOrCreate([
                'user_id' => $userId,
                'scope_type' => $scopeData['scope_type'],
                'scope_value' => $scopeData['scope_value']
            ]);
            
            $this->line("Created scope: {$scopeData['scope_type']} = {$scopeData['scope_value']}");
        }
        
        $this->info('Test regional scopes created successfully!');
        
        // Test the formatted scopes
        $formattedScopes = $user->getFormattedRegionalScopes();
        $this->info('Formatted scopes:');
        foreach ($formattedScopes as $scope) {
            $lgaText = $scope->lga ? " / {$scope->lga}" : ' (All LGAs)';
            $this->line("- {$scope->state}{$lgaText}");
        }
        
        return 0;
    }
}
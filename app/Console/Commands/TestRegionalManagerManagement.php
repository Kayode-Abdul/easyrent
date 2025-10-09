<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\RegionalScope;

class TestRegionalManagerManagement extends Command
{
    protected $signature = 'test:regional-manager-management';
    protected $description = 'Test the regional manager management functionality';

    public function handle()
    {
        $this->info('Testing Regional Manager Management Functionality');
        $this->newLine();

        // Find a regional manager
        $regionalManagerRole = Role::where('name', 'Regional Manager')->orWhere('id', 8)->first();
        
        if (!$regionalManagerRole) {
            $this->error('Regional Manager role not found');
            return 1;
        }

        $regionalManager = User::whereHas('roles', function($q) use ($regionalManagerRole) {
            $q->where('role_id', $regionalManagerRole->id);
        })->first();

        if (!$regionalManager) {
            $this->error('No Regional Manager users found');
            return 1;
        }

        $this->info("Testing with Regional Manager: {$regionalManager->first_name} {$regionalManager->last_name} (ID: {$regionalManager->user_id})");

        // Test getting formatted scopes
        $scopes = $regionalManager->getFormattedRegionalScopes();
        $this->info("Current regional scopes: {$scopes->count()}");
        
        foreach ($scopes as $scope) {
            $lgaText = $scope->lga ? " / {$scope->lga}" : ' (All LGAs)';
            $this->line("  - {$scope->state}{$lgaText}");
        }

        // Test removing a scope if any exist
        $rawScopes = $regionalManager->regionalScopes()->get();
        if ($rawScopes->count() > 0) {
            $this->newLine();
            $this->info('Testing scope removal...');
            
            $scopeToRemove = $rawScopes->first();
            $scopeDescription = $scopeToRemove->scope_type . ': ' . $scopeToRemove->scope_value;
            
            if ($this->confirm("Remove scope '{$scopeDescription}'?")) {
                $scopeToRemove->delete();
                $this->info("Scope '{$scopeDescription}' removed successfully");
                
                // Show updated scopes
                $updatedScopes = $regionalManager->getFormattedRegionalScopes();
                $this->info("Updated regional scopes: {$updatedScopes->count()}");
                
                foreach ($updatedScopes as $scope) {
                    $lgaText = $scope->lga ? " / {$scope->lga}" : ' (All LGAs)';
                    $this->line("  - {$scope->state}{$lgaText}");
                }
            }
        }

        // Test adding a new scope
        $this->newLine();
        if ($this->confirm('Add a test scope?')) {
            RegionalScope::create([
                'user_id' => $regionalManager->user_id,
                'scope_type' => 'state',
                'scope_value' => 'Test State'
            ]);
            
            $this->info('Test scope added successfully');
            
            // Show updated scopes
            $finalScopes = $regionalManager->getFormattedRegionalScopes();
            $this->info("Final regional scopes: {$finalScopes->count()}");
            
            foreach ($finalScopes as $scope) {
                $lgaText = $scope->lga ? " / {$scope->lga}" : ' (All LGAs)';
                $this->line("  - {$scope->state}{$lgaText}");
            }
        }

        $this->newLine();
        $this->info('Regional Manager Management functionality test completed!');
        $this->info('You can now access the admin interface at: /admin/regional-managers');
        
        return 0;
    }
}
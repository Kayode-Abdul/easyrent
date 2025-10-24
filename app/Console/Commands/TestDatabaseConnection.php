<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\CommissionRate;
use Illuminate\Support\Facades\DB;

class TestDatabaseConnection extends Command
{
    protected $signature = 'test:database';
    protected $description = 'Test database connection and models';

    public function handle()
    {
        $this->info('Testing database connection...');
        
        try {
            // Test basic database connection
            DB::connection()->getPdo();
            $this->info('✓ Database connection successful');
            
            // Test Role model
            $roleCount = Role::count();
            $this->info("✓ Role model works - found {$roleCount} roles");
            
            // Test CommissionRate model
            $rateCount = CommissionRate::count();
            $this->info("✓ CommissionRate model works - found {$rateCount} rates");
            
            // Test if roles table has data
            $roles = Role::all();
            if ($roles->count() > 0) {
                $this->info('✓ Roles found:');
                foreach ($roles as $role) {
                    $this->line("  - ID: {$role->id}, Name: {$role->name}");
                }
            } else {
                $this->warn('⚠ No roles found in database');
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('✗ Database error: ' . $e->getMessage());
            return 1;
        }
    }
}
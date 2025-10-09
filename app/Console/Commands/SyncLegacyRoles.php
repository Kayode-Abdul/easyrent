<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\User;

class SyncLegacyRoles extends Command
{
    protected $signature = 'roles:sync-legacy {--dry-run}';
    protected $description = 'Sync legacy users.role into roles table (role_user pivot)';

    public function handle(): int
    {
        $dry = $this->option('dry-run');
        $this->info('Syncing legacy roles into role_user ' . ($dry ? '(dry-run)' : ''));

        $map = [
            'admin' => 'admin',
            'tenant' => 'tenant',
            'landlord' => 'landlord',
            'agent' => 'agent',
            'property_manager' => 'property_manager',
            'super_marketer' => 'super_marketer',
            'regional_manager' => 'regional_manager',
        ];

        $rolesByName = Role::pluck('id','name')->all();
        $count = 0; $inserted = 0;

        User::chunk(500, function($users) use (&$count, &$inserted, $rolesByName, $map, $dry){
            foreach ($users as $user) {
                $count++;
                $legacy = $user->role;
                $roleId = null;

                if (is_numeric($legacy)) {
                    $roleId = (int) $legacy;
                } else {
                    $name = strtolower((string) $legacy);
                    $mapped = $map[$name] ?? $name;
                    $roleId = $rolesByName[$mapped] ?? null;
                }

                if (!$roleId) continue;

                $exists = DB::table('role_user')
                    ->where('user_id', $user->user_id)
                    ->where('role_id', $roleId)
                    ->exists();

                if (!$exists) {
                    if (!$dry) {
                        DB::table('role_user')->insert([
                            'user_id' => $user->user_id,
                            'role_id' => $roleId,
                        ]);
                    }
                    $inserted++;
                }
            }
        });

        $this->info("Processed: {$count}, Inserted: {$inserted}");
        return Command::SUCCESS;
    }
}

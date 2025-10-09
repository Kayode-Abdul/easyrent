<?php
/**
 * Script to check for hardcoded role IDs in the codebase
 * Run with: php check_role_ids.php
 */

// Get current role mappings from database
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CURRENT ROLE MAPPINGS ===\n";
$roles = DB::table('roles')->select('id', 'name')->orderBy('id')->get();
foreach ($roles as $role) {
    echo "{$role->id} => {$role->name}\n";
}

echo "\n=== POTENTIAL ISSUES TO CHECK ===\n";

// Files that might still have hardcoded role IDs
$filesToCheck = [
    'resources/views/admin/users.blade.php' => 'Role display logic (lines 222-240)',
    'resources/views/admin-dashboard.blade.php' => 'Dashboard access checks',
    'resources/views/dashboard-new.blade.php' => 'Role badges and display',
    'resources/views/marketer/dashboard.blade.php' => 'Role display in referrals',
    'resources/views/payments/index.blade.php' => 'Role-based column display',
    'app/Http/Controllers/Admin/AdminController.php' => 'Admin role checks',
    'app/Http/Controllers/PaymentController.php' => 'Payment access checks',
    'app/Http/Controllers/SearchController.php' => 'Search permission checks',
    'assign_role.php' => 'Role assignment script',
];

foreach ($filesToCheck as $file => $description) {
    if (file_exists($file)) {
        echo "⚠️  {$file}: {$description}\n";
    }
}

echo "\n=== RECOMMENDED ACTIONS ===\n";
echo "1. Search for 'role == [number]' or 'role === [number]' patterns\n";
echo "2. Replace hardcoded IDs with dynamic lookups using User::getRoleId('role_name')\n";
echo "3. Consider using hasRole('role_name') method instead of role field comparisons\n";
echo "4. Test registration, login, and role-based access after changes\n";

echo "\n=== QUICK SEARCH COMMANDS ===\n";
echo "grep -r 'role.*=.*[1-9]' app/ resources/ --include='*.php'\n";
echo "grep -r 'role.*===.*[1-9]' app/ resources/ --include='*.php'\n";
echo "grep -r 'role.*==.*[1-9]' app/ resources/ --include='*.php'\n";
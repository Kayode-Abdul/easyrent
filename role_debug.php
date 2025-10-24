<?php
// role_debug.php - A utility to debug user roles and help with troubleshooting

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;

// Create Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Check if user is logged in
if (!Auth::check()) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Role Debug Tool</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
            body { padding: 20px; }
            .card { margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='alert alert-warning'>
                <h4>Not Logged In</h4>
                <p>You need to be logged in to use this tool. <a href='/login' class='btn btn-primary btn-sm'>Login</a></p>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

// Get user data
$user = Auth::user();
$userId = $user->user_id ?? $user->id;
$sessionRole = session('selected_role');

// Get all roles from the roles table
try {
    $rolesFromTable = [];
    if (DB::getSchemaBuilder()->hasTable('roles')) {
        $userRolesFromPivot = [];
        if (DB::getSchemaBuilder()->hasTable('role_user')) {
            $userRolesFromPivot = DB::table('role_user')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', $userId)
                ->select('roles.id', 'roles.name', 'roles.display_name')
                ->get();
        }
        
        $allRoles = DB::table('roles')->get();
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Role map for the legacy system
$roleMap = [
    1 => 'admin',
    2 => 'landlord', 
    3 => 'tenant', 
    4 => 'property_manager',
    5 => 'marketer',
    6 => 'regional_manager'
];

// Check all roles through different methods
$userLegacyRole = isset($user->role) && isset($roleMap[$user->role]) ? $roleMap[$user->role] : null;
$isAdmin = $user->admin == 1;

// Check routes
$regionalDashboardRoute = route('regional.dashboard', [], false);
$switchRoleUrl = route('switch.role', [], false);

// Check which regional manager views exist
$viewExists = [
    'regional_manager.dashboard' => view()->exists('regional_manager.dashboard'),
    'regional_manager_dashboard' => view()->exists('regional_manager_dashboard'),
    'regional-manager-dashboard' => view()->exists('regional-manager-dashboard'),
];

// Test hasRole function if available
$hasRoleResults = [];
if (method_exists($user, 'hasRole')) {
    foreach ($roleMap as $roleId => $roleName) {
        $hasRoleResults[$roleName] = $user->hasRole($roleName);
    }
}

// Output debug info
echo "<!DOCTYPE html>
<html>
<head>
    <title>Role Debug Tool</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; }
        .card { margin-bottom: 20px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 4px; }
        .badge { margin-right: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>EasyRent Role Debug Tool</h1>
        <p class='lead'>This tool helps diagnose role and permission issues.</p>
        
        <div class='row'>
            <div class='col-md-6'>
                <div class='card'>
                    <div class='card-header'>
                        <h5>User Information</h5>
                    </div>
                    <div class='card-body'>
                        <p><strong>User ID:</strong> $userId</p>
                        <p><strong>Name:</strong> {$user->first_name} {$user->last_name}</p>
                        <p><strong>Email:</strong> {$user->email}</p>
                        <p><strong>Legacy Role ID:</strong> {$user->role}</p>
                        <p><strong>Legacy Role Name:</strong> " . ($userLegacyRole ? ucfirst($userLegacyRole) : 'None') . "</p>
                        <p><strong>Admin Flag:</strong> " . ($user->admin == 1 ? 'Yes' : 'No') . "</p>
                    </div>
                </div>

                <div class='card'>
                    <div class='card-header'>
                        <h5>Session & Routes</h5>
                    </div>
                    <div class='card-body'>
                        <p><strong>Selected Role in Session:</strong> " . ($sessionRole ? ucfirst($sessionRole) : 'None') . "</p>
                        <p><strong>Regional Dashboard URL:</strong> $regionalDashboardRoute</p>
                        <p><strong>Switch Role URL:</strong> $switchRoleUrl</p>
                    </div>
                </div>
            </div>
            
            <div class='col-md-6'>
                <div class='card'>
                    <div class='card-header'>
                        <h5>Role Detection</h5>
                    </div>
                    <div class='card-body'>";
                    
if (!empty($hasRoleResults)) {
    echo "<h6>hasRole() Method Results:</h6>
          <ul>";
    foreach ($hasRoleResults as $role => $hasRole) {
        $badge = $hasRole ? "<span class='badge bg-success'>Yes</span>" : "<span class='badge bg-secondary'>No</span>";
        echo "<li>$badge <strong>" . ucfirst($role) . "</strong></li>";
    }
    echo "</ul>";
}

if (!empty($userRolesFromPivot)) {
    echo "<h6>Roles from role_user Table:</h6>
          <ul>";
    foreach ($userRolesFromPivot as $role) {
        echo "<li><span class='badge bg-primary'>{$role->id}</span> <strong>{$role->name}</strong> ({$role->display_name})</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No roles found in the role_user table or table doesn't exist.</p>";
}

echo "          </div>
                </div>
                
                <div class='card'>
                    <div class='card-header'>
                        <h5>View Status</h5>
                    </div>
                    <div class='card-body'>
                        <ul>";
foreach ($viewExists as $view => $exists) {
    $badge = $exists ? "<span class='badge bg-success'>Exists</span>" : "<span class='badge bg-danger'>Missing</span>";
    echo "<li>$badge <code>$view</code></li>";
}
echo "              </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class='row mt-3'>
            <div class='col-12'>
                <div class='card'>
                    <div class='card-header'>
                        <h5>Role Switcher</h5>
                    </div>
                    <div class='card-body'>
                        <p>Use these buttons to test role switching:</p>
                        <div class='d-flex flex-wrap gap-2'>";
                        
foreach ($roleMap as $roleId => $roleName) {
    $active = ($sessionRole == $roleName || (!$sessionRole && $user->role == $roleId)) ? 'btn-primary' : 'btn-outline-secondary';
    echo "<form method='POST' action='$switchRoleUrl' style='margin-right: 10px;'>
            <input type='hidden' name='_token' value='" . csrf_token() . "'>
            <input type='hidden' name='role' value='$roleName'>
            <button type='submit' class='btn $active'>" . ucfirst(str_replace('_', ' ', $roleName)) . "</button>
          </form>";
}

echo "              </div>
                    </div>
                </div>
            </div>
        </div>

        <div class='row mt-3'>
            <div class='col-12'>
                <div class='card'>
                    <div class='card-header'>
                        <h5>Quick Access Links</h5>
                    </div>
                    <div class='card-body'>
                        <div class='d-flex flex-wrap gap-2'>
                            <a href='/dashboard' class='btn btn-info'>Main Dashboard</a>
                            <a href='" . route('regional.dashboard') . "' class='btn btn-info'>Regional Dashboard</a>
                            <a href='" . route('regional.properties') . "' class='btn btn-info'>Regional Properties</a>
                            <a href='" . route('regional.marketers') . "' class='btn btn-info'>Marketers</a>
                            <a href='" . route('regional.analytics') . "' class='btn btn-info'>Analytics</a>
                            <a href='" . route('regional.pending_approvals') . "' class='btn btn-info'>Pending Approvals</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";

$kernel->terminate($request, $response);

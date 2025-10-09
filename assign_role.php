<?php
/**
 * Role Assignment Utility for EasyRent
 * This file provides a simple interface to assign roles to users.
 * 
 * ROLE ASSIGNMENT RESTRICTIONS:
 * 
 * 1. USER-SELECTED ROLES (Cannot be assigned by admin):
 *    - Landlord (2): User selects during registration or from dashboard
 *    - Tenant (3): User selects during registration or from dashboard
 *    - Artisan (4): User selects during registration or from dashboard
 *    - Marketer (5): User selects during registration, activated by referrals
 *    - Property Manager (7): User selects during registration or from dashboard
 * 
 * 2. AUTOMATIC ROLES (Cannot be manually assigned):
 *    - Super Marketer (9): Automatically assigned when marketer refers another marketer who refers a landlord
 * 
 * 3. ADMIN-ONLY ROLES (Only Super Admin can assign):
 *    - Super Admin (1): Only Super Admins can assign
 *    - Admin (6): Only Super Admins can assign
 *    - Regional Manager (8): Admins can assign
 * 
 * 4. UPGRADE ROLES:
 *    - Verified Property Manager: Admins can upgrade Property Managers to verified status
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// Boot the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get authenticated user
$currentUser = auth()->user();
if (!$currentUser || (!$currentUser->admin && $currentUser->role != 1)) {
    die("You must be logged in as an admin to use this tool.");
}

// Define role restrictions based on business rules
$userSelectedRoles = [
    2 => 'Landlord',        // User selects during registration or from dashboard
    3 => 'Tenant',          // User selects during registration or from dashboard  
    4 => 'Artisan',         // User selects during registration or from dashboard
    5 => 'Marketer',        // User selects during registration, activated by referrals
    7 => 'Property Manager', // User selects during registration or from dashboard
];

$automaticRoles = [
    9 => 'Super Marketer',  // Automatically assigned when marketer refers another marketer who refers a landlord
];

$adminOnlyRoles = [
    1 => 'Super Admin',     // Only super admins can assign
    6 => 'Admin',           // Only super admins can assign
    8 => 'Regional Manager', // Only admins can assign
];

$upgradeRoles = [
    // Roles that are upgrades from base roles (admin can assign these)
    // 7 -> Verified Property Manager (admin upgrades from Property Manager)
];

// Check if current user is super admin
$isSuperAdmin = ($currentUser->admin == 1 && $currentUser->role == 1);

// Function to check if a role can be assigned by current admin
function canAssignRole($roleId, $isSuperAdmin, $userSelectedRoles, $automaticRoles, $adminOnlyRoles) {
    // Super admin can assign admin-only roles and upgrade roles
    if ($isSuperAdmin) {
        // Super admin cannot assign user-selected roles or automatic roles
        return !array_key_exists($roleId, $userSelectedRoles) && !array_key_exists($roleId, $automaticRoles);
    }
    
    // Regular admin can only assign admin-only roles (except super admin) and upgrade roles
    if (array_key_exists($roleId, $adminOnlyRoles) && $roleId != 1) {
        return true; // Can assign Regional Manager, but not Super Admin
    }
    
    // Cannot assign user-selected roles or automatic roles
    return false;
}

// Check for POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form submission
    $userId = $_POST['user_id'] ?? null;
    $roleId = $_POST['role_id'] ?? null;
    
    if (!$userId || !$roleId) {
        $error = "User ID and Role ID are required.";
    } elseif (!canAssignRole($roleId, $isSuperAdmin, $userSelectedRoles, $automaticRoles, $adminOnlyRoles)) {
        if (array_key_exists($roleId, $userSelectedRoles)) {
            $error = "Cannot assign '{$userSelectedRoles[$roleId]}' role. Users must select this role during registration or from their dashboard.";
        } elseif (array_key_exists($roleId, $automaticRoles)) {
            $error = "Cannot assign '{$automaticRoles[$roleId]}' role. This role is automatically assigned based on referral activity.";
        } elseif (array_key_exists($roleId, $adminOnlyRoles)) {
            $error = "You do not have permission to assign '{$adminOnlyRoles[$roleId]}' role. Only Super Admins can assign this role.";
        } else {
            $error = "You do not have permission to assign this role.";
        }
    } else {
        try {
            $user = \App\Models\User::findOrFail($userId);
            $oldRole = $user->role;
            
            // Special validation for specific roles
            if ($roleId == 9) { // Super Marketer role - should never be manually assigned
                $error = "Super Marketer role cannot be manually assigned. It is automatically granted when a marketer refers another marketer who refers a landlord.";
                goto skip_assignment;
            }
            
            $user->role = $roleId;
            $user->save();
            
            // Create activity log
            \App\Models\ActivityLog::create([
                'user_id' => $currentUser->user_id,
                'action' => 'role_change',
                'description' => 'Changed user ' . $user->first_name . ' ' . $user->last_name . '\'s role to ' . $roleId,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
            ]);
            
            $success = "Role changed successfully!";
            
            // Force cache refresh
            cache()->forget('user_roles_' . $userId);
            
            skip_assignment:
            
        } catch (\Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Get all users
$users = \App\Models\User::orderBy('first_name')->get();

// Get role names
$roleNames = [
    1 => 'Super Admin',
    2 => 'Landlord',
    3 => 'Tenant',
    4 => 'Artisan',
    5 => 'Marketer',
    6 => 'Admin',
    7 => 'Property Manager',
    8 => 'Regional Manager',
    9 => 'Super Marketer'
];

// Filter roles based on admin permissions
$availableRoles = [];
foreach ($roleNames as $id => $name) {
    if (canAssignRole($id, $isSuperAdmin, $userSelectedRoles, $automaticRoles, $adminOnlyRoles)) {
        $availableRoles[$id] = $name;
    }
}

// Add special upgrade roles that admins can assign
if (!$isSuperAdmin) {
    // Regular admins can upgrade Property Manager to Verified Property Manager
    // This would be a separate process or additional role
    $availableRoles['verify_pm'] = 'Verify Property Manager (Upgrade)';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyRent Role Assignment</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 800px; margin-top: 50px; }
        .role-badge {
            display: inline-block;
            padding: 0.25em 0.6em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            color: #fff;
            background-color: #6c757d;
        }
        .role-1 { background-color: #dc3545; } /* Admin */
        .role-2 { background-color: #28a745; } /* Landlord */
        .role-3 { background-color: #17a2b8; } /* Tenant */
        .role-4 { background-color: #fd7e14; } /* Property Manager */
        .role-5 { background-color: #6610f2; } /* Marketer */
        .role-6 { background-color: #007bff; } /* Regional Manager */
        
        .user-table { margin-top: 20px; }
        .user-table th { position: sticky; top: 0; background-color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>EasyRent Role Assignment Tool</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5>Assign Role to User</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="user_id">Select User:</label>
                        <select name="user_id" id="user_id" class="form-control" required>
                            <option value="">-- Select User --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user->user_id ?>">
                                    <?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?>
                                    (<?= htmlspecialchars($user->email) ?>) -
                                    Current Role: <?= $roleNames[$user->role] ?? 'Unknown' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="role_id">Assign Role:</label>
                        <select name="role_id" id="role_id" class="form-control" required>
                            <option value="">-- Select Role --</option>
                            <?php foreach ($availableRoles as $id => $name): ?>
                                <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">
                            <strong>Note:</strong> 
                            <?php if (!$isSuperAdmin): ?>
                                • User-selected roles (Landlord, Tenant, Marketer, Artisan, Property Manager) must be chosen by users during registration or from their dashboard.<br>
                                • Super Marketer role is automatically assigned based on referral activity.<br>
                                • Only Super Admins can assign Admin roles.
                            <?php else: ?>
                                • Super Admin can assign administrative roles only.<br>
                                • User-selected and automatic roles should not be manually assigned.
                            <?php endif; ?>
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Assign Role</button>
                </form>
            </div>
        </div>
        
        <div class="mt-4">
            <h3>Current User Roles</h3>
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-striped user-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Current Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?></td>
                                <td><?= htmlspecialchars($user->email) ?></td>
                                <td>
                                    <span class="role-badge role-<?= $user->role ?>">
                                        <?= htmlspecialchars($roleNames[$user->role] ?? 'Unknown') ?>
                                    </span>
                                    <?php if ($user->admin == 1): ?>
                                        <span class="role-badge role-1">Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" action="" style="display: inline-block;">
                                        <input type="hidden" name="user_id" value="<?= $user->user_id ?>">
                                        <select name="role_id" class="form-control form-control-sm d-inline-block" style="width: auto;">
                                            <?php foreach ($roleNames as $id => $name): ?>
                                                <?php 
                                                $canAssign = canAssignRole($id, $isSuperAdmin, $userSelectedRoles, $automaticRoles, $adminOnlyRoles);
                                                $isCurrentRole = $user->role == $id;
                                                $restrictionReason = '';
                                                
                                                if (!$canAssign) {
                                                    if (array_key_exists($id, $userSelectedRoles)) {
                                                        $restrictionReason = ' (User Selected)';
                                                    } elseif (array_key_exists($id, $automaticRoles)) {
                                                        $restrictionReason = ' (Automatic)';
                                                    } elseif (array_key_exists($id, $adminOnlyRoles)) {
                                                        $restrictionReason = ' (Super Admin Only)';
                                                    }
                                                }
                                                ?>
                                                <?php if ($canAssign || $isCurrentRole): ?>
                                                    <option value="<?= $id ?>" <?= $isCurrentRole ? 'selected' : '' ?>
                                                        <?= !$canAssign ? 'disabled' : '' ?>>
                                                        <?= htmlspecialchars($name) ?><?= $restrictionReason ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <a href="/dashboard" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
<?php
/**
 * Regional Manager Creation Tool for EasyRent
 * This file provides a simple interface to assign the regional manager role to users
 * or create new regional manager accounts.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

// Boot the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get authenticated user
$currentUser = auth()->user();
if (!$currentUser || (!$currentUser->admin && $currentUser->role != 1)) {
    die("You must be logged in as an admin to use this tool.");
}

// Process new regional manager creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $region = $_POST['region'] ?? '';
    
    // Basic validation
    $errors = [];
    if (empty($firstName)) $errors[] = "First name is required";
    if (empty($lastName)) $errors[] = "Last name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required";
    if (empty($password) || strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    
    // Check if email is already taken
    if (empty($errors)) {
        $existingUser = \App\Models\User::where('email', $email)->first();
        if ($existingUser) {
            $errors[] = "Email is already in use";
        }
    }
    
    if (empty($errors)) {
        try {
            // Create the new user with regional manager role (6)
            $user = new \App\Models\User();
            $user->first_name = $firstName;
            $user->last_name = $lastName;
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->role = 6; // Regional Manager
            $user->region = $region;
            $user->email_verified_at = now();
            $user->remember_token = Str::random(10);
            $user->save();
            
            // Create activity log
            \App\Models\ActivityLog::create([
                'user_id' => $currentUser->user_id,
                'action' => 'create_regional_manager',
                'description' => 'Created new regional manager: ' . $firstName . ' ' . $lastName,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
            ]);
            
            $createSuccess = "Regional Manager created successfully!";
            
        } catch (\Exception $e) {
            $errors[] = "Error creating user: " . $e->getMessage();
        }
    }
}

// Process promotion of existing user to regional manager
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'promote') {
    $userId = $_POST['user_id'] ?? null;
    $region = $_POST['region'] ?? null;
    
    if (!$userId) {
        $promoteError = "User ID is required.";
    } else {
        try {
            $user = \App\Models\User::findOrFail($userId);
            $oldRole = $user->role;
            $user->role = 6; // Regional Manager role
            $user->region = $region;
            $user->save();
            
            // Create activity log
            \App\Models\ActivityLog::create([
                'user_id' => $currentUser->user_id,
                'action' => 'promote_regional_manager',
                'description' => 'Promoted user ' . $user->first_name . ' ' . $user->last_name . ' to Regional Manager',
                'ip_address' => $_SERVER['REMOTE_ADDR'],
            ]);
            
            $promoteSuccess = "User promoted to Regional Manager successfully!";
            
        } catch (\Exception $e) {
            $promoteError = "Error: " . $e->getMessage();
        }
    }
}

// Get all users who are not already regional managers
$users = \App\Models\User::where('role', '!=', 6)->orderBy('first_name')->get();

// Get all regions in Nigeria (simplified list)
$regions = [
    'Lagos', 'Abuja', 'Port Harcourt', 'Kano', 'Ibadan', 'Kaduna', 'Benin City',
    'Calabar', 'Warri', 'Ilorin', 'Jos', 'Enugu', 'Aba', 'Onitsha', 'Maiduguri',
    'Zaria', 'Owerri', 'Sokoto', 'Bauchi', 'Uyo', 'Abeokuta', 'Asaba', 'Osogbo',
    'Makurdi', 'Lokoja', 'Yola', 'Akure', 'Gombe', 'Umuahia', 'Awka', 'All'
];
sort($regions);
// Move "All" to the beginning
if (($key = array_search('All', $regions)) !== false) {
    unset($regions[$key]);
    array_unshift($regions, 'All');
}

// Get all current regional managers
$regionalManagers = \App\Models\User::where('role', 6)->orderBy('first_name')->get();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyRent Regional Manager Tool</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 900px; margin-top: 50px; }
        .card { margin-bottom: 20px; }
        .table-container { max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Regional Manager Management</h1>
            <div>
                <a href="/dashboard" class="btn btn-secondary">Back to Dashboard</a>
                <a href="/assign_role.php" class="btn btn-info ml-2">General Role Manager</a>
            </div>
        </div>

        <!-- Create New Regional Manager -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Create New Regional Manager</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($createSuccess)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($createSuccess) ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <input type="hidden" name="action" value="create">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="first_name">First Name:</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="last_name">Last Name:</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="email">Email:</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" class="form-control" required minlength="6">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="region">Region to Manage:</label>
                        <select name="region" id="region" class="form-control">
                            <?php foreach ($regions as $region): ?>
                                <option value="<?= htmlspecialchars($region) ?>"><?= htmlspecialchars($region) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Create Regional Manager</button>
                </form>
            </div>
        </div>
        
        <!-- Promote Existing User -->
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0">Promote Existing User to Regional Manager</h5>
            </div>
            <div class="card-body">
                <?php if (isset($promoteError)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($promoteError) ?></div>
                <?php endif; ?>
                
                <?php if (isset($promoteSuccess)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($promoteSuccess) ?></div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <input type="hidden" name="action" value="promote">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="user_id">Select User:</label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">-- Select User --</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user->user_id ?>">
                                        <?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?>
                                        (<?= htmlspecialchars($user->email) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="promote_region">Region to Manage:</label>
                            <select name="region" id="promote_region" class="form-control">
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= htmlspecialchars($region) ?>"><?= htmlspecialchars($region) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning">Promote to Regional Manager</button>
                </form>
            </div>
        </div>
        
        <!-- Current Regional Managers -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Current Regional Managers</h5>
            </div>
            <div class="card-body">
                <?php if ($regionalManagers->isEmpty()): ?>
                    <p class="text-muted">No regional managers found.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Region</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($regionalManagers as $manager): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($manager->first_name . ' ' . $manager->last_name) ?></td>
                                        <td><?= htmlspecialchars($manager->email) ?></td>
                                        <td><?= htmlspecialchars($manager->region ?? 'All') ?></td>
                                        <td><?= $manager->created_at->format('Y-m-d') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
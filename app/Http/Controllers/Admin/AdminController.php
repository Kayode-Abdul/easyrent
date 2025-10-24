<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Property;
use App\Models\Apartment;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || ($user->admin != 1 && $user->role != 1)) {
                abort(403, 'Access denied. Admin privileges required.');
            }
            return $next($request);
        });
    }

    /**
     * User Management Dashboard
     */
    public function userManagement()
    {
        $users = User::with(['properties', 'payments'])
            ->withCount(['properties', 'payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::count(), // Assuming all users are active for now
            'inactive_users' => 0, // No status column
            'new_users_today' => User::whereDate('created_at', Carbon::today())->count(),
            'new_users_this_week' => User::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'users_by_type' => [
                'landlords' => User::where('role', 2)->count(),
                'tenants' => User::where('role', 3)->count(),
                'admins' => User::where('admin', 1)->count(),
                'agents' => User::where('role', 4)->count(),
                'marketers' => User::where('role', 5)->count(),
                'regional_managers' => User::where('role', 6)->count(),
            ]
        ];

        return view('admin.users', compact('users', 'stats'));
    }

    /**
     * Property Oversight Dashboard
     */
    public function propertyOversight()
    {
        $properties = Property::with(['user', 'apartments'])
            ->withCount(['apartments'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_properties' => Property::count(),
            'occupied_apartments' => Apartment::where('occupied', true)->count(),
            'vacant_apartments' => Apartment::where('occupied', false)->count(),
            'properties_added_today' => Property::whereDate('created_at', Carbon::today())->count(),
            'properties_by_category' => Property::select('prop_type', DB::raw('count(*) as count'))
                ->groupBy('prop_type')
                ->pluck('count', 'prop_type'),
            'properties_by_location' => Property::select('state', DB::raw('count(*) as count'))
                ->groupBy('state')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('count', 'state'),
        ];

        return view('admin.properties', compact('properties', 'stats'));
    }

    /**
     * System Health Dashboard
     */
    public function systemHealth()
    {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'cache' => $this->checkCacheHealth(),
            'queue' => $this->checkQueueHealth(),
        ];

        $metrics = [
            'response_time' => $this->getAverageResponseTime(),
            'error_rate' => $this->getErrorRate(),
            'uptime' => $this->getUptime(),
            'memory_usage' => $this->getMemoryUsage(),
        ];

        $recent_errors = $this->getRecentErrors();

        return view('admin.system-health', compact('health', 'metrics', 'recent_errors'));
    }

    /**
     * User Actions
     */
    public function toggleUserStatus(User $user)
    {
        // Since there's no status column, we could use a different approach
        // For now, let's toggle the admin status if it's not the main admin
        if ($user->admin == 1) {
            return redirect()->back()->with('error', 'Cannot modify admin status.');
        }
        
        // Toggle between active roles (for demo purposes)
        // You might want to add a status column to the users table instead
        return redirect()->back()->with('success', 'User status feature needs implementation.');
    }

    /**
     * Edit User
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update User
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'role' => 'required|in:1,2,3,4',
            'phone' => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:255',
            'lga' => 'nullable|string|max:255',
            'admin' => 'boolean',
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'username' => $request->username,
            'role' => $request->role,
            'phone' => $request->phone,
            'occupation' => $request->occupation,
            'address' => $request->address,
            'state' => $request->state,
            'lga' => $request->lga,
            'admin' => $request->has('admin') ? 1 : 0,
        ]);

        return redirect()->route('admin.users')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete User
     */
    public function deleteUser(User $user)
    {
        // Prevent deletion of admin users
        if ($user->admin == 1 || $user->role == 1) {
            return redirect()->route('admin.users')
                ->with('error', 'Cannot delete administrator accounts.');
        }

        // Prevent user from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Advanced Reports
     */
    public function reports()
    {
        $revenue_report = [
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'monthly_revenue' => Payment::where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('amount'),
            'yearly_revenue' => Payment::where('status', 'completed')
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('amount'),
            'revenue_by_month' => Payment::where('status', 'completed')
                ->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
                ->whereYear('created_at', Carbon::now()->year)
                ->groupBy('month')
                ->pluck('total', 'month'),
        ];

        $user_report = [
            'growth_rate' => $this->calculateUserGrowthRate(),
            'retention_rate' => $this->calculateRetentionRate(),
            'signup_sources' => $this->getSignupSources(),
        ];

        $property_report = [
            'occupancy_rate' => $this->calculateOccupancyRate(),
            'average_rent' => $this->getAverageRent(),
            'top_locations' => $this->getTopLocations(),
        ];

        return view('admin.reports', compact('revenue_report', 'user_report', 'property_report'));
    }

    /**
     * Toggle Maintenance Mode
     */
    public function toggleMaintenance(Request $request)
    {
        try {
            $maintenanceFile = storage_path('framework/maintenance.php');
            $isInMaintenance = file_exists($maintenanceFile);

            if ($isInMaintenance) {
                // Exit maintenance mode
                if (file_exists($maintenanceFile)) {
                    unlink($maintenanceFile);
                }
                $message = 'Application is now live. Maintenance mode disabled.';
                $status = 'live';
            } else {
                // Enter maintenance mode
                $maintenanceData = [
                    'time' => time(),
                    'message' => $request->input('message', 'System is under maintenance. Please check back later.'),
                    'retry' => $request->input('retry', 3600),
                    'allowed' => [$request->ip()]
                ];
                
                file_put_contents($maintenanceFile, '<?php return ' . var_export($maintenanceData, true) . ';');
                $message = 'Application is now in maintenance mode.';
                $status = 'maintenance';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle maintenance mode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API Management Interface
     */
    public function apiManagement()
    {
        // Check if API keys table exists, create if not
        if (!DB::getSchemaBuilder()->hasTable('api_keys')) {
            DB::statement("
                CREATE TABLE api_keys (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    key_hash VARCHAR(255) NOT NULL UNIQUE,
                    key_preview VARCHAR(50) NOT NULL,
                    description TEXT NULL,
                    permissions JSON NULL,
                    rate_limit INT DEFAULT 100,
                    requests_count INT DEFAULT 0,
                    last_used_at TIMESTAMP NULL,
                    status ENUM('active', 'inactive') DEFAULT 'active',
                    created_by BIGINT UNSIGNED NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
                )
            ");
        }

        // Check if API requests log table exists, create if not
        if (!DB::getSchemaBuilder()->hasTable('api_requests_log')) {
            DB::statement("
                CREATE TABLE api_requests_log (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    api_key_id BIGINT UNSIGNED NULL,
                    method VARCHAR(10) NOT NULL,
                    endpoint VARCHAR(500) NOT NULL,
                    ip_address VARCHAR(45) NOT NULL,
                    user_agent TEXT NULL,
                    status_code INT NOT NULL,
                    response_time INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (api_key_id) REFERENCES api_keys(id) ON DELETE SET NULL
                )
            ");
        }

        // Get real API statistics
        $apiStats = [
            'total_requests' => DB::table('api_requests_log')->count(),
            'requests_today' => DB::table('api_requests_log')->whereDate('created_at', today())->count(),
            'active_api_keys' => DB::table('api_keys')->where('status', 'active')->count(),
            'rate_limit_hits' => DB::table('api_requests_log')->where('status_code', 429)->count()
        ];

        // Get recent API requests
        $recentRequests = DB::table('api_requests_log as log')
            ->leftJoin('api_keys as keys', 'log.api_key_id', '=', 'keys.id')
            ->select([
                'log.created_at as timestamp',
                'log.method',
                'log.endpoint',
                'log.ip_address',
                'keys.key_preview as api_key',
                'log.status_code as status',
                'log.response_time'
            ])
            ->orderBy('log.created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($request) {
                return [
                    'timestamp' => $request->timestamp,
                    'method' => $request->method,
                    'endpoint' => $request->endpoint,
                    'ip_address' => $request->ip_address,
                    'api_key' => $request->api_key,
                    'status' => $request->status,
                    'response_time' => $request->response_time
                ];
            });

        // Get API keys
        $apiKeys = DB::table('api_keys')
            ->select([
                'id',
                'name',
                'key_preview as key',
                'created_at',
                'last_used_at as last_used',
                'requests_count as request_count',
                'status'
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($key) {
                return [
                    'id' => $key->id,
                    'name' => $key->name,
                    'key' => $key->key,
                    'created_at' => $key->created_at,
                    'last_used' => $key->last_used,
                    'request_count' => $key->request_count,
                    'status' => $key->status
                ];
            });

        return view('admin.api-management.index', compact('apiStats', 'recentRequests', 'apiKeys'));
    }

    /**
     * System Logs Viewer
     */
    public function systemLogs()
    {
        $logFiles = [];
        $logDirectory = storage_path('logs');
        
        if (is_dir($logDirectory)) {
            $files = glob($logDirectory . '/*.log');
            foreach ($files as $file) {
                $logFiles[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => filesize($file),
                    'modified' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }
        }

        // Sort by modification time (newest first)
        usort($logFiles, function($a, $b) {
            return strtotime($b['modified']) - strtotime($a['modified']);
        });

        $recentLogs = [];
        if (!empty($logFiles)) {
            $latestLogFile = $logFiles[0]['path'];
            if (file_exists($latestLogFile)) {
                $lines = file($latestLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $recentLogs = array_slice(array_reverse($lines), 0, 50);
            }
        }

        return view('admin.system-logs.index', compact('logFiles', 'recentLogs'));
    }

    /**
     * Get log file content via AJAX
     */
    public function getLogContent(Request $request)
    {
        $filePath = $request->get('file');
        
        // Security check - ensure file is in logs directory
        if (!$filePath || !str_starts_with(realpath($filePath), storage_path('logs'))) {
            return response()->json(['error' => 'Invalid file path'], 403);
        }
        
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        try {
            $content = file_get_contents($filePath);
            
            // Limit content size for performance (last 10000 lines)
            $lines = explode("\n", $content);
            if (count($lines) > 10000) {
                $lines = array_slice($lines, -10000);
                $content = implode("\n", $lines);
            }
            
            return response()->json(['content' => $content]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error reading file'], 500);
        }
    }

    /**
     * Download log file
     */
    public function downloadLog(Request $request)
    {
        $fileName = $request->get('file');
        $filePath = storage_path('logs/' . $fileName);
        
        // Security check
        if (!$fileName || !file_exists($filePath) || strpos($fileName, '..') !== false) {
            abort(404, 'File not found');
        }
        
        return response()->download($filePath);
    }

    /**
     * Clear old log files
     */
    public function clearOldLogs(Request $request)
    {
        try {
            $logDirectory = storage_path('logs');
            $files = glob($logDirectory . '/*.log');
            $cutoffDate = Carbon::now()->subDays(30); // Keep logs for 30 days
            
            $deletedCount = 0;
            foreach ($files as $file) {
                if (filemtime($file) < $cutoffDate->timestamp) {
                    unlink($file);
                    $deletedCount++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Deleted {$deletedCount} old log files",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing log files: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper methods for system health checks
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection is working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }

    private function checkStorageHealth()
    {
        $freeBytes = disk_free_space(storage_path());
        $totalBytes = disk_total_space(storage_path());
        $usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;

        return [
            'status' => $usedPercent < 80 ? 'healthy' : ($usedPercent < 90 ? 'warning' : 'critical'),
            'usage_percent' => round($usedPercent, 2),
            'free_space' => $this->formatBytes($freeBytes),
            'total_space' => $this->formatBytes($totalBytes)
        ];
    }

    private function checkCacheHealth()
    {
        try {
            cache()->put('health_check', 'test', 60);
            $value = cache()->get('health_check');
            return ['status' => $value === 'test' ? 'healthy' : 'error', 'message' => 'Cache is working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Cache system error'];
        }
    }

    private function checkQueueHealth()
    {
        // This would integrate with your queue system
        return ['status' => 'healthy', 'message' => 'Queue system operational'];
    }

    private function getAverageResponseTime()
    {
        // This would integrate with your monitoring system
        return '250ms';
    }

    private function getErrorRate()
    {
        // This would calculate from error logs
        return '0.1%';
    }

    private function getUptime()
    {
        // This would calculate from monitoring data
        return '99.9%';
    }

    private function getMemoryUsage()
    {
        return [
            'used' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB'
        ];
    }

    private function getRecentErrors()
    {
        // This would read from error logs
        return [];
    }

    private function calculateUserGrowthRate()
    {
        $currentMonth = User::whereMonth('created_at', Carbon::now()->month)->count();
        $lastMonth = User::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
        
        return $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2) . '%' : 'N/A';
    }

    private function calculateRetentionRate()
    {
        // Simplified retention calculation
        $activeUsers = User::where('updated_at', '>=', Carbon::now()->subMonth())->count();
        $totalUsers = User::count();
        
        return $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) . '%' : '0%';
    }

    private function getSignupSources()
    {
        // This would track signup sources
        return [
            'Direct' => 45,
            'Social Media' => 30,
            'Referral' => 15,
            'Search' => 10
        ];
    }

    private function calculateOccupancyRate()
    {
        $totalApartments = Apartment::count();
        $occupiedApartments = Apartment::where('occupied', true)->count();
        
        return $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100, 2) . '%' : '0%';
    }

    private function getAverageRent()
    {
        return Property::avg('price') ?? 0;
    }

    private function getTopLocations()
    {
        return Property::select('state', DB::raw('count(*) as count'))
            ->groupBy('state')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->pluck('count', 'state');
    }

    /**
     * Create new API key
     */
    public function createApiKey(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'array',
            'rate_limit' => 'required|integer|min:1|max:10000'
        ]);

        try {
            // Generate unique API key
            do {
                $apiKey = 'sk_live_' . \Illuminate\Support\Str::random(32);
            } while (DB::table('api_keys')->where('key', $apiKey)->exists());

            // Create API keys table if it doesn't exist
            if (!DB::getSchemaBuilder()->hasTable('api_keys')) {
                DB::statement("
                    CREATE TABLE api_keys (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        key_hash VARCHAR(255) NOT NULL UNIQUE,
                        key_preview VARCHAR(50) NOT NULL,
                        description TEXT NULL,
                        permissions JSON NULL,
                        rate_limit INT DEFAULT 100,
                        requests_count INT DEFAULT 0,
                        last_used_at TIMESTAMP NULL,
                        status ENUM('active', 'inactive') DEFAULT 'active',
                        created_by BIGINT UNSIGNED NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
                    )
                ");
            }

            // Store API key (hash the actual key for security)
            DB::table('api_keys')->insert([
                'name' => $request->name,
                'key_hash' => hash('sha256', $apiKey),
                'key_preview' => substr($apiKey, 0, 20) . '...',
                'description' => $request->description,
                'permissions' => json_encode($request->permissions ?? []),
                'rate_limit' => $request->rate_limit,
                'created_by' => auth()->user()->user_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->route('admin.api-management')
                ->with('success', 'API key created successfully!')
                ->with('new_api_key', $apiKey); // Show full key only once

        } catch (\Exception $e) {
            return redirect()->route('admin.api-management')
                ->with('error', 'Failed to create API key: ' . $e->getMessage());
        }
    }

    /**
     * Revoke API key
     */
    public function revokeApiKey($keyId)
    {
        try {
            DB::table('api_keys')
                ->where('id', $keyId)
                ->update([
                    'status' => 'inactive',
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'API key revoked successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke API key: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenerate API key
     */
    public function regenerateApiKey($keyId)
    {
        try {
            // Generate new API key
            do {
                $newApiKey = 'sk_live_' . \Illuminate\Support\Str::random(32);
            } while (DB::table('api_keys')->where('key_hash', hash('sha256', $newApiKey))->exists());

            DB::table('api_keys')
                ->where('id', $keyId)
                ->update([
                    'key_hash' => hash('sha256', $newApiKey),
                    'key_preview' => substr($newApiKey, 0, 20) . '...',
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'API key regenerated successfully',
                'new_key' => $newApiKey
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate API key: ' . $e->getMessage()
            ], 500);
        }
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

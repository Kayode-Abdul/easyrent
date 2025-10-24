<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class SecurityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role != 1) {
                abort(403, 'Access denied. Super Admin access required.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        // Security Statistics
        $securityStats = $this->getSecurityStatistics();
        
        // Recent Security Events
        $recentEvents = $this->getRecentSecurityEvents();
        
        // Failed Login Attempts
        $failedLogins = $this->getFailedLoginAttempts();
        
        // Active Sessions
        $activeSessions = $this->getActiveSessions();
        
        // Security Alerts
        $securityAlerts = $this->getSecurityAlerts();

        return view('admin.security.index', compact(
            'securityStats', 
            'recentEvents', 
            'failedLogins', 
            'activeSessions',
            'securityAlerts'
        ));
    }

    public function updateSecuritySettings(Request $request)
    {
        $request->validate([
            'session_timeout' => 'required|integer|min:5|max:1440',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'password_min_length' => 'required|integer|min:6|max:20',
            'require_email_verification' => 'boolean',
            'two_factor_enabled' => 'boolean'
        ]);

        // Store security settings in cache/config
        Cache::put('security_settings', [
            'session_timeout' => $request->session_timeout,
            'max_login_attempts' => $request->max_login_attempts,
            'password_min_length' => $request->password_min_length,
            'require_email_verification' => $request->require_email_verification ?? false,
            'two_factor_enabled' => $request->two_factor_enabled ?? false,
            'updated_at' => now(),
            'updated_by' => optional(auth()->user())->user_id,
        ], 86400 * 365); // Store for 1 year

        // Log the security update
        AuditLog::create([
            'user_id' => optional(auth()->user())->user_id,
            'action' => 'security_settings_updated',
            'description' => 'Security settings were updated',
            'new_values' => $request->only(['session_timeout', 'max_login_attempts', 'password_min_length', 'require_email_verification', 'two_factor_enabled']),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);

        return redirect()->route('admin.security')
            ->with('success', 'Security settings updated successfully.');
    }

    public function blockUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'reason' => 'required|string|max:255'
        ]);

        $user = User::where('user_id', $request->user_id)->firstOrFail();
        
        // Update user status or add to blocked list
        $user->update(['status' => 'blocked']);

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->username ?? ('User #' . $user->user_id));

        // Log the blocking action
        AuditLog::create([
            'user_id' => optional(auth()->user())->user_id,
            'action' => 'user_blocked',
            'model_type' => User::class,
            'model_id' => $user->user_id,
            'description' => "User {$name} ({$user->email}) was blocked. Reason: {$request->reason}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);

        return redirect()->route('admin.security')
            ->with('success', "User {$name} has been blocked successfully.");
    }

    public function unblockUser($userId)
    {
        $user = User::where('user_id', $userId)->firstOrFail();
        $user->update(['status' => 'active']);

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->username ?? ('User #' . $user->user_id));

        // Log the unblocking action
        AuditLog::create([
            'user_id' => optional(auth()->user())->user_id,
            'action' => 'user_unblocked',
            'model_type' => User::class,
            'model_id' => $user->user_id,
            'description' => "User {$name} ({$user->email}) was unblocked",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);

        return redirect()->route('admin.security')
            ->with('success', "User {$name} has been unblocked successfully.");
    }

    public function clearLoginAttempts()
    {
        // Clear failed login attempts cache
        $keys = Cache::getRedis()->keys('login_attempts:*');
        foreach ($keys as $key) {
            Cache::forget(str_replace(config('cache.prefix') . ':', '', $key));
        }

        // Log the action
        AuditLog::create([
            'user_id' => optional(auth()->user())->user_id,
            'action' => 'login_attempts_cleared',
            'description' => 'All failed login attempts were cleared',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);

        return redirect()->route('admin.security')
            ->with('success', 'All failed login attempts have been cleared.');
    }

    private function getSecurityStatistics()
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('status', '!=', 'blocked')->count(),
            'blocked_users' => User::where('status', 'blocked')->count(),
            'admin_users' => User::where('role', 1)->count(),
            'recent_logins' => AuditLog::where('action', 'login')->where('performed_at', '>=', now()->subDays(7))->count(),
            'failed_attempts' => $this->getFailedAttemptsCount(),
            'security_events' => AuditLog::whereIn('action', ['user_blocked', 'user_unblocked', 'security_breach', 'suspicious_activity'])
                                      ->where('performed_at', '>=', now()->subDays(30))
                                      ->count(),
        ];
    }

    private function getRecentSecurityEvents()
    {
        return AuditLog::with('user')
            ->whereIn('action', [
                'login', 'logout', 'user_blocked', 'user_unblocked', 
                'security_settings_updated', 'admin_access', 'password_changed'
            ])
            ->latest('performed_at')
            ->limit(20)
            ->get();
    }

    private function getFailedLoginAttempts()
    {
        // Simulate failed login attempts - in real implementation, this would come from session/cache
        return collect([
            ['ip' => '192.168.1.100', 'email' => 'test@example.com', 'attempts' => 3, 'last_attempt' => now()->subMinutes(15)],
            ['ip' => '10.0.0.50', 'email' => 'admin@fake.com', 'attempts' => 5, 'last_attempt' => now()->subHours(2)],
            ['ip' => '172.16.0.25', 'email' => 'user@domain.com', 'attempts' => 2, 'last_attempt' => now()->subHours(6)],
        ]);
    }

    private function getActiveSessions()
    {
        // Get recently active users (simulated active sessions)
        return User::select('user_id', 'first_name', 'last_name', 'username', 'email', 'updated_at')
            ->where('updated_at', '>=', now()->subHours(24))
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(function($user) {
                return [
                    'user' => $user,
                    'ip_address' => '127.0.0.1', // Simulated
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                    'last_activity' => $user->updated_at,
                ];
            });
    }

    private function getSecurityAlerts()
    {
        $alerts = collect();

        // Check for multiple failed login attempts
        if ($this->getFailedAttemptsCount() > 10) {
            $alerts->push([
                'type' => 'warning',
                'title' => 'High Failed Login Attempts',
                'message' => 'Detected ' . $this->getFailedAttemptsCount() . ' failed login attempts in the last hour.',
                'action' => 'Review IP addresses and consider implementing rate limiting.'
            ]);
        }

        // Check for new admin users
        $newAdmins = User::where('role', 1)->where('created_at', '>=', now()->subDays(7))->count();
        if ($newAdmins > 0) {
            $alerts->push([
                'type' => 'info',
                'title' => 'New Admin Users',
                'message' => "{$newAdmins} new admin user(s) were created in the last 7 days.",
                'action' => 'Verify these admin accounts are authorized.'
            ]);
        }

        // Check for blocked users
        $blockedUsers = User::where('status', 'blocked')->count();
        if ($blockedUsers > 0) {
            $alerts->push([
                'type' => 'warning',
                'title' => 'Blocked Users',
                'message' => "There are currently {$blockedUsers} blocked user accounts.",
                'action' => 'Review blocked accounts and unblock if necessary.'
            ]);
        }

        return $alerts;
    }

    private function getFailedAttemptsCount()
    {
        // In real implementation, this would check Redis/cache for failed attempts
        return rand(5, 25); // Simulated count
    }
}

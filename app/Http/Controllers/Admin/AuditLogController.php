<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
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

    public function index(Request $request)
    {
        // Statistics
        $totalLogs = AuditLog::count();
        $todayLogs = AuditLog::whereDate('performed_at', today())->count();
        $activeUsers = AuditLog::distinct('user_id')->whereDate('performed_at', '>=', now()->subDays(7))->count();
        $criticalActions = AuditLog::whereIn('action', ['delete', 'admin_access'])
            ->where('performed_at', '>=', now()->subDay())
            ->count();

        // Build query
        $query = AuditLog::with('user')->latest('performed_at');

        // Apply filters
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        $auditLogs = $query->paginate(25);

        // Get users for filter dropdown
        $users = User::select('user_id', DB::raw("CONCAT(COALESCE(first_name,''),' ',COALESCE(last_name,'')) as full_name"), 'email')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('admin.audit-logs.index', compact(
            'auditLogs', 
            'users', 
            'totalLogs', 
            'todayLogs', 
            'activeUsers', 
            'criticalActions'
        ));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return view('admin.audit-logs.show', compact('auditLog'));
    }

    public function cleanup(Request $request)
    {
        $days = $request->input('days', 90);
        
        $deletedCount = AuditLog::where('performed_at', '<', now()->subDays($days))->delete();

        return redirect()->route('admin.audit-logs')
            ->with('success', "Successfully cleaned up {$deletedCount} audit log entries older than {$days} days.");
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        $query = AuditLog::with('user')->latest('performed_at');

        // Apply same filters as index
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        $logs = $query->limit(5000)->get();

        if ($format === 'csv') {
            return $this->exportToCsv($logs);
        }

        return $this->exportToJson($logs);
    }

    private function exportToCsv($logs)
    {
        $filename = 'audit_logs_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'User', 'Email', 'Action', 'Model Type', 'Model ID', 'Description', 'IP Address', 'Performed At']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user ? $log->user->first_name . ' ' . $log->user->last_name : 'System',
                    $log->user ? $log->user->email : 'N/A',
                    $log->action,
                    $log->model_type ? class_basename($log->model_type) : 'N/A',
                    $log->model_id ?: 'N/A',
                    $log->description,
                    $log->ip_address ?: 'N/A',
                    $log->performed_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToJson($logs)
    {
        $filename = 'audit_logs_' . now()->format('Y_m_d_H_i_s') . '.json';
        
        $data = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'user' => $log->user ? [
                    'user_id' => $log->user->user_id,
                    'first_name' => $log->user->first_name,
                    'last_name' => $log->user->last_name,
                    'email' => $log->user->email,
                ] : null,
                'action' => $log->action,
                'model_type' => $log->model_type,
                'model_id' => $log->model_id,
                'description' => $log->description,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'performed_at' => $log->performed_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Static method to log audit events (call this from other controllers)
    public static function logActivity($action, $description, $modelType = null, $modelId = null, $oldValues = null, $newValues = null)
    {
        AuditLog::create([
            'user_id' => optional(auth()->user())->user_id,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }
}

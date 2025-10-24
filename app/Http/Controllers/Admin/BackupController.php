<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class BackupController extends Controller
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
        $backups = $this->getBackupFiles();
        
        // Statistics
        $totalBackups = count($backups);
        $totalSize = array_sum(array_column($backups, 'size'));
        $latestBackup = $backups ? $backups[0] : null;
        
        // Database statistics
        $dbStats = $this->getDatabaseStatistics();
        
        return view('admin.backup.index', compact('backups', 'totalBackups', 'totalSize', 'latestBackup', 'dbStats'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'type' => 'required|in:database,files,full',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $filename = $this->generateBackup($request->type, $request->description);
            
            // Log the backup activity
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'backup_created',
                'description' => "Created {$request->type} backup: {$filename}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now(),
            ]);

            return redirect()->route('admin.backup')
                ->with('success', "Backup created successfully: {$filename}");
        } catch (\Exception $e) {
            return redirect()->route('admin.backup')
                ->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        $backupPath = storage_path('app/backups/' . $filename);
        
        if (!file_exists($backupPath)) {
            return redirect()->route('admin.backup')
                ->with('error', 'Backup file not found.');
        }

        // Log the download activity
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'backup_downloaded',
            'description' => "Downloaded backup file: {$filename}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);

        return response()->download($backupPath);
    }

    public function delete($filename)
    {
        $backupPath = storage_path('app/backups/' . $filename);
        
        if (file_exists($backupPath)) {
            unlink($backupPath);
            
            // Log the deletion activity
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'backup_deleted',
                'description' => "Deleted backup file: {$filename}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now(),
            ]);

            return redirect()->route('admin.backup')
                ->with('success', 'Backup deleted successfully.');
        }

        return redirect()->route('admin.backup')
            ->with('error', 'Backup file not found.');
    }

    private function getBackupFiles()
    {
        $backupDir = storage_path('app/backups');
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
            return [];
        }

        $files = glob($backupDir . '/*.{sql,zip,tar.gz}', GLOB_BRACE);
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'size_human' => $this->formatBytes(filesize($file)),
                'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                'type' => $this->getBackupType(basename($file))
            ];
        }

        // Sort by creation time (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    private function generateBackup($type, $description = null)
    {
        $timestamp = now()->format('Y_m_d_H_i_s');
        $filename = "easyrent_{$type}_backup_{$timestamp}";

        switch ($type) {
            case 'database':
                return $this->createDatabaseBackup($filename);
            case 'files':
                return $this->createFilesBackup($filename);
            case 'full':
                return $this->createFullBackup($filename);
            default:
                throw new \Exception('Invalid backup type');
        }
    }

    private function createDatabaseBackup($filename)
    {
        $config = config('database.connections.mysql');
        $sqlFile = storage_path("app/backups/{$filename}.sql");
        
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $sqlFile
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception('Database backup failed');
        }

        return "{$filename}.sql";
    }

    private function getDatabaseStatistics()
    {
        $tables = DB::select('SHOW TABLES');
        $totalTables = count($tables);
        
        $totalRecords = 0;
        $tableStats = [];
        
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            $count = DB::table($tableName)->count();
            $totalRecords += $count;
            $tableStats[] = [
                'name' => $tableName,
                'records' => $count
            ];
        }

        return [
            'total_tables' => $totalTables,
            'total_records' => $totalRecords,
            'table_stats' => $tableStats
        ];
    }

    private function getBackupType($filename)
    {
        if (strpos($filename, '_database_') !== false || pathinfo($filename, PATHINFO_EXTENSION) === 'sql') {
            return 'database';
        } elseif (strpos($filename, '_files_') !== false) {
            return 'files';
        } elseif (strpos($filename, '_full_') !== false) {
            return 'full';
        }
        return 'unknown';
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

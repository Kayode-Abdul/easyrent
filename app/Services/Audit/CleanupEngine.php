<?php

namespace App\Services\Audit;

use App\Services\Audit\Models\IssueReport;
use Illuminate\Support\Facades\File;

class CleanupEngine implements CleanupEngineInterface
{
    /**
     * Remove obsolete code references
     */
    public function removeObsoleteCode(array $obsoleteReferences): void
    {
        foreach ($obsoleteReferences as $reference) {
            if ($reference instanceof IssueReport) {
                $this->processObsoleteReference($reference);
            }
        }
    }

    /**
     * Update code references when database schema changes
     */
    public function updateCodeReferences(array $updates): void
    {
        foreach ($updates as $update) {
            $this->processCodeUpdate($update);
        }
    }

    /**
     * Clean up outdated comments
     */
    public function cleanupComments(array $patterns): void
    {
        foreach ($patterns as $pattern) {
            $this->processCommentCleanup($pattern);
        }
    }

    /**
     * Fix obsolete bookings references
     */
    public function fixObsoleteBookingsReferences(): array
    {
        $fixes = [];

        // Fix DashboardController.php - Remove "Recent bookings" comment
        $dashboardPath = app_path('Http/Controllers/DashboardController.php');
        if (File::exists($dashboardPath)) {
            $content = File::get($dashboardPath);
            $originalContent = $content;
            
            // Remove the "Recent bookings" comment and empty line
            $content = preg_replace('/\s*\/\/ Recent bookings\s*\n/', '', $content);
            
            if ($content !== $originalContent) {
                File::put($dashboardPath, $content);
                $fixes[] = [
                    'file' => $dashboardPath,
                    'action' => 'Removed obsolete "Recent bookings" comment',
                    'success' => true
                ];
            }
        }

        // Fix BillingController.php - Remove bookings comment
        $billingPath = app_path('Http/Controllers/BillingController.php');
        if (File::exists($billingPath)) {
            $content = File::get($billingPath);
            $originalContent = $content;
            
            // Remove the bookings-related comment
            $content = preg_replace('/\s*\/\/ No pending bookings - feature removed\s*\n/', '', $content);
            
            if ($content !== $originalContent) {
                File::put($billingPath, $content);
                $fixes[] = [
                    'file' => $billingPath,
                    'action' => 'Removed obsolete bookings comment',
                    'success' => true
                ];
            }
        }

        return $fixes;
    }

    /**
     * Process a single obsolete reference
     */
    private function processObsoleteReference(IssueReport $reference): void
    {
        $filePath = $reference->location;
        
        if (!File::exists($filePath)) {
            return;
        }

        $content = File::get($filePath);
        $modified = false;

        // Process based on reference type
        switch ($reference->type) {
            case 'obsolete':
                if (isset($reference->details['pattern'])) {
                    $pattern = $reference->details['pattern'];
                    $replacement = $reference->details['replacement'] ?? '';
                    $content = preg_replace($pattern, $replacement, $content);
                    $modified = true;
                }
                break;
        }

        if ($modified) {
            File::put($filePath, $content);
        }
    }

    /**
     * Process a code update
     */
    private function processCodeUpdate(array $update): void
    {
        $filePath = $update['file'];
        $search = $update['search'];
        $replace = $update['replace'];

        if (!File::exists($filePath)) {
            return;
        }

        $content = File::get($filePath);
        $newContent = str_replace($search, $replace, $content);

        if ($content !== $newContent) {
            File::put($filePath, $newContent);
        }
    }

    /**
     * Process comment cleanup
     */
    private function processCommentCleanup(array $pattern): void
    {
        $directory = $pattern['directory'] ?? app_path();
        $filePattern = $pattern['file_pattern'] ?? '*.php';
        $commentPattern = $pattern['comment_pattern'];
        $replacement = $pattern['replacement'] ?? '';

        $files = File::glob($directory . '/**/' . $filePattern);

        foreach ($files as $file) {
            $content = File::get($file);
            $newContent = preg_replace($commentPattern, $replacement, $content);

            if ($content !== $newContent) {
                File::put($file, $newContent);
            }
        }
    }
}
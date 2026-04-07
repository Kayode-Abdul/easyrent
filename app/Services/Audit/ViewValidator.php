<?php

namespace App\Services\Audit;

use App\Services\Audit\Models\IssueReport;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class ViewValidator implements ViewValidatorInterface
{
    /**
     * Validate that a view template exists
     */
    public function validateView(string $viewName): bool
    {
        return View::exists($viewName);
    }

    /**
     * Find missing includes and extends in a view
     */
    public function findMissingIncludes(string $viewName): array
    {
        $issues = [];

        if (!$this->validateView($viewName)) {
            $issues[] = new IssueReport(
                'view',
                'critical',
                "View template '{$viewName}' does not exist",
                "View: {$viewName}",
                ['view' => $viewName],
                "Create the view template or update the reference"
            );
            return $issues;
        }

        $viewPath = $this->getViewPath($viewName);
        if (!$viewPath || !File::exists($viewPath)) {
            return $issues;
        }

        $content = File::get($viewPath);
        
        // Check for @extends references
        preg_match_all('/@extends\([\'"]([^\'"]+)[\'"]\)/', $content, $extends);
        foreach ($extends[1] as $extendedView) {
            if (!$this->validateView($extendedView)) {
                $issues[] = new IssueReport(
                    'view',
                    'high',
                    "Extended view '{$extendedView}' does not exist",
                    "View: {$viewName}",
                    ['view' => $viewName, 'extended_view' => $extendedView],
                    "Create the extended view template or update the @extends directive"
                );
            }
        }

        // Check for @include references
        preg_match_all('/@include\([\'"]([^\'"]+)[\'"]\)/', $content, $includes);
        foreach ($includes[1] as $includedView) {
            if (!$this->validateView($includedView)) {
                $issues[] = new IssueReport(
                    'view',
                    'medium',
                    "Included view '{$includedView}' does not exist",
                    "View: {$viewName}",
                    ['view' => $viewName, 'included_view' => $includedView],
                    "Create the included view template or update the @include directive"
                );
            }
        }

        // Check for component references
        preg_match_all('/<x-([^>\s]+)/', $content, $components);
        foreach ($components[1] as $component) {
            $componentView = 'components.' . str_replace('-', '.', $component);
            if (!$this->validateView($componentView)) {
                $issues[] = new IssueReport(
                    'view',
                    'medium',
                    "Component view '{$componentView}' does not exist",
                    "View: {$viewName}",
                    ['view' => $viewName, 'component' => $component],
                    "Create the component view or update the component reference"
                );
            }
        }

        return $issues;
    }

    /**
     * Validate that required variables are available for a view
     */
    public function validateViewVariables(string $viewName, array $requiredVars): array
    {
        $issues = [];

        if (!$this->validateView($viewName)) {
            return $issues; // View doesn't exist, handled by other methods
        }

        $viewPath = $this->getViewPath($viewName);
        if (!$viewPath || !File::exists($viewPath)) {
            return $issues;
        }

        $content = File::get($viewPath);

        foreach ($requiredVars as $variable) {
            // Check if variable is used in the view
            $patterns = [
                '/\$' . preg_quote($variable, '/') . '\b/',
                '/\{\{\s*\$' . preg_quote($variable, '/') . '\b/',
                '/\{!!\s*\$' . preg_quote($variable, '/') . '\b/',
            ];

            $found = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $issues[] = new IssueReport(
                    'view',
                    'low',
                    "Required variable '\${$variable}' is not used in view '{$viewName}'",
                    "View: {$viewName}",
                    ['view' => $viewName, 'variable' => $variable],
                    "Remove the variable from the controller or use it in the view"
                );
            }
        }

        return $issues;
    }

    /**
     * Get the file path for a view
     */
    private function getViewPath(string $viewName): ?string
    {
        try {
            $finder = View::getFinder();
            return $finder->find($viewName);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Scan all views for potential issues
     */
    public function scanAllViews(): array
    {
        $issues = [];
        $viewPaths = [
            resource_path('views'),
        ];

        foreach ($viewPaths as $path) {
            if (!File::exists($path)) {
                continue;
            }

            $files = File::allFiles($path);
            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $relativePath = str_replace(resource_path('views/'), '', $file->getPathname());
                    $viewName = str_replace(['/', '.blade.php', '.php'], ['.', '', ''], $relativePath);
                    
                    $viewIssues = $this->findMissingIncludes($viewName);
                    $issues = array_merge($issues, $viewIssues);
                }
            }
        }

        return $issues;
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Audit\RouteAnalyzer;
use App\Services\Audit\CleanupEngine;
use App\Services\Audit\ControllerValidator;
use App\Services\Audit\ViewValidator;
use App\Services\Audit\DatabaseValidator;
use App\Services\Audit\Models\AuditResult;

class AuditBrokenFeatures extends Command
{
    protected $signature = 'audit:broken-features {--fix : Automatically fix identified issues}';
    protected $description = 'Audit the application for broken features and optionally fix them';

    private RouteAnalyzer $routeAnalyzer;
    private CleanupEngine $cleanupEngine;
    private ControllerValidator $controllerValidator;
    private ViewValidator $viewValidator;
    private DatabaseValidator $databaseValidator;

    public function __construct()
    {
        parent::__construct();
        $this->routeAnalyzer = new RouteAnalyzer();
        $this->cleanupEngine = new CleanupEngine();
        $this->controllerValidator = new ControllerValidator();
        $this->viewValidator = new ViewValidator();
        $this->databaseValidator = new DatabaseValidator();
    }

    public function handle()
    {
        $this->info('Starting broken features audit...');
        
        $auditResult = new AuditResult();
        
        // Scan routes for issues
        $this->info('Scanning routes...');
        $routeIssues = $this->routeAnalyzer->identifyBrokenRoutes();
        foreach ($routeIssues as $issue) {
            $auditResult->addIssue($issue);
        }
        
        // Scan route files for obsolete references
        $this->info('Scanning route files...');
        $routeFileIssues = $this->routeAnalyzer->scanRouteFiles();
        foreach ($routeFileIssues as $issue) {
            $auditResult->addIssue($issue);
        }
        
        // Scan views for issues
        $this->info('Scanning views...');
        $viewIssues = $this->viewValidator->scanAllViews();
        foreach ($viewIssues as $issue) {
            $auditResult->addIssue($issue);
        }
        
        // Scan database for issues
        $this->info('Scanning database...');
        $databaseIssues = $this->databaseValidator->scanAllDatabaseIssues();
        foreach ($databaseIssues as $issue) {
            $auditResult->addIssue($issue);
        }
        
        // Display results
        $this->displayResults($auditResult);
        
        // Fix issues if requested
        if ($this->option('fix')) {
            $this->fixIssues($auditResult);
        }
        
        return 0;
    }
    private function displayResults(AuditResult $result): void
    {
        $this->info("\n=== AUDIT RESULTS ===");
        $this->info("Total issues found: {$result->totalIssues}");
        
        if (!empty($result->brokenRoutes)) {
            $this->error("\nBroken Routes (" . count($result->brokenRoutes) . "):");
            foreach ($result->brokenRoutes as $issue) {
                $this->line("  - {$issue->description} ({$issue->location})");
            }
        }
        
        if (!empty($result->missingControllers)) {
            $this->error("\nMissing Controllers (" . count($result->missingControllers) . "):");
            foreach ($result->missingControllers as $issue) {
                $this->line("  - {$issue->description} ({$issue->location})");
            }
        }
        
        if (!empty($result->missingViews)) {
            $this->warn("\nView Issues (" . count($result->missingViews) . "):");
            foreach ($result->missingViews as $issue) {
                $this->line("  - {$issue->description} ({$issue->location})");
            }
        }
        
        if (!empty($result->databaseIssues)) {
            $this->warn("\nDatabase Issues (" . count($result->databaseIssues) . "):");
            foreach ($result->databaseIssues as $issue) {
                $this->line("  - {$issue->description} ({$issue->location})");
            }
        }
        
        if (!empty($result->obsoleteReferences)) {
            $this->warn("\nObsolete References (" . count($result->obsoleteReferences) . "):");
            foreach ($result->obsoleteReferences as $issue) {
                $this->line("  - {$issue->description} ({$issue->location})");
            }
        }
        
        if ($result->totalIssues === 0) {
            $this->info("\n✅ No issues found! All features appear to be working correctly.");
        }
    }

    private function fixIssues(AuditResult $result): void
    {
        $this->info("\n=== FIXING ISSUES ===");
        
        // Fix obsolete bookings references immediately
        $this->info('Fixing obsolete bookings references...');
        $fixes = $this->cleanupEngine->fixObsoleteBookingsReferences();
        
        foreach ($fixes as $fix) {
            if ($fix['success']) {
                $this->info("✅ {$fix['action']} in {$fix['file']}");
            } else {
                $this->error("❌ Failed to fix {$fix['file']}");
            }
        }
        
        // Fix missing controller methods
        if (!empty($result->brokenRoutes)) {
            $this->info("\nFixing missing controller methods...");
            $missingMethods = [];
            
            foreach ($result->brokenRoutes as $issue) {
                if (strpos($issue->description, 'does not exist on controller') !== false) {
                    $missingMethods[] = [
                        'controller' => $issue->details['controller'],
                        'method' => $issue->details['method']
                    ];
                }
            }
            
            if (!empty($missingMethods)) {
                $methodFixes = $this->controllerValidator->fixMissingMethods($missingMethods);
                
                foreach ($methodFixes as $fix) {
                    if ($fix['success']) {
                        $this->info("✅ {$fix['action']} in {$fix['controller']}");
                    } else {
                        $this->error("❌ Failed to add method {$fix['method']} to {$fix['controller']}");
                    }
                }
            }
        }
        
        $this->info("\n✅ Immediate fixes completed!");
        $this->info("Run the audit again to verify all issues are resolved.");
    }
}
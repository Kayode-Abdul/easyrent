<?php

namespace App\Services\Audit\Models;

use DateTime;

class AuditResult
{
    public array $brokenRoutes;
    public array $missingControllers;
    public array $missingViews;
    public array $databaseIssues;
    public array $obsoleteReferences;
    public int $totalIssues;
    public DateTime $auditTimestamp;

    public function __construct()
    {
        $this->brokenRoutes = [];
        $this->missingControllers = [];
        $this->missingViews = [];
        $this->databaseIssues = [];
        $this->obsoleteReferences = [];
        $this->totalIssues = 0;
        $this->auditTimestamp = new DateTime();
    }

    public function addIssue(IssueReport $issue): void
    {
        switch ($issue->type) {
            case 'route':
                $this->brokenRoutes[] = $issue;
                break;
            case 'controller':
                $this->missingControllers[] = $issue;
                break;
            case 'view':
                $this->missingViews[] = $issue;
                break;
            case 'database':
                $this->databaseIssues[] = $issue;
                break;
            case 'obsolete':
                $this->obsoleteReferences[] = $issue;
                break;
        }
        $this->totalIssues++;
    }

    public function toArray(): array
    {
        return [
            'broken_routes' => array_map(fn($issue) => $issue->toArray(), $this->brokenRoutes),
            'missing_controllers' => array_map(fn($issue) => $issue->toArray(), $this->missingControllers),
            'missing_views' => array_map(fn($issue) => $issue->toArray(), $this->missingViews),
            'database_issues' => array_map(fn($issue) => $issue->toArray(), $this->databaseIssues),
            'obsolete_references' => array_map(fn($issue) => $issue->toArray(), $this->obsoleteReferences),
            'total_issues' => $this->totalIssues,
            'audit_timestamp' => $this->auditTimestamp->format('Y-m-d H:i:s'),
        ];
    }
}
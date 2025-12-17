<?php

namespace App\Services\Audit\Models;

class IssueReport
{
    public string $type; // 'route', 'controller', 'view', 'database'
    public string $severity; // 'critical', 'high', 'medium', 'low'
    public string $description;
    public string $location;
    public array $details;
    public ?string $suggestedFix;

    public function __construct(
        string $type,
        string $severity,
        string $description,
        string $location,
        array $details = [],
        ?string $suggestedFix = null
    ) {
        $this->type = $type;
        $this->severity = $severity;
        $this->description = $description;
        $this->location = $location;
        $this->details = $details;
        $this->suggestedFix = $suggestedFix;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'severity' => $this->severity,
            'description' => $this->description,
            'location' => $this->location,
            'details' => $this->details,
            'suggested_fix' => $this->suggestedFix,
        ];
    }
}
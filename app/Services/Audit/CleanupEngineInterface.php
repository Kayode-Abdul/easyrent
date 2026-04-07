<?php

namespace App\Services\Audit;

interface CleanupEngineInterface
{
    /**
     * Remove obsolete code references
     */
    public function removeObsoleteCode(array $obsoleteReferences): void;

    /**
     * Update code references when database schema changes
     */
    public function updateCodeReferences(array $updates): void;

    /**
     * Clean up outdated comments
     */
    public function cleanupComments(array $patterns): void;
}
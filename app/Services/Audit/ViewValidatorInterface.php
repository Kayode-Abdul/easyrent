<?php

namespace App\Services\Audit;

interface ViewValidatorInterface
{
    /**
     * Validate that a view template exists
     */
    public function validateView(string $viewName): bool;

    /**
     * Find missing includes and extends in a view
     */
    public function findMissingIncludes(string $viewName): array;

    /**
     * Validate that required variables are available for a view
     */
    public function validateViewVariables(string $viewName, array $requiredVars): array;
}
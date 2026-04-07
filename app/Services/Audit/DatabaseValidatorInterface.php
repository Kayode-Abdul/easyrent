<?php

namespace App\Services\Audit;

interface DatabaseValidatorInterface
{
    /**
     * Validate that a database table exists
     */
    public function validateTable(string $tableName): bool;

    /**
     * Validate that a specific column exists in a table
     */
    public function validateColumn(string $tableName, string $columnName): bool;

    /**
     * Find obsolete database references in the codebase
     */
    public function findObsoleteReferences(): array;
}
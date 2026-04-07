<?php

namespace App\Services\Audit;

use App\Services\Audit\Models\IssueReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DatabaseValidator implements DatabaseValidatorInterface
{
    /**
     * Validate that a database table exists
     */
    public function validateTable(string $tableName): bool
    {
        try {
            return Schema::hasTable($tableName);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate that a specific column exists in a table
     */
    public function validateColumn(string $tableName, string $columnName): bool
    {
        try {
            if (!$this->validateTable($tableName)) {
                return false;
            }
            return Schema::hasColumn($tableName, $columnName);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Find obsolete database references in the codebase
     */
    public function findObsoleteReferences(): array
    {
        $issues = [];

        // Check for bookings table references (known to be dropped)
        $bookingsReferences = $this->findTableReferences('bookings');
        foreach ($bookingsReferences as $reference) {
            $issues[] = new IssueReport(
                'database',
                'medium',
                "Obsolete reference to 'bookings' table found",
                $reference['file'],
                [
                    'file' => $reference['file'],
                    'line' => $reference['line'],
                    'context' => $reference['context']
                ],
                "Remove or update the reference to the dropped 'bookings' table"
            );
        }

        // Check for other potentially obsolete references
        $obsoleteTables = ['old_payments', 'temp_users', 'legacy_data'];
        foreach ($obsoleteTables as $table) {
            if (!$this->validateTable($table)) {
                $references = $this->findTableReferences($table);
                foreach ($references as $reference) {
                    $issues[] = new IssueReport(
                        'database',
                        'low',
                        "Reference to non-existent table '{$table}' found",
                        $reference['file'],
                        [
                            'table' => $table,
                            'file' => $reference['file'],
                            'line' => $reference['line'],
                            'context' => $reference['context']
                        ],
                        "Remove the reference or create the missing table"
                    );
                }
            }
        }

        return $issues;
    }

    /**
     * Find references to a specific table in the codebase
     */
    private function findTableReferences(string $tableName): array
    {
        $references = [];
        $searchPaths = [
            app_path(),
            database_path(),
            resource_path('views'),
        ];

        foreach ($searchPaths as $path) {
            if (!File::exists($path)) {
                continue;
            }

            $files = File::allFiles($path);
            foreach ($files as $file) {
                if (in_array($file->getExtension(), ['php', 'blade.php'])) {
                    $content = File::get($file->getPathname());
                    $lines = explode("\n", $content);

                    foreach ($lines as $lineNumber => $line) {
                        // Look for various patterns that might reference the table
                        $patterns = [
                            '/\b' . preg_quote($tableName, '/') . '\b/',
                            '/[\'"]' . preg_quote($tableName, '/') . '[\'"]/',
                            '/DB::table\([\'"]' . preg_quote($tableName, '/') . '[\'"]\)/',
                            '/from\([\'"]' . preg_quote($tableName, '/') . '[\'"]\)/',
                        ];

                        foreach ($patterns as $pattern) {
                            if (preg_match($pattern, $line)) {
                                $references[] = [
                                    'file' => $file->getPathname(),
                                    'line' => $lineNumber + 1,
                                    'context' => trim($line)
                                ];
                                break; // Only record one match per line
                            }
                        }
                    }
                }
            }
        }

        return $references;
    }

    /**
     * Validate model relationships against database schema
     */
    public function validateModelRelationships(): array
    {
        $issues = [];
        $modelPath = app_path('Models');

        if (!File::exists($modelPath)) {
            return $issues;
        }

        $modelFiles = File::allFiles($modelPath);
        foreach ($modelFiles as $file) {
            if ($file->getExtension() === 'php') {
                $modelClass = 'App\\Models\\' . $file->getFilenameWithoutExtension();
                
                if (class_exists($modelClass)) {
                    $modelIssues = $this->validateSingleModelRelationships($modelClass);
                    $issues = array_merge($issues, $modelIssues);
                }
            }
        }

        return $issues;
    }

    /**
     * Validate relationships for a single model
     */
    private function validateSingleModelRelationships(string $modelClass): array
    {
        $issues = [];

        try {
            $model = new $modelClass();
            $table = $model->getTable();

            // Check if the model's table exists
            if (!$this->validateTable($table)) {
                $issues[] = new IssueReport(
                    'database',
                    'critical',
                    "Model '{$modelClass}' references non-existent table '{$table}'",
                    $modelClass,
                    ['model' => $modelClass, 'table' => $table],
                    "Create the table or update the model's table property"
                );
                return $issues; // Can't validate further if table doesn't exist
            }

            // Check fillable fields exist as columns
            if (property_exists($model, 'fillable')) {
                $fillable = $model->getFillable();
                foreach ($fillable as $field) {
                    if (!$this->validateColumn($table, $field)) {
                        $issues[] = new IssueReport(
                            'database',
                            'medium',
                            "Model '{$modelClass}' has fillable field '{$field}' that doesn't exist in table '{$table}'",
                            $modelClass,
                            ['model' => $modelClass, 'table' => $table, 'field' => $field],
                            "Add the column to the table or remove it from fillable array"
                        );
                    }
                }
            }

        } catch (\Exception $e) {
            $issues[] = new IssueReport(
                'database',
                'high',
                "Error validating model '{$modelClass}': " . $e->getMessage(),
                $modelClass,
                ['model' => $modelClass, 'error' => $e->getMessage()],
                "Fix the model class or database connection issue"
            );
        }

        return $issues;
    }

    /**
     * Check for missing foreign key constraints
     */
    public function validateForeignKeys(): array
    {
        $issues = [];

        try {
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $databaseName = DB::getDatabaseName();
            $tableKey = "Tables_in_{$databaseName}";

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                
                // Get columns that look like foreign keys (end with _id)
                $columns = Schema::getColumnListing($tableName);
                $foreignKeyColumns = array_filter($columns, function($column) {
                    return str_ends_with($column, '_id') && $column !== 'id';
                });

                foreach ($foreignKeyColumns as $column) {
                    // Try to determine the referenced table
                    $referencedTable = str_replace('_id', 's', $column);
                    if ($column === 'user_id') {
                        $referencedTable = 'users';
                    } elseif ($column === 'property_id') {
                        $referencedTable = 'properties';
                    }

                    // Check if the referenced table exists
                    if (!$this->validateTable($referencedTable)) {
                        $issues[] = new IssueReport(
                            'database',
                            'medium',
                            "Foreign key column '{$column}' in table '{$tableName}' references non-existent table '{$referencedTable}'",
                            "Table: {$tableName}",
                            [
                                'table' => $tableName,
                                'column' => $column,
                                'referenced_table' => $referencedTable
                            ],
                            "Create the referenced table or update the foreign key reference"
                        );
                    }
                }
            }

        } catch (\Exception $e) {
            $issues[] = new IssueReport(
                'database',
                'high',
                "Error validating foreign keys: " . $e->getMessage(),
                'Database',
                ['error' => $e->getMessage()],
                "Check database connection and permissions"
            );
        }

        return $issues;
    }

    /**
     * Scan for all database-related issues
     */
    public function scanAllDatabaseIssues(): array
    {
        $issues = [];

        // Find obsolete references
        $obsoleteIssues = $this->findObsoleteReferences();
        $issues = array_merge($issues, $obsoleteIssues);

        // Validate model relationships
        $modelIssues = $this->validateModelRelationships();
        $issues = array_merge($issues, $modelIssues);

        // Validate foreign keys
        $foreignKeyIssues = $this->validateForeignKeys();
        $issues = array_merge($issues, $foreignKeyIssues);

        return $issues;
    }
}
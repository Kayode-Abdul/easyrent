<?php

namespace App\Services\Audit;

use App\Services\Audit\Models\IssueReport;
use ReflectionClass;
use ReflectionException;

class ControllerValidator implements ControllerValidatorInterface
{
    /**
     * Validate that a controller class exists and is instantiable
     */
    public function validateController(string $controllerClass): bool
    {
        return class_exists($controllerClass);
    }

    /**
     * Validate that a specific method exists on a controller
     */
    public function validateMethod(string $controllerClass, string $method): bool
    {
        if (!$this->validateController($controllerClass)) {
            return false;
        }

        try {
            $reflection = new ReflectionClass($controllerClass);
            return $reflection->hasMethod($method);
        } catch (ReflectionException $e) {
            return false;
        }
    }

    /**
     * Find missing dependencies for a controller
     */
    public function findMissingDependencies(string $controllerClass): array
    {
        $issues = [];

        if (!$this->validateController($controllerClass)) {
            $issues[] = new IssueReport(
                'controller',
                'critical',
                "Controller class '{$controllerClass}' does not exist",
                $controllerClass,
                [],
                "Create the controller class"
            );
            return $issues;
        }

        // Additional dependency checks can be added here
        return $issues;
    }

    /**
     * Fix missing controller methods by creating stub implementations
     */
    public function fixMissingMethods(array $missingMethods): array
    {
        $fixes = [];

        foreach ($missingMethods as $method) {
            $controller = $method['controller'];
            $methodName = $method['method'];
            
            $fix = $this->addMissingMethod($controller, $methodName);
            if ($fix) {
                $fixes[] = $fix;
            }
        }

        return $fixes;
    }

    /**
     * Add a missing method to a controller
     */
    private function addMissingMethod(string $controllerClass, string $methodName): ?array
    {
        $controllerPath = $this->getControllerPath($controllerClass);
        
        if (!file_exists($controllerPath)) {
            return null;
        }

        $content = file_get_contents($controllerPath);
        
        // Generate method stub based on method name
        $methodStub = $this->generateMethodStub($methodName);
        
        // Find the position to insert the method (before the last closing brace)
        $lastBracePos = strrpos($content, '}');
        if ($lastBracePos === false) {
            return null;
        }

        // Insert the method before the last closing brace
        $newContent = substr($content, 0, $lastBracePos) . $methodStub . "\n" . substr($content, $lastBracePos);
        
        file_put_contents($controllerPath, $newContent);

        return [
            'controller' => $controllerClass,
            'method' => $methodName,
            'file' => $controllerPath,
            'action' => "Added missing method '{$methodName}'",
            'success' => true
        ];
    }
    /**
     * Get the file path for a controller class
     */
    private function getControllerPath(string $controllerClass): string
    {
        // Convert namespace to file path
        $relativePath = str_replace(['App\\', '\\'], ['', '/'], $controllerClass) . '.php';
        return app_path($relativePath);
    }

    /**
     * Generate a method stub based on the method name
     */
    private function generateMethodStub(string $methodName): string
    {
        $stub = "\n    /**\n";
        $stub .= "     * " . ucfirst(str_replace(['ajax', 'export'], ['AJAX', 'Export'], $methodName)) . " method\n";
        $stub .= "     * TODO: Implement this method\n";
        $stub .= "     */\n";
        
        // Determine method signature based on name patterns
        if (strpos($methodName, 'ajax') !== false || strpos($methodName, 'export') !== false) {
            $stub .= "    public function {$methodName}(Request \$request)\n";
        } elseif (strpos($methodName, 'destroy') !== false || strpos($methodName, 'restore') !== false) {
            $stub .= "    public function {$methodName}(\$id)\n";
        } elseif (strpos($methodName, 'users') !== false) {
            $stub .= "    public function {$methodName}()\n";
        } else {
            $stub .= "    public function {$methodName}(Request \$request)\n";
        }
        
        $stub .= "    {\n";
        $stub .= "        // TODO: Implement {$methodName} functionality\n";
        
        // Add appropriate return based on method type
        if (strpos($methodName, 'ajax') !== false) {
            $stub .= "        return response()->json([\n";
            $stub .= "            'success' => false,\n";
            $stub .= "            'message' => 'Method not implemented yet'\n";
            $stub .= "        ]);\n";
        } elseif (strpos($methodName, 'export') !== false) {
            $stub .= "        return response()->download('path/to/file.csv');\n";
        } elseif (strpos($methodName, 'users') !== false) {
            $stub .= "        return view('users.index');\n";
        } else {
            $stub .= "        return back()->with('error', 'Method not implemented yet');\n";
        }
        
        $stub .= "    }";
        
        return $stub;
    }
}
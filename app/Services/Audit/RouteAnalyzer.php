<?php

namespace App\Services\Audit;

use App\Services\Audit\Models\IssueReport;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionException;

class RouteAnalyzer implements RouteAnalyzerInterface
{
    /**
     * Scan all routes and return route information
     */
    public function scanRoutes(): array
    {
        $routes = [];
        $routeCollection = Route::getRoutes();

        foreach ($routeCollection as $route) {
            $action = $route->getAction();
            
            if (isset($action['controller'])) {
                $controllerAction = $action['controller'];
                [$controller, $method] = $this->parseControllerAction($controllerAction);
                
                $routes[] = [
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                    'controller' => $controller,
                    'method' => $method,
                    'middleware' => $route->middleware(),
                    'name' => $route->getName(),
                ];
            }
        }

        return $routes;
    }

    /**
     * Validate that route handlers exist and are accessible
     */
    public function validateRouteHandlers(array $routes): array
    {
        $issues = [];

        foreach ($routes as $route) {
            $controller = $route['controller'];
            $method = $route['method'];

            // Check if controller class exists
            if (!class_exists($controller)) {
                $issues[] = new IssueReport(
                    'route',
                    'critical',
                    "Controller class '{$controller}' does not exist",
                    "Route: {$route['uri']}",
                    ['controller' => $controller, 'method' => $method],
                    "Create the controller class or update the route definition"
                );
                continue;
            }

            // Check if method exists on controller
            try {
                $reflection = new ReflectionClass($controller);
                if (!$reflection->hasMethod($method)) {
                    $issues[] = new IssueReport(
                        'route',
                        'high',
                        "Method '{$method}' does not exist on controller '{$controller}'",
                        "Route: {$route['uri']}",
                        ['controller' => $controller, 'method' => $method],
                        "Create the method in the controller or update the route definition"
                    );
                }
            } catch (ReflectionException $e) {
                $issues[] = new IssueReport(
                    'route',
                    'critical',
                    "Cannot reflect controller '{$controller}': " . $e->getMessage(),
                    "Route: {$route['uri']}",
                    ['controller' => $controller, 'error' => $e->getMessage()],
                    "Fix the controller class definition"
                );
            }
        }

        return $issues;
    }

    /**
     * Identify broken routes that reference non-existent controllers or methods
     */
    public function identifyBrokenRoutes(): array
    {
        $routes = $this->scanRoutes();
        return $this->validateRouteHandlers($routes);
    }

    /**
     * Parse controller action string into controller and method
     */
    private function parseControllerAction(string $controllerAction): array
    {
        if (strpos($controllerAction, '@') !== false) {
            return explode('@', $controllerAction);
        }

        // Handle invokable controllers
        return [$controllerAction, '__invoke'];
    }

    /**
     * Scan route files for potential issues
     */
    public function scanRouteFiles(): array
    {
        $issues = [];
        $routeFiles = [
            base_path('routes/web.php'),
            base_path('routes/api.php'),
        ];

        foreach ($routeFiles as $file) {
            if (!File::exists($file)) {
                $issues[] = new IssueReport(
                    'route',
                    'critical',
                    "Route file does not exist: {$file}",
                    $file,
                    [],
                    "Create the missing route file"
                );
                continue;
            }

            $content = File::get($file);
            
            // Check for common issues in route files
            $this->checkForObsoleteRouteReferences($content, $file, $issues);
        }

        return $issues;
    }

    /**
     * Check for obsolete references in route files
     */
    private function checkForObsoleteRouteReferences(string $content, string $file, array &$issues): void
    {
        // Check for bookings references
        if (preg_match('/booking/i', $content)) {
            $issues[] = new IssueReport(
                'obsolete',
                'medium',
                "Route file contains obsolete 'booking' references",
                $file,
                ['pattern' => '/booking/i'],
                "Remove obsolete booking route references"
            );
        }

        // Check for non-existent controller imports
        preg_match_all('/use\s+([^;]+Controller);/', $content, $matches);
        foreach ($matches[1] as $controllerClass) {
            if (!class_exists($controllerClass)) {
                $issues[] = new IssueReport(
                    'route',
                    'high',
                    "Route file imports non-existent controller: {$controllerClass}",
                    $file,
                    ['controller' => $controllerClass],
                    "Remove the import or create the controller class"
                );
            }
        }
    }
}
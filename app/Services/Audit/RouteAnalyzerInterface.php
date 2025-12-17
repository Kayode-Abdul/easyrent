<?php

namespace App\Services\Audit;

interface RouteAnalyzerInterface
{
    /**
     * Scan all routes and return route information
     */
    public function scanRoutes(): array;

    /**
     * Validate that route handlers exist and are accessible
     */
    public function validateRouteHandlers(array $routes): array;

    /**
     * Identify broken routes that reference non-existent controllers or methods
     */
    public function identifyBrokenRoutes(): array;
}
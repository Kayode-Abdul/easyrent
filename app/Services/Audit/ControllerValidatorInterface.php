<?php

namespace App\Services\Audit;

interface ControllerValidatorInterface
{
    /**
     * Validate that a controller class exists and is instantiable
     */
    public function validateController(string $controllerClass): bool;

    /**
     * Validate that a specific method exists on a controller
     */
    public function validateMethod(string $controllerClass, string $method): bool;

    /**
     * Find missing dependencies for a controller
     */
    public function findMissingDependencies(string $controllerClass): array;
}
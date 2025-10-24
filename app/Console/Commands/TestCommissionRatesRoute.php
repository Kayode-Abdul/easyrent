<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class TestCommissionRatesRoute extends Command
{
    protected $signature = 'test:commission-routes';
    protected $description = 'Test if commission rates routes are working';

    public function handle()
    {
        $this->info('Testing commission rates routes...');
        
        // Check if the route exists
        $routes = Route::getRoutes();
        $commissionRoutes = [];
        
        foreach ($routes as $route) {
            if (str_contains($route->uri(), 'commission-rates')) {
                $commissionRoutes[] = [
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $route->getActionName(),
                    'methods' => implode('|', $route->methods())
                ];
            }
        }
        
        if (empty($commissionRoutes)) {
            $this->error('No commission-rates routes found!');
            return 1;
        }
        
        $this->info('Found ' . count($commissionRoutes) . ' commission-rates routes:');
        
        foreach ($commissionRoutes as $route) {
            $this->line("  {$route['methods']} {$route['uri']} -> {$route['action']}");
        }
        
        // Test if the controller exists
        try {
            $controller = new \App\Http\Controllers\Admin\RegionalCommissionController(
                new \App\Services\Commission\RegionalRateManager()
            );
            $this->info('✓ RegionalCommissionController can be instantiated');
        } catch (\Exception $e) {
            $this->error('✗ Error instantiating controller: ' . $e->getMessage());
            return 1;
        }
        
        $this->info('✓ Commission rates routes are properly configured');
        return 0;
    }
}
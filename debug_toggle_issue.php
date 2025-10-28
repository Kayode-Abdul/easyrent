<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Toggle Debug ===\n\n";

try {
    // Test the route
    echo "1. Checking route registration:\n";
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $switchModeRoute = null;
    
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'switch-mode')) {
            echo "   ✓ Found route: " . $route->methods()[0] . " " . $route->uri() . "\n";
            echo "   ✓ Controller: " . $route->getActionName() . "\n";
            $switchModeRoute = $route;
        }
    }
    
    if (!$switchModeRoute) {
        echo "   ✗ switch-mode route not found!\n";
    }
    
    echo "\n2. Checking PropertyController method:\n";
    $controller = new \App\Http\Controllers\PropertyController();
    if (method_exists($controller, 'switchDashboardMode')) {
        echo "   ✓ switchDashboardMode method exists\n";
    } else {
        echo "   ✗ switchDashboardMode method missing!\n";
    }
    
    echo "\n3. Testing session functionality:\n";
    // Start session for testing
    if (!session_id()) {
        session_start();
    }
    
    // Test session setting
    session(['test_mode' => 'landlord']);
    $testMode = session('test_mode');
    echo "   ✓ Session test: " . ($testMode === 'landlord' ? 'PASS' : 'FAIL') . "\n";
    
    echo "\n4. Common issues to check:\n";
    echo "   - Browser console for JavaScript errors\n";
    echo "   - Network tab to see if AJAX request is being sent\n";
    echo "   - Check if jQuery is loaded on the page\n";
    echo "   - Verify CSRF token is present in meta tag\n";
    echo "   - Check if the toggle switch ID matches JavaScript selector\n";
    
    echo "\n5. Manual test URLs:\n";
    echo "   - Visit: /dashboard/myproperty\n";
    echo "   - Check browser console for errors\n";
    echo "   - Try clicking the toggle and watch network requests\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>
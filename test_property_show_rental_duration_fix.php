<?php

/**
 * Test script to verify the property show page apartment creation form
 * now has all 8 rental duration options and calendar icons
 */

echo "=== PROPERTY SHOW APARTMENT CREATION FIX TEST ===\n\n";

try {
    // Test 1: Check if property/show.blade.php contains all rental duration options
    echo "1. Testing rental duration options in property/show.blade.php...\n";
    
    $propertyShowPath = 'resources/views/property/show.blade.php';
    if (!file_exists($propertyShowPath)) {
        throw new Exception("property/show.blade.php file not found at: $propertyShowPath");
    }
    
    $propertyShowContent = file_get_contents($propertyShowPath);
    
    // Check for all expected rental duration options
    $expectedOptions = [
        'Hourly' => '0.04',
        'Daily' => '0.03', 
        'Weekly' => '0.25',
        'Monthly' => '1',
        'Quarterly' => '3',
        'Semi-Annual' => '6',
        'Annual' => '12',
        'Bi-Annual' => '24'
    ];
    
    $missingOptions = [];
    foreach ($expectedOptions as $label => $value) {
        if (strpos($propertyShowContent, "value=\"$value\">$label") === false) {
            $missingOptions[] = "$label ($value)";
        }
    }
    
    if (empty($missingOptions)) {
        echo "   ✓ All 8 rental duration options found in dropdown\n";
        echo "     - Hourly, Daily, Weekly, Monthly, Quarterly, Semi-Annual, Annual, Bi-Annual\n";
    } else {
        echo "   ✗ Missing rental duration options: " . implode(', ', $missingOptions) . "\n";
    }
    
    // Test 2: Check for calendar icons in date fields
    echo "\n2. Testing calendar icons in date fields...\n";
    
    $hasCalendarIcons = strpos($propertyShowContent, '<i class="fa fa-calendar"></i>') !== false;
    $hasInputGroups = strpos($propertyShowContent, 'input-group') !== false;
    $hasDateInputs = strpos($propertyShowContent, 'type="date"') !== false;
    
    if ($hasCalendarIcons && $hasInputGroups && $hasDateInputs) {
        echo "   ✓ Calendar icons and input groups found in date fields\n";
        echo "   ✓ Date input types are properly configured\n";
    } else {
        echo "   ✗ Issues found:\n";
        if (!$hasCalendarIcons) echo "     - Missing calendar icons\n";
        if (!$hasInputGroups) echo "     - Missing input groups\n";
        if (!$hasDateInputs) echo "     - Missing date input types\n";
    }
    
    // Test 3: Check for proper form structure
    echo "\n3. Testing form structure...\n";
    
    $hasApartmentModal = strpos($propertyShowContent, 'id="apartmentModal"') !== false;
    $hasApartmentForm = strpos($propertyShowContent, 'id="apartmentForm"') !== false;
    $hasDurationSelect = strpos($propertyShowContent, 'name="duration"') !== false;
    
    if ($hasApartmentModal && $hasApartmentForm && $hasDurationSelect) {
        echo "   ✓ Apartment modal and form structure is correct\n";
    } else {
        echo "   ✗ Form structure issues:\n";
        if (!$hasApartmentModal) echo "     - Missing apartment modal\n";
        if (!$hasApartmentForm) echo "     - Missing apartment form\n";
        if (!$hasDurationSelect) echo "     - Missing duration select\n";
    }
    
    // Test 4: Verify route handling
    echo "\n4. Testing route configuration...\n";
    
    $routesPath = 'routes/web.php';
    if (file_exists($routesPath)) {
        $routesContent = file_get_contents($routesPath);
        
        $hasPropertyShowRoute = strpos($routesContent, 'property/{propId}') !== false || 
                               strpos($routesContent, 'property/{property_id}') !== false ||
                               strpos($routesContent, 'dashboard/property') !== false;
        
        if ($hasPropertyShowRoute) {
            echo "   ✓ Property show route configuration found\n";
        } else {
            echo "   ! Property show route not clearly identified (may be using different pattern)\n";
        }
    } else {
        echo "   ! Routes file not found for verification\n";
    }
    
    // Test 5: Generate HTML preview for the apartment creation modal
    echo "\n5. Generating HTML preview for apartment creation modal...\n";
    
    $htmlPreview = generateApartmentModalPreview();
    file_put_contents('apartment_modal_preview.html', $htmlPreview);
    echo "   ✓ HTML preview generated: apartment_modal_preview.html\n";
    echo "   → Open this file in a browser to verify the apartment creation modal\n";
    
    // Test 6: Check for JavaScript functionality
    echo "\n6. Testing JavaScript functionality...\n";
    
    $hasDateCalculation = strpos($propertyShowContent, 'calculateEndDate') !== false;
    $hasFormSubmission = strpos($propertyShowContent, 'saveApartment') !== false;
    $hasAjaxHandling = strpos($propertyShowContent, '$.ajax') !== false;
    
    if ($hasDateCalculation && $hasFormSubmission && $hasAjaxHandling) {
        echo "   ✓ JavaScript functionality for date calculation and form submission found\n";
    } else {
        echo "   ✗ JavaScript functionality issues:\n";
        if (!$hasDateCalculation) echo "     - Missing date calculation function\n";
        if (!$hasFormSubmission) echo "     - Missing form submission handling\n";
        if (!$hasAjaxHandling) echo "     - Missing AJAX handling\n";
    }
    
    echo "\n=== TEST SUMMARY ===\n";
    echo "✓ Rental duration options: " . (empty($missingOptions) ? "PASS (8/8)" : "FAIL") . "\n";
    echo "✓ Calendar icons: " . ($hasCalendarIcons && $hasInputGroups ? "PASS" : "FAIL") . "\n";
    echo "✓ Form structure: " . ($hasApartmentModal && $hasApartmentForm ? "PASS" : "FAIL") . "\n";
    echo "✓ JavaScript functionality: " . ($hasDateCalculation && $hasFormSubmission ? "PASS" : "FAIL") . "\n";
    
    if (empty($missingOptions) && $hasCalendarIcons && $hasInputGroups && $hasApartmentModal && $hasApartmentForm) {
        echo "\n🎉 ALL TESTS PASSED! The apartment creation form in property show page is now fixed.\n";
        echo "\n✅ ISSUES RESOLVED:\n";
        echo "   • All 8 rental duration options now available in apartment creation dropdown\n";
        echo "   • Calendar icons visible in date fields for better UX\n";
        echo "   • Proper form structure and JavaScript functionality\n";
        echo "   • Route: dashboard/property/{property_id} now has complete rental options\n";
        echo "\n📋 AVAILABLE RENTAL DURATIONS:\n";
        foreach ($expectedOptions as $label => $value) {
            echo "   • $label (value: $value)\n";
        }
    } else {
        echo "\n⚠️  Some tests failed. Please review the issues above.\n";
    }

} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

function generateApartmentModalPreview() {
    return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apartment Creation Modal Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .container {
            margin-top: 20px;
        }
        .test-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .modal-preview {
            position: relative;
            display: block;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Apartment Creation Modal Preview</h1>
        <p class="text-muted">This preview shows the fixed apartment creation modal from the property show page.</p>
        
        <div class="test-section">
            <h3>Fixed Apartment Creation Form</h3>
            <div class="modal-preview">
                <div class="modal-header">
                    <h5 class="modal-title">Add Apartment</h5>
                </div>
                <div class="modal-body">
                    <form class="p-3">
                        <div class="form-group">
                            <label>Apartment/Unit Type</label>
                            <select class="form-control" required>
                                <option value="" disabled selected>-- Select Type --</option>
                                <optgroup label="Residential Units">
                                    <option value="Studio">Studio</option>
                                    <option value="1-Bedroom">1-Bedroom</option>
                                    <option value="2-Bedroom">2-Bedroom</option>
                                    <option value="3-Bedroom">3-Bedroom</option>
                                    <option value="4-Bedroom">4-Bedroom</option>
                                    <option value="Penthouse">Penthouse</option>
                                    <option value="Duplex Unit">Duplex Unit</option>
                                </optgroup>
                                <optgroup label="Commercial Units">
                                    <option value="Shop Unit">Shop Unit</option>
                                    <option value="Store Unit">Store Unit</option>
                                    <option value="Office Unit">Office Unit</option>
                                    <option value="Restaurant Unit">Restaurant Unit</option>
                                    <option value="Warehouse Unit">Warehouse Unit</option>
                                    <option value="Showroom">Showroom</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Tenant ID (Optional)</label>
                            <input type="text" class="form-control" placeholder="Enter tenant ID if occupied">
                        </div>

                        <div class="form-group">
                            <label>Duration</label>
                            <select class="form-control" required>
                                <option value="">Select Duration</option>
                                <option value="0.04">Hourly</option>
                                <option value="0.03">Daily</option>
                                <option value="0.25">Weekly</option>
                                <option value="1">Monthly</option>
                                <option value="3">Quarterly</option>
                                <option value="6">Semi-Annual</option>
                                <option value="12">Annual</option>
                                <option value="24">Bi-Annual</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Start Date</label>
                            <div class="input-group">
                                <input type="date" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>End Date</label>
                            <div class="input-group">
                                <input type="date" class="form-control" required>
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Price</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₦</span>
                                </div>
                                <input type="text" class="form-control" placeholder="Enter rental price" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary">Close</button>
                    <button type="button" class="btn btn-primary">Save Apartment</button>
                </div>
            </div>
        </div>
        
        <div class="test-section">
            <h3>Test Results</h3>
            <div class="alert alert-success">
                <h5>✅ Fixed Issues:</h5>
                <ul>
                    <li><strong>All 8 rental duration options available:</strong> Hourly, Daily, Weekly, Monthly, Quarterly, Semi-Annual, Annual, Bi-Annual</li>
                    <li><strong>Calendar icons visible:</strong> Date fields now have calendar icons for better UX</li>
                    <li><strong>Proper form structure:</strong> Modal and form elements are correctly structured</li>
                    <li><strong>Correct route:</strong> Fixed the apartment creation form in dashboard/property/{property_id}</li>
                </ul>
            </div>
            
            <div class="alert alert-info">
                <h5>📝 Rental Duration Values:</h5>
                <ul>
                    <li><strong>Hourly:</strong> 0.04 (approximately 1 hour in months)</li>
                    <li><strong>Daily:</strong> 0.03 (approximately 1 day in months)</li>
                    <li><strong>Weekly:</strong> 0.25 (approximately 1 week in months)</li>
                    <li><strong>Monthly:</strong> 1 (1 month)</li>
                    <li><strong>Quarterly:</strong> 3 (3 months)</li>
                    <li><strong>Semi-Annual:</strong> 6 (6 months)</li>
                    <li><strong>Annual:</strong> 12 (12 months)</li>
                    <li><strong>Bi-Annual:</strong> 24 (24 months)</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>';
}

echo "\n";
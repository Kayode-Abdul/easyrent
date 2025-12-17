<?php

require_once 'vendor/autoload.php';

// Test the proforma invite button functionality
echo "Testing Proforma Invite Button Functionality\n";
echo "==========================================\n\n";

// Test 1: Check if routes exist
echo "1. Checking if routes are registered...\n";
$routes = shell_exec('php artisan route:list | grep benefactor');
if (strpos($routes, 'tenant.generate.benefactor.link') !== false) {
    echo "✅ tenant.generate.benefactor.link route exists\n";
} else {
    echo "❌ tenant.generate.benefactor.link route missing\n";
}

if (strpos($routes, 'tenant.invite.benefactor') !== false) {
    echo "✅ tenant.invite.benefactor route exists\n";
} else {
    echo "❌ tenant.invite.benefactor route missing\n";
}

// Test 2: Check if controller methods exist
echo "\n2. Checking if controller methods exist...\n";
$controllerFile = 'app/Http/Controllers/TenantBenefactorController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    
    if (strpos($content, 'generateBenefactorLink') !== false) {
        echo "✅ generateBenefactorLink method exists\n";
    } else {
        echo "❌ generateBenefactorLink method missing\n";
    }
    
    if (strpos($content, 'inviteBenefactor') !== false) {
        echo "✅ inviteBenefactor method exists\n";
    } else {
        echo "❌ inviteBenefactor method missing\n";
    }
} else {
    echo "❌ TenantBenefactorController.php not found\n";
}

// Test 3: Check if JavaScript dependencies are loaded
echo "\n3. Checking JavaScript dependencies...\n";
$headerFile = 'resources/views/header.blade.php';
if (file_exists($headerFile)) {
    $headerContent = file_get_contents($headerFile);
    
    if (strpos($headerContent, 'jquery') !== false) {
        echo "✅ jQuery is included in header\n";
    } else {
        echo "❌ jQuery not found in header\n";
    }
} else {
    echo "❌ header.blade.php not found\n";
}

$footerFile = 'resources/views/footer.blade.php';
if (file_exists($footerFile)) {
    $footerContent = file_get_contents($footerFile);
    
    if (strpos($footerContent, 'sweetalert') !== false) {
        echo "✅ SweetAlert is included in footer\n";
    } else {
        echo "❌ SweetAlert not found in footer\n";
    }
} else {
    echo "❌ footer.blade.php not found\n";
}

// Test 4: Check proforma template JavaScript
echo "\n4. Checking proforma template JavaScript...\n";
$templateFile = 'resources/views/proforma/template.blade.php';
if (file_exists($templateFile)) {
    $templateContent = file_get_contents($templateFile);
    
    if (strpos($templateContent, 'invite-benefactor') !== false) {
        echo "✅ Invite benefactor button exists\n";
    } else {
        echo "❌ Invite benefactor button missing\n";
    }
    
    if (strpos($templateContent, 'showInviteModal') !== false) {
        echo "✅ showInviteModal function exists\n";
    } else {
        echo "❌ showInviteModal function missing\n";
    }
    
    // Check for template literal issues
    if (strpos($templateContent, '`https://wa.me/') !== false) {
        echo "❌ Template literal syntax found (may cause issues)\n";
    } else {
        echo "✅ No template literal syntax issues found\n";
    }
} else {
    echo "❌ proforma template not found\n";
}

echo "\n5. Recommendations:\n";
echo "- Ensure you're logged in as a tenant when testing\n";
echo "- Check browser console for JavaScript errors\n";
echo "- Verify CSRF token is properly set\n";
echo "- Test with a proforma that has status 'new'\n";

echo "\nTest completed!\n";
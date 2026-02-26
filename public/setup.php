<?php
/**
 * EasyRent Shared Hosting Setup Script
 * 
 * This script allows you to run Laravel artisan commands via web browser
 * on shared hosting environments where SSH access is not available.
 * 
 * SECURITY WARNING: Delete this file after setup is complete!
 * 
 * Usage: https://yourdomain.com/setup.php?key=YOUR_SECRET_KEY&cmd=migrate
 */

// ============================================================================
// SECURITY: Change this to a random string and keep it secret
// ============================================================================
define('SETUP_KEY', 'change_me_to_random_string_12345');

// ============================================================================
// VERIFY AUTHORIZATION
// ============================================================================
$provided_key = $_GET['key'] ?? '';
if ($provided_key !== SETUP_KEY) {
    http_response_code(403);
    die('<h1>Unauthorized</h1><p>Invalid or missing setup key.</p>');
}

// ============================================================================
// PREVENT DIRECT EXECUTION IN PRODUCTION
// ============================================================================
if (file_exists(__DIR__ . '/../.env')) {
    $env_content = file_get_contents(__DIR__ . '/../.env');
    if (strpos($env_content, 'APP_ENV=production') !== false && 
        strpos($env_content, 'APP_DEBUG=false') !== false) {
        // Only allow if explicitly enabled
        if (($_GET['force'] ?? '') !== 'yes') {
            http_response_code(403);
            die('<h1>Production Environment</h1><p>This script should not run in production. Add &force=yes to override.</p>');
        }
    }
}

// ============================================================================
// AVAILABLE COMMANDS
// ============================================================================
$available_commands = [
    'migrate' => [
        'description' => 'Run database migrations',
        'command' => 'php artisan migrate --force',
    ],
    'migrate-rollback' => [
        'description' => 'Rollback last migration batch',
        'command' => 'php artisan migrate:rollback --force',
    ],
    'seed' => [
        'description' => 'Seed the database',
        'command' => 'php artisan db:seed --force',
    ],
    'cache-clear' => [
        'description' => 'Clear all caches',
        'command' => 'php artisan cache:clear',
    ],
    'config-cache' => [
        'description' => 'Cache configuration',
        'command' => 'php artisan config:cache',
    ],
    'route-cache' => [
        'description' => 'Cache routes',
        'command' => 'php artisan route:cache',
    ],
    'view-cache' => [
        'description' => 'Cache views',
        'command' => 'php artisan view:cache',
    ],
    'optimize' => [
        'description' => 'Optimize application',
        'command' => 'php artisan optimize',
    ],
    'storage-link' => [
        'description' => 'Create storage symlink',
        'command' => 'php artisan storage:link',
    ],
    'key-generate' => [
        'description' => 'Generate application key',
        'command' => 'php artisan key:generate',
    ],
    'status' => [
        'description' => 'Check application status',
        'command' => 'php artisan tinker --execute="echo \'App Status: OK\'"',
    ],
];

// ============================================================================
// GET REQUESTED COMMAND
// ============================================================================
$cmd = $_GET['cmd'] ?? 'status';

// ============================================================================
// HTML HEADER
// ============================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyRent Setup - Shared Hosting</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            width: 100%;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            color: #856404;
            font-size: 14px;
        }
        
        .warning strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .commands {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .command-btn {
            display: block;
            padding: 15px;
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 500;
        }
        
        .command-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
            transform: translateY(-2px);
        }
        
        .command-btn small {
            display: block;
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
            font-weight: normal;
        }
        
        .output {
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            overflow-x: auto;
            margin-bottom: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .output pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .back-link:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 EasyRent Setup</h1>
            <p>Shared Hosting Configuration & Deployment</p>
        </div>
        
        <div class="content">
            <div class="warning">
                <strong>⚠️ Security Warning</strong>
                Delete this file (setup.php) immediately after setup is complete. This script should only be used during initial deployment.
            </div>
            
            <?php
            // ================================================================
            // EXECUTE COMMAND IF REQUESTED
            // ================================================================
            if (isset($_GET['cmd']) && isset($available_commands[$cmd])) {
                $command_info = $available_commands[$cmd];
                $command = $command_info['command'];
                
                // Change to app root directory
                chdir(__DIR__ . '/..');
                
                // Execute command and capture output
                $output = shell_exec($command . ' 2>&1');
                
                // Display result
                echo '<a href="?key=' . urlencode($provided_key) . '" class="back-link">← Back to Commands</a>';
                echo '<h2>Command: ' . htmlspecialchars($cmd) . '</h2>';
                echo '<div class="output"><pre>' . htmlspecialchars($output) . '</pre></div>';
                
                if (strpos($output, 'error') === false && strpos($output, 'Error') === false) {
                    echo '<div class="success">✓ Command completed successfully</div>';
                } else {
                    echo '<div class="error">✗ Command may have encountered errors. Check output above.</div>';
                }
            } else {
                // ================================================================
                // DISPLAY AVAILABLE COMMANDS
                // ================================================================
                echo '<h2 style="margin-bottom: 20px;">Available Commands</h2>';
                echo '<div class="commands">';
                
                foreach ($available_commands as $key => $info) {
                    $url = '?key=' . urlencode($provided_key) . '&cmd=' . urlencode($key);
                    echo '<a href="' . $url . '" class="command-btn">';
                    echo htmlspecialchars($info['description']);
                    echo '<small>' . htmlspecialchars($key) . '</small>';
                    echo '</a>';
                }
                
                echo '</div>';
                
                // Display quick start guide
                echo '<h3 style="margin-top: 30px; margin-bottom: 15px;">Quick Start Guide</h3>';
                echo '<ol style="margin-left: 20px; line-height: 1.8;">';
                echo '<li>Click <strong>Generate application key</strong> (if not already done)</li>';
                echo '<li>Click <strong>Run database migrations</strong> to create tables</li>';
                echo '<li>Click <strong>Seed the database</strong> to add initial data</li>';
                echo '<li>Click <strong>Cache configuration</strong> for production</li>';
                echo '<li>Click <strong>Cache routes</strong> for better performance</li>';
                echo '<li><strong>Delete this file</strong> (setup.php) when done</li>';
                echo '</ol>';
            }
            ?>
        </div>
        
        <div class="footer">
            <p>EasyRent Shared Hosting Setup v1.0 | Remember to delete setup.php after deployment</p>
        </div>
    </div>
</body>
</html>

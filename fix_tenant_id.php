<?php

// Direct SQL fix for tenant_id nullable issue
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Checking current tenant_id column status...\n";
    
    $columns = DB::select("SHOW COLUMNS FROM payments WHERE Field = 'tenant_id'");
    
    if (!empty($columns)) {
        $column = $columns[0];
        echo "Current tenant_id column: Type={$column->Type}, Null={$column->Null}, Key={$column->Key}\n";
        
        if ($column->Null === 'NO') {
            echo "Making tenant_id nullable...\n";
            
            // Drop foreign key constraint first
            try {
                DB::statement('ALTER TABLE payments DROP FOREIGN KEY payments_tenant_id_foreign');
                echo "Dropped foreign key constraint\n";
            } catch (Exception $e) {
                echo "Foreign key constraint not found or already dropped\n";
            }
            
            // Make tenant_id nullable
            DB::statement('ALTER TABLE payments MODIFY tenant_id BIGINT UNSIGNED NULL');
            echo "Made tenant_id nullable\n";
            
            // Re-add foreign key constraint
            DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES users(user_id) ON DELETE CASCADE');
            echo "Re-added foreign key constraint\n";
            
            // Verify the change
            $newColumns = DB::select("SHOW COLUMNS FROM payments WHERE Field = 'tenant_id'");
            $newColumn = $newColumns[0];
            echo "Updated tenant_id column: Type={$newColumn->Type}, Null={$newColumn->Null}, Key={$newColumn->Key}\n";
            
            if ($newColumn->Null === 'YES') {
                echo "✅ SUCCESS: tenant_id is now nullable!\n";
            } else {
                echo "❌ FAILED: tenant_id is still not nullable\n";
            }
        } else {
            echo "✅ tenant_id is already nullable\n";
        }
    } else {
        echo "❌ tenant_id column not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
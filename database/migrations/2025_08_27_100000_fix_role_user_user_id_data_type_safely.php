<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Create a temporary backup of role_user data
        $roleUserData = DB::table('role_user')->get();
        $backupData = [];
        
        // Step 2: Validate data - only keep records where user_id exists in users table
        foreach ($roleUserData as $record) {
            $userExists = DB::table('users')->where('user_id', $record->user_id)->exists();
            if ($userExists) {
                $backupData[] = [
                    'user_id' => $record->user_id,
                    'role_id' => $record->role_id,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at
                ];
            } else {
                // Log records that will be dropped due to missing user_id
                \Log::warning("Removing invalid role_user record: role_id={$record->role_id}, user_id={$record->user_id} - User not found");
            }
        }
        
        // Step 3: Drop the existing role_user table
        Schema::dropIfExists('role_user');
        
        // Step 4: Recreate the role_user table with bigint user_id column
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            // Use bigint unsigned for user_id to match users.user_id
            $table->unsignedBigInteger('user_id');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
            
            // Add index and foreign key to users.user_id (not users.id)
            $table->index('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
        
        // Step 5: Restore the validated data
        foreach ($backupData as $record) {
            DB::table('role_user')->insert([
                'user_id' => $record['user_id'],
                'role_id' => $record['role_id'],
                'created_at' => $record['created_at'],
                'updated_at' => $record['updated_at']
            ]);
        }
    }

    public function down()
    {
        // Step 1: Create a temporary backup of role_user data
        $roleUserData = DB::table('role_user')->get();
        $backupData = [];
        
        foreach ($roleUserData as $record) {
            $backupData[] = [
                'user_id' => $record->user_id,
                'role_id' => $record->role_id,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at
            ];
        }
        
        // Step 2: Drop the existing role_user table
        Schema::dropIfExists('role_user');
        
        // Step 3: Recreate the table with string user_id (previous state)
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
            
            // Add index but not foreign key (as it was before)
            $table->index('user_id');
        });
        
        // Step 4: Restore data
        foreach ($backupData as $record) {
            DB::table('role_user')->insert([
                'user_id' => $record['user_id'],
                'role_id' => $record['role_id'],
                'created_at' => $record['created_at'],
                'updated_at' => $record['updated_at']
            ]);
        }
    }
};

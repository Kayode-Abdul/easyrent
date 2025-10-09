<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixRoleUserTableUserIdReference extends Migration
{
    public function up()
    {
        // Step 1: Create a temporary backup of role_user data
        $roleUserData = DB::table('role_user')->get();
        $backupData = [];
        
        // Map old user.id to user.user_id for all role assignments
        foreach ($roleUserData as $record) {
            $user = DB::table('users')->where('id', $record->user_id)->first();
            if ($user) {
                $backupData[] = [
                    'string_user_id' => $user->user_id,
                    'role_id' => $record->role_id,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at
                ];
            }
        }

        // Step 2: Drop the existing role_user table
        Schema::dropIfExists('role_user');

        // Step 3: Recreate the role_user table with string user_id column
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            // Use string for user_id to match users.user_id
            $table->string('user_id');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
            
            // Add index but not foreign key (safer)
            $table->index('user_id');
        });

        // Step 4: Restore the data
        foreach ($backupData as $record) {
            DB::table('role_user')->insert([
                'user_id' => $record['string_user_id'],
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
        
        // Map user.user_id back to user.id for all role assignments
        foreach ($roleUserData as $record) {
            $user = DB::table('users')->where('user_id', $record->user_id)->first();
            if ($user) {
                $backupData[] = [
                    'int_user_id' => $user->id,
                    'role_id' => $record->role_id,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at
                ];
            }
        }
        
        // Step 2: Drop the existing role_user table
        Schema::dropIfExists('role_user');

        // Step 3: Recreate the original table structure with integer user_id
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->timestamps();
        });

        // Step 4: Restore data with integer ids
        foreach ($backupData as $record) {
            DB::table('role_user')->insert([
                'user_id' => $record['int_user_id'],
                'role_id' => $record['role_id'],
                'created_at' => $record['created_at'],
                'updated_at' => $record['updated_at']
            ]);
        }
    }
}

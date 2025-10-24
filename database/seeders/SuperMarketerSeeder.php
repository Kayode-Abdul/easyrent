<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperMarketerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', 'supermarketer@easyrent.com')->first();
        if ($user) {
            // Ensure supermarketer role in pivot table
            \App\Models\UserRole::updateOrCreate([
                'user_id' => $user->user_id,
                'role' => 8
            ]);
        } else {
            User::create([
                'user_id' => 9001,
                'first_name' => 'Super',
                'last_name' => 'Marketer',
                'username' => 'supermarketer',
                'email' => 'supermarketer@easyrent.com',
                'phone' => '0709999999',
                'password' => Hash::make('superpassword123'),
                'role' => 6, // Supermarketer role
                'commission_rate' => 0,
                'marketer_status' => 'active',
                'referral_code' => 'SUPERMKTR',
            ]);
        }
    }
}

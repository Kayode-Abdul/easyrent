<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SimpleMarketerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a simple marketer for testing
        $marketer = User::create([
            'user_id' => 1001,
            'first_name' => 'Test',
            'last_name' => 'Marketer',
            'username' => 'test_marketer',
            'email' => 'test@marketer.com',
            'phone' => '0700000000',
            'password' => Hash::make('password123'),
            'role' => 5, // Marketer role
            'marketer_status' => 'active',
            'commission_rate' => 5.0,
            'referral_code' => strtoupper(Str::random(8)),
            'bank_name' => 'Test Bank',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Test Marketer'
        ]);

        echo "Simple marketer created with ID: {$marketer->id}\n";
        echo "Email: {$marketer->email}\n";
        echo "Referral Code: {$marketer->referral_code}\n";
    }
}

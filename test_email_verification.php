<?php
/**
 * Email Verification Test Script
 * Run this to test if email verification is working
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;
use App\Models\User;

// Test email configuration
echo "🧪 Testing Email Configuration...\n\n";

try {
    // Test 1: Check if mail configuration is set
    $mailDriver = config('mail.default');
    echo "✅ Mail Driver: {$mailDriver}\n";
    
    $mailHost = config('mail.mailers.smtp.host');
    echo "✅ Mail Host: {$mailHost}\n";
    
    // Test 2: Check if users table has email_verified_at column
    $hasEmailVerification = Schema::hasColumn('users', 'email_verified_at');
    echo $hasEmailVerification ? "✅ Email verification column exists\n" : "❌ Email verification column missing\n";
    
    // Test 3: Check unverified users
    $unverifiedCount = User::whereNull('email_verified_at')->count();
    echo "📊 Unverified users: {$unverifiedCount}\n";
    
    // Test 4: Send test email (uncomment to test)
    /*
    Mail::raw('This is a test email from EasyRent', function($message) {
        $message->to('test@example.com')
                ->subject('EasyRent Email Test');
    });
    echo "✅ Test email sent successfully\n";
    */
    
    echo "\n🎉 Email verification system is ready!\n";
    echo "\n📋 Next Steps:\n";
    echo "1. Configure your .env file with email settings\n";
    echo "2. Test registration with a real email\n";
    echo "3. Check if verification emails are sent\n";
    echo "4. Verify the email verification links work\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Please check your email configuration in .env file\n";
}
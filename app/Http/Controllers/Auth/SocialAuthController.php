<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirect to social provider
     */
    public function redirect($provider)
    {
        $validProviders = ['google', 'facebook', 'github'];
        
        if (!in_array($provider, $validProviders)) {
            return redirect()->route('register')->with('error', 'Invalid social provider');
        }

        try {
            return Socialite::driver($provider)->redirect();
        } catch (Exception $e) {
            return redirect()->route('register')->with('error', 'Unable to connect to ' . ucfirst($provider) . '. Please check your configuration.');
        }
    }

    /**
     * Handle callback from social provider
     */
    public function callback($provider)
    {
        $validProviders = ['google', 'facebook', 'github'];
        
        if (!in_array($provider, $validProviders)) {
            return redirect()->route('register')->with('error', 'Invalid social provider');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // Check if user already exists
            $user = User::where('email', $socialUser->getEmail())->first();
            
            if ($user) {
                // User exists, log them in
                Auth::login($user);
                return redirect()->intended('/dashboard')->with('success', 'Welcome back!');
            }
            
            // Create new user
            $user = $this->createUserFromSocial($socialUser, $provider);
            
            // Log the user in
            Auth::login($user);
            
            return redirect('/dashboard')->with('success', 'Account created successfully! Welcome to EasyRent.');
            
        } catch (Exception $e) {
            return redirect()->route('register')->with('error', 'Unable to authenticate with ' . ucfirst($provider) . '. Please try again or use email registration.');
        }
    }

    /**
     * Create user from social provider data
     */
    protected function createUserFromSocial($socialUser, $provider)
    {
        // Generate unique user_id
        do {
            $user_id = mt_rand(100000, 999999);
        } while (User::where('user_id', $user_id)->exists());

        // Parse name
        $name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'User';
        $nameParts = explode(' ', $name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        // Generate username from email
        $emailParts = explode('@', $socialUser->getEmail());
        $baseUsername = $emailParts[0];
        $username = $baseUsername;
        $counter = 1;
        
        // Ensure unique username
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . '_' . $counter;
            $counter++;
        }

        // Get phone from social provider if available (usually not)
        $phone = $socialUser->user['phone'] ?? null;

        return User::create([
            'user_id' => $user_id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'email' => $socialUser->getEmail(),
            'role' => 1, // Default to tenant role
            'phone' => $phone,
            'password' => Hash::make(Str::random(32)), // Random password for social auth users
            'photo' => $socialUser->getAvatar(),
            'registration_source' => 'social_' . $provider,
            'email_verified_at' => now(), // Social accounts are pre-verified
            'created_at' => now(),
        ]);
    }
}

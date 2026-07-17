<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\AccountUpdateOtpMail;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show settings dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $categories = \App\Models\ComplaintCategory::active()->get();
        return view('dashboard.settings', compact('user', 'categories'));
    }

    /**
     * Update Payout/Bank details
     */
    public function updatePayouts(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_name' => 'required|string|max:255',
            'bvn' => 'nullable|string|max:20',
        ]);

        // If OTP is not present, generate and send it
        if (!$request->has('otp')) {
            $otp = strtoupper(Str::random(6));
            
            session([
                'pending_update_data' => $request->only(['bank_name', 'bank_account_number', 'bank_account_name', 'bvn']),
                'pending_update_type' => 'Payouts',
                'update_otp' => $otp,
                'update_otp_expires_at' => now()->addMinutes(10)
            ]);

            Mail::to(Auth::user()->email)->send(new AccountUpdateOtpMail($otp, 'Payout Settings'));

            return redirect()->route('settings.otp.verify');
        }

        // If we reach here directly somehow (which shouldn't happen via UI), we deny it
        return back()->with('error', 'Invalid request. Please submit the form properly.');
    }



    /**
     * Update Security settings (Password & 2FA)
     */
    public function updateSecurity(Request $request)
    {
        $user = Auth::user();

        // Password Update
        if ($request->filled('current_password')) {
            $request->validate([
                'current_password' => 'required',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
            }

            // If OTP is not present, generate and send it
            if (!$request->has('otp')) {
                $otp = strtoupper(Str::random(6));
                
                session([
                    'pending_update_data' => [
                        'password' => Hash::make($request->password)
                    ],
                    'pending_update_type' => 'Security',
                    'update_otp' => $otp,
                    'update_otp_expires_at' => now()->addMinutes(10)
                ]);

                Mail::to(Auth::user()->email)->send(new AccountUpdateOtpMail($otp, 'Security Settings (Password)'));

                return redirect()->route('settings.otp.verify');
            }
        }

        // 2FA Toggle (Doesn't need OTP, just immediate update)
        $user->update([
            'two_factor_enabled' => $request->has('two_factor_enabled')
        ]);

        return back()->with('success', 'Security settings updated successfully!');
    }

    /**
     * Update Notification Preferences
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();
        
        $preferences = [
            'bids' => $request->has('notif_bids'),
            'messages' => $request->has('notif_messages'),
            'payments' => $request->has('notif_payments'),
            'overdue' => $request->has('notif_overdue'),
        ];

        $user->update([
            'notification_preferences' => $preferences
        ]);

        return back()->with('success', 'Notification preferences updated successfully!');
    }

    /**
     * Show OTP Verification Page
     */
    public function showOtpVerification()
    {
        if (!session()->has('pending_update_type')) {
            return redirect()->route('settings.index')->with('error', 'No pending updates found.');
        }

        return view('dashboard.verify-otp');
    }

    /**
     * Confirm OTP and Apply Changes
     */
    public function confirmOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|max:10'
        ]);

        if (!session()->has('update_otp')) {
            return redirect()->route('settings.index')->with('error', 'OTP session expired. Please try again.');
        }

        if (now()->greaterThan(session('update_otp_expires_at'))) {
            session()->forget(['pending_update_data', 'pending_update_type', 'update_otp', 'update_otp_expires_at']);
            return redirect()->route('settings.index')->with('error', 'OTP expired. Please try again.');
        }

        if (strtoupper($request->otp) !== session('update_otp')) {
            return back()->withErrors(['otp' => 'Invalid Security Code. Please check your email.']);
        }

        // OTP is valid, apply changes
        $user = Auth::user();
        $data = session('pending_update_data');
        $type = session('pending_update_type');

        $user->update($data);

        // Log the action
        ActivityLog::create([
            'user_id' => $user->user_id,
            'action' => 'updated_' . strtolower($type) . '_details',
            'description' => 'User securely updated their ' . strtolower($type) . ' settings.',
            'ip_address' => $request->ip(),
        ]);

        // Clear session
        session()->forget(['pending_update_data', 'pending_update_type', 'update_otp', 'update_otp_expires_at']);

        return redirect()->route('settings.index')->with('success', $type . ' updated successfully!');
    }
}

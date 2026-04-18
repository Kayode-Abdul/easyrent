<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

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
        return view('dashboard.settings', compact('user'));
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

        $user = Auth::user();
        $user->update($request->only([
            'bank_name',
            'bank_account_number',
            'bank_account_name',
            'bvn'
        ]));

        ActivityLog::create([
            'user_id' => $user->user_id,
            'action' => 'updated_payout_details',
            'description' => 'User updated their bank payout details.',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Payout details updated successfully!');
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

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            ActivityLog::create([
                'user_id' => $user->user_id,
                'action' => 'changed_password',
                'description' => 'User changed their account password.',
                'ip_address' => $request->ip(),
            ]);
        }

        // 2FA Toggle
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
}

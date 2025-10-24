<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    /**
     * Display the user's billing information
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        // Billing page is accessible to all authenticated users
        
        // Debug: Log user information
        \Log::info('Billing page accessed', [
            'user_id' => $user->user_id,
            'email' => $user->email
        ]);
        
        // Get all payments made by the user (both as tenant and landlord)
        $payments = Payment::where(function($query) use ($user) {
                            $query->where('tenant_id', $user->user_id)
                                  ->orWhere('landlord_id', $user->user_id);
                        })
                        ->whereIn('status', ['success', 'completed'])
                        ->orderBy('created_at', 'desc')
                        ->get();
        
        // Debug: Log payment query results
        \Log::info('Payments found for user', [
            'user_id' => $user->user_id,
            'payment_count' => $payments->count(),
            'payments' => $payments->toArray()
        ]);
        
        // Also check all payments for this user regardless of status
        $allUserPayments = Payment::where('tenant_id', $user->user_id)->get();
        \Log::info('All payments for user (any status)', [
            'user_id' => $user->user_id,
            'all_payment_count' => $allUserPayments->count(),
            'all_payments' => $allUserPayments->toArray()
        ]);
        
        // Get all bookings with pending payments
        $pendingBookings = Booking::where('user_id', $user->user_id)
                            ->where('status', 'pending')
                            ->orderBy('created_at', 'desc')
                            ->get();
        
        // Calculate total amount paid
        $totalPaid = $payments->sum('amount');
        
        // Calculate total pending amount
        $totalPending = $pendingBookings->sum('amount');
        
        return view('billing.index', compact('payments', 'pendingBookings', 'totalPaid', 'totalPending'));
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    /**
     * Display the user's billing information
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Billing page is accessible to all authenticated users

        // Debug: Log user information
        Log::info('Billing page accessed', [
            'user_id' => $user->user_id,
            'email' => $user->email
        ]);

        // Get all payments made by the user as tenant (completed or successful)
        $payments = Payment::where('tenant_id', $user->user_id)
            ->whereIn('status', ['success', 'completed'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get pending proformas (instead of initiated payments) to ensure 'Pay Now' links work correctly
        $pendingPayments = \App\Models\ProfomaReceipt::where('tenant_id', $user->user_id)
            ->whereIn('status', [\App\Models\ProfomaReceipt::STATUS_NEW, \App\Models\ProfomaReceipt::STATUS_CONFIRMED, 3])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        // Debug: Log payment query results
        Log::info('Payments found for user', [
            'user_id' => $user->user_id,
            'payment_count' => $payments->count(),
            'pending_count' => $pendingPayments->count()
        ]);

        // Calculate total amount paid and pending grouped by currency
        $totalPaidByCurrency = $payments->groupBy(function($item) { return $item->currency->code ?? 'NGN'; })->map(function ($group) {
            $first = $group->first();
            return [
                'amount' => $group->sum('amount'),
                'symbol' => $first->currency->symbol ?? '₦'
            ];
        })->toArray();

        $totalPendingByCurrency = $pendingPayments->getCollection()->groupBy(function($item) { return $item->currency->code ?? 'NGN'; })->map(function ($group) {
            $first = $group->first();
            return [
                'amount' => $group->sum('total'), // Profoma uses total
                'symbol' => $first->currency->symbol ?? '₦'
            ];
        })->toArray();

        return view('billing.index', compact('payments', 'pendingPayments', 'totalPaidByCurrency', 'totalPendingByCurrency'));
    }
}
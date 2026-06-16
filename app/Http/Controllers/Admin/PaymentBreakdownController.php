<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentBreakdownController extends Controller
{
    /**
     * Display a listing of the payments with their commission breakdowns.
     */
    public function index(Request $request)
    {
        $query = Payment::with([
            'apartment',
            'apartment.property',
            'currency',
            'commissionPayments' => function ($q) {
                $q->with('marketer');
            },
            'referralRewards' => function ($q) {
                $q->with('marketer');
            }
        ])
        ->whereHas('commissionPayments') // Only payments that generated commissions
        ->latest();

        // Optional: filter by search term (apartment name, etc)
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->whereHas('apartment', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('apartment.property', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $payments = $query->paginate(20);

        return view('admin.payments.breakdown', compact('payments'));
    }
}

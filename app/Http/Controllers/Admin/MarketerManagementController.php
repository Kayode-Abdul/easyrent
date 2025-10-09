<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MarketerProfile;
use App\Models\ReferralReward;
use App\Models\CommissionPayment;
use App\Models\Referral;
use Carbon\Carbon;

class MarketerManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || ($user->admin != 1 && $user->role != 1)) {
                abort(403, 'Access denied. Admin privileges required.');
            }
            return $next($request);
        });
    }

    /**
     * Marketer Management Dashboard
     */
    public function index()
    {
        $stats = [
            'total_marketers' => User::where('role', 5)->count(),
            'active_marketers' => User::where('role', 5)->where('marketer_status', 'active')->count(),
            'pending_marketers' => User::where('role', 5)->where('marketer_status', 'pending')->count(),
            'total_referrals' => Referral::count(),
            'successful_referrals' => Referral::whereHas('referred', function($q) {
                $q->where('role', 2); // Landlords
            })->count(),
            'total_commission_paid' => ReferralReward::where('status', 'paid')->sum('amount'),
            'pending_commission' => ReferralReward::where('status', 'approved')->sum('amount'),
        ];

        // Recent marketer applications
        $recentApplications = User::where('role', 5)
            ->where('marketer_status', 'pending')
            ->with('marketerProfile')
            ->latest()
            ->limit(10)
            ->get();

        // Top performing marketers
        $topMarketers = User::where('role', 5)
            ->where('marketer_status', 'active')
            ->withCount('referrals')
            ->orderBy('referrals_count', 'desc')
            ->limit(10)
            ->get();

        // Pending commission approvals
        $pendingRewards = ReferralReward::where('status', 'pending')
            ->with(['marketer', 'referral.referred'])
            ->latest()
            ->limit(15)
            ->get();

        return view('admin.marketers.index', compact(
            'stats', 
            'recentApplications', 
            'topMarketers', 
            'pendingRewards'
        ));
    }

    /**
     * List all marketers
     */
    public function marketers(Request $request)
    {
        $query = User::where('role', 5)->with('marketerProfile');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('marketer_status', $request->status);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $marketers = $query->paginate(20);
        
        return view('admin.marketers.list', compact('marketers'));
    }

    /**
     * Show marketer details
     */
    public function show(User $marketer)
    {
        if (!$marketer->isMarketer()) {
            abort(404);
        }

        $marketer->load(['marketerProfile', 'referralCampaigns', 'referralRewards', 'commissionPayments']);
        
        $stats = $marketer->getMarketerStats();
        
        // Recent activity
        $recentReferrals = $marketer->referrals()
            ->with('referred')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.marketers.show', compact('marketer', 'stats', 'recentReferrals'));
    }

    /**
     * Approve marketer application
     */
    public function approve(User $marketer)
    {
        if (!$marketer->isMarketer() || $marketer->marketer_status !== 'pending') {
            return back()->with('error', 'Invalid marketer or status.');
        }

        // Approve marketer
        $marketer->update([
            'marketer_status' => 'active',
            'commission_rate' => $marketer->marketerProfile->preferred_commission_rate ?? 5.0
        ]);

        // Update profile KYC status
        $marketer->marketerProfile->update([
            'kyc_status' => MarketerProfile::KYC_APPROVED,
            'verified_at' => now()
        ]);

        // Generate referral code if not exists
        $marketer->generateReferralCode();

        return back()->with('success', 'Marketer approved successfully!');
    }

    /**
     * Reject marketer application
     */
    public function reject(Request $request, User $marketer)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        if (!$marketer->isMarketer() || $marketer->marketer_status !== 'pending') {
            return back()->with('error', 'Invalid marketer or status.');
        }

        $marketer->update(['marketer_status' => 'rejected']);
        
        $marketer->marketerProfile->update([
            'kyc_status' => MarketerProfile::KYC_REJECTED,
            'kyc_documents' => array_merge($marketer->marketerProfile->kyc_documents ?? [], [
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now()->toISOString()
            ])
        ]);

        return back()->with('success', 'Marketer application rejected.');
    }

    /**
     * Suspend marketer
     */
    public function suspend(Request $request, User $marketer)
    {
        $request->validate([
            'suspension_reason' => 'required|string|max:500'
        ]);

        if (!$marketer->isMarketer()) {
            return back()->with('error', 'Invalid marketer.');
        }

        $marketer->update(['marketer_status' => 'suspended']);

        // Log suspension reason
        $marketer->marketerProfile->update([
            'kyc_documents' => array_merge($marketer->marketerProfile->kyc_documents ?? [], [
                'suspension_reason' => $request->suspension_reason,
                'suspended_at' => now()->toISOString()
            ])
        ]);

        return back()->with('success', 'Marketer suspended successfully.');
    }

    /**
     * Reactivate marketer
     */
    public function reactivate(User $marketer)
    {
        if (!$marketer->isMarketer()) {
            return back()->with('error', 'Invalid marketer.');
        }

        $marketer->update(['marketer_status' => 'active']);

        return back()->with('success', 'Marketer reactivated successfully.');
    }

    /**
     * Update commission rate
     */
    public function updateCommissionRate(Request $request, User $marketer)
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:1|max:20'
        ]);

        if (!$marketer->isMarketer()) {
            return back()->with('error', 'Invalid marketer.');
        }

        $marketer->update(['commission_rate' => $request->commission_rate]);

        return back()->with('success', 'Commission rate updated successfully.');
    }

    /**
     * Referral rewards management
     */
    public function rewards(Request $request)
    {
        $query = ReferralReward::with(['marketer', 'referral.referred']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by reward type
        if ($request->filled('type')) {
            $query->where('reward_type', $request->type);
        }

        // Search by marketer
        if ($request->filled('marketer_search')) {
            $search = $request->marketer_search;
            $query->whereHas('marketer', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $rewards = $query->latest()->paginate(25);

        return view('admin.marketers.rewards', compact('rewards'));
    }

    /**
     * Approve reward
     */
    public function approveReward(ReferralReward $reward)
    {
        if (!$reward->isPending()) {
            return back()->with('error', 'Reward is not pending approval.');
        }

        $reward->approve(auth()->user()->user_id);

        return back()->with('success', 'Reward approved successfully.');
    }

    /**
     * Reject reward
     */
    public function rejectReward(Request $request, ReferralReward $reward)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        if (!$reward->isPending()) {
            return back()->with('error', 'Reward is not pending approval.');
        }

        $reward->cancel($request->rejection_reason);

        return back()->with('success', 'Reward rejected successfully.');
    }

    /**
     * Commission payments management
     */
    public function payments(Request $request)
    {
        $query = CommissionPayment::with('marketer');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $payments = $query->latest()->paginate(20);

        // Summary statistics
        $stats = [
            'total_paid' => CommissionPayment::where('payment_status', 'completed')->sum('total_amount'),
            'pending_amount' => CommissionPayment::where('payment_status', 'pending')->sum('total_amount'),
            'failed_amount' => CommissionPayment::where('payment_status', 'failed')->sum('total_amount'),
        ];

        return view('admin.marketers.payments', compact('payments', 'stats'));
    }

    /**
     * Process commission payment
     */
    public function processPayment(CommissionPayment $payment)
    {
        if (!$payment->isPending()) {
            return back()->with('error', 'Payment is not pending.');
        }

        $payment->markAsProcessing();

        return back()->with('success', 'Payment marked as processing.');
    }

    /**
     * Complete commission payment
     */
    public function completePayment(Request $request, CommissionPayment $payment)
    {
        $request->validate([
            'transaction_id' => 'required|string|max:255'
        ]);

        if (!$payment->isProcessing()) {
            return back()->with('error', 'Payment is not in processing status.');
        }

        $payment->markAsCompleted($request->transaction_id);

        return back()->with('success', 'Payment completed successfully.');
    }

    /**
     * Generate analytics report
     */
    public function analytics()
    {
        // Monthly performance data
        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'new_marketers' => User::where('role', 5)
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'referrals' => Referral::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'commission_paid' => ReferralReward::where('status', 'paid')
                    ->whereMonth('processed_at', $date->month)
                    ->whereYear('processed_at', $date->year)
                    ->sum('amount')
            ];
        }

        // Top performers
        $topPerformers = User::where('role', 5)
            ->where('marketer_status', 'active')
            ->withCount('referrals')
            ->with('marketerProfile')
            ->orderBy('referrals_count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.marketers.analytics', compact('monthlyData', 'topPerformers'));
    }
}

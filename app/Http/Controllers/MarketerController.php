<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MarketerProfile;
use App\Models\ReferralCampaign;
use App\Models\ReferralReward;
use App\Models\CommissionPayment;
use App\Models\Referral;
use App\Models\ReferralChain;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class MarketerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user->isMarketer()) {
                abort(403, 'Access denied. Marketer privileges required.');
            }
            return $next($request);
        });
    }

    /**
     * Marketer Dashboard
     */
    public function dashboard()
    {
        $marketer = Auth::user();
        $profile = $marketer->marketerProfile;
        
        // Get marketer statistics
        $stats = $marketer->getMarketerStats();
        
        // Recent referrals
        $recentReferrals = $marketer->referrals()
            ->with('referred')
            ->latest()
            ->limit(10)
            ->get();
        
        // Active campaigns
        $activeCampaigns = $marketer->referralCampaigns()
            ->active()
            ->withCount('referrals')
            ->get();
        
        // Pending rewards
        $pendingRewards = $marketer->referralRewards()
            ->pending()
            ->with('referral.referred')
            ->get();
        
        // Recent payments
        $recentPayments = $marketer->commissionPayments()
            ->latest()
            ->limit(5)
            ->get();

        // Performance data for charts (last 12 months)
        $performanceData = $this->getPerformanceData($marketer);

        // Hierarchy information
        $referringSuperMarketer = $marketer->referringSuperMarketer();
        $referralChain = $marketer->getReferralChain();
        $commissionBreakdown = $marketer->getCommissionBreakdown();

        return view('marketer.dashboard', compact(
            'marketer', 
            'profile', 
            'stats', 
            'recentReferrals', 
            'activeCampaigns', 
            'pendingRewards', 
            'recentPayments',
            'performanceData',
            'referringSuperMarketer',
            'referralChain',
            'commissionBreakdown'
        ));
    }

    /**
     * Show marketer profile
     */
    public function profile()
    {
        $marketer = Auth::user();
        $profile = $marketer->marketerProfile;
        
        if (!$profile) {
            return redirect()->route('marketer.profile.create');
        }
        
        return view('marketer.profile.show', compact('marketer', 'profile'));
    }

    /**
     * Show create profile form
     */
    public function createProfile()
    {
        $marketer = Auth::user();
        
        if ($marketer->marketerProfile) {
            return redirect()->route('marketer.profile');
        }
        
        return view('marketer.profile.create', compact('marketer'));
    }

    /**
     * Store marketer profile
     */
    public function storeProfile(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:100',
            'years_of_experience' => 'required|integer|min:0|max:50',
            'marketing_channels' => 'required|string',
            'target_regions' => 'required|array|min:1',
            'bio' => 'nullable|string|max:1000',
            'website' => 'nullable|url',
            'social_media_handles' => 'nullable|string',
            'preferred_commission_rate' => 'required|numeric|min:1|max:15',
            'kyc_documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        $marketer = Auth::user();
        
        // Handle KYC document uploads
        $kycDocuments = [];
        if ($request->hasFile('kyc_documents')) {
            foreach ($request->file('kyc_documents') as $key => $file) {
                $path = $file->store('kyc_documents/' . $marketer->user_id, 'private');
                $kycDocuments[$key] = $path;
            }
        }

        $profile = MarketerProfile::create([
            'user_id' => $marketer->user_id,
            'business_name' => $request->business_name,
            'business_type' => $request->business_type,
            'years_of_experience' => $request->years_of_experience,
            'marketing_channels' => $request->marketing_channels,
            'target_regions' => $request->target_regions,
            'bio' => $request->bio,
            'website' => $request->website,
            'social_media_handles' => $request->social_media_handles,
            'preferred_commission_rate' => $request->preferred_commission_rate,
            'kyc_documents' => $kycDocuments,
            'kyc_status' => MarketerProfile::KYC_PENDING
        ]);

        // Update user status to pending
        $marketer->update(['marketer_status' => 'pending']);

        return redirect()->route('marketer.profile')
            ->with('success', 'Profile created successfully! Your application is under review.');
    }

    /**
     * Edit marketer profile
     */
    public function editProfile()
    {
        $marketer = Auth::user();
        $profile = $marketer->marketerProfile;
        
        if (!$profile) {
            return redirect()->route('marketer.profile.create');
        }
        
        return view('marketer.profile.edit', compact('marketer', 'profile'));
    }

    /**
     * Update marketer profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:100',
            'years_of_experience' => 'required|integer|min:0|max:50',
            'marketing_channels' => 'required|string',
            'target_regions' => 'required|array|min:1',
            'bio' => 'nullable|string|max:1000',
            'website' => 'nullable|url',
            'social_media_handles' => 'nullable|string',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255'
        ]);

        $marketer = Auth::user();
        $profile = $marketer->marketerProfile;

        $profile->update($request->only([
            'business_name',
            'business_type', 
            'years_of_experience',
            'marketing_channels',
            'target_regions',
            'bio',
            'website',
            'social_media_handles'
        ]));

        // Update bank details in user table
        $marketer->update($request->only([
            'bank_account_name',
            'bank_account_number', 
            'bank_name'
        ]));

        return redirect()->route('marketer.profile')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Show campaigns list
     */
    public function campaigns()
    {
        $marketer = Auth::user();
        $campaigns = $marketer->referralCampaigns()
            ->withCount('referrals')
            ->latest()
            ->paginate(15);
        
        return view('marketer.campaigns.index', compact('campaigns'));
    }

    /**
     * Show create campaign form
     */
    public function createCampaign()
    {
        return view('marketer.campaigns.create');
    }

    /**
     * Store new campaign
     */
    public function storeCampaign(Request $request)
    {
        $request->validate([
            'campaign_name' => 'required|string|max:255',
            'target_audience' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        $marketer = Auth::user();
        
        $campaign = ReferralCampaign::create([
            'marketer_id' => $marketer->user_id,
            'campaign_name' => $request->campaign_name,
            'target_audience' => $request->target_audience,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => ReferralCampaign::STATUS_ACTIVE
        ]);

        // Generate QR code
        $this->generateQrCode($campaign);

        return redirect()->route('marketer.campaigns')
            ->with('success', 'Campaign created successfully!');
    }

    /**
     * Show campaign details
     */
    public function showCampaign(ReferralCampaign $campaign)
    {
        $this->authorize('view', $campaign);
        
        $campaign->load('referrals.referred');
        
        return view('marketer.campaigns.show', compact('campaign'));
    }

    /**
     * Generate QR code for campaign
     */
    private function generateQrCode(ReferralCampaign $campaign)
    {
        $referralLink = $campaign->getReferralLink();
        $qrCodePath = 'qr_codes/' . $campaign->marketer_id . '/' . $campaign->campaign_code . '.png';
        
        // Ensure directory exists
        Storage::disk('public')->makeDirectory(dirname($qrCodePath));
        
        // Generate QR code
        $qrCode = QrCode::format('png')
            ->size(300)
            ->generate($referralLink);
            
        Storage::disk('public')->put($qrCodePath, $qrCode);
        
        $campaign->update(['qr_code_path' => 'storage/' . $qrCodePath]);
    }

    /**
     * Get performance data for charts
     */
    private function getPerformanceData($marketer)
    {
        $months = [];
        $referrals = [];
        $commissions = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // Count referrals for this month
            $monthlyReferrals = $marketer->referrals()
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $referrals[] = $monthlyReferrals;
            
            // Sum commissions for this month
            $monthlyCommissions = $marketer->referralRewards()
                ->where('status', 'paid')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');
            $commissions[] = (float)$monthlyCommissions;
        }
        
        return [
            'months' => $months,
            'referrals' => $referrals,
            'commissions' => $commissions
        ];
    }

    /**
     * Show referrals list
     */
    public function referrals()
    {
        $marketer = Auth::user();
        $referrals = $marketer->referrals()
            ->with(['referred', 'referralRewards'])
            ->latest()
            ->paginate(20);
        
        return view('marketer.referrals.index', compact('referrals'));
    }

    /**
     * Show commission payments
     */
    public function payments()
    {
        $marketer = Auth::user();
        $payments = $marketer->commissionPayments()
            ->latest()
            ->paginate(15);
        
        $pendingPayment = $marketer->referralRewards()
            ->where('status', 'approved')
            ->sum('amount');
            
        $totalEarned = $marketer->referralRewards()
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');

        $totalPaid = $marketer->commissionPayments()
            ->where('status', 'completed')
            ->sum('amount');

        $totalReferrals = $marketer->referrals()->count();

        $pendingRewards = $marketer->referralRewards()
            ->where('status', 'pending')
            ->with(['landlord', 'referral.campaign'])
            ->latest()
            ->limit(10)
            ->get();

        $summary = [
            'total_earned' => $totalEarned,
            'total_paid' => $totalPaid,
            'pending_payment' => $pendingPayment,
            'total_referrals' => $totalReferrals
        ];
        
        return view('marketer.payments.index', compact('payments', 'summary', 'pendingRewards'));
    }

    /**
     * Request payment
     */
    public function requestPayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:bank_transfer,mobile_money',
            'bank_name' => 'required_if:payment_method,bank_transfer|string|max:255',
            'account_number' => 'required_if:payment_method,bank_transfer|string|max:50',
            'account_name' => 'required_if:payment_method,bank_transfer|string|max:255',
            'mobile_number' => 'required_if:payment_method,mobile_money|string|max:20',
            'notes' => 'nullable|string|max:1000'
        ]);

        $marketer = Auth::user();
        
        $pendingAmount = $marketer->referralRewards()
            ->where('status', 'approved')
            ->sum('amount');

        if ($pendingAmount < 1000) {
            return back()->with('error', 'Minimum payment amount is KSh 1,000');
        }

        $paymentReference = 'PAY' . strtoupper(substr(md5(time() . $marketer->id), 0, 8));

        CommissionPayment::create([
            'marketer_id' => $marketer->id,
            'amount' => $pendingAmount,
            'payment_method' => $request->payment_method,
            'payment_reference' => $paymentReference,
            'bank_name' => $request->bank_name ?? $marketer->bank_name,
            'account_number' => $request->account_number ?? $marketer->account_number,
            'account_name' => $request->account_name ?? $marketer->account_name,
            'mobile_number' => $request->mobile_number,
            'status' => 'pending',
            'notes' => $request->notes
        ]);

        return back()->with('success', 'Payment request submitted successfully! Reference: ' . $paymentReference);
    }

    /**
     * Show payment details
     */
    public function showPayment($id)
    {
        $marketer = Auth::user();
        $payment = $marketer->commissionPayments()->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'payment' => $payment
        ]);
    }

    /**
     * Cancel payment request
     */
    public function cancelPayment($id)
    {
        $marketer = Auth::user();
        $payment = $marketer->commissionPayments()
            ->where('status', 'pending')
            ->findOrFail($id);
        
        $payment->update(['status' => 'cancelled']);
        
        return response()->json(['success' => true]);
    }

    /**
     * Show campaign details with performance data
     */
    public function showCampaignDetailed(ReferralCampaign $campaign)
    {
        if ($campaign->marketer_id !== auth()->id()) {
            abort(403);
        }
        
        $recentReferrals = $campaign->referrals()
            ->with('referred')
            ->latest()
            ->limit(10)
            ->get();

        // Generate chart data for the last 30 days
        $chartData = $this->getCampaignChartData($campaign);
        
        return view('marketer.campaigns.show', compact('campaign', 'recentReferrals', 'chartData'));
    }

    /**
     * Pause campaign
     */
    public function pauseCampaign(ReferralCampaign $campaign)
    {
        if ($campaign->marketer_id !== auth()->id()) {
            abort(403);
        }
        
        $campaign->update(['status' => 'paused']);
        
        return response()->json(['success' => true]);
    }

    /**
     * Resume campaign
     */
    public function resumeCampaign(ReferralCampaign $campaign)
    {
        if ($campaign->marketer_id !== auth()->id()) {
            abort(403);
        }
        
        $campaign->update(['status' => 'active']);
        
        return response()->json(['success' => true]);
    }

    /**
     * Get QR code for campaign
     */
    public function getCampaignQRCode(ReferralCampaign $campaign)
    {
        if ($campaign->marketer_id !== auth()->id()) {
            abort(403);
        }
        
        if (!$campaign->qr_code_path && $campaign->campaign_type === 'qr_code') {
            $campaign->generateQrCode();
        }
        
        return response()->json([
            'success' => true,
            'qr_url' => $campaign->getQrCodeUrl(),
            'download_url' => $campaign->getQrCodeUrl()
        ]);
    }

    /**
     * Get referrals with filtering
     */
    public function getReferrals(Request $request)
    {
        $marketer = Auth::user();
        $query = $marketer->referrals()->with(['referred', 'campaign']);
        
        if ($request->status) {
            $query->where('commission_status', $request->status);
        }
        
        if ($request->campaign) {
            $query->where('campaign_id', $request->campaign);
        }
        
        $referrals = $query->latest()->paginate(20);
        
        $stats = [
            'total' => $marketer->referrals()->count(),
            'pending' => $marketer->referrals()->where('commission_status', 'pending')->count(),
            'approved' => $marketer->referrals()->where('commission_status', 'approved')->count(),
            'paid' => $marketer->referrals()->where('commission_status', 'paid')->count(),
            'total_commission' => $marketer->referrals()->sum('commission_amount') // TODO: migrate to rewards->sum('amount') once referrals table field removed
        ];
        
        return view('marketer.referrals.index', compact('referrals', 'stats'));
    }

    /**
     * Show referral details
     */
    public function showReferral($id)
    {
        $marketer = Auth::user();
        $referral = $marketer->referrals()
            ->with(['referred', 'campaign', 'reward'])
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'referral' => [
                'id' => $referral->id,
                'referral_code' => $referral->referral_code,
                'referral_source' => $referral->referral_source,
                'commission_amount' => $referral->commission_amount, // legacy field retained for now
                'commission_status' => $referral->commission_status,
                'commission_percentage' => auth()->user()->commission_rate,
                'conversion_date' => $referral->conversion_date,
                'referred' => [
                    'name' => $referral->referred->name,
                    'email' => $referral->referred->email,
                    'phone' => $referral->referred->phone,
                    'created_at' => $referral->referred->created_at
                ],
                'campaign' => $referral->campaign ? [
                    'name' => $referral->campaign->name,
                    'campaign_code' => $referral->campaign->campaign_code
                ] : null,
                'reward' => $referral->reward ? [
                    'created_at' => $referral->reward->created_at,
                    'approved_at' => $referral->reward->approved_at,
                    'paid_at' => $referral->reward->paid_at
                ] : null
            ]
        ]);
    }

    /**
     * Get campaign chart data
     */
    private function getCampaignChartData(ReferralCampaign $campaign)
    {
        $days = [];
        $clicks = [];
        $conversions = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('M d');
            
            // For now, we'll use dummy data since we don't have daily tracking
            // In a real implementation, you'd track daily clicks and conversions
            $clicks[] = rand(0, 10);
            $conversions[] = rand(0, 3);
        }
        
        return [
            'labels' => $days,
            'clicks' => $clicks,
            'conversions' => $conversions
        ];
    }

    /**
     * Get referral chain visualization data
     */
    public function getReferralChainVisualization()
    {
        $marketer = Auth::user();
        $chain = $marketer->getReferralChain();
        
        return response()->json([
            'success' => true,
            'chain' => $chain
        ]);
    }

    /**
     * Get commission breakdown with tier information
     */
    public function getCommissionBreakdownWithTiers(Request $request)
    {
        $marketer = Auth::user();
        $region = $request->get('region', $marketer->state ?? 'default');
        
        $breakdown = $marketer->getCommissionBreakdown($region);
        
        // Get recent commission payments with tier information
        $recentCommissions = $marketer->commissionPayments()
            ->with(['referralChain.superMarketer', 'referralChain.marketer'])
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'tier' => $payment->commission_tier,
                    'date' => $payment->created_at->format('M d, Y'),
                    'status' => $payment->status,
                    'referral_chain' => $payment->referralChain ? [
                        'super_marketer' => $payment->referralChain->superMarketer ? [
                            'name' => $payment->referralChain->superMarketer->first_name . ' ' . $payment->referralChain->superMarketer->last_name,
                            'email' => $payment->referralChain->superMarketer->email
                        ] : null,
                        'marketer' => $payment->referralChain->marketer ? [
                            'name' => $payment->referralChain->marketer->first_name . ' ' . $payment->referralChain->marketer->last_name,
                            'email' => $payment->referralChain->marketer->email
                        ] : null
                    ] : null
                ];
            });
        
        return response()->json([
            'success' => true,
            'breakdown' => $breakdown,
            'recent_commissions' => $recentCommissions
        ]);
    }

    /**
     * Get referring Super Marketer information
     */
    public function getReferringSuperMarketerInfo()
    {
        $marketer = Auth::user();
        $superMarketer = $marketer->referringSuperMarketer();
        
        if (!$superMarketer) {
            return response()->json([
                'success' => false,
                'message' => 'No referring Super Marketer found'
            ]);
        }
        
        // Get performance metrics with this Super Marketer
        $referralStats = [
            'total_referrals' => $marketer->referrals()->count(),
            'successful_referrals' => $marketer->referrals()->whereHas('referred', function($q) {
                $q->where('role', 2); // Landlords
            })->count(),
            'total_commission' => $marketer->referralRewards()->where('status', 'paid')->sum('amount'),
            'pending_commission' => $marketer->referralRewards()->where('status', 'approved')->sum('amount')
        ];
        
        return response()->json([
            'success' => true,
            'super_marketer' => [
                'id' => $superMarketer->user_id,
                'name' => $superMarketer->first_name . ' ' . $superMarketer->last_name,
                'email' => $superMarketer->email,
                'phone' => $superMarketer->phone,
                'joined_date' => $superMarketer->created_at->format('M d, Y'),
                'profile' => $superMarketer->marketerProfile ? [
                    'business_name' => $superMarketer->marketerProfile->business_name,
                    'business_type' => $superMarketer->marketerProfile->business_type,
                    'years_of_experience' => $superMarketer->marketerProfile->years_of_experience
                ] : null
            ],
            'referral_stats' => $referralStats
        ]);
    }

    /**
     * Get referral performance comparison tools
     */
    public function getReferralPerformanceComparison(Request $request)
    {
        $marketer = Auth::user();
        $period = $request->get('period', '30'); // days
        $startDate = Carbon::now()->subDays($period);
        
        // Get marketer's performance
        $marketerStats = [
            'referrals' => $marketer->referrals()->where('created_at', '>=', $startDate)->count(),
            'successful_referrals' => $marketer->referrals()
                ->where('created_at', '>=', $startDate)
                ->whereHas('referred', function($q) {
                    $q->where('role', 2);
                })->count(),
            'commission_earned' => $marketer->referralRewards()
                ->where('created_at', '>=', $startDate)
                ->where('status', 'paid')
                ->sum('amount'),
            'conversion_rate' => 0
        ];
        
        if ($marketerStats['referrals'] > 0) {
            $marketerStats['conversion_rate'] = ($marketerStats['successful_referrals'] / $marketerStats['referrals']) * 100;
        }
        
        // Get average performance of other marketers in the same region
        $region = $marketer->state ?? 'default';
        $otherMarketers = User::whereHas('roles', function($q) {
                $q->where('name', 'marketer');
            })
            ->where('state', $region)
            ->where('user_id', '!=', $marketer->user_id)
            ->get();
        
        $avgStats = [
            'referrals' => 0,
            'successful_referrals' => 0,
            'commission_earned' => 0,
            'conversion_rate' => 0
        ];
        
        if ($otherMarketers->count() > 0) {
            $totalReferrals = 0;
            $totalSuccessful = 0;
            $totalCommission = 0;
            
            foreach ($otherMarketers as $other) {
                $referrals = $other->referrals()->where('created_at', '>=', $startDate)->count();
                $successful = $other->referrals()
                    ->where('created_at', '>=', $startDate)
                    ->whereHas('referred', function($q) {
                        $q->where('role', 2);
                    })->count();
                $commission = $other->referralRewards()
                    ->where('created_at', '>=', $startDate)
                    ->where('status', 'paid')
                    ->sum('amount');
                
                $totalReferrals += $referrals;
                $totalSuccessful += $successful;
                $totalCommission += $commission;
            }
            
            $avgStats = [
                'referrals' => round($totalReferrals / $otherMarketers->count(), 1),
                'successful_referrals' => round($totalSuccessful / $otherMarketers->count(), 1),
                'commission_earned' => round($totalCommission / $otherMarketers->count(), 0),
                'conversion_rate' => $totalReferrals > 0 ? round(($totalSuccessful / $totalReferrals) * 100, 1) : 0
            ];
        }
        
        return response()->json([
            'success' => true,
            'period' => $period,
            'marketer_stats' => $marketerStats,
            'regional_average' => $avgStats,
            'region' => $region,
            'comparison_count' => $otherMarketers->count()
        ]);
    }
}

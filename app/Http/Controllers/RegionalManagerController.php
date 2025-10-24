<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\AnalyticsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use App\Models\ReferralReward;
use App\Models\Referral;
use App\Models\RegionalScope;

class RegionalManagerController extends Controller
{
    public function dashboard(Request $request)
    {
        $manager = $request->user();
        $scopes = $manager->regionalScopes()->get();
        $properties = Property::query();
        if ($scopes->count()) {
            $stateScopes = $scopes->where('scope_type', 'state')->pluck('scope_value')->filter();
            $lgaScopes = $scopes->where('scope_type', 'lga')->pluck('scope_value')->filter();

            $properties->where(function ($q) use ($stateScopes, $lgaScopes) {
                foreach ($stateScopes as $state) {
                    $q->orWhere('state', $state);
                }
                foreach ($lgaScopes as $pair) {
                    [$state, $lga] = array_pad(explode('::', $pair, 2), 2, null);
                    if ($state && $lga) {
                        $q->orWhere(function ($inner) use ($state, $lga) {
                            $inner->where('state', $state)->where('lga', $lga);
                        });
                    }
                }
            });
        } else {
            $properties->whereRaw('1=0'); // none if no scopes
        }
        $propertyCount = $properties->count();
        return view('regional_manager.dashboard', compact('scopes','propertyCount'));
    }

    public function properties(Request $request)
    {
        $manager = $request->user();
        $scopes = $manager->regionalScopes()->get();

        $query = Property::query()
            ->with(['owner', 'agent'])
            ->orderByDesc('created_at');

        if ($scopes->count()) {
            $stateScopes = $scopes->where('scope_type', 'state')->pluck('scope_value')->filter();
            $lgaScopes = $scopes->where('scope_type', 'lga')->pluck('scope_value')->filter();
            $query->where(function ($q) use ($stateScopes, $lgaScopes) {
                foreach ($stateScopes as $state) {
                    $q->orWhere('state', $state);
                }
                foreach ($lgaScopes as $pair) {
                    [$state, $lga] = array_pad(explode('::', $pair, 2), 2, null);
                    if ($state && $lga) {
                        $q->orWhere(function ($inner) use ($state, $lga) {
                            $inner->where('state', $state)->where('lga', $lga);
                        });
                    }
                }
            });
        } else {
            // If no scopes assigned, return no properties (explicitly)
            $query->whereRaw('1=0');
        }

        // Optional filters
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->orWhere('prop_id', $search)
                  ->orWhere('address', 'like', "%$search%")
                  ->orWhere('lga', 'like', "%$search%")
                  ->orWhere('state', 'like', "%$search%");
            });
        }

        if ($status = $request->query('status')) {
            if (\Schema::hasColumn('properties', 'status')) {
                $query->where('status', $status);
            }
        }

        // Sorting
        $sort = $request->query('sort');
        if ($sort === 'oldest') {
            $query->reorder('created_at', 'asc');
        }

        $properties = $query->paginate(20)->appends($request->query());
        return view('regional_manager.properties', compact('properties', 'scopes'));
    }

    public function marketers(Request $request)
    {
        $manager = $request->user();
        $scopes = $manager->regionalScopes()->get();
        $users = User::query()->whereHas('roles', function($q){ $q->where('name','marketer'); });
        if ($scopes->count()) {
            $stateScopes = $scopes->where('scope_type', 'state')->pluck('scope_value')->filter();
            $lgaScopes = $scopes->where('scope_type', 'lga')->pluck('scope_value')->filter();
            $users->where(function ($q) use ($stateScopes, $lgaScopes) {
                foreach ($stateScopes as $state) {
                    $q->orWhere('state', $state);
                }
                foreach ($lgaScopes as $pair) {
                    [$state, $lga] = array_pad(explode('::', $pair, 2), 2, null);
                    if ($state && $lga) {
                        $q->orWhere(function ($inner) use ($state, $lga) {
                            $inner->where('state', $state)->where('lga', $lga);
                        });
                    }
                }
            });
        } else {
            $users->whereRaw('1=0');
        }
        $marketers = $users->orderBy('first_name')->paginate(25);
        return view('regional_manager.marketers', compact('marketers','scopes'));
    }

    public function analytics(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonths(5)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $propertyType = $request->input('property_type');
        $referralTier = $request->input('referral_tier');

        $hasReferralRewards = \Schema::hasTable('referral_rewards');
        $hasReferrals = \Schema::hasTable('referrals');

        // 6-month performance trend, tiered (defensive if table/columns missing)
        $performanceData = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $label = $m->format('M Y');
            $data = [
                'month' => $label,
                'super_marketer_commissions' => 0.0,
                'marketer_commissions' => 0.0,
                'regional_manager_commissions' => 0.0,
            ];
            if ($hasReferralRewards && \Schema::hasColumn('referral_rewards','tier') && \Schema::hasColumn('referral_rewards','amount')) {
                $rows = DB::table('referral_rewards')
                    ->select('tier', DB::raw('SUM(amount) as total'))
                    ->whereYear('created_at', $m->year)
                    ->whereMonth('created_at', $m->month)
                    ->whereIn('tier', ['super_marketer','marketer','regional_manager'])
                    ->groupBy('tier')
                    ->pluck('total','tier');
                $data['super_marketer_commissions'] = (float) ($rows['super_marketer'] ?? 0);
                $data['marketer_commissions'] = (float) ($rows['marketer'] ?? 0);
                $data['regional_manager_commissions'] = (float) ($rows['regional_manager'] ?? 0);
            }
            $performanceData[] = $data;
        }

        // Commission breakdown by tier within date range
        $commissionBreakdown = [ 'total_amount' => 0.0, 'total_count' => 0 ];
        $tiers = ['super_marketer','marketer','regional_manager','company'];
        if ($hasReferralRewards && \Schema::hasColumn('referral_rewards','tier') && \Schema::hasColumn('referral_rewards','amount')) {
            foreach ($tiers as $t) {
                $query = DB::table('referral_rewards')->whereBetween('created_at', [$startDate, $endDate]);
                if ($t !== 'company') $query->where('tier',$t); else $query->where('tier','company');
                $amount = (float) $query->sum('amount');
                $count = (int) $query->count();
                $commissionBreakdown[$t] = ['total_amount'=>$amount,'count'=>$count];
                $commissionBreakdown['total_amount'] += $amount;
                $commissionBreakdown['total_count'] += $count;
            }
        } else {
            foreach ($tiers as $t) { $commissionBreakdown[$t] = ['total_amount'=>0.0,'count'=>0]; }
        }

        // Chain effectiveness placeholders
        $chainEffectiveness = [
            'total_chains' => 0,
            'conversion_rate' => 0.0,
            'active_chains' => 0,
            'avg_tier_count' => 0.0,
            'completed_chains' => 0,
            'broken_chains' => 0,
        ];
        if ($hasReferrals) {
            $chainEffectiveness['total_chains'] = (int) DB::table('referrals')->whereBetween('created_at', [$startDate, $endDate])->count();
        }

        // Regional comparison: derive by state if available
        $regionalComparison = [];
        $states = Property::select('state')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('state')
            ->groupBy('state')
            ->pluck('state');
        if ($hasReferralRewards && \Schema::hasColumn('referral_rewards','region') && \Schema::hasColumn('referral_rewards','amount')) {
            foreach ($states as $state) {
                $q = DB::table('referral_rewards')->whereBetween('created_at', [$startDate, $endDate])->where('region', $state);
                $total = (float) $q->sum('amount');
                $count = (int) $q->count();
                $regionalComparison[$state] = [
                    'total_commissions' => $total,
                    'avg_commission' => $count > 0 ? $total / $count : 0,
                ];
            }
        }

        // Top performers placeholders
        $topPerformers = [
            'super_marketers' => [],
            'marketers' => [],
            'regional_managers' => [],
        ];

        $hasData = ($commissionBreakdown['total_amount'] ?? 0) > 0 || ($chainEffectiveness['total_chains'] ?? 0) > 0 || count($regionalComparison) > 0;

        return view('regional_manager.analytics', compact(
            'startDate','endDate','propertyType','referralTier',
            'commissionBreakdown','chainEffectiveness','performanceData',
            'regionalComparison','topPerformers','hasData'
        ));
    }

    public function exportAnalytics()
    {
        return Excel::download(new AnalyticsExport(), 'regional-analytics.xlsx');
    }

    public function pendingApprovals(Request $request)
    {
        // For now fetch properties with a 'pending' like status if column exists; else empty collection
        $query = Property::query();
        if (\Schema::hasColumn('properties','status')) {
            $query->where('status','pending');
        } else {
            // fallback: maybe a boolean approved column
            if (\Schema::hasColumn('properties','approved')) {
                $query->where(function($q){
                    $q->whereNull('approved')->orWhere('approved',0);
                });
            }
        }
        $properties = $query->orderByDesc('created_at')->limit(50)->get();
        return view('regional_manager.pending_approvals', compact('properties'));
    }

    public function marketerProperties($id)
    {
        $properties = Property::where('agent_id', $id)->paginate(20);
        return view('regional_manager.marketer_properties', compact('properties'));
    }

    public function approveProperty($propId)
    {
        $property = Property::where('prop_id', $propId)->first();
        if(!$property) return back()->with('error','Property not found.');
        $property->status = 'approved';
        $property->approved_at = now();
        $property->rejected_at = null;
        $property->save();
        return back()->with('success','Property approved successfully.');
    }

    public function rejectProperty($propId)
    {
        $property = Property::where('prop_id', $propId)->first();
        if(!$property) return back()->with('error','Property not found.');
        $property->status = 'rejected';
        $property->rejected_at = now();
        $property->save();
        return back()->with('success','Property rejected successfully.');
    }

    public function activateProperty($propId)
    {
        $property = Property::where('prop_id', $propId)->first();
        if(!$property) return back()->with('error','Property not found.');
        $property->status = 'approved';
        $property->save();
        return back()->with('success','Property activated.');
    }

    public function suspendProperty($propId)
    {
        $property = Property::where('prop_id', $propId)->first();
        if(!$property) return back()->with('error','Property not found.');
        $property->status = 'suspended';
        $property->save();
        return back()->with('success','Property suspended.');
    }
}

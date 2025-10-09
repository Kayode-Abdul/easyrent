<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Property;
use App\Models\Referral;
use App\Models\ReferralReward;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalyticsExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $region = Auth::user()->region;
        $data = [];
        
        // Get monthly performance data for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthName = $month->format('M Y');
            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();
            
            $marketersInRegion = User::whereHas('roles', function($q) {
                $q->where('name', 'Marketer');
            })
            ->where('region', $region)
            ->pluck('user_id');
            
            // Get referrals for this month by marketers in the region
            $monthlyReferrals = Referral::whereIn('referrer_id', $marketersInRegion)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
                
            // Get commissions earned for this month
            $monthlyCommissions = ReferralReward::whereIn('marketer_id', $marketersInRegion)
                ->whereIn('status', ['approved', 'paid'])
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
                
            // Get properties added in this month for this region
            $monthlyProperties = Property::where('state', $region)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
                
            // Calculate success rate (referrals that resulted in landlord registrations)
            $successfulReferrals = Referral::whereIn('referrer_id', $marketersInRegion)
                ->whereHas('referred', function($q) {
                    $q->where('role', 2); // Assuming 2 is the role ID for landlords
                })
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
                
            $successRate = $monthlyReferrals > 0 ? round(($successfulReferrals / $monthlyReferrals) * 100, 1) : 0;
            
            $data[] = [
                'month' => $monthName,
                'referrals' => $monthlyReferrals,
                'properties' => $monthlyProperties,
                'commissions' => $monthlyCommissions,
                'success_rate' => $successRate,
                'successful_referrals' => $successfulReferrals
            ];
        }
        
        return collect($data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Month',
            'Referrals',
            'Properties',
            'Commissions (₦)',
            'Success Rate (%)',
            'Successful Referrals'
        ];
    }

    /**
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row['month'],
            $row['referrals'],
            $row['properties'],
            number_format($row['commissions'], 2),
            $row['success_rate'] . '%',
            $row['successful_referrals']
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Regional Performance Analytics';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

@extends('layout')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h3 class="mb-4">My Referral Commissions</h3>
                <p class="text-muted">Track your earnings from landlords you've referred to the platform.</p>
            </div>
        </div>

        <!-- Stats Row -->
        @php
            $earnedCurrencies = $stats['total_earned']->map(function($s) {
                return [
                    'code' => $s->currency ? $s->currency->code : 'NGN',
                    'symbol' => $s->currency ? $s->currency->symbol : '₦',
                    'amount' => $s->total,
                ];
            })->toArray();
            $pendingCurrencies = $stats['pending_approval']->map(function($s) {
                return [
                    'code' => $s->currency ? $s->currency->code : 'NGN',
                    'symbol' => $s->currency ? $s->currency->symbol : '₦',
                    'amount' => $s->total,
                ];
            })->toArray();
        @endphp
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center icon-success">
                                    <i class="nc-icon nc-money-coins text-success"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="numbers">
                                    <p class="card-category">Total Earned</p>
                                    <x-currency-carousel :currencies="$earnedCurrencies" id="commission-earned" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-watch-time text-warning"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="numbers">
                                    <p class="card-category">Pending Approval</p>
                                    <x-currency-carousel :currencies="$pendingCurrencies" id="commission-pending" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center icon-info">
                                    <i class="nc-icon nc-badge text-info"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="numbers">
                                    <p class="card-category">Total Referrals</p>
                                    <h4 class="card-title">{{ $stats['total_referrals'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commissions Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Earnings Breakdown</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="text-primary">
                                    <th>Date</th>
                                    <th>Commission Type</th>
                                    <th>Referred Landlord</th>
                                    <th>Apartment/Transaction</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </thead>
                                <tbody>
                                    @forelse($rewards as $reward)
                                    <tr>
                                        <td>{{ $reward->created_at->format('M j, Y') }}</td>
                                        <td>
                                            @php
                                                $desc = $reward->description ?? 'Referral Commission';
                                                // Convert e.g., "Commission (super_marketer)" to "Super Marketer Referral Commission"
                                                if (preg_match('/\((.*?)\)/', $desc, $matches)) {
                                                    $tier = ucwords(str_replace('_', ' ', $matches[1]));
                                                    $displayType = $tier . ' Referral Commission';
                                                } else {
                                                    $displayType = ucwords(str_replace('_', ' ', $desc));
                                                }
                                            @endphp
                                            <span class="badge badge-info">{{ $displayType }}</span>
                                        </td>
                                        <td>
                                            @if($reward->referral && $reward->referral->referred)
                                                <strong>{{ $reward->referral->referred->first_name }} {{ $reward->referral->referred->last_name }}</strong>
                                            @else
                                                <strong>Unknown Landlord</strong>
                                            @endif
                                        </td>
                                        <td>
                                            @if($reward->referral && $reward->referral->referred)
                                                Referral: {{ $reward->referral->referred->email }}
                                            @else
                                                Direct Referral
                                            @endif
                                        </td>
                                        <td><span class="text-success font-weight-bold">{{ format_money($reward->amount, $reward->currency) }}</span></td>
                                        <td>
                                            @php
                                                $badgeClass = 'secondary';
                                                $status = $reward->status;
                                                if($status == 'paid') $badgeClass = 'success';
                                                elseif($status == 'approved') $badgeClass = 'info';
                                                elseif($status == 'pending') $badgeClass = 'warning';
                                                elseif($status == 'rejected') $badgeClass = 'danger';
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="nc-icon nc-zoom-split fa-3x mb-3 d-block"></i>
                                            <p>No commissions found. Start referring landlords to earn rewards!</p>
                                            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-round">Get Started</a>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $rewards->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

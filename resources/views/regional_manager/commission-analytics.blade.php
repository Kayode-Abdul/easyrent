@extends('layout')
@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Detailed Commission Analytics</h3>
        <div>
            <a href="{{ route('regional.analytics') }}" class="btn btn-outline-secondary btn-sm">Back to Analytics</a>
            <a href="{{ route('regional.analytics.export.multi-tier') }}" class="btn btn-success btn-sm ml-2">Export Data</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">Filter Commission Data</div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}" id="start_date">
                </div>
                <div class="col-md-3">
                    <label for="end_date">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}" id="end_date">
                </div>
                <div class="col-md-3">
                    <label for="tier">Commission Tier</label>
                    <select class="form-control" name="tier" id="tier">
                        <option value="">All Tiers</option>
                        <option value="super_marketer" {{ $tier == 'super_marketer' ? 'selected' : '' }}>Super Marketer</option>
                        <option value="marketer" {{ $tier == 'marketer' ? 'selected' : '' }}>Marketer</option>
                        <option value="regional_manager" {{ $tier == 'regional_manager' ? 'selected' : '' }}>Regional Manager</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('regional.commission-analytics') }}" class="btn btn-outline-secondary ml-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-primary">₦{{ number_format($summary['total_amount'], 2) }}</h4>
                    <small class="text-muted">Total Amount</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-success">{{ number_format($summary['total_count']) }}</h4>
                    <small class="text-muted">Total Payments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-info">₦{{ number_format($summary['avg_amount'], 2) }}</h4>
                    <small class="text-muted">Average Payment</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-warning">{{ count($summary['tier_breakdown']) }}</h4>
                    <small class="text-muted">Active Tiers</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tier Breakdown -->
    @if(count($summary['tier_breakdown']) > 0)
    <div class="card mb-4">
        <div class="card-header">Commission Breakdown by Tier</div>
        <div class="card-body">
            <div class="row">
                @foreach($summary['tier_breakdown'] as $tier => $data)
                <div class="col-md-3">
                    <div class="card border-left-primary">
                        <div class="card-body">
                            <h5>{{ ucfirst(str_replace('_', ' ', $tier)) }}</h5>
                            <p class="mb-1"><strong>Count:</strong> {{ $data['count'] }}</p>
                            <p class="mb-1"><strong>Total:</strong> ₦{{ number_format($data['total'], 2) }}</p>
                            <p class="mb-0"><strong>Average:</strong> ₦{{ number_format($data['avg'], 2) }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Payments Table -->
    <div class="card">
        <div class="card-header">Commission Payments Detail</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Marketer</th>
                            <th>Tier</th>
                            <th>Amount</th>
                            <th>Region</th>
                            <th>Status</th>
                            <th>Chain ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->created_at->format('M d, Y') }}</td>
                            <td>
                                <small class="font-monospace">{{ $payment->payment_reference }}</small>
                            </td>
                            <td>
                                @if($payment->marketer)
                                    {{ $payment->marketer->first_name }} {{ $payment->marketer->last_name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $payment->commission_tier == 'super_marketer' ? 'primary' : ($payment->commission_tier == 'marketer' ? 'success' : 'warning') }}">
                                    {{ $payment->formatted_commission_tier }}
                                </span>
                            </td>
                            <td>₦{{ number_format($payment->total_amount, 2) }}</td>
                            <td>{{ $payment->region ?? 'N/A' }}</td>
                            <td>
                                <span class="badge {{ $payment->status_badge_class }}">
                                    {{ ucfirst($payment->payment_status) }}
                                </span>
                            </td>
                            <td>
                                @if($payment->referral_chain_id)
                                    <small class="font-monospace">#{{ $payment->referral_chain_id }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No commission payments found for the selected criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
        <div class="card-footer">
            {{ $payments->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
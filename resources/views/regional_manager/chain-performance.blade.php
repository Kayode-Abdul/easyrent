@extends('layout')
@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Referral Chain Performance</h3>
        <div>
            <a href="{{ route('regional.analytics') }}" class="btn btn-outline-secondary btn-sm">Back to Analytics</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">Filter Chain Data</div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-4">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}" id="start_date">
                </div>
                <div class="col-md-4">
                    <label for="end_date">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}" id="end_date">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('regional.chain-performance') }}" class="btn btn-outline-secondary ml-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-left-primary">
                <div class="card-body">
                    <h4 class="text-primary">{{ $metrics['total_chains'] }}</h4>
                    <small class="text-muted">Total Chains</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-left-success">
                <div class="card-body">
                    <h4 class="text-success">{{ $metrics['active_chains'] }}</h4>
                    <small class="text-muted">Active Chains</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-left-info">
                <div class="card-body">
                    <h4 class="text-info">{{ number_format($metrics['completion_rate'], 1) }}%</h4>
                    <small class="text-muted">Completion Rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-left-warning">
                <div class="card-body">
                    <h4 class="text-warning">₦{{ number_format($metrics['avg_commission_per_chain'], 2) }}</h4>
                    <small class="text-muted">Avg Commission/Chain</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tier Distribution Chart -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Chain Tier Distribution</div>
                <div class="card-body">
                    <canvas id="tierDistributionChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Chain Status Overview</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h5 class="text-success">{{ $metrics['active_chains'] }}</h5>
                            <small class="text-muted">Active</small>
                        </div>
                        <div class="col-4">
                            <h5 class="text-primary">{{ $chains->where('status', 'completed')->count() }}</h5>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div class="col-4">
                            <h5 class="text-danger">{{ $chains->where('status', 'broken')->count() }}</h5>
                            <small class="text-muted">Broken</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-4">
                            <h6>{{ $metrics['tier_distribution']['1_tier'] }}</h6>
                            <small class="text-muted">1-Tier Chains</small>
                        </div>
                        <div class="col-4">
                            <h6>{{ $metrics['tier_distribution']['2_tier'] }}</h6>
                            <small class="text-muted">2-Tier Chains</small>
                        </div>
                        <div class="col-4">
                            <h6>{{ $metrics['tier_distribution']['3_tier'] }}</h6>
                            <small class="text-muted">3-Tier Chains</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Chains Table -->
    <div class="card">
        <div class="card-header">Referral Chain Details</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Chain ID</th>
                            <th>Created</th>
                            <th>Super Marketer</th>
                            <th>Marketer</th>
                            <th>Landlord</th>
                            <th>Tiers</th>
                            <th>Status</th>
                            <th>Region</th>
                            <th>Commission %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($chains as $chain)
                        <tr>
                            <td>
                                <small class="font-monospace">#{{ $chain->id }}</small>
                            </td>
                            <td>{{ $chain->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($chain->superMarketer)
                                    <small>{{ $chain->superMarketer->first_name }} {{ $chain->superMarketer->last_name }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($chain->marketer)
                                    <small>{{ $chain->marketer->first_name }} {{ $chain->marketer->last_name }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($chain->landlord)
                                    <small>{{ $chain->landlord->first_name }} {{ $chain->landlord->last_name }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $chain->getTierCount() }} Tier{{ $chain->getTierCount() > 1 ? 's' : '' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $chain->status == 'active' ? 'success' : ($chain->status == 'completed' ? 'primary' : 'danger') }}">
                                    {{ ucfirst($chain->status) }}
                                </span>
                            </td>
                            <td>{{ $chain->region ?? 'N/A' }}</td>
                            <td>
                                @if($chain->total_commission_percentage)
                                    {{ number_format($chain->total_commission_percentage, 2) }}%
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                No referral chains found for the selected criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($chains->hasPages())
        <div class="card-footer">
            {{ $chains->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tier Distribution Chart
    const tierCtx = document.getElementById('tierDistributionChart').getContext('2d');
    new Chart(tierCtx, {
        type: 'doughnut',
        data: {
            labels: ['1-Tier Chains', '2-Tier Chains', '3-Tier Chains'],
            datasets: [{
                data: [
                    {{ $metrics['tier_distribution']['1_tier'] }},
                    {{ $metrics['tier_distribution']['2_tier'] }},
                    {{ $metrics['tier_distribution']['3_tier'] }}
                ],
                backgroundColor: ['#28a745', '#007bff', '#ffc107'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endsection
@extends('layout')
@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Multi-Tier Regional Analytics</h3>
        <div>
            <a href="{{ route('regional.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back</a>
            <a href="{{ route('regional.analytics.export') }}" class="btn btn-success btn-sm ml-2">Export</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">Analytics Filters</div>
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
                    <label for="property_type">Property Type</label>
                    <select class="form-control" name="property_type" id="property_type">
                        <option value="">All Types</option>
                        <option value="apartment" {{ $propertyType == 'apartment' ? 'selected' : '' }}>Apartment</option>
                        <option value="house" {{ $propertyType == 'house' ? 'selected' : '' }}>House</option>
                        <option value="commercial" {{ $propertyType == 'commercial' ? 'selected' : '' }}>Commercial</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="referral_tier">Commission Tier</label>
                    <select class="form-control" name="referral_tier" id="referral_tier">
                        <option value="">All Tiers</option>
                        <option value="super_marketer" {{ $referralTier == 'super_marketer' ? 'selected' : '' }}>Super Marketer</option>
                        <option value="marketer" {{ $referralTier == 'marketer' ? 'selected' : '' }}>Marketer</option>
                        <option value="regional_manager" {{ $referralTier == 'regional_manager' ? 'selected' : '' }}>Regional Manager</option>
                    </select>
                </div>
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('regional.analytics') }}" class="btn btn-outline-secondary ml-2">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if(!$hasData)
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No data found for the selected filters. Try adjusting your date range or filter criteria.
        </div>
    @else

    <!-- Commission Breakdown by Tier -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Commission Breakdown by Tier</div>
                <div class="card-body">
                    <canvas id="commissionBreakdownChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Commission Summary</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <h4 class="text-primary">₦{{ number_format($commissionBreakdown['total_amount'], 2) }}</h4>
                            <small class="text-muted">Total Commissions</small>
                        </div>
                        <div class="col-6">
                            <h5>{{ $commissionBreakdown['total_count'] }}</h5>
                            <small class="text-muted">Total Payments</small>
                        </div>
                        <div class="col-6">
                            <h5>₦{{ number_format($commissionBreakdown['total_count'] > 0 ? $commissionBreakdown['total_amount'] / $commissionBreakdown['total_count'] : 0, 2) }}</h5>
                            <small class="text-muted">Avg Payment</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Chain Effectiveness -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Referral Chain Effectiveness</div>
                <div class="card-body">
                    <canvas id="chainEffectivenessChart" height="150"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Chain Performance Metrics</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center mb-3">
                            <h4 class="text-success">{{ $chainEffectiveness['total_chains'] }}</h4>
                            <small class="text-muted">Total Chains</small>
                        </div>
                        <div class="col-6 text-center mb-3">
                            <h4 class="text-info">{{ number_format($chainEffectiveness['conversion_rate'], 1) }}%</h4>
                            <small class="text-muted">Conversion Rate</small>
                        </div>
                        <div class="col-6 text-center">
                            <h4 class="text-warning">{{ $chainEffectiveness['active_chains'] }}</h4>
                            <small class="text-muted">Active Chains</small>
                        </div>
                        <div class="col-6 text-center">
                            <h4 class="text-primary">{{ number_format($chainEffectiveness['avg_tier_count'], 1) }}</h4>
                            <small class="text-muted">Avg Tier Count</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Multi-Tier Performance Trend -->
    <div class="card mb-4">
        <div class="card-header">6-Month Multi-Tier Performance Trend</div>
        <div class="card-body">
            <canvas id="performanceTrendChart" height="80"></canvas>
        </div>
    </div>

    <!-- Regional Comparison -->
    @if(count($regionalComparison) > 1)
    <div class="card mb-4">
        <div class="card-header">Regional Performance Comparison</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <canvas id="regionalComparisonChart" height="100"></canvas>
                </div>
                <div class="col-md-4">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Region</th>
                                    <th>Total</th>
                                    <th>Avg</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($regionalComparison as $region => $data)
                                <tr>
                                    <td><small>{{ $region }}</small></td>
                                    <td><small>₦{{ number_format($data['total_commissions'], 0) }}</small></td>
                                    <td><small>₦{{ number_format($data['avg_commission'], 0) }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Top Performers by Tier -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Top Super Marketers</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topPerformers['super_marketers'] as $idx => $performer)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>
                                        <small>{{ $performer['user']->first_name ?? 'N/A' }} {{ $performer['user']->last_name ?? '' }}</small>
                                    </td>
                                    <td><small>₦{{ number_format($performer['total_commissions'], 0) }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Top Marketers</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topPerformers['marketers'] as $idx => $performer)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>
                                        <small>{{ $performer['user']->first_name ?? 'N/A' }} {{ $performer['user']->last_name ?? '' }}</small>
                                    </td>
                                    <td><small>₦{{ number_format($performer['total_commissions'], 0) }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Top Regional Managers</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topPerformers['regional_managers'] as $idx => $performer)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>
                                        <small>{{ $performer['user']->first_name ?? 'N/A' }} {{ $performer['user']->last_name ?? '' }}</small>
                                    </td>
                                    <td><small>₦{{ number_format($performer['total_commissions'], 0) }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($hasData)
    
    // Commission Breakdown Chart
    const commissionCtx = document.getElementById('commissionBreakdownChart').getContext('2d');
    new Chart(commissionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Super Marketer', 'Marketer', 'Regional Manager', 'Company'],
            datasets: [{
                data: [
                    {{ $commissionBreakdown['super_marketer']['total_amount'] }},
                    {{ $commissionBreakdown['marketer']['total_amount'] }},
                    {{ $commissionBreakdown['regional_manager']['total_amount'] }},
                    {{ $commissionBreakdown['company']['total_amount'] }}
                ],
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#6c757d'],
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

    // Chain Effectiveness Chart
    const chainCtx = document.getElementById('chainEffectivenessChart').getContext('2d');
    new Chart(chainCtx, {
        type: 'bar',
        data: {
            labels: ['Active', 'Completed', 'Broken'],
            datasets: [{
                label: 'Chains',
                data: [
                    {{ $chainEffectiveness['active_chains'] }},
                    {{ $chainEffectiveness['completed_chains'] }},
                    {{ $chainEffectiveness['broken_chains'] }}
                ],
                backgroundColor: ['#28a745', '#007bff', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Performance Trend Chart
    const trendCtx = document.getElementById('performanceTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach($performanceData as $data)
                '{{ $data['month'] }}',
                @endforeach
            ],
            datasets: [{
                label: 'Super Marketer',
                data: [
                    @foreach($performanceData as $data)
                    {{ $data['super_marketer_commissions'] }},
                    @endforeach
                ],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: false
            }, {
                label: 'Marketer',
                data: [
                    @foreach($performanceData as $data)
                    {{ $data['marketer_commissions'] }},
                    @endforeach
                ],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: false
            }, {
                label: 'Regional Manager',
                data: [
                    @foreach($performanceData as $data)
                    {{ $data['regional_manager_commissions'] }},
                    @endforeach
                ],
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    @if(count($regionalComparison) > 1)
    // Regional Comparison Chart
    const regionalCtx = document.getElementById('regionalComparisonChart').getContext('2d');
    new Chart(regionalCtx, {
        type: 'bar',
        data: {
            labels: [
                @foreach($regionalComparison as $region => $data)
                '{{ $region }}',
                @endforeach
            ],
            datasets: [{
                label: 'Total Commissions',
                data: [
                    @foreach($regionalComparison as $region => $data)
                    {{ $data['total_commissions'] }},
                    @endforeach
                ],
                backgroundColor: '#007bff'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    @endif

    @endif
});
</script>
@endsection

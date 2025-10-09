@extends('layouts.app')

@section('title', 'System Performance Analytics')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">System Performance Analytics</h1>
                    <p class="text-muted">Monitor commission system health and performance metrics</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="fas fa-download"></i> Export Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ $startDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ $endDate->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-heartbeat text-primary"></i> System Health Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="health-indicator {{ $healthMetrics['calculation_health']['health_status'] }}">
                                    <i class="fas fa-calculator fa-2x"></i>
                                </div>
                                <h6 class="mt-2">Calculation Health</h6>
                                <p class="text-muted">{{ $healthMetrics['calculation_health']['success_rate'] }}% Success Rate</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="health-indicator {{ $healthMetrics['payment_processing']['health_status'] }}">
                                    <i class="fas fa-credit-card fa-2x"></i>
                                </div>
                                <h6 class="mt-2">Payment Processing</h6>
                                <p class="text-muted">{{ $healthMetrics['payment_processing']['success_rate'] }}% Success Rate</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="health-indicator {{ $healthMetrics['fraud_detection']['health_status'] }}">
                                    <i class="fas fa-shield-alt fa-2x"></i>
                                </div>
                                <h6 class="mt-2">Fraud Detection</h6>
                                <p class="text-muted">{{ $healthMetrics['fraud_detection']['active_alerts'] }} Active Alerts</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="health-indicator {{ $healthMetrics['system_performance']['health_status'] }}">
                                    <i class="fas fa-tachometer-alt fa-2x"></i>
                                </div>
                                <h6 class="mt-2">System Performance</h6>
                                <p class="text-muted">{{ $healthMetrics['system_performance']['average_response_time_ms'] }}ms Avg Response</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Alerts -->
    @if(!empty($activeAlerts))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Active System Alerts
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($activeAlerts as $alert)
                    <div class="alert alert-{{ $alert['severity'] === 'critical' ? 'danger' : ($alert['severity'] === 'error' ? 'warning' : 'info') }} mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ ucfirst(str_replace('_', ' ', $alert['type'])) }}</strong>
                                <p class="mb-0 mt-1">
                                    @if(isset($alert['data']['description']))
                                        {{ $alert['data']['description'] }}
                                    @else
                                        Alert created at {{ $alert['created_at'] }}
                                    @endif
                                </p>
                            </div>
                            <span class="badge bg-{{ $alert['severity'] === 'critical' ? 'danger' : ($alert['severity'] === 'error' ? 'warning' : 'info') }}">
                                {{ strtoupper($alert['severity']) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Performance Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">₦{{ number_format($commissionMetrics['total_commissions'], 2) }}</h4>
                            <p class="card-text">Total Commissions</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $commissionMetrics['success_rate'] }}%</h4>
                            <p class="card-text">Commission Success Rate</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $chainEffectiveness['total_chains'] }}</h4>
                            <p class="card-text">Referral Chains</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-link fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ $fraudStats['flagged_users'] + $fraudStats['flagged_referrals'] }}</h4>
                            <p class="card-text">Flagged Items</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-flag fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Commission Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="commissionTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment Processing Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission by Tier -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Commission Distribution by Tier</h5>
                </div>
                <div class="card-body">
                    <canvas id="commissionTierChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Referral Chain Effectiveness</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="text-primary">{{ $chainEffectiveness['conversion_rate'] }}%</h3>
                            <p class="text-muted">Conversion Rate</p>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success">₦{{ number_format($chainEffectiveness['average_commission_per_chain'], 2) }}</h3>
                            <p class="text-muted">Avg Commission per Chain</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-info">{{ $chainEffectiveness['active_chains'] }}</h4>
                            <p class="text-muted">Active Chains</p>
                        </div>
                        <div class="col-6">
                            <h4 class="text-secondary">{{ $chainEffectiveness['total_chains'] - $chainEffectiveness['active_chains'] }}</h4>
                            <p class="text-muted">Inactive Chains</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Regional Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Regional Performance Comparison</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Region</th>
                                    <th>Total Payments</th>
                                    <th>Total Amount</th>
                                    <th>Average Amount</th>
                                    <th>Success Rate</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($regionalPerformance['regional_commissions'] as $region)
                                <tr>
                                    <td><strong>{{ $region->region ?: 'Unknown' }}</strong></td>
                                    <td>{{ number_format($region->total_payments) }}</td>
                                    <td>₦{{ number_format($region->total_amount, 2) }}</td>
                                    <td>₦{{ number_format($region->avg_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $region->success_rate >= 90 ? 'success' : ($region->success_rate >= 70 ? 'warning' : 'danger') }}">
                                            {{ $region->success_rate }}%
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $region->success_rate >= 90 ? 'success' : ($region->success_rate >= 70 ? 'warning' : 'danger') }}" 
                                                 role="progressbar" style="width: {{ $region->success_rate }}%">
                                                {{ $region->success_rate }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performing Chains -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Performing Referral Chains</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Chain ID</th>
                                    <th>Super Marketer</th>
                                    <th>Marketer</th>
                                    <th>Landlord</th>
                                    <th>Total Commission</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chainEffectiveness['top_performing_chains'] as $chain)
                                <tr>
                                    <td><code>#{{ $chain->id }}</code></td>
                                    <td>{{ $chain->superMarketer->name ?? 'N/A' }}</td>
                                    <td>{{ $chain->marketer->name ?? 'N/A' }}</td>
                                    <td>{{ $chain->landlord->name ?? 'N/A' }}</td>
                                    <td>₦{{ number_format($chain->commission_payments_sum_amount ?? 0, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $chain->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($chain->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Analytics Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.system-analytics.export') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="export_start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="export_start_date" name="start_date" 
                               value="{{ $startDate->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="export_end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="export_end_date" name="end_date" 
                               value="{{ $endDate->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="export_format" class="form-label">Format</label>
                        <select class="form-select" id="export_format" name="format" required>
                            <option value="csv">CSV</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Export Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.health-indicator {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.health-indicator.healthy {
    background-color: #d4edda;
    color: #155724;
}

.health-indicator.warning {
    background-color: #fff3cd;
    color: #856404;
}

.health-indicator.critical {
    background-color: #f8d7da;
    color: #721c24;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.progress {
    background-color: #e9ecef;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Commission Trends Chart
const commissionTrendsCtx = document.getElementById('commissionTrendsChart').getContext('2d');
const commissionTrendsChart = new Chart(commissionTrendsCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($commissionMetrics['daily_trends']->pluck('date')) !!},
        datasets: [{
            label: 'Commission Amount',
            data: {!! json_encode($commissionMetrics['daily_trends']->pluck('total_amount')) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Payment Count',
            data: {!! json_encode($commissionMetrics['daily_trends']->pluck('count')) !!},
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});

// Payment Trends Chart
const paymentTrendsCtx = document.getElementById('paymentTrendsChart').getContext('2d');
const paymentTrendsChart = new Chart(paymentTrendsCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($paymentTrends->pluck('date')) !!},
        datasets: [{
            label: 'Success Rate (%)',
            data: {!! json_encode($paymentTrends->pluck('success_rate')) !!},
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});

// Commission by Tier Chart
const commissionTierCtx = document.getElementById('commissionTierChart').getContext('2d');
const commissionTierChart = new Chart(commissionTierCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($commissionMetrics['commission_by_tier']->pluck('commission_tier')) !!},
        datasets: [{
            data: {!! json_encode($commissionMetrics['commission_by_tier']->pluck('total_amount')) !!},
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Refresh Dashboard Function
function refreshDashboard() {
    location.reload();
}

// Auto-refresh every 5 minutes
setInterval(function() {
    // Update real-time metrics without full page reload
    fetch('{{ route("admin.system-analytics.real-time") }}')
        .then(response => response.json())
        .then(data => {
            // Update specific elements with new data
            console.log('Real-time metrics updated:', data);
        })
        .catch(error => console.error('Error updating metrics:', error));
}, 300000); // 5 minutes
</script>
@endpush
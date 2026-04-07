@extends('layouts.admin')

@section('title', 'Payment Calculation Monitoring')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Payment Calculation Monitoring</h1>
                    <p class="text-muted">Monitor performance, accuracy, and system health</p>
                </div>
                <div class="d-flex gap-2">
                    <!-- Time Period Selector -->
                    <select id="timePeriodSelect" class="form-select" style="width: auto;">
                        @foreach($availablePeriods as $hours => $label)
                            <option value="{{ $hours }}" {{ $selectedHours == $hours ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    
                    <!-- Export Button -->
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportData('json')">JSON</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('csv')">CSV</a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('excel')">Excel</a></li>
                        </ul>
                    </div>
                    
                    <!-- Refresh Button -->
                    <button id="refreshBtn" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status Alert -->
    <div id="systemStatusAlert" class="row mb-4">
        <div class="col-12">
            <div class="alert alert-{{ $dashboardData['overview']['system_status'] === 'healthy' ? 'success' : ($dashboardData['overview']['system_status'] === 'critical' ? 'danger' : 'warning') }}" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-{{ $dashboardData['overview']['system_status'] === 'healthy' ? 'check-circle' : ($dashboardData['overview']['system_status'] === 'critical' ? 'exclamation-triangle' : 'exclamation-circle') }} me-2"></i>
                    <strong>System Status: {{ ucfirst($dashboardData['overview']['system_status']) }}</strong>
                    <span class="ms-auto">Last Updated: <span id="lastUpdated">{{ $dashboardData['overview']['generated_at'] }}</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Alerts -->
    @if(!empty($dashboardData['alerts']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Active Alerts ({{ count($dashboardData['alerts']) }})
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($dashboardData['alerts'] as $alert)
                    <div class="alert alert-{{ $alert['severity'] === 'critical' ? 'danger' : ($alert['severity'] === 'warning' ? 'warning' : 'info') }} mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ ucfirst(str_replace('_', ' ', $alert['type'])) }}</strong>
                                <p class="mb-0">{{ $alert['message'] }}</p>
                            </div>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($alert['timestamp'])->diffForHumans() }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-primary mb-2">
                        <i class="fas fa-calculator fa-2x"></i>
                    </div>
                    <h3 class="card-title">{{ number_format($dashboardData['performance']['total_calculations']) }}</h3>
                    <p class="card-text text-muted">Total Calculations</p>
                    <small class="text-success">
                        <i class="fas fa-arrow-up"></i> {{ $dashboardData['performance']['success_rate'] }}% Success Rate
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-success mb-2">
                        <i class="fas fa-tachometer-alt fa-2x"></i>
                    </div>
                    <h3 class="card-title">{{ $dashboardData['performance']['avg_execution_time_ms'] }}ms</h3>
                    <p class="card-text text-muted">Avg Execution Time</p>
                    <small class="{{ $dashboardData['performance']['avg_execution_time_ms'] <= 100 ? 'text-success' : 'text-warning' }}">
                        <i class="fas fa-{{ $dashboardData['performance']['avg_execution_time_ms'] <= 100 ? 'check' : 'exclamation-triangle' }}"></i>
                        {{ $dashboardData['performance']['avg_execution_time_ms'] <= 100 ? 'Optimal' : 'Needs Attention' }}
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-info mb-2">
                        <i class="fas fa-bullseye fa-2x"></i>
                    </div>
                    <h3 class="card-title">{{ $dashboardData['accuracy']['accuracy_rate'] }}%</h3>
                    <p class="card-text text-muted">Accuracy Rate</p>
                    <small class="{{ $dashboardData['accuracy']['accuracy_rate'] >= 98 ? 'text-success' : 'text-warning' }}">
                        <i class="fas fa-{{ $dashboardData['accuracy']['accuracy_rate'] >= 98 ? 'check' : 'exclamation-triangle' }}"></i>
                        {{ number_format($dashboardData['accuracy']['total_verified_calculations']) }} Verified
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-{{ $dashboardData['errors']['error_rate'] <= 1 ? 'success' : 'danger' }} mb-2">
                        <i class="fas fa-exclamation-circle fa-2x"></i>
                    </div>
                    <h3 class="card-title">{{ $dashboardData['errors']['error_rate'] }}%</h3>
                    <p class="card-text text-muted">Error Rate</p>
                    <small class="{{ $dashboardData['errors']['error_rate'] <= 1 ? 'text-success' : 'text-danger' }}">
                        <i class="fas fa-{{ $dashboardData['errors']['error_rate'] <= 1 ? 'check' : 'times' }}"></i>
                        {{ number_format($dashboardData['errors']['total_errors']) }} Total Errors
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Performance Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Pricing Type Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="pricingTypeChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Metrics Tables -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Performance Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Total Calculations</td>
                                <td class="text-end">{{ number_format($dashboardData['performance']['total_calculations']) }}</td>
                            </tr>
                            <tr>
                                <td>Successful Calculations</td>
                                <td class="text-end">{{ number_format($dashboardData['performance']['successful_calculations']) }}</td>
                            </tr>
                            <tr>
                                <td>Failed Calculations</td>
                                <td class="text-end">{{ number_format($dashboardData['performance']['failed_calculations']) }}</td>
                            </tr>
                            <tr>
                                <td>Slow Calculations</td>
                                <td class="text-end">{{ number_format($dashboardData['performance']['slow_calculations']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Accuracy Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Verified Calculations</td>
                                <td class="text-end">{{ number_format($dashboardData['accuracy']['total_verified_calculations']) }}</td>
                            </tr>
                            <tr>
                                <td>Accurate Calculations</td>
                                <td class="text-end">{{ number_format($dashboardData['accuracy']['accurate_calculations']) }}</td>
                            </tr>
                            <tr>
                                <td>Average Deviation</td>
                                <td class="text-end">${{ number_format($dashboardData['accuracy']['avg_deviation'], 2) }}</td>
                            </tr>
                            <tr>
                                <td>Fallback Usage Rate</td>
                                <td class="text-end">{{ $dashboardData['accuracy']['fallback_usage_rate'] }}%</td>
                            </tr>
                            <tr>
                                <td>High Value Calculations</td>
                                <td class="text-end">{{ number_format($dashboardData['accuracy']['high_value_calculations']) }}</td>
                            </tr>
                            <tr>
                                <td>Suspicious Calculations</td>
                                <td class="text-end">{{ number_format($dashboardData['accuracy']['suspicious_calculations']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($dashboardData['errors']['error_types']))
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Error Types</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Error Type</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($dashboardData['errors']['error_types'], 0, 10, true) as $type => $count)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                                <td class="text-end">{{ number_format($count) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Pricing Configuration Usage</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Total Pricing Type</td>
                                <td class="text-end">{{ number_format($dashboardData['pricing_configuration_usage']['total_pricing_type']) }}</td>
                            </tr>
                            <tr>
                                <td>Monthly Pricing Type</td>
                                <td class="text-end">{{ number_format($dashboardData['pricing_configuration_usage']['monthly_pricing_type']) }}</td>
                            </tr>
                            <tr>
                                <td>Fallback Usage</td>
                                <td class="text-end">{{ number_format($dashboardData['pricing_configuration_usage']['fallback_usage']) }}</td>
                            </tr>
                            <tr>
                                <td>Configuration Changes</td>
                                <td class="text-end">{{ number_format($dashboardData['pricing_configuration_usage']['configuration_changes']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Auto-refresh indicator -->
<div id="autoRefreshIndicator" class="position-fixed bottom-0 end-0 m-3" style="display: none;">
    <div class="alert alert-info alert-dismissible">
        <i class="fas fa-sync-alt fa-spin"></i> Auto-refreshing...
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.alert {
    border-radius: 0.375rem;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.text-muted {
    color: #6c757d !important;
}

#autoRefreshIndicator {
    z-index: 1050;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let performanceChart, pricingTypeChart;
let autoRefreshInterval;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupEventListeners();
    startAutoRefresh();
});

function initializeCharts() {
    // Performance Trends Chart
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    const hourlyTrends = @json($dashboardData['performance']['hourly_trends'] ?? []);
    
    performanceChart = new Chart(performanceCtx, {
        type: 'line',
        data: {
            labels: hourlyTrends.map(trend => trend.hour || 'N/A'),
            datasets: [{
                label: 'Calculations per Hour',
                data: hourlyTrends.map(trend => trend.total_calculations || 0),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'Success Rate %',
                data: hourlyTrends.map(trend => {
                    const total = trend.total_calculations || 0;
                    const successful = trend.successful_calculations || 0;
                    return total > 0 ? (successful / total) * 100 : 0;
                }),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Calculations'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Success Rate %'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    min: 0,
                    max: 100
                }
            }
        }
    });

    // Pricing Type Distribution Chart
    const pricingCtx = document.getElementById('pricingTypeChart').getContext('2d');
    const pricingBreakdown = @json($dashboardData['performance']['pricing_type_breakdown'] ?? []);
    
    pricingTypeChart = new Chart(pricingCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(pricingBreakdown),
            datasets: [{
                data: Object.values(pricingBreakdown),
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
}

function setupEventListeners() {
    // Time period selector
    document.getElementById('timePeriodSelect').addEventListener('change', function() {
        const hours = this.value;
        window.location.href = `{{ route('admin.payment-monitoring.dashboard') }}?hours=${hours}`;
    });

    // Refresh button
    document.getElementById('refreshBtn').addEventListener('click', function() {
        refreshDashboard();
    });
}

function startAutoRefresh() {
    // Auto-refresh every 5 minutes
    autoRefreshInterval = setInterval(function() {
        refreshDashboard(true);
    }, 300000);
}

function refreshDashboard(isAutoRefresh = false) {
    if (isAutoRefresh) {
        document.getElementById('autoRefreshIndicator').style.display = 'block';
    }
    
    const hours = document.getElementById('timePeriodSelect').value;
    
    fetch(`{{ route('admin.payment-monitoring.dashboard-data') }}?hours=${hours}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardData(data.data);
                document.getElementById('lastUpdated').textContent = new Date().toLocaleString();
            }
        })
        .catch(error => {
            console.error('Error refreshing dashboard:', error);
        })
        .finally(() => {
            if (isAutoRefresh) {
                setTimeout(() => {
                    document.getElementById('autoRefreshIndicator').style.display = 'none';
                }, 2000);
            }
        });
}

function updateDashboardData(data) {
    // Update key metrics cards
    // This would involve updating the DOM elements with new data
    // For brevity, showing concept only
    console.log('Dashboard data updated:', data);
    
    // Update charts if needed
    if (performanceChart && data.performance.hourly_trends) {
        performanceChart.data.labels = data.performance.hourly_trends.map(trend => trend.hour || 'N/A');
        performanceChart.data.datasets[0].data = data.performance.hourly_trends.map(trend => trend.total_calculations || 0);
        performanceChart.update();
    }
}

function exportData(format) {
    const hours = document.getElementById('timePeriodSelect').value;
    const url = `{{ route('admin.payment-monitoring.export') }}?hours=${hours}&format=${format}`;
    window.open(url, '_blank');
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
</script>
@endpush
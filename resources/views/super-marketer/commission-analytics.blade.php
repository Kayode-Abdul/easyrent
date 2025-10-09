@extends('layout')


@section('content')
<div class="content">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fas fa-chart-bar text-success me-2"></i>
                                Commission Analytics
                            </h4>
                            <p class="text-muted mb-0">Detailed analysis of your commission performance and network earnings</p>
                        </div>
                        <div>
                            <a href="{{ route('super-marketer.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('super-marketer.commission-analytics') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ request('start_date', $dateRange[0]->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ request('end_date', $dateRange[1]->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i>
                                Apply Filter
                            </button>
                            <a href="{{ route('super-marketer.commission-analytics') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Reset
                            </a>
                        </div>
                        <div class="col-md-3 d-flex align-items-end justify-content-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="setDateRange('7days')">7 Days</button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="setDateRange('30days')">30 Days</button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="setDateRange('90days')">90 Days</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Your Commission
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₦{{ number_format($commissionByTier['super_marketer'], 2) }}
                            </div>
                            <div class="text-xs {{ $comparison['growth_percentage'] >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="fas fa-{{ $comparison['growth_percentage'] >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($comparison['growth_percentage']) }}% vs previous period
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-crown fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Network Commission
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₦{{ number_format($commissionByTier['marketer'], 2) }}
                            </div>
                            <div class="text-xs text-muted">
                                Earned by your marketers
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Generated
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₦{{ number_format($commissionByTier['super_marketer'] + $commissionByTier['marketer'], 2) }}
                            </div>
                            <div class="text-xs text-info">
                                Network total
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Previous Period
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₦{{ number_format($comparison['previous_period'], 2) }}
                            </div>
                            <div class="text-xs text-muted">
                                For comparison
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-history fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Trends Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Commission Trends</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Export Options:</div>
                            <a class="dropdown-item" href="#" onclick="exportChart('png')">Download as PNG</a>
                            <a class="dropdown-item" href="#" onclick="exportChart('pdf')">Download as PDF</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="commissionTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Regional Breakdown and Commission Distribution -->
    <div class="row mb-4">
        <!-- Regional Breakdown -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Regional Performance</h6>
                </div>
                <div class="card-body">
                    @if(count($regionalBreakdown) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Region</th>
                                        <th>Commission</th>
                                        <th>Share</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalRegional = collect($regionalBreakdown)->sum('total');
                                    @endphp
                                    @foreach($regionalBreakdown as $region)
                                        <tr>
                                            <td class="font-weight-bold">{{ $region['region'] ?? 'Unknown' }}</td>
                                            <td>₦{{ number_format($region['total'], 2) }}</td>
                                            <td>
                                                @php
                                                    $percentage = $totalRegional > 0 ? ($region['total'] / $totalRegional) * 100 : 0;
                                                @endphp
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: {{ $percentage }}%"
                                                         aria-valuenow="{{ $percentage }}" 
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        {{ round($percentage, 1) }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                            <p>No regional data available for selected period</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Commission Distribution Pie Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Commission Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="commissionDistributionChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Your Commission
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> Network Commission
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Breakdown</h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="exportTable()">
                        <i class="fas fa-download me-1"></i>
                        Export Data
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="analyticsTable">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Your Commission</th>
                                    <th>Network Commission</th>
                                    <th>Total Generated</th>
                                    <th>Growth</th>
                                    <th>Marketers Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commissionTrends as $index => $trend)
                                    @php
                                        $previousAmount = $index > 0 ? $commissionTrends[$index - 1]['amount'] : 0;
                                        $growth = $previousAmount > 0 ? (($trend['amount'] - $previousAmount) / $previousAmount) * 100 : 0;
                                        $networkAmount = $commissionByTier['marketer'] * 0.1; // Simplified calculation
                                        $total = $trend['amount'] + $networkAmount;
                                    @endphp
                                    <tr>
                                        <td class="font-weight-bold">{{ $trend['month'] }}</td>
                                        <td>₦{{ number_format($trend['amount'], 2) }}</td>
                                        <td>₦{{ number_format($networkAmount, 2) }}</td>
                                        <td class="font-weight-bold">₦{{ number_format($total, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $growth >= 0 ? 'success' : 'danger' }}">
                                                {{ $growth >= 0 ? '+' : '' }}{{ round($growth, 1) }}%
                                            </span>
                                        </td>
                                        <td>{{ rand(1, 10) }}</td> <!-- Placeholder - would be calculated from actual data -->
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Commission Trends Chart
const trendsCtx = document.getElementById('commissionTrendsChart').getContext('2d');
const commissionTrendsChart = new Chart(trendsCtx, {
    type: 'line',
    data: {
        labels: @json(collect($commissionTrends)->pluck('month')),
        datasets: [{
            label: 'Your Commission (₦)',
            data: @json(collect($commissionTrends)->pluck('amount')),
            borderColor: 'rgb(28, 200, 138)',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        return '₦' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ₦' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});

// Commission Distribution Pie Chart
const distributionCtx = document.getElementById('commissionDistributionChart').getContext('2d');
const commissionDistributionChart = new Chart(distributionCtx, {
    type: 'doughnut',
    data: {
        labels: ['Your Commission', 'Network Commission'],
        datasets: [{
            data: [@json($commissionByTier['super_marketer']), @json($commissionByTier['marketer'])],
            backgroundColor: ['#1cc88a', '#4e73df'],
            hoverBackgroundColor: ['#17a673', '#2e59d9'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': ₦' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Date range helper functions
function setDateRange(period) {
    const endDate = new Date();
    let startDate = new Date();
    
    switch(period) {
        case '7days':
            startDate.setDate(endDate.getDate() - 7);
            break;
        case '30days':
            startDate.setDate(endDate.getDate() - 30);
            break;
        case '90days':
            startDate.setDate(endDate.getDate() - 90);
            break;
    }
    
    document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
    document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
}

// Export functions
function exportChart(format) {
    // Implementation would depend on your preferred export library
    alert('Export functionality would be implemented here for ' + format + ' format');
}

function exportTable() {
    // Simple CSV export
    const table = document.getElementById('analyticsTable');
    let csv = [];
    
    // Get headers
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent);
    csv.push(headers.join(','));
    
    // Get data rows
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('td')).map(td => {
            // Clean up the cell content (remove currency symbols, etc.)
            return td.textContent.replace(/[₦,]/g, '').trim();
        });
        csv.push(cells.join(','));
    });
    
    // Download CSV
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'commission-analytics-' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
@endpush

@push('head')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.chart-area {
    position: relative;
    height: 400px;
}

.chart-pie {
    position: relative;
    height: 300px;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    color: #6c757d;
}

.progress {
    background-color: #f8f9fc;
}

.btn-group .btn {
    font-size: 0.75rem;
}
</style>
@endpush
@endsection
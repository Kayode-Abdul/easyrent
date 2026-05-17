@extends('layout')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="card-title">Performance & Analytics</h4>
                            <p class="card-category">Deep insights into revenue and occupancy rates</p>
                        </div>
                        <a href="{{ route('property-manager.dashboard') }}" class="btn btn-outline-secondary btn-round btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #fff9f6;">
                <div class="card-body">
                    <form method="GET" class="row align-items-end">
                        <div class="col-md-4 col-sm-6 mb-2">
                            <div class="form-group mb-0">
                                <label for="start_date" class="text-secondary font-weight-bold" style="font-size: 0.85rem;">Start Date</label>
                                <input type="date" class="form-control" name="start_date" id="start_date" value="{{ $startDate }}" style="border-color: #ef8157; background-color: #fff;">
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6 mb-2">
                            <div class="form-group mb-0">
                                <label for="end_date" class="text-secondary font-weight-bold" style="font-size: 0.85rem;">End Date</label>
                                <input type="date" class="form-control" name="end_date" id="end_date" value="{{ $endDate }}" style="border-color: #ef8157; background-color: #fff;">
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-12 mb-2">
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary btn-round btn-block mr-2" style="background-color: #ef8157; border-color: #ef8157;">
                                    <i class="fa fa-filter"></i> Apply Range
                                </button>
                                <a href="{{ route('property-manager.analytics') }}" class="btn btn-outline-secondary btn-round btn-block mt-0 d-flex align-items-center justify-content-center">
                                    <i class="fa fa-refresh"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="card card-stats border-0 shadow-sm" style="border-radius: 12px; border-left: 5px solid #28a745 !important;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center text-success" style="font-size: 3rem;">
                                <i class="nc-icon nc-money-coins"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">Period Revenue</p>
                                <p class="card-title text-success font-weight-bold">{{ format_money($analytics['total_revenue']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-sm-12">
            <div class="card card-stats border-0 shadow-sm" style="border-radius: 12px; border-left: 5px solid #007bff !important;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center text-primary" style="font-size: 3rem;">
                                <i class="nc-icon nc-bank"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">Managed Properties</p>
                                <p class="card-title text-primary font-weight-bold">{{ $analytics['total_properties'] }} Properties</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Occupancy Analytics & Revenue Trend charts -->
    <div class="row">
        <!-- Revenue Trend -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title text-dark font-weight-bold mb-0">Revenue History & Trends</h5>
                    <p class="card-category text-muted">Monthly earnings overview</p>
                </div>
                <div class="card-body">
                    <!-- Interactive Chart Container -->
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Occupancy Rates by Property -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title text-dark font-weight-bold mb-0">Property Occupancy Rates</h5>
                    <p class="card-category text-muted">Occupancy density distribution</p>
                </div>
                <div class="card-body">
                    @if(empty($analytics['occupancy_data']))
                        <div class="text-center py-5">
                            <i class="nc-icon nc-chart-bar-32 text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2">No occupancy details available</p>
                        </div>
                    @else
                        @foreach($analytics['occupancy_data'] as $occupancy)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="font-weight-bold text-dark text-truncate" style="max-width: 75%; font-size: 0.9rem;">
                                        {{ $occupancy['property'] }}
                                    </span>
                                    <span class="badge badge-pill badge-primary font-weight-bold">
                                        {{ $occupancy['occupancy_rate'] }}%
                                    </span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-{{ $occupancy['occupancy_rate'] >= 80 ? 'success' : ($occupancy['occupancy_rate'] >= 50 ? 'warning' : 'danger') }}"
                                         role="progressbar"
                                         style="width: {{ $occupancy['occupancy_rate'] }}%; border-radius: 6px;"
                                         aria-valuenow="{{ $occupancy['occupancy_rate'] }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js CDN for beautiful interactive charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('revenueTrendChart').getContext('2d');
        
        // Prepare chart data from Laravel PHP variables
        const months = {!! json_encode(array_column($analytics['revenue_trend'], 'month')) !!};
        const revenues = {!! json_encode(array_column($analytics['revenue_trend'], 'revenue')) !!};

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Earnings History',
                    data: revenues,
                    borderColor: '#ef8157',
                    backgroundColor: 'rgba(239, 129, 87, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ef8157',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₦' + Number(value).toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@endsection

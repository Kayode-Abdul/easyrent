<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Analytics</title>
    @include('header')
</head>
<body>
<div class="content">
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="statistics statistics-horizontal">
                        <div class="info info-horizontal">
                            <div class="row">
                                <div class="col-5">
                                    <div class="icon icon-primary icon-circle">
                                        <i class="nc-icon nc-money-coins"></i>
                                    </div>
                                </div>
                                <div class="col-7 text-right">
                                    <h3 class="info-title">₦{{ number_format($totalRevenue, 2) }}</h3>
                                    <h6 class="stats-title">Total Revenue</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="statistics statistics-horizontal">
                        <div class="info info-horizontal">
                            <div class="row">
                                <div class="col-5">
                                    <div class="icon icon-success icon-circle">
                                        <i class="nc-icon nc-single-copy-04"></i>
                                    </div>
                                </div>
                                <div class="col-7 text-right">
                                    <h3 class="info-title">{{ $totalTransactions }}</h3>
                                    <h6 class="stats-title">Transactions</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="statistics statistics-horizontal">
                        <div class="info info-horizontal">
                            <div class="row">
                                <div class="col-5">
                                    <div class="icon icon-info icon-circle">
                                        <i class="nc-icon nc-chart-bar-32"></i>
                                    </div>
                                </div>
                                <div class="col-7 text-right">
                                    <h3 class="info-title">₦{{ number_format($monthlyAverage, 2) }}</h3>
                                    <h6 class="stats-title">Monthly Average</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="statistics statistics-horizontal">
                        <div class="info info-horizontal">
                            <div class="row">
                                <div class="col-5">
                                    <div class="icon icon-warning icon-circle">
                                        <i class="nc-icon nc-time-alarm"></i>
                                    </div>
                                </div>
                                <div class="col-7 text-right">
                                    <h3 class="info-title">{{ $pendingPayments }}</h3>
                                    <h6 class="stats-title">Pending Payments</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Revenue Overview</h4>
                    <p class="card-category">Monthly revenue for the past year</p>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Payment Methods</h4>
                    <p class="card-category">Distribution by payment type</p>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodsChart" width="200" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueData['labels']) !!},
            datasets: [{
                label: 'Monthly Revenue',
                data: {!! json_encode($revenueData['values']) !!},
                borderColor: '#51cbce',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });

    // Payment Methods Chart
    const methodsCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    new Chart(methodsCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($paymentMethods['labels']) !!},
            datasets: [{
                data: {!! json_encode($paymentMethods['values']) !!},
                backgroundColor: ['#51cbce', '#fbc658', '#ef8157', '#6bd098'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>

@include('footer')
</body>
</html>

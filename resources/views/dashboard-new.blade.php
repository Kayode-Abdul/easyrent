<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Dashboard - EasyRent</title>
</head>
<body>
    @include('header')
    
    <div class="content">
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Welcome back, {{ $user->first_name }} {{ $user->last_name }}!
                        </h4>
                        <p class="card-category">
                            {{ $currentMonth }} Dashboard Overview
                            @if($user->admin)
                                <span class="badge badge-danger">Admin</span>
                            @elseif($user->role == 1)
                                <span class="badge badge-primary">Landlord</span>
                            @elseif($user->role == 2)
                                <span class="badge badge-success">Tenant</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            @if($user->admin)
                <!-- Admin Statistics -->
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-md-4">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="nc-icon nc-single-02 text-primary"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-md-8">
                                    <div class="numbers">
                                        <p class="card-category">Total Users</p>
                                        <p class="card-title">{{ number_format($totalUsers) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <hr>
                            <div class="stats">
                                <i class="fa fa-users"></i> System Users
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-md-4">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="nc-icon nc-bank text-success"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-md-8">
                                    <div class="numbers">
                                        <p class="card-category">Total Properties</p>
                                        <p class="card-title">{{ number_format($totalProperties) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <hr>
                            <div class="stats">
                                <i class="fa fa-building"></i> Properties Listed
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-md-4">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="nc-icon nc-tile-56 text-warning"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-md-8">
                                    <div class="numbers">
                                        <p class="card-category">Occupancy Rate</p>
                                        <p class="card-title">{{ $occupancyRate }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <hr>
                            <div class="stats">
                                <i class="fa fa-home"></i> {{ $occupiedApartments }}/{{ $totalApartments }} Occupied
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-md-4">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="nc-icon nc-money-coins text-info"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-md-8">
                                    <div class="numbers">
                                        <p class="card-category">Monthly Revenue</p>
                                        <p class="card-title">₦{{ number_format($monthlyRevenue, 0) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <hr>
                            <div class="stats">
                                <i class="fa fa-calendar"></i> This Month
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($user->role == 1)
                <!-- Landlord Statistics -->
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-md-4">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="nc-icon nc-bank text-success"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-md-8">
                                    <div class="numbers">
                                        <p class="card-category">My Properties</p>
                                        <p class="card-title">{{ number_format($totalProperties) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <hr>
                            <div class="stats">
                                <i class="fa fa-building"></i> Total Properties
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-md-4">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="nc-icon nc-tile-56 text-primary"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-md-8">
                                    <div class="numbers">
                                        <p class="card-category">Total Units</p>
                                        <p class="card-title">{{ number_format($totalApartments) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <hr>
                            <div class="stats">
                                <i class="fa fa-home"></i> {{ $occupiedApartments }} Occupied, {{ $vacantApartments }} Vacant
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-md-4">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="nc-icon nc-chart-pie-36 text-warning"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-md-8">
                                    <div class="numbers">
                                        <p class="card-category">Occupancy Rate</p>
                                        <p class="card-title">{{ $occupancyRate }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <hr>
                            <div class="stats">
                                <i class="fa fa-chart-line"></i> Property Performance
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-md-4">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="nc-icon nc-money-coins text-info"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-md-8">
                                    <div class="numbers">
                                        <p class="card-category">Monthly Revenue</p>
                                        <p class="card-title">₦{{ number_format($monthlyRevenue, 0) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <hr>
                            <div class="stats">
                                <i class="fa fa-calendar"></i> This Month
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($user->role == 2)
                <!-- Tenant Statistics -->
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-md-4">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="nc-icon nc-home-4 text-success"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-md-8">
                                    <div class="numbers">
                                        <p class="card-category">My Rentals</p>
                                        <p class="card-title">{{ number_format($totalRentals) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <hr>
                            <div class="stats">
                                <i class="fa fa-home"></i> Active Rentals
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-md-4">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="nc-icon nc-money-coins text-primary"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-md-8">
                                    <div class="numbers">
                                        <p class="card-category">Total Paid</p>
                                        <p class="card-title">₦{{ number_format($totalPaid, 0) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <hr>
                            <div class="stats">
                                <i class="fa fa-check"></i> All Time Payments
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-md-4">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="nc-icon nc-bell-55 text-warning"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-md-8">
                                    <div class="numbers">
                                        <p class="card-category">Pending Payments</p>
                                        <p class="card-title">{{ number_format($pendingPayments) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <hr>
                            <div class="stats">
                                <i class="fa fa-clock-o"></i> Due Soon
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-5 col-md-4">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="nc-icon nc-email-85 text-info"></i>
                                    </div>
                                </div>
                                <div class="col-7 col-md-8">
                                    <div class="numbers">
                                        <p class="card-category">Messages</p>
                                        <p class="card-title">{{ number_format($unreadMessages) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <hr>
                            <div class="stats">
                                <i class="fa fa-envelope"></i> Unread Messages
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Charts and Analytics -->
        <div class="row mt-4">
            @if($user->admin || $user->role == 1)
                <div class="col-md-8">
                    <div class="card card-chart">
                        <div class="card-header">
                            <h5 class="card-title">
                                @if($user->admin)
                                    System Revenue Trend
                                @else
                                    My Revenue Trend
                                @endif
                            </h5>
                            <p class="card-category">Last 6 months performance</p>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart" width="400" height="100"></canvas>
                        </div>
                        <div class="card-footer">
                            <div class="chart-legend">
                                <i class="fa fa-circle text-info"></i> Monthly Revenue
                            </div>
                            <hr>
                            <div class="card-stats">
                                <i class="fa fa-check"></i> Data updated in real-time
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Quick Actions</h5>
                            <p class="card-category">Manage your properties</p>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="/listing" class="btn btn-primary btn-block">
                                    <i class="nc-icon nc-simple-add"></i> Add New Property
                                </a>
                                <a href="/dashboard/myproperty" class="btn btn-info btn-block">
                                    <i class="nc-icon nc-tile-56"></i> View My Properties
                                </a>
                                <a href="{{ route('payments.index') }}" class="btn btn-success btn-block">
                                    <i class="nc-icon nc-money-coins"></i> View Payments
                                </a>
                                <a href="/dashboard/messages/inbox" class="btn btn-warning btn-block">
                                    <i class="nc-icon nc-email-85"></i> Check Messages
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($user->role == 2)
                <div class="col-md-8">
                    <div class="card card-chart">
                        <div class="card-header">
                            <h5 class="card-title">My Payment History</h5>
                            <p class="card-category">Last 6 months payments</p>
                        </div>
                        <div class="card-body">
                            <canvas id="paymentChart" width="400" height="100"></canvas>
                        </div>
                        <div class="card-footer">
                            <div class="chart-legend">
                                <i class="fa fa-circle text-success"></i> Payments Made
                            </div>
                            <hr>
                            <div class="card-stats">
                                <i class="fa fa-check"></i> Payment history
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Quick Actions</h5>
                            <p class="card-category">Tenant dashboard</p>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if($nextPaymentDue)
                                    <div class="alert alert-warning">
                                        <strong>Next Payment Due:</strong><br>
                                        ₦{{ number_format($nextPaymentDue->amount) }}<br>
                                        <small>Due: {{ $nextPaymentDue->due_date->format('M d, Y') }}</small>
                                    </div>
                                @endif
                                <a href="{{ route('payments.index') }}" class="btn btn-primary btn-block">
                                    <i class="nc-icon nc-money-coins"></i> Make Payment
                                </a>
                                <a href="{{ route('payments.index') }}" class="btn btn-info btn-block">
                                    <i class="nc-icon nc-paper"></i> Payment History
                                </a>
                                <a href="/dashboard/messages/inbox" class="btn btn-success btn-block">
                                    <i class="nc-icon nc-email-85"></i> Messages
                                </a>
                                <a href="/dashboard/user" class="btn btn-warning btn-block">
                                    <i class="nc-icon nc-single-02"></i> My Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Recent Activities -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Recent Activities</h5>
                        <p class="card-category">Latest updates and notifications</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="text-primary">
                                    <tr>
                                        <th>Activity</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($user->admin && isset($recentUsers))
                                        @foreach($recentUsers as $recentUser)
                                        <tr>
                                            <td>New user registered: {{ $recentUser->first_name }} {{ $recentUser->last_name }}</td>
                                            <td>{{ $recentUser->created_at->diffForHumans() }}</td>
                                            <td><span class="badge badge-success">New</span></td>
                                            <td>
                                                <a href="/dashboard/users" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @elseif($user->role == 1 && isset($recentBookings))
                                        @foreach($recentBookings as $booking)
                                        <tr>
                                            <td>New booking for apartment {{ $booking->apartment_id }}</td>
                                            <td>{{ $booking->created_at->diffForHumans() }}</td>
                                            <td><span class="badge badge-info">Booking</span></td>
                                            <td>
                                                <a href="/dashboard/bookings" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @elseif($user->role == 2 && isset($recentMessages))
                                        @foreach($recentMessages as $message)
                                        <tr>
                                            <td>{{ $message->is_read ? 'Read' : 'New' }} message from {{ $message->sender->first_name ?? 'Unknown' }}</td>
                                            <td>{{ $message->created_at->diffForHumans() }}</td>
                                            <td>
                                                <span class="badge badge-{{ $message->is_read ? 'success' : 'warning' }}">
                                                    {{ $message->is_read ? 'Read' : 'Unread' }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/dashboard/messages/{{ $message->id }}" class="btn btn-sm btn-primary">View</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('footer')

    <script>
        // Revenue/Payment Chart
        @if($user->admin || $user->role == 1)
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(collect($revenueTrend ?? [])->pluck('month')) !!},
                    datasets: [{
                        label: 'Revenue (₦)',
                        data: {!! json_encode(collect($revenueTrend ?? [])->pluck('revenue')) !!},
                        borderColor: '#51cbce',
                        backgroundColor: 'rgba(81, 203, 206, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₦' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        @elseif($user->role == 2)
            const paymentCtx = document.getElementById('paymentChart').getContext('2d');
            new Chart(paymentCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(collect($paymentHistory ?? [])->pluck('month')) !!},
                    datasets: [{
                        label: 'Payments (₦)',
                        data: {!! json_encode(collect($paymentHistory ?? [])->pluck('amount')) !!},
                        backgroundColor: 'rgba(81, 203, 206, 0.8)',
                        borderColor: '#51cbce',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₦' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        @endif
    </script>
</body>
</html>

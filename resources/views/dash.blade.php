<!--
=========================================================
* EasyRent Dashboard - Enhanced Version
=========================================================
-->

<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@include('header')
<!-- end of header -->

<div class="content">
    <!-- Dashboard Mode Toggles -->
    <div class="container-fluid mb-3">
        <div class="d-flex justify-content-end align-items-center">
            
            @if(auth()->user()->admin == 1 || auth()->user()->role == 7)
                <!-- Admin Toggle (only for admins) -->
                <div>
                    <span class="switch-label-left">Personal</span>
                    <label class="switch mb-0">
                        <input type="checkbox" id="adminDashboardSwitch">
                        <span class="slider"></span>
                    </label>
                    <span class="switch-label">Admin Dashboard</span>
                </div>
            @endif
            
        </div>
    </div>

    <!-- Role-based greeting -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <h4>Welcome back, {{ auth()->user()->first_name }}!</h4>
                <p>{{ $greeting ?? 'Here\'s your dashboard overview for today.' }}</p>
            </div>
        </div>
    </div>

    <!-- Main Stats Cards -->
    <div class="row">
        @if(auth()->user()->user_type === 'admin')
            <!-- Admin Dashboard Stats -->
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5 col-md-4">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-single-02 text-info"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Total Users</p>
                                    <p class="card-title">{{ $stats['total_users'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-users"></i>
                            System wide
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
                                    <i class="nc-icon nc-istanbul text-success"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Total Properties</p>
                                    <p class="card-title">{{ $stats['total_properties'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-building"></i>
                            All properties
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
                                    <i class="nc-icon nc-money-coins text-warning"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Total Revenue</p>
                                    <p class="card-title">₦{{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-calendar"></i>
                            All time
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
                                    <i class="nc-icon nc-refresh-69 text-danger"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Pending Payments</p>
                                    <p class="card-title">{{ $stats['pending_payments'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-clock-o"></i>
                            Needs attention
                        </div>
                    </div>
                </div>
            </div>

        @elseif(auth()->user()->user_type === 'landlord')
            <!-- Landlord Dashboard Stats -->
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5 col-md-4">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-istanbul text-success"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">My Properties</p>
                                    <p class="card-title">{{ $stats['my_properties'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-building"></i>
                            Total properties
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
                                    <i class="nc-icon nc-check-2 text-info"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Occupied Units</p>
                                    <p class="card-title">{{ $stats['occupied_apartments'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-home"></i>
                            Currently rented
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
                                    <i class="nc-icon nc-money-coins text-warning"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Monthly Revenue</p>
                                    <p class="card-title">₦{{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-calendar"></i>
                            This month
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
                                    <i class="nc-icon nc-bell-55 text-danger"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">New Bookings</p>
                                    <p class="card-title">{{ $stats['new_bookings'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-refresh"></i>
                            This week
                        </div>
                    </div>
                </div>
            </div>

        @else
            <!-- Tenant Dashboard Stats -->
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5 col-md-4">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-home-2 text-info"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Current Rentals</p>
                                    <p class="card-title">{{ $stats['my_rentals'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-home"></i>
                            Active rentals
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
                                    <i class="nc-icon nc-money-coins text-success"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Paid This Month</p>
                                    <p class="card-title">₦{{ number_format($stats['payments_this_month'] ?? 0, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-calendar"></i>
                            Current month
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
                                    <i class="nc-icon nc-time-alarm text-warning"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Pending Payments</p>
                                    <p class="card-title">{{ $stats['my_pending_payments'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-exclamation"></i>
                            Requires payment
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
                                    <i class="nc-icon nc-email-85 text-primary"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">New Messages</p>
                                    <p class="card-title">{{ $stats['unread_messages'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-envelope"></i>
                            Unread messages
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- Charts Section -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        @if(auth()->user()->user_type === 'admin')
                            System Revenue Overview
                        @elseif(auth()->user()->user_type === 'landlord')
                            My Properties Revenue
                        @else
                            My Payment History
                        @endif
                    </h5>
                    <p class="card-category">Monthly performance for the past 6 months</p>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-history"></i> Updated in real-time
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        @if(auth()->user()->user_type === 'admin')
                            User Distribution
                        @elseif(auth()->user()->user_type === 'landlord')
                            Property Status
                        @else
                            Payment Status
                        @endif
                    </h5>
                    <p class="card-category">Current distribution</p>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart" width="200" height="200"></canvas>
                </div>
                <div class="card-footer">
                    <div class="legend">
                        @if(auth()->user()->user_type === 'admin')
                            <i class="fa fa-circle text-primary"></i> Landlords
                            <i class="fa fa-circle text-warning"></i> Tenants
                            <i class="fa fa-circle text-success"></i> Property Managers
                            <i class="fa fa-circle text-danger"></i> Admin
                        @elseif(auth()->user()->user_type === 'landlord')
                            <i class="fa fa-circle text-success"></i> Occupied
                            <i class="fa fa-circle text-warning"></i> Vacant
                            <i class="fa fa-circle text-danger"></i> Maintenance
                        @else
                            <i class="fa fa-circle text-success"></i> Completed
                            <i class="fa fa-circle text-warning"></i> Pending
                            <i class="fa fa-circle text-danger"></i> Failed
                        @endif
                    </div>
                    <hr>
                    <div class="stats">
                        <i class="fa fa-calendar"></i> Real-time data
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities and Quick Actions -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Activities</h5>
                    <p class="card-category">Latest system activities</p>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if(isset($recentActivities) && count($recentActivities) > 0)
                        <div class="timeline">
                            @foreach($recentActivities as $activity)
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">{{ $activity['title'] }}</h6>
                                        <p class="timeline-description">{{ $activity['description'] }}</p>
                                        <small class="text-muted">{{ $activity['time'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="nc-icon nc-time-alarm" style="font-size: 3em;"></i>
                            <p>No recent activities to display</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                    <p class="card-category">Common tasks and shortcuts</p>
                </div>
                <div class="card-body">
                    @if(auth()->user()->user_type === 'admin')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="/dashboard/users" class="btn btn-info btn-block">
                                    <i class="nc-icon nc-single-02"></i> Manage Users
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/dashboard/properties" class="btn btn-success btn-block">
                                    <i class="nc-icon nc-istanbul"></i> All Properties
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/payments/analytics" class="btn btn-warning btn-block">
                                    <i class="nc-icon nc-chart-bar-32"></i> Analytics
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/dashboard/messages/inbox" class="btn btn-primary btn-block">
                                    <i class="nc-icon nc-email-85"></i> Messages
                                </a>
                            </div>
                        </div>
                    @elseif(auth()->user()->user_type === 'landlord')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="/listing" class="btn btn-success btn-block">
                                    <i class="nc-icon nc-simple-add"></i> Add Property
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/dashboard/myproperty" class="btn btn-info btn-block">
                                    <i class="nc-icon nc-istanbul"></i> My Properties
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/dashboard/payments" class="btn btn-warning btn-block">
                                    <i class="nc-icon nc-money-coins"></i> View Payments
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/dashboard/messages/inbox" class="btn btn-primary btn-block">
                                    <i class="nc-icon nc-email-85"></i> Messages
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <a href="/properties" class="btn btn-success btn-block">
                                    <i class="nc-icon nc-zoom-split"></i> Browse Properties
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/dashboard/bookings" class="btn btn-info btn-block">
                                    <i class="nc-icon nc-bookmark-2"></i> My Bookings
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/dashboard/payments" class="btn btn-warning btn-block">
                                    <i class="nc-icon nc-money-coins"></i> Payment History
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <a href="/dashboard/messages/inbox" class="btn btn-primary btn-block">
                                    <i class="nc-icon nc-email-85"></i> Messages
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['revenueLabels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']) !!},
            datasets: [{
                label: 'Revenue (₦)',
                data: {!! json_encode($chartData['revenueData'] ?? [0, 0, 0, 0, 0, 0]) !!},
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
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + new Intl.NumberFormat().format(value);
                        }
                    }
                }
            }
        }
    });

    // Distribution Chart
    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartData['distributionLabels'] ?? ['Category 1', 'Category 2']) !!},
            datasets: [{
                data: {!! json_encode($chartData['distributionData'] ?? [50, 50]) !!},
                backgroundColor: [
                    '#51cbce',
                    '#fbc658',
                    '#ef8157',
                    '#6bd098',
                    '#e14eca'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
    padding-bottom: 10px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    background-color: #51cbce;
    border-radius: 50%;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -31px;
    top: 15px;
    bottom: -15px;
    width: 2px;
    background-color: #eee;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-title {
    margin-bottom: 5px;
    color: #333;
}

.timeline-description {
    margin-bottom: 5px;
    color: #666;
    font-size: 0.9em;
}

/* Modern switch toggle */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}
.switch input {display:none;}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 34px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #007bff;
}
input:checked + .slider:before {
  transform: translateX(26px);
}
.switch-label {
  margin-left: 12px;
  font-weight: bold;
  vertical-align: middle;
}
.switch-label-left {
  margin-right: 12px;
  font-weight: bold;
  vertical-align: middle;
}

/* Spacing for multiple toggles */
.mr-4 {
  margin-right: 1.5rem;
}

/* Toggle container styling */
.d-flex .switch-label-left,
.d-flex .switch-label {
  font-size: 14px;
  color: #495057;
}
</style>

<script>
$(function() {
    // Debug: Check if elements exist
    console.log('jQuery loaded:', typeof $ !== 'undefined');
    console.log('Admin switch element found:', $('#adminDashboardSwitch').length);
    console.log('PM switch element found:', $('#propertyManagerSwitch').length);

    // Admin Dashboard Toggle (only for admins)
    $('#adminDashboardSwitch').on('change', function() {
        var mode = this.checked ? 'admin' : 'personal';
        var $switch = $(this);
        $switch.prop('disabled', true); // Prevent double clicks
        
        console.log('Admin toggle clicked, switching to mode:', mode);
        
        $.ajax({
            url: '/dashboard/switch-admin-mode',
            method: 'POST',
            data: { mode: mode },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                console.log('Success response:', res);
                if(res.success) {
                    if (mode === 'admin') {
                        window.location.href = '/dashboard';
                    } else {
                        location.reload();
                    }
                } else {
                    $switch.prop('disabled', false);
                    alert('Failed to switch admin mode: ' + (res.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.log('Error response:', xhr.responseText);
                console.log('Status:', status, 'Error:', error);
                $switch.prop('disabled', false);
                alert('Error switching admin mode. Please check console for details.');
            }
        });
    });
});
</script>

@include('footer')
<!-- Footer area end -->
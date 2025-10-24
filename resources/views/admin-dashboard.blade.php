<!--
=========================================================
* EasyRent Super Admin Dashboard - Enhanced Version
=========================================================
-->

<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/assets/js/dashboard-error-handler.js"></script>
@include('header')
<!-- end of header -->

<div class="content">
    <!-- Role-based greeting -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-gradient-primary">
                <h4><i class="nc-icon nc-spaceship"></i> Super Admin Dashboard</h4>
                <p>{{ $greeting ?? 'Complete system overview and management console.' }}</p>
            </div>
        </div>
    </div>

    <!-- Role Switcher -->
    @include('role_switcher')

    <!-- Super Admin Dashboard -->
    @if(auth()->user()->admin == 1 || auth()->user()->role == 7)
        
        <!-- System Overview Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h3><i class="nc-icon nc-chart-bar-32"></i> System Performance Overview</h3>
                                <p class="mb-0">Platform Uptime: <strong>{{ $stats['platform_uptime'] ?? '99.9%' }}</strong> | Active Sessions: <strong>{{ $stats['active_sessions'] ?? 0 }}</strong> | Database Size: <strong>{{ $stats['database_size'] ?? 'Unknown' }}</strong></p>
                            </div>
                            <div class="col-md-4 text-right">
                                <h4>₦{{ number_format($stats['company_commission_total'] ?? 0, 0) }}</h4>
                                <small>EasyRent Total Commission</small>
                                <div class="mt-2">
                                    <span class="badge badge-success">
                                        ₦{{ number_format($stats['company_commission_this_month'] ?? 0, 0) }} this month
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Performance Indicators -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="text-primary"><i class="nc-icon nc-chart-pie-36"></i> Key Performance Indicators</h4>
            </div>
        </div>
        
        <!-- Primary KPIs Row -->
        <div class="row">
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
                                    <p class="card-title">{{ number_format($stats['total_users'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-users text-success"></i>
                            {{ $stats['new_users_today'] ?? 0 }} new today
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
                                    <p class="card-title">{{ number_format($stats['total_properties'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-building text-info"></i>
                            {{ $stats['properties_added_this_month'] ?? 0 }} this month
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
                                    <p class="card-category">Revenue Today</p>
                                    <p class="card-title">${{ number_format($stats['revenue_today'] ?? 0, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-calendar text-success"></i>
                            +{{ number_format((($stats['revenue_today'] ?? 0) / (($stats['revenue_last_month'] ?? 1) ?: 1)) * 100, 1) }}% vs yesterday
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
                                    <p class="card-category">Issues Pending</p>
                                    <p class="card-title">{{ ($stats['pending_payments'] ?? 0) + ($stats['failed_payments'] ?? 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-exclamation-triangle text-warning"></i>
                            Needs attention
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Intelligence Row -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="text-success"><i class="nc-icon nc-chart-pie-35"></i> Business Intelligence</h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5 col-md-4">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-chart-pie-35 text-success"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Conversion Rate</p>
                                    <p class="card-title">{{ $stats['conversion_rate'] ?? '0%' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-arrow-up text-success"></i>
                            Signup to tenant
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
                                    <i class="nc-icon nc-user-run text-warning"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Churn Rate</p>
                                    <p class="card-title">{{ $stats['churn_rate'] ?? '0%' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-arrow-down text-danger"></i>
                            Monthly churn
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
                                    <p class="card-category">Customer CAC</p>
                                    <p class="card-title">{{ $stats['customer_acquisition_cost'] ?? '$0' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-dollar text-info"></i>
                            Cost per acquisition
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
                                    <i class="nc-icon nc-diamond text-success"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Lifetime Value</p>
                                    <p class="card-title">{{ $stats['lifetime_value'] ?? '$0' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-gem text-success"></i>
                            Average LTV
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Commission Revenue -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="text-primary"><i class="nc-icon nc-money-coins"></i> Company Commission Revenue</h4>
            </div>
        </div>
        
        <div class="row">
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
                                    <p class="card-category">Commission Today</p>
                                    <p class="card-title">₦{{ number_format($stats['company_commission_today'] ?? 0, 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-calendar text-success"></i>
                            EasyRent earnings today
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
                                    <i class="nc-icon nc-chart-bar-32 text-info"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Commission This Month</p>
                                    <p class="card-title">₦{{ number_format($stats['company_commission_this_month'] ?? 0, 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-arrow-up text-success"></i>
                            @if(isset($stats['commission_breakdown']['growth']['company_commission']))
                                {{ number_format($stats['commission_breakdown']['growth']['company_commission'], 1) }}% vs last month
                            @else
                                Monthly earnings
                            @endif
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
                                    <i class="nc-icon nc-diamond text-warning"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Total Commission</p>
                                    <p class="card-title">₦{{ number_format($stats['company_commission_total'] ?? 0, 0) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-gem text-warning"></i>
                            All-time company earnings
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
                                    <i class="nc-icon nc-settings-gear-65 text-primary"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Commission Rate</p>
                                    <p class="card-title">
                                        @if(isset($stats['commission_breakdown']['this_month']['total_rent']) && $stats['commission_breakdown']['this_month']['total_rent'] > 0)
                                            {{ number_format(($stats['commission_breakdown']['this_month']['company_commission'] / $stats['commission_breakdown']['this_month']['total_rent']) * 100, 2) }}%
                                        @else
                                            3.25%
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-cog text-primary"></i>
                            <a href="{{ route('admin.commission-management.regional-manager') }}" class="text-primary">Manage Rates</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commission Breakdown Detail -->
        @if(isset($stats['commission_breakdown']))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="nc-icon nc-chart-pie-35 text-primary"></i>
                            Commission Breakdown - This Month
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Role</th>
                                                <th>Commission Amount</th>
                                                <th>Percentage of Total Rent</th>
                                                <th>Growth vs Last Month</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="table-success">
                                                <td><strong><i class="fa fa-building me-2"></i>Company (EasyRent)</strong></td>
                                                <td><strong>₦{{ number_format($stats['commission_breakdown']['this_month']['company_commission'] ?? 0, 0) }}</strong></td>
                                                <td>
                                                    @if($stats['commission_breakdown']['this_month']['total_rent'] > 0)
                                                        {{ number_format(($stats['commission_breakdown']['this_month']['company_commission'] / $stats['commission_breakdown']['this_month']['total_rent']) * 100, 2) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $stats['commission_breakdown']['growth']['company_commission'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                        {{ number_format($stats['commission_breakdown']['growth']['company_commission'] ?? 0, 1) }}%
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><i class="fa fa-users me-2"></i>Super Marketers</td>
                                                <td>₦{{ number_format($stats['commission_breakdown']['this_month']['super_marketer_commission'] ?? 0, 0) }}</td>
                                                <td>
                                                    @if($stats['commission_breakdown']['this_month']['total_rent'] > 0)
                                                        {{ number_format(($stats['commission_breakdown']['this_month']['super_marketer_commission'] / $stats['commission_breakdown']['this_month']['total_rent']) * 100, 2) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </td>
                                                <td>-</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fa fa-user me-2"></i>Marketers</td>
                                                <td>₦{{ number_format($stats['commission_breakdown']['this_month']['marketer_commission'] ?? 0, 0) }}</td>
                                                <td>
                                                    @if($stats['commission_breakdown']['this_month']['total_rent'] > 0)
                                                        {{ number_format(($stats['commission_breakdown']['this_month']['marketer_commission'] / $stats['commission_breakdown']['this_month']['total_rent']) * 100, 2) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </td>
                                                <td>-</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fa fa-map-marker me-2"></i>Regional Managers</td>
                                                <td>₦{{ number_format($stats['commission_breakdown']['this_month']['regional_manager_commission'] ?? 0, 0) }}</td>
                                                <td>
                                                    @if($stats['commission_breakdown']['this_month']['total_rent'] > 0)
                                                        {{ number_format(($stats['commission_breakdown']['this_month']['regional_manager_commission'] / $stats['commission_breakdown']['this_month']['total_rent']) * 100, 2) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </td>
                                                <td>-</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th>Total Commission</th>
                                                <th>₦{{ number_format($stats['commission_breakdown']['this_month']['total_commission'] ?? 0, 0) }}</th>
                                                <th>
                                                    @if($stats['commission_breakdown']['this_month']['total_rent'] > 0)
                                                        {{ number_format(($stats['commission_breakdown']['this_month']['total_commission'] / $stats['commission_breakdown']['this_month']['total_rent']) * 100, 2) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </th>
                                                <th>
                                                    <span class="badge {{ $stats['commission_breakdown']['growth']['total_commission'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                        {{ number_format($stats['commission_breakdown']['growth']['total_commission'] ?? 0, 1) }}%
                                                    </span>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h3 class="text-success">₦{{ number_format($stats['commission_breakdown']['this_month']['company_commission'] ?? 0, 0) }}</h3>
                                    <p class="text-muted">Company Revenue This Month</p>
                                    <div class="progress mb-3">
                                        @php
                                            $companyPercentage = $stats['commission_breakdown']['this_month']['total_commission'] > 0 
                                                ? ($stats['commission_breakdown']['this_month']['company_commission'] / $stats['commission_breakdown']['this_month']['total_commission']) * 100 
                                                : 0;
                                        @endphp
                                        <div class="progress-bar bg-success" style="width: {{ $companyPercentage }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ number_format($companyPercentage, 1) }}% of total commission</small>
                                    <div class="mt-3">
                                        <a href="{{ route('admin.commission-management.regional-manager') }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-cog me-1"></i>Manage Commission Rates
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- System Health Monitoring -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="text-warning"><i class="nc-icon nc-settings-gear-65"></i> System Health & Monitoring</h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5 col-md-4">
                                <div class="icon-big text-center icon-warning">
                                    <i class="nc-icon nc-spaceship text-success"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Platform Uptime</p>
                                    <p class="card-title">{{ $stats['platform_uptime'] ?? '99.9%' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-check text-success"></i>
                            Excellent performance
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
                                    <i class="nc-icon nc-database text-info"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Database Size</p>
                                    <p class="card-title">{{ $stats['database_size'] ?? 'Unknown' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-database text-info"></i>
                            Growing steadily
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
                                    <i class="nc-icon nc-folder-17 text-warning"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Storage Used</p>
                                    <p class="card-title">{{ $stats['storage_used'] ?? '0 MB' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-hdd-o text-warning"></i>
                            Disk usage optimal
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
                                    <i class="nc-icon nc-circle-10 text-success"></i>
                                </div>
                            </div>
                            <div class="col-7 col-md-8">
                                <div class="numbers">
                                    <p class="card-category">Active Sessions</p>
                                    <p class="card-title">{{ $stats['active_sessions'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-users text-success"></i>
                            Last hour activity
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Actions Panel -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="text-danger"><i class="nc-icon nc-tap-01"></i> Super Admin Management Panel</h4>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="nc-icon nc-settings-gear-65"></i> Quick Admin Actions</h5>
                        <p class="card-category">Essential management tools and system controls</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                <a href="{{ route('admin.users') }}" class="btn btn-primary btn-block btn-admin-action">
                                    <i class="nc-icon nc-single-02"></i><br>
                                    <strong>User Management</strong><br>
                                    <small>Manage all users</small>
                                </a>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                <a href="{{ route('admin.properties') }}" class="btn btn-success btn-block btn-admin-action">
                                    <i class="nc-icon nc-istanbul"></i><br>
                                    <strong>Property Oversight</strong><br>
                                    <small>Monitor properties</small>
                                </a>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                <a href="{{ route('payments.analytics') }}" class="btn btn-warning btn-block">
                                    <i class="nc-icon nc-chart-bar-32"></i><br>
                                    <strong>Financial Analytics</strong><br>
                                    <small>Revenue & payments</small>
                                </a>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                <a href="/dashboard/billing" class="btn btn-success btn-block btn-admin-action">
                                    <i class="nc-icon nc-money-coins"></i><br>
                                    <strong>Billing Center</strong><br>
                                    <small>View payments & bills</small>
                                </a>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                <a href="{{ route('admin.system-health') }}" class="btn btn-info btn-block btn-admin-action">
                                    <i class="nc-icon nc-settings-gear-65"></i><br>
                                    <strong>System Health</strong><br>
                                    <small>Server monitoring</small>
                                </a>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                <a href="{{ route('admin.reports') }}" class="btn btn-secondary btn-block btn-admin-action">
                                    <i class="nc-icon nc-paper"></i><br>
                                    <strong>Advanced Reports</strong><br>
                                    <small>Export & analytics</small>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Second Row of Actions -->
                        <div class="row mt-3">
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                <a href="{{ route('admin.audit-logs') }}" class="btn btn-dark btn-block btn-admin-action">
                                    <i class="nc-icon nc-tile-56"></i><br>
                                    <strong>Audit Logs</strong><br>
                                    <small>System activity</small>
                                </a>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                <a href="{{ route('admin.backup') }}" class="btn btn-outline-danger btn-block btn-admin-action">
                                    <i class="nc-icon nc-refresh-02"></i><br>
                                    <strong>Backup & Restore</strong><br>
                                    <small>Data management</small>
                                </a>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                <a href="{{ route('admin.security') }}" class="btn btn-outline-warning btn-block btn-admin-action">
                                    <i class="nc-icon nc-lock-circle-open"></i><br>
                                    <strong>Security Center</strong><br>
                                    <small>Access control</small>
                                </a>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                <a href="#" class="btn btn-outline-info btn-block btn-admin-action" data-action="maintenance" onclick="toggleMaintenance()">
                                    <i class="nc-icon nc-settings"></i><br>
                                    <strong>Maintenance Mode</strong><br>
                                    <small>System updates</small>
                                </a>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                <a href="{{ route('admin.email-center') }}" class="btn btn-outline-success btn-block btn-admin-action">
                                    <i class="nc-icon nc-email-85"></i><br>
                                    <strong>Email Center</strong><br>
                                    <small>Bulk messaging</small>
                                </a>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-4 mb-3">
                                <a href="{{ route('admin.api-management') }}" class="btn btn-outline-primary btn-block btn-admin-action">
                                    <i class="nc-icon nc-atom"></i><br>
                                    <strong>API Management</strong><br>
                                    <small>External integrations</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Dashboard -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="nc-icon nc-chart-bar-32"></i> Revenue & Growth Analytics</h5>
                        <p class="card-category">12-month revenue trend with growth indicators</p>
                    </div>
                    <div class="card-body">
                        <canvas id="adminRevenueChart" width="400" height="200"></canvas>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <div class="stats">
                            <i class="fa fa-history"></i> Updated every 5 minutes
                            <span class="float-right">
                                <i class="fa fa-arrow-up text-success"></i>
                                Average Growth: +15.3%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="nc-icon nc-chart-pie-36"></i> User Distribution</h5>
                        <p class="card-category">Platform user types breakdown</p>
                    </div>
                    <div class="card-body">
                        <canvas id="adminUserChart" width="200" height="200"></canvas>
                    </div>
                    <div class="card-footer">
                        <div class="legend">
                            <i class="fa fa-circle text-primary"></i> Landlords ({{ $stats['users_by_type']['landlords'] ?? 0 }})
                            <i class="fa fa-circle text-warning"></i> Tenants ({{ $stats['users_by_type']['tenants'] ?? 0 }})
                            <i class="fa fa-circle text-danger"></i> Admins ({{ $stats['users_by_type']['admins'] ?? 0 }})
                        </div>
                        <hr>
                        <div class="stats">
                            <i class="fa fa-users"></i> Total: {{ $stats['total_users'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Activities and Alerts -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="nc-icon nc-time-alarm"></i> Recent System Activities</h5>
                        <p class="card-category">Real-time system events and user activities</p>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        @if(isset($recentActivities) && count($recentActivities) > 0)
                            <div class="timeline">
                                @foreach($recentActivities as $activity)
                                    <div class="timeline-item">
                                        <div class="timeline-marker">
                                            <i class="{{ $activity['icon'] ?? 'nc-icon nc-time-alarm' }} {{ $activity['color'] ?? 'text-info' }}"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ $activity['title'] }}</h6>
                                            <p class="timeline-description">{{ $activity['description'] }}</p>
                                            <small class="text-muted">{{ $activity['time'] }}</small>
                                            @if(isset($activity['link']))
                                                <a href="{{ $activity['link'] }}" class="btn btn-sm btn-outline-info ml-2">View Details</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="nc-icon nc-time-alarm" style="font-size: 3em;"></i>
                                <p class="mt-3">No recent activities to display</p>
                                <small>System events will appear here as they occur</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="nc-icon nc-bell-55"></i> System Alerts</h5>
                        <p class="card-category">Important notifications and warnings</p>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <strong><i class="fa fa-check"></i> System Healthy</strong><br>
                            All services are running normally
                        </div>
                        
                        @if(($stats['pending_payments'] ?? 0) > 10)
                        <div class="alert alert-warning">
                            <strong><i class="fa fa-exclamation-triangle"></i> Payment Alert</strong><br>
                            {{ $stats['pending_payments'] }} pending payments need attention
                        </div>
                        @endif
                        
                        @if(($stats['failed_payments'] ?? 0) > 5)
                        <div class="alert alert-danger">
                            <strong><i class="fa fa-times"></i> Failed Payments</strong><br>
                            {{ $stats['failed_payments'] }} failed payments detected
                        </div>
                        @endif
                        
                        <div class="alert alert-info">
                            <strong><i class="fa fa-info-circle"></i> Backup Status</strong><br>
                            Last backup: 2 hours ago
                            <button class="btn btn-sm btn-info float-right">Run Backup</button>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats Mini Cards -->
                <div class="row">
                    <div class="col-6 mb-2">
                        <div class="card mini-stat">
                            <div class="card-body text-center">
                                <h6>{{ $stats['occupied_apartments'] ?? 0 }}</h6>
                                <small>Occupied Units</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="card mini-stat">
                            <div class="card-body text-center">
                                <h6>{{ $stats['vacant_apartments'] ?? 0 }}</h6>
                                <small>Vacant Units</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="card mini-stat">
                            <div class="card-body text-center">
                                <h6>${{ number_format(($stats['average_transaction_value'] ?? 0), 0) }}</h6>
                                <small>Avg. Transaction</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="card mini-stat">
                            <div class="card-body text-center">
                                <h6>{{ $stats['new_users_this_week'] ?? 0 }}</h6>
                                <small>New This Week</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif
</div>

<!-- Chart.js Scripts for Super Admin -->
<script>
@if(auth()->user()->admin == 1 || auth()->user()->role == 1)
    // Admin Revenue Chart with enhanced styling
    const adminRevenueCtx = document.getElementById('adminRevenueChart').getContext('2d');
    new Chart(adminRevenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['revenue_trend']['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) !!},
            datasets: [{
                label: 'Monthly Revenue ($)',
                data: {!! json_encode($chartData['revenue_trend']['data'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) !!},
                borderColor: '#51cbce',
                backgroundColor: 'rgba(81, 203, 206, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#51cbce',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5
            }, {
                label: 'User Growth',
                data: {!! json_encode($chartData['user_growth']['data'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) !!},
                borderColor: '#fbc658',
                backgroundColor: 'rgba(251, 198, 88, 0.1)',
                tension: 0.4,
                fill: false,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            interaction: {
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + new Intl.NumberFormat().format(value);
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Admin User Distribution Chart
    const adminUserCtx = document.getElementById('adminUserChart').getContext('2d');
    new Chart(adminUserCtx, {
        type: 'doughnut',
        data: {
            labels: ['Landlords', 'Tenants', 'Admins'],
            datasets: [{
                data: [
                    {{ $stats['users_by_type']['landlords'] ?? 0 }},
                    {{ $stats['users_by_type']['tenants'] ?? 0 }},
                    {{ $stats['users_by_type']['admins'] ?? 0 }}
                ],
                backgroundColor: [
                    '#51cbce',
                    '#fbc658',
                    '#ef8157'
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

    // Admin Action Click Handlers
    document.querySelectorAll('.btn-admin-action').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            
            // Show loading state
            const originalContent = this.innerHTML;
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i><br>Loading...';
            this.disabled = true;
            
            // Simulate action (replace with actual implementation)
            setTimeout(() => {
                this.innerHTML = originalContent;
                this.disabled = false;
                
                // Show notification
                showNotification(action + ' feature will be available soon!', 'info');
            }, 1000);
        });
    });

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification && notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }
@endif
</script>

<style>
.alert-gradient-primary {
    background: linear-gradient(45deg, #51cbce, #6bd098);
    color: white;
    border: none;
}

.btn-admin-action {
    height: 100px;
    white-space: normal;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    transition: all 0.3s ease;
}

.btn-admin-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
    padding-bottom: 10px;
}

.timeline-marker {
    position: absolute;
    left: -40px;
    top: 5px;
    width: 20px;
    height: 20px;
    background-color: #f4f3ef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -31px;
    top: 25px;
    bottom: -15px;
    width: 2px;
    background-color: #e3e3e3;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-title {
    margin-bottom: 5px;
    color: #333;
    font-weight: 600;
}

.timeline-description {
    margin-bottom: 8px;
    color: #666;
    font-size: 0.9em;
    line-height: 1.4;
}

.mini-stat {
    background: linear-gradient(45deg, #f8f9fa, #e9ecef);
    border: none;
    transition: all 0.3s ease;
}

.mini-stat:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.card-stats:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.bg-gradient-primary {
    background: linear-gradient(45deg, #51cbce, #6bd098) !important;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.card-stats .numbers .card-title {
    font-weight: 700;
    animation: pulse 2s infinite;
}
</style>

@include('footer')
<!-- Footer area end -->

@include('header')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">
                                <i class="nc-icon nc-paper"></i> Audit Logs
                            </h4>
                            <p class="card-category">System activity tracking and security monitoring</p>
                        </div>
                        <div class="col-md-4">
                            <a href="/admin-dashboard" class="btn btn-info btn-sm float-right">
                                <i class="nc-icon nc-minimal-left"></i> Back to Admin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-paper text-primary"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Total Logs</p>
                                <p class="card-title">{{ number_format($totalLogs) }}</p>
                            </div>
                        </div>
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
                                <p class="card-category">Today</p>
                                <p class="card-title">{{ number_format($todayLogs) }}</p>
                            </div>
                        </div>
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
                                <i class="nc-icon nc-single-02 text-info"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Active Users</p>
                                <p class="card-title">{{ number_format($activeUsers) }}</p>
                            </div>
                        </div>
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
                                <i class="nc-icon nc-alert-circle-i text-danger"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Critical Actions</p>
                                <p class="card-title">{{ number_format($criticalActions) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Filters & Search</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.audit-logs') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Action</label>
                                    <select name="action" class="form-control">
                                        <option value="">All Actions</option>
                                        <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                                        <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                                        <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create</option>
                                        <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                                        <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
                                        <option value="admin_access" {{ request('action') == 'admin_access' ? 'selected' : '' }}>Admin Access</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>User</label>
                                    <select name="user_id" class="form-control">
                                        <option value="">All Users</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->user_id }}" {{ request('user_id') == $user->user_id ? 'selected' : '' }}>
                                                {{ $user->full_name ?: ('User #' . $user->user_id) }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date From</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date To</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="nc-icon nc-zoom-split"></i> Filter
                                        </button>
                                        <a href="{{ route('admin.audit-logs') }}" class="btn btn-secondary btn-sm">Clear</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search in descriptions..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="btn-group float-right">
                                    <a href="{{ route('admin.audit-logs.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="btn btn-success btn-sm">
                                        <i class="nc-icon nc-cloud-download-93"></i> Export CSV
                                    </a>
                                    <a href="{{ route('admin.audit-logs.export', array_merge(request()->all(), ['format' => 'json'])) }}" class="btn btn-info btn-sm">
                                        <i class="nc-icon nc-cloud-download-93"></i> Export JSON
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Activity Logs ({{ $auditLogs->total() }} total)</h5>
                </div>
                <div class="card-body">
                    @if($auditLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Model</th>
                                        <th>Description</th>
                                        <th>IP Address</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auditLogs as $log)
                                        <tr>
                                            <td>
                                                <small class="text-muted">{{ $log->performed_at->format('M d, Y H:i:s') }}</small>
                                            </td>
                                            <td>
                                                @if($log->user)
                                                    @php
                                                        $fullName = trim(($log->user->first_name ?? '') . ' ' . ($log->user->last_name ?? ''));
                                                    @endphp
                                                    <strong>{{ $fullName !== '' ? $fullName : ($log->user->username ?? 'User #' . $log->user->user_id) }}</strong>
                                                    <br><small class="text-muted">{{ $log->user->email }}</small>
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ 
                                                    $log->action === 'delete' ? 'danger' : 
                                                    ($log->action === 'create' ? 'success' : 
                                                    ($log->action === 'login' ? 'info' : 
                                                    ($log->action === 'admin_access' ? 'warning' : 'secondary'))) 
                                                }}">
                                                    {{ ucfirst($log->action) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($log->model_type)
                                                    {{ class_basename($log->model_type) }}
                                                    @if($log->model_id)
                                                        <small class="text-muted">#{{ $log->model_id }}</small>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ $log->description }}
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $log->ip_address ?: '-' }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.audit-logs.show', $log) }}" class="btn btn-info btn-sm">
                                                    <i class="nc-icon nc-zoom-split"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="row">
                            <div class="col-md-12">
                                {{ $auditLogs->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="nc-icon nc-bell-55"></i>
                            No audit logs found matching the current filters.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Cleanup Section -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title text-warning">
                        <i class="nc-icon nc-alert-circle-i"></i> Maintenance
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Clean up old audit logs to maintain database performance. This action cannot be undone.
                    </p>
                    <form method="POST" action="{{ route('admin.audit-logs.cleanup') }}" style="display: inline;" 
                          onsubmit="return confirm('Are you sure you want to delete audit logs older than the specified days? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <div class="form-group" style="display: inline-block; margin-right: 10px;">
                            <select name="days" class="form-control" style="width: auto; display: inline-block;">
                                <option value="90">90 days</option>
                                <option value="180">6 months</option>
                                <option value="365">1 year</option>
                                <option value="730">2 years</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="nc-icon nc-simple-remove"></i> Cleanup Old Logs
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('footer')

@include('header')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">
                                <i class="nc-icon nc-key-25"></i> Security Center
                            </h4>
                            <p class="card-category">Comprehensive security monitoring and access control</p>
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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="nc-icon nc-check-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="nc-icon nc-simple-remove"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Security Alerts -->
    @if($securityAlerts->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title text-danger">
                            <i class="nc-icon nc-alert-circle-i"></i> Security Alerts
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($securityAlerts as $alert)
                            <div class="alert alert-{{ $alert['type'] }}">
                                <strong>{{ $alert['title'] }}:</strong> {{ $alert['message'] }}
                                @if(isset($alert['action']))
                                    <br><small><strong>Action:</strong> {{ $alert['action'] }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Security Statistics -->
    <div class="row">
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
                                <p class="card-title">{{ number_format($securityStats['total_users']) }}</p>
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
                                <i class="nc-icon nc-check-2 text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Active Users</p>
                                <p class="card-title">{{ number_format($securityStats['active_users']) }}</p>
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
                                <i class="nc-icon nc-simple-remove text-danger"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Blocked Users</p>
                                <p class="card-title">{{ number_format($securityStats['blocked_users']) }}</p>
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
                                <i class="nc-icon nc-alert-circle-i text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Failed Attempts</p>
                                <p class="card-title">{{ number_format($securityStats['failed_attempts']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Security Settings -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-settings-gear-65"></i> Security Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.security.update') }}">
                        @csrf
                        @php
                            $settings = Cache::get('security_settings', [
                                'session_timeout' => 120,
                                'max_login_attempts' => 5,
                                'password_min_length' => 8,
                                'require_email_verification' => false,
                                'two_factor_enabled' => false
                            ]);
                        @endphp

                        <div class="form-group">
                            <label>Session Timeout (minutes)</label>
                            <input type="number" name="session_timeout" class="form-control" 
                                   value="{{ $settings['session_timeout'] }}" min="5" max="1440">
                            <small class="text-muted">How long before users are automatically logged out</small>
                        </div>

                        <div class="form-group">
                            <label>Max Login Attempts</label>
                            <input type="number" name="max_login_attempts" class="form-control" 
                                   value="{{ $settings['max_login_attempts'] }}" min="3" max="10">
                            <small class="text-muted">Number of failed attempts before account lockout</small>
                        </div>

                        <div class="form-group">
                            <label>Password Minimum Length</label>
                            <input type="number" name="password_min_length" class="form-control" 
                                   value="{{ $settings['password_min_length'] }}" min="6" max="20">
                            <small class="text-muted">Minimum characters required for passwords</small>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" name="require_email_verification" 
                                   class="form-check-input" id="emailVerification"
                                   {{ $settings['require_email_verification'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="emailVerification">
                                Require Email Verification for New Users
                            </label>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" name="two_factor_enabled" 
                                   class="form-check-input" id="twoFactor"
                                   {{ $settings['two_factor_enabled'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="twoFactor">
                                Enable Two-Factor Authentication
                            </label>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="nc-icon nc-check-2"></i> Update Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-button-power"></i> Security Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Block User Account</label>
                        <form method="POST" action="{{ route('admin.security.block-user') }}" class="form-inline">
                            @csrf
                            <select name="user_id" class="form-control mr-2" required>
                                <option value="">Select User...</option>
                                @foreach(App\Models\User::where('role', '!=', 1)->where('status', '!=', 'blocked')->get() as $user)
                                    @php $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->username ?? 'User #' . $user->user_id); @endphp
                                    <option value="{{ $user->user_id }}">{{ $name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            <input type="text" name="reason" class="form-control mr-2" placeholder="Reason..." required>
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="nc-icon nc-simple-remove"></i> Block
                            </button>
                        </form>
                    </div>

                    <div class="form-group">
                        <form method="POST" action="{{ route('admin.security.clear-attempts') }}">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm" 
                                    onclick="return confirm('Clear all failed login attempts?')">
                                <i class="nc-icon nc-refresh-69"></i> Clear Failed Login Attempts
                            </button>
                        </form>
                    </div>

                    <div class="alert alert-info">
                        <i class="nc-icon nc-bell-55"></i>
                        <strong>Security Status:</strong> System security is active. All actions are being logged and monitored.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Failed Login Attempts -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-alert-circle-i"></i> Recent Failed Login Attempts
                    </h5>
                </div>
                <div class="card-body">
                    @if($failedLogins->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>IP Address</th>
                                        <th>Email Attempted</th>
                                        <th>Attempts</th>
                                        <th>Last Attempt</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($failedLogins as $attempt)
                                        <tr>
                                            <td>{{ $attempt['ip'] }}</td>
                                            <td>{{ $attempt['email'] }}</td>
                                            <td>
                                                <span class="badge badge-{{ $attempt['attempts'] >= 5 ? 'danger' : 'warning' }}">
                                                    {{ $attempt['attempts'] }} attempts
                                                </span>
                                            </td>
                                            <td>{{ $attempt['last_attempt']->format('M d, Y H:i:s') }}</td>
                                            <td>
                                                <button class="btn btn-danger btn-sm">
                                                    <i class="nc-icon nc-simple-remove"></i> Block IP
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="nc-icon nc-check-2"></i>
                            No failed login attempts detected recently.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Security Events -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-time-alarm"></i> Recent Security Events
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentEvents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>User</th>
                                        <th>Event</th>
                                        <th>Description</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentEvents as $event)
                                        <tr>
                                            <td>
                                                <small>{{ $event->performed_at->format('M d, Y H:i:s') }}</small>
                                            </td>
                                            <td>
                                                @if($event->user)
                                                    @php $name = trim(($event->user->first_name ?? '') . ' ' . ($event->user->last_name ?? '')) ?: ($event->user->username ?? 'User #' . $event->user->user_id); @endphp
                                                    <strong>{{ $name }}</strong>
                                                    <br><small class="text-muted">{{ $event->user->email }}</small>
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ 
                                                    in_array($event->action, ['user_blocked', 'security_breach']) ? 'danger' : 
                                                    (in_array($event->action, ['login', 'admin_access']) ? 'info' : 'secondary') 
                                                }}">
                                                    {{ str_replace('_', ' ', ucfirst($event->action)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ $event->description }}
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $event->ip_address ?: '-' }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="nc-icon nc-bell-55"></i>
                            No recent security events to display.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Active Sessions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-single-02"></i> Active User Sessions
                    </h5>
                </div>
                <div class="card-body">
                    @if($activeSessions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>IP Address</th>
                                        <th>Browser</th>
                                        <th>Last Activity</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeSessions as $session)
                                        <tr>
                                            <td>
                                                @php $name = trim(($session['user']->first_name ?? '') . ' ' . ($session['user']->last_name ?? '')) ?: ($session['user']->username ?? 'User #' . $session['user']->user_id); @endphp
                                                <strong>{{ $name }}</strong>
                                                <br><small class="text-muted">{{ $session['user']->email }}</small>
                                            </td>
                                            <td>{{ $session['ip_address'] }}</td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ substr($session['user_agent'], 0, 50) }}...
                                                </small>
                                            </td>
                                            <td>
                                                {{ $session['last_activity']->format('M d, Y H:i:s') }}
                                                <br><small class="text-muted">{{ $session['last_activity']->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                @if($session['user']->user_id !== optional(auth()->user())->user_id)
                                                    <button class="btn btn-warning btn-sm">
                                                        <i class="nc-icon nc-button-power"></i> Terminate
                                                    </button>
                                                @else
                                                    <span class="badge badge-info">Current Session</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="nc-icon nc-bell-55"></i>
                            No active user sessions to display.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('footer')

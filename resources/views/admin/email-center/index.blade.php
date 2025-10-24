@extends('layout')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">
                                <i class="nc-icon nc-email-85"></i> Email Center
                            </h4>
                            <p class="card-category">Bulk email management and communication hub</p>
                        </div>
                        <div class="col-md-4">
                            <div class="btn-group float-right">
                                <a href="{{ route('admin.email-center.compose') }}" class="btn btn-primary btn-sm">
                                    <i class="nc-icon nc-send"></i> Compose Email
                                </a>
                                <a href="/admin-dashboard" class="btn btn-info btn-sm">
                                    <i class="nc-icon nc-minimal-left"></i> Back to Admin
                                </a>
                            </div>
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

    <!-- Email Statistics -->
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
                                <p class="card-title">{{ number_format($stats['total_users']) }}</p>
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
                                <p class="card-category">Verified Emails</p>
                                <p class="card-title">{{ number_format($stats['verified_emails']) }}</p>
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
                                <p class="card-category">Unverified</p>
                                <p class="card-title">{{ number_format($stats['unverified_emails']) }}</p>
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
                                <i class="nc-icon nc-send text-info"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Campaigns Sent</p>
                                <p class="card-title">{{ $recentCampaigns->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-button-power"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('admin.email-center.compose') }}" class="btn btn-primary btn-block">
                                <i class="nc-icon nc-send"></i><br>
                                <strong>Compose Email</strong><br>
                                <small>Send bulk emails</small>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.email-center.templates') }}" class="btn btn-info btn-block">
                                <i class="nc-icon nc-paper"></i><br>
                                <strong>Email Templates</strong><br>
                                <small>Manage templates</small>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.email-center.settings') }}" class="btn btn-secondary btn-block">
                                <i class="nc-icon nc-settings-gear-65"></i><br>
                                <strong>Email Settings</strong><br>
                                <small>SMTP configuration</small>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-success btn-block" data-toggle="modal" data-target="#testEmailModal">
                                <i class="nc-icon nc-check-2"></i><br>
                                <strong>Test Email</strong><br>
                                <small>Send test message</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Groups Overview -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-chart-pie-35"></i> User Groups
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Group</th>
                                    <th>Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Admin Users</strong></td>
                                    <td>{{ $stats['admin_users'] }}</td>
                                    <td>
                                        <a href="{{ route('admin.email-center.compose', ['group' => 'admins']) }}" class="btn btn-sm btn-primary">
                                            <i class="nc-icon nc-send"></i> Email
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Landlords</strong></td>
                                    <td>{{ $stats['landlords'] }}</td>
                                    <td>
                                        <a href="{{ route('admin.email-center.compose', ['group' => 'landlords']) }}" class="btn btn-sm btn-primary">
                                            <i class="nc-icon nc-send"></i> Email
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tenants</strong></td>
                                    <td>{{ $stats['tenants'] }}</td>
                                    <td>
                                        <a href="{{ route('admin.email-center.compose', ['group' => 'tenants']) }}" class="btn btn-sm btn-primary">
                                            <i class="nc-icon nc-send"></i> Email
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Agents</strong></td>
                                    <td>{{ $stats['agents'] }}</td>
                                    <td>
                                        <a href="{{ route('admin.email-center.compose', ['group' => 'agents']) }}" class="btn btn-sm btn-primary">
                                            <i class="nc-icon nc-send"></i> Email
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-email-85"></i> Email Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="icon-big text-center">
                                        <i class="nc-icon nc-check-2 text-success" style="font-size: 2em;"></i>
                                    </div>
                                    <h4>{{ number_format($stats['verified_emails']) }}</h4>
                                    <p class="card-category">Verified Addresses</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="icon-big text-center">
                                        <i class="nc-icon nc-alert-circle-i text-warning" style="font-size: 2em;"></i>
                                    </div>
                                    <h4>{{ number_format($stats['unverified_emails']) }}</h4>
                                    <p class="card-category">Unverified Addresses</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($stats['unverified_emails'] > 0)
                        <div class="alert alert-warning mt-3">
                            <i class="nc-icon nc-bell-55"></i>
                            <strong>{{ $stats['unverified_emails'] }}</strong> users have unverified email addresses. 
                            Consider sending a verification reminder.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Email Campaigns -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="nc-icon nc-time-alarm"></i> Recent Email Campaigns
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentCampaigns->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Recipients</th>
                                        <th>Sent Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentCampaigns as $campaign)
                                        <tr>
                                            <td>
                                                <strong>{{ $campaign['subject'] }}</strong>
                                            </td>
                                            <td>{{ number_format($campaign['recipients']) }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($campaign['sent_at'])->format('M d, Y H:i') }}
                                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($campaign['sent_at'])->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $campaign['status'] === 'completed' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($campaign['status']) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-info btn-sm" title="View Details">
                                                        <i class="nc-icon nc-zoom-split"></i>
                                                    </button>
                                                    <button class="btn btn-secondary btn-sm" title="Duplicate">
                                                        <i class="nc-icon nc-simple-add"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="nc-icon nc-bell-55"></i>
                            No email campaigns have been sent yet. Start by composing your first email!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.email-center.test') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="nc-icon nc-send"></i> Send Test Email
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Test Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="test_email" class="form-control" placeholder="Enter email address..." value="{{ auth()->user()->email }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control" value="EasyRent Test Email" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="4" required>This is a test email from the EasyRent Email Center.

If you receive this message, your email configuration is working correctly.

Best regards,
EasyRent Admin Team</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="nc-icon nc-send"></i> Send Test Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

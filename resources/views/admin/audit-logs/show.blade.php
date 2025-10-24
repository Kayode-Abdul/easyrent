@include('header')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">
                                <i class="nc-icon nc-paper"></i> Audit Log Details
                            </h4>
                            <p class="card-category">Detailed view of system activity</p>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.audit-logs') }}" class="btn btn-info btn-sm float-right">
                                <i class="nc-icon nc-minimal-left"></i> Back to Logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Main Details -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Activity Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Date & Time:</strong></label>
                                <p>{{ $auditLog->performed_at->format('F d, Y \a\t g:i:s A') }}</p>
                                <small class="text-muted">{{ $auditLog->performed_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Action:</strong></label>
                                <p>
                                    <span class="badge badge-{{ 
                                        $auditLog->action === 'delete' ? 'danger' : 
                                        ($auditLog->action === 'create' ? 'success' : 
                                        ($auditLog->action === 'login' ? 'info' : 
                                        ($auditLog->action === 'admin_access' ? 'warning' : 'secondary'))) 
                                    }} badge-lg">
                                        {{ ucfirst($auditLog->action) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><strong>Description:</strong></label>
                                <p class="alert alert-light">{{ $auditLog->description }}</p>
                            </div>
                        </div>
                    </div>

                    @if($auditLog->model_type)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Model Type:</strong></label>
                                    <p>{{ class_basename($auditLog->model_type) }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Model ID:</strong></label>
                                    <p>{{ $auditLog->model_id ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Data Changes -->
            @if($auditLog->old_values || $auditLog->new_values)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Data Changes</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($auditLog->old_values)
                                <div class="col-md-6">
                                    <h6 class="text-danger">Old Values</h6>
                                    <div class="alert alert-light">
                                        <pre class="mb-0">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </div>
                            @endif

                            @if($auditLog->new_values)
                                <div class="col-md-6">
                                    <h6 class="text-success">New Values</h6>
                                    <div class="alert alert-light">
                                        <pre class="mb-0">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- User Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">User Information</h5>
                </div>
                <div class="card-body">
                    @if($auditLog->user)
                        <div class="text-center mb-3">
                            <div class="icon-big">
                                <i class="nc-icon nc-single-02 text-primary" style="font-size: 48px;"></i>
                            </div>
                        </div>
                        
                        @php
                            $fullName = trim(($auditLog->user->first_name ?? '') . ' ' . ($auditLog->user->last_name ?? ''));
                        @endphp
                        <div class="form-group">
                            <label><strong>Name:</strong></label>
                            <p>{{ $fullName !== '' ? $fullName : ($auditLog->user->username ?? 'User #' . $auditLog->user->user_id) }}</p>
                        </div>

                        <div class="form-group">
                            <label><strong>Email:</strong></label>
                            <p>{{ $auditLog->user->email }}</p>
                        </div>

                        <div class="form-group">
                            <label><strong>User ID:</strong></label>
                            <p>{{ $auditLog->user->user_id }}</p>
                        </div>

                        @if($auditLog->user->role)
                            <div class="form-group">
                                <label><strong>Role:</strong></label>
                                <p>
                                    <span class="badge badge-info">{{ $auditLog->user->role }}</span>
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="text-center mb-3">
                            <div class="icon-big">
                                <i class="nc-icon nc-settings text-muted" style="font-size: 48px;"></i>
                            </div>
                        </div>
                        <p class="text-center text-muted">System Generated Action</p>
                    @endif
                </div>
            </div>

            <!-- Technical Details -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Technical Details</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label><strong>IP Address:</strong></label>
                        <p>{{ $auditLog->ip_address ?: 'Unknown' }}</p>
                    </div>

                    @if($auditLog->user_agent)
                        <div class="form-group">
                            <label><strong>User Agent:</strong></label>
                            <p class="small text-muted" style="word-break: break-all;">
                                {{ $auditLog->user_agent }}
                            </p>
                        </div>
                    @endif

                    <div class="form-group">
                        <label><strong>Log ID:</strong></label>
                        <p>{{ $auditLog->id }}</p>
                    </div>

                    <div class="form-group">
                        <label><strong>Created:</strong></label>
                        <p>{{ $auditLog->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.audit-logs') }}" class="btn btn-primary btn-block">
                        <i class="nc-icon nc-minimal-left"></i> Back to All Logs
                    </a>
                    
                    @if($auditLog->user)
                        <a href="{{ route('admin.audit-logs', ['user_id' => $auditLog->user->user_id]) }}" class="btn btn-info btn-block">
                            <i class="nc-icon nc-single-02"></i> View User's Logs
                        </a>
                    @endif

                    @if($auditLog->model_type && $auditLog->model_id)
                        <a href="{{ route('admin.audit-logs', ['action' => $auditLog->action]) }}" class="btn btn-secondary btn-block">
                            <i class="nc-icon nc-zoom-split"></i> Similar Actions
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('footer')

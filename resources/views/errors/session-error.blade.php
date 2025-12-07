@extends('layout')

@section('title', 'Session Error')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-clock"></i>
                        Session Issue Detected
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Your session has encountered an issue.</strong>
                        <p class="mb-0 mt-2">{{ $error_message ?? 'We encountered an issue with your session data. This may be due to inactivity or a temporary system issue.' }}</p>
                    </div>

                    @if(isset($recovered_data) && !empty($recovered_data))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <strong>Good news!</strong> We were able to recover some of your data:
                        <ul class="mt-2 mb-0">
                            @if(isset($recovered_data['apartment_info']))
                            <li>Apartment information: {{ $recovered_data['apartment_info'] }}</li>
                            @endif
                            @if(isset($recovered_data['application_data']))
                            <li>Application details: Saved</li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    @if(isset($recovery_strategy))
                    <div class="mt-4">
                        <h5>What happens next:</h5>
                        
                        @if($recovery_strategy['requires_fresh_start'] ?? false)
                        <div class="alert alert-warning">
                            <i class="fas fa-refresh"></i>
                            <strong>Fresh Start Required:</strong> You'll need to start over by accessing the apartment invitation link again.
                        </div>
                        @endif

                        @if($recovery_strategy['data_recoverable'] ?? false)
                        <div class="alert alert-info">
                            <i class="fas fa-database"></i>
                            <strong>Data Recovery:</strong> We can attempt to recover your previous session data.
                        </div>
                        @endif

                        @if($recovery_strategy['preserve_invitation_context'] ?? false)
                        <div class="alert alert-success">
                            <i class="fas fa-bookmark"></i>
                            <strong>Context Preserved:</strong> Your apartment invitation context has been preserved.
                        </div>
                        @endif
                    </div>
                    @endif

                    @if(session('preserved_invitation_token'))
                    <div class="alert alert-success mt-4">
                        <i class="fas fa-link"></i>
                        <strong>Invitation Link Available:</strong> We've preserved your apartment invitation link.
                    </div>
                    @endif

                    <div class="mt-4">
                        <h5>Recommended Actions:</h5>
                        <div class="list-group">
                            @if(session('preserved_invitation_token'))
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Continue with Preserved Link</h6>
                                    <small>Recommended</small>
                                </div>
                                <p class="mb-1">Use your preserved apartment invitation to continue where you left off.</p>
                                <a href="{{ route('apartment.invite.show', session('preserved_invitation_token')) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-play"></i> Continue Application
                                </a>
                            </div>
                            @endif
                            
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Start Fresh</h6>
                                    <small>Alternative</small>
                                </div>
                                <p class="mb-1">Clear all session data and start over with a new apartment invitation link.</p>
                                <button onclick="clearSessionAndRestart()" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-refresh"></i> Start Fresh
                                </button>
                            </div>
                            
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Contact Support</h6>
                                    <small>If issues persist</small>
                                </div>
                                <p class="mb-1">Get help from our support team if you continue experiencing session issues.</p>
                                <a href="mailto:support@easyrent.com" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-envelope"></i> Contact Support
                                </a>
                            </div>
                        </div>
                    </div>

                    @if(isset($session_info))
                    <div class="mt-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Session Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Session ID:</strong> {{ substr($session_info['session_id'] ?? 'Unknown', 0, 8) }}...
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Last Activity:</strong> {{ $session_info['last_activity'] ?? 'Unknown' }}
                                    </div>
                                </div>
                                @if(isset($session_info['expiry_time']))
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <strong>Session Expired:</strong> {{ $session_info['expiry_time'] }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="mt-4 text-center">
                        <button onclick="location.reload()" class="btn btn-primary me-2">
                            <i class="fas fa-sync-alt"></i> Refresh Page
                        </button>
                        
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Go Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function clearSessionAndRestart() {
    if (confirm('This will clear all your session data. Are you sure you want to start fresh?')) {
        // Clear local storage and session storage
        localStorage.clear();
        sessionStorage.clear();
        
        // Redirect to home page
        window.location.href = '{{ route("home") }}';
    }
}

// Auto-refresh after 30 seconds if no action taken
setTimeout(function() {
    if (confirm('Would you like to refresh the page to try recovering your session?')) {
        location.reload();
    }
}, 30000);
</script>
@endsection
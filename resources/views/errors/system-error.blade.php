@extends('layout')

@section('title', 'System Error')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        System Temporarily Unavailable
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>We're experiencing technical difficulties.</strong>
                        <p class="mb-0 mt-2">{{ $error_message ?? 'Our system is temporarily unavailable. We\'re working to resolve this issue as quickly as possible.' }}</p>
                    </div>

                    @if(isset($estimated_recovery_time))
                    <div class="alert alert-secondary">
                        <strong>Estimated Recovery Time:</strong> {{ $estimated_recovery_time }}
                    </div>
                    @endif

                    @if(isset($alternative_actions) && !empty($alternative_actions))
                    <div class="mt-4">
                        <h5>What you can do now:</h5>
                        <ul class="list-group list-group-flush">
                            @foreach($alternative_actions as $action => $description)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $action)) }}</strong>
                                    <p class="mb-0 text-muted">{{ $description }}</p>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if(isset($support_reference))
                    <div class="alert alert-light mt-4">
                        <strong>Support Reference:</strong> {{ $support_reference }}
                        <br>
                        <small class="text-muted">Please provide this reference when contacting support.</small>
                    </div>
                    @endif

                    <div class="mt-4 text-center">
                        <button onclick="location.reload()" class="btn btn-primary me-2">
                            <i class="fas fa-sync-alt"></i> Try Again
                        </button>
                        
                        @if(session('easyrent_invitation_token'))
                        <a href="{{ route('apartment.invite.show', session('easyrent_invitation_token')) }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-home"></i> Return to Apartment
                        </a>
                        @endif
                        
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Go Home
                        </a>
                    </div>
                </div>
            </div>

            @if(isset($system_status))
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">System Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Database:</strong> 
                            <span class="badge bg-{{ $system_status['database'] === 'connected' ? 'success' : 'danger' }}">
                                {{ ucfirst($system_status['database']) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Services:</strong> 
                            <span class="badge bg-{{ $system_status['services'] === 'operational' ? 'success' : 'warning' }}">
                                {{ ucfirst($system_status['services']) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Auto-refresh page every 2 minutes to check if system is back online
setTimeout(function() {
    location.reload();
}, 120000);

// Show countdown timer
let countdown = 120;
const timer = setInterval(function() {
    countdown--;
    if (countdown <= 0) {
        clearInterval(timer);
    }
}, 1000);
</script>
@endsection
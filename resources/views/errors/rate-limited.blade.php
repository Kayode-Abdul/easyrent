@extends('layouts.app')

@section('title', 'Rate Limited')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        Rate Limit Exceeded
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning" role="alert">
                        <h5 class="alert-heading">Too Many Requests</h5>
                        <p>{{ $message ?? 'You have made too many requests in a short period of time.' }}</p>
                        
                        @if(isset($retry_after) && $retry_after > 0)
                            <hr>
                            <p class="mb-0">
                                <strong>Please wait {{ $retry_after }} seconds before trying again.</strong>
                            </p>
                            
                            <div class="mt-3">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" 
                                         style="width: 100%" 
                                         id="countdown-progress">
                                    </div>
                                </div>
                                <small class="text-muted">
                                    Time remaining: <span id="countdown">{{ $retry_after }}</span> seconds
                                </small>
                            </div>
                        @endif
                    </div>

                    @if(isset($is_suspicious) && $is_suspicious)
                        <div class="alert alert-danger mt-3" role="alert">
                            <h6 class="alert-heading">Suspicious Activity Detected</h6>
                            <p class="mb-0">
                                Your request pattern has been flagged as potentially suspicious. 
                                If you believe this is an error, please contact support.
                            </p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <h6>What can you do?</h6>
                        <ul>
                            <li>Wait for the rate limit to reset</li>
                            <li>Reduce the frequency of your requests</li>
                            <li>If you're using automation, implement proper delays between requests</li>
                            @if(isset($is_suspicious) && $is_suspicious)
                                <li>Contact support if you believe this is an error</li>
                            @endif
                        </ul>
                    </div>

                    <div class="mt-4">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($retry_after) && $retry_after > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    let timeLeft = {{ $retry_after }};
    const countdownElement = document.getElementById('countdown');
    const progressElement = document.getElementById('countdown-progress');
    const totalTime = timeLeft;
    
    const timer = setInterval(function() {
        timeLeft--;
        
        if (countdownElement) {
            countdownElement.textContent = timeLeft;
        }
        
        if (progressElement) {
            const percentage = (timeLeft / totalTime) * 100;
            progressElement.style.width = percentage + '%';
        }
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            
            // Show reload button
            const cardBody = document.querySelector('.card-body');
            if (cardBody) {
                const reloadDiv = document.createElement('div');
                reloadDiv.className = 'alert alert-success mt-3';
                reloadDiv.innerHTML = `
                    <h6 class="alert-heading">Rate limit has been reset!</h6>
                    <p class="mb-2">You can now try your request again.</p>
                    <button onclick="window.location.reload()" class="btn btn-success btn-sm">
                        <i class="fas fa-refresh"></i> Reload Page
                    </button>
                `;
                cardBody.appendChild(reloadDiv);
            }
        }
    }, 1000);
});
</script>
@endif
@endsection
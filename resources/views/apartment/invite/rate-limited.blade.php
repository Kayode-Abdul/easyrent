@extends('layout')

@section('title', 'Access Limited')

@section('content')
<style>
.rate-limit-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    padding: 2rem 0;
}

.rate-limit-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
    transition: all 0.3s ease;
}

.rate-limit-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.rate-limit-icon {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

.btn-modern {
    border-radius: 12px;
    padding: 0.875rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.rate-limit-notice {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-radius: 12px;
    padding: 1.5rem;
    border-left: 4px solid #ffc107;
}

.rate-limit-tips {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    border-radius: 12px;
    padding: 1.5rem;
    border-left: 4px solid #17a2b8;
}

.progress-container {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1rem;
    margin: 1rem 0;
}

.progress {
    height: 8px;
    border-radius: 4px;
    background-color: #e9ecef;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(90deg, #ffc107 0%, #fd7e14 100%);
    height: 100%;
    border-radius: 4px;
    transition: width 1s ease-in-out;
    animation: progress-animation 60s linear;
}

@keyframes progress-animation {
    from { width: 0%; }
    to { width: 100%; }
}

@media (max-width: 768px) {
    .rate-limit-container {
        padding: 1rem;
        min-height: 70vh;
    }
    
    .rate-limit-card .card-body {
        padding: 2rem 1.5rem;
    }
    
    .rate-limit-icon {
        font-size: 3rem !important;
    }
    
    .rate-limit-notice, .rate-limit-tips, .progress-container {
        padding: 1rem;
    }
}
</style>

<div class="container rate-limit-container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="rate-limit-card card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-hourglass-half fa-4x rate-limit-icon mb-3"></i>
                        <h2 class="text-warning mb-3 fw-bold">Please Wait a Moment</h2>
                        <p class="text-muted mb-4 lead">
                            This invitation has been accessed frequently. Please wait a moment before trying again.
                        </p>
                    </div>
                    
                    <div class="rate-limit-notice mb-4 text-start">
                        <h6 class="text-warning mb-3">
                            <i class="fas fa-info-circle me-2"></i>Rate Limit Active
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-clock me-2 text-warning"></i>
                                Too many requests in a short time period
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-shield-alt me-2 text-warning"></i>
                                Protection against automated access
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-refresh me-2 text-warning"></i>
                                Access will be restored automatically
                            </li>
                        </ul>
                    </div>
                    
                    <div class="progress-container">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Cooldown Progress</small>
                            <small class="text-muted" id="countdown">1:00</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" id="progressBar"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">Please wait for the cooldown to complete</small>
                    </div>
                    
                    <div class="rate-limit-tips mb-4 text-start">
                        <h6 class="text-info mb-3">
                            <i class="fas fa-lightbulb me-2"></i>While You Wait
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-search me-2 text-info"></i>
                                Browse other available properties
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-user-plus me-2 text-info"></i>
                                Create an account for faster access
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-bookmark me-2 text-info"></i>
                                Save this link for later access
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-envelope me-2 text-info"></i>
                                Contact the landlord directly
                            </li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <a href="{{ url('/') }}" class="btn btn-primary btn-modern w-100">
                                    <i class="fas fa-search me-2"></i>Browse Properties
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button onclick="location.reload()" class="btn btn-warning btn-modern w-100" id="retryBtn" disabled>
                                    <i class="fas fa-sync-alt me-2"></i>Try Again (<span id="retryCountdown">60</span>s)
                                </button>
                            </div>
                        </div>
                        
                        <a href="{{ route('register') }}" class="btn btn-outline-success btn-modern">
                            <i class="fas fa-user-plus me-2"></i>Create Account for Faster Access
                        </a>
                    </div>
                    
                    <!-- Help Section -->
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-question-circle me-2"></i>Need Help?
                        </h6>
                        <p class="small text-muted mb-3">
                            If you continue to experience issues, our support team can assist you.
                        </p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <p class="small mb-1">
                                    <i class="fas fa-envelope me-2"></i>
                                    <a href="mailto:support@easyrent.com" class="text-decoration-none">support@easyrent.com</a>
                                </p>
                                <p class="small mb-0">
                                    <i class="fas fa-phone me-2"></i>
                                    <a href="tel:+2348001234567" class="text-decoration-none">+234 800 EASYRENT</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Countdown timer and progress bar
let timeLeft = 60; // 60 seconds
const totalTime = 60;

function updateCountdown() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    
    // Update countdown displays
    document.getElementById('countdown').textContent = 
        `${minutes}:${seconds.toString().padStart(2, '0')}`;
    document.getElementById('retryCountdown').textContent = timeLeft;
    
    // Update progress bar
    const progressPercent = ((totalTime - timeLeft) / totalTime) * 100;
    document.getElementById('progressBar').style.width = progressPercent + '%';
    
    if (timeLeft > 0) {
        timeLeft--;
        setTimeout(updateCountdown, 1000);
    } else {
        // Enable retry button
        const retryBtn = document.getElementById('retryBtn');
        retryBtn.disabled = false;
        retryBtn.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Try Again Now';
        retryBtn.classList.remove('btn-warning');
        retryBtn.classList.add('btn-success');
        
        // Update countdown display
        document.getElementById('countdown').textContent = '0:00';
        document.getElementById('progressBar').style.width = '100%';
        
        // Show success message
        const progressContainer = document.querySelector('.progress-container');
        progressContainer.innerHTML = `
            <div class="text-success text-center">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <div>Cooldown Complete!</div>
                <small>You can now access the invitation again</small>
            </div>
        `;
    }
}

// Start countdown when page loads
document.addEventListener('DOMContentLoaded', function() {
    updateCountdown();
});
</script>
@endsection
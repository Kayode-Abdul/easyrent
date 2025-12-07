@extends('layout')

@section('title', 'Security Block')

@section('content')
<style>
.security-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    padding: 2rem 0;
}

.security-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
    transition: all 0.3s ease;
}

.security-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.security-icon {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shake 0.5s ease-in-out infinite alternate;
}

@keyframes shake {
    0% { transform: translateX(0); }
    100% { transform: translateX(5px); }
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

.security-notice {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    border-radius: 12px;
    padding: 1.5rem;
    border-left: 4px solid #dc3545;
}

.security-steps {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    border-radius: 12px;
    padding: 1.5rem;
    border-left: 4px solid #17a2b8;
}

.countdown-timer {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-radius: 12px;
    padding: 1rem;
    border-left: 4px solid #ffc107;
    font-family: 'Courier New', monospace;
    font-size: 1.2rem;
    font-weight: bold;
}

@media (max-width: 768px) {
    .security-container {
        padding: 1rem;
        min-height: 70vh;
    }
    
    .security-card .card-body {
        padding: 2rem 1.5rem;
    }
    
    .security-icon {
        font-size: 3rem !important;
    }
    
    .security-notice, .security-steps, .countdown-timer {
        padding: 1rem;
    }
}
</style>

<div class="container security-container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="security-card card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-shield-alt fa-4x security-icon mb-3"></i>
                        <h2 class="text-danger mb-3 fw-bold">Access Temporarily Blocked</h2>
                        <p class="text-muted mb-4 lead">
                            This invitation has been temporarily blocked due to suspicious activity detected from your location.
                        </p>
                    </div>
                    
                    <div class="security-notice mb-4 text-start">
                        <h6 class="text-danger mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>Security Alert
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-times-circle me-2 text-danger"></i>
                                Multiple failed access attempts detected
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-clock me-2 text-danger"></i>
                                Unusual access patterns identified
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-lock me-2 text-danger"></i>
                                Automatic security measures activated
                            </li>
                        </ul>
                    </div>
                    
                    <div class="countdown-timer mb-4 text-center">
                        <div class="text-warning mb-2">
                            <i class="fas fa-hourglass-half me-2"></i>Access will be restored in:
                        </div>
                        <div id="countdown" class="text-dark">15:00</div>
                    </div>
                    
                    <div class="security-steps mb-4 text-start">
                        <h6 class="text-info mb-3">
                            <i class="fas fa-lightbulb me-2"></i>What You Can Do
                        </h6>
                        <ol class="mb-0">
                            <li class="mb-2">Wait for the security block to automatically expire (15 minutes)</li>
                            <li class="mb-2">Ensure you're using the correct invitation link</li>
                            <li class="mb-2">Try accessing from a different network if possible</li>
                            <li class="mb-0">Contact support if you believe this is an error</li>
                        </ol>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <a href="{{ url('/') }}" class="btn btn-primary btn-modern w-100">
                                    <i class="fas fa-home me-2"></i>Browse Properties
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="mailto:support@easyrent.com?subject=Security Block Appeal&body=I believe my access has been incorrectly blocked. Please review my case." 
                                   class="btn btn-danger btn-modern w-100">
                                    <i class="fas fa-envelope me-2"></i>Appeal Block
                                </a>
                            </div>
                        </div>
                        
                        <button onclick="location.reload()" class="btn btn-outline-secondary btn-modern">
                            <i class="fas fa-sync-alt me-2"></i>Check Status Again
                        </button>
                    </div>
                    
                    <!-- Help Section -->
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-question-circle me-2"></i>Need Immediate Help?
                        </h6>
                        <p class="small text-muted mb-3">
                            If this is urgent or you believe this block is in error, contact our security team directly.
                        </p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <p class="small mb-1">
                                    <i class="fas fa-envelope me-2"></i>
                                    <a href="mailto:security@easyrent.com" class="text-decoration-none">security@easyrent.com</a>
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
// Countdown timer
let timeLeft = 15 * 60; // 15 minutes in seconds

function updateCountdown() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    
    document.getElementById('countdown').textContent = 
        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    
    if (timeLeft > 0) {
        timeLeft--;
        setTimeout(updateCountdown, 1000);
    } else {
        document.getElementById('countdown').textContent = '00:00';
        document.getElementById('countdown').parentElement.innerHTML = 
            '<div class="text-success"><i class="fas fa-check-circle me-2"></i>You can now try accessing the invitation again</div>';
    }
}

// Start countdown when page loads
document.addEventListener('DOMContentLoaded', function() {
    updateCountdown();
});
</script>
@endsection
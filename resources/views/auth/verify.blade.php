@include('header')

<style>
.auth-container {
    min-height: 100vh;
    background: linear-gradient(45deg, #17a2b8, #6bd098) !important;
    display: flex;
    align-items: center;
    padding: 2rem 0;
}

.auth-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
    transition: all 0.3s ease;
}

.auth-header {

    background: linear-gradient(45deg, #17a2b8, #6bd098) !important;    color: white;
    padding: 2rem;
    text-align: center;
    border: none;
}

.auth-header h2 {
    margin: 0;
    font-weight: 600;
    font-size: 1.8rem;
}

.auth-body {
    padding: 2.5rem;
    text-align: center;
}

.verification-icon {
    font-size: 4rem;
    color: #667eea;
    margin-bottom: 1.5rem;
}

.verification-message {
    font-size: 1.1rem;
    color: #6c757d;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.btn-auth {
    background: linear-gradient(45deg, #17a2b8, #6bd098) !important;
    border: none;
    border-radius: 12px;
    padding: 0.875rem 2rem;
    font-weight: 600;
    font-size: 1rem;
    color: white;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-decoration: none;
    display: inline-block;
}

.btn-auth:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    color: white;
    text-decoration: none;
}

.alert-modern {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    font-weight: 500;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.auth-links {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e9ecef;
}

.auth-links a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.auth-links a:hover {
    color: #764ba2;
    text-decoration: underline;
}

@media (max-width: 768px) {
    .auth-container {
        padding: 1rem;
    }
    
    .auth-body {
        padding: 1.5rem;
    }
    
    .auth-header {
        padding: 1.5rem;
    }
}
</style>

<div class="pt-pad">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card">
                    <div class="auth-header">
                        <h2><i class="fas fa-envelope-open me-2"></i>Email Verification</h2>
                        <p class="mb-0 mt-2 opacity-90">Verify your email to continue</p>
                    </div>

                    <div class="auth-body">
                        <div id="toast-container" class="modern-toast-container"></div>

                        <div class="verification-icon">
                            <i class="fas fa-envelope-circle-check"></i>
                        </div>

                        <div class="verification-message">
                            <p><strong>Almost there!</strong></p>
                            <p>{{ __('Before proceeding, please check your email for a verification link.') }}</p>
                            <p class="text-muted">{{ __('If you did not receive the email, you can request a new one below.') }}</p>
                        </div>

                        <form method="POST" action="{{ route('verification.resend') }}" id="resendForm">
                            @csrf
                            <button type="submit" class="btn btn-auth" id="resendBtn">
                                <i class="fas fa-paper-plane me-2"></i>{{ __('Resend Verification Email') }}
                            </button>
                        </form>

                        <div class="auth-links">
                            <a href="{{ route('login') }}">
                                <i class="fas fa-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show session messages as modern toasts
document.addEventListener('DOMContentLoaded', function() {
    @if (session('resent'))
        showToast("{{ __('A fresh verification link has been sent to your email address.') }}", 'success');
    @endif
    @if (session('status'))
        showToast("{{ session('status') }}", 'success');
    @endif
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            showToast("{{ $error }}", 'error');
        @endforeach
    @endif
    
    // Handle resend form submission
    const resendForm = document.getElementById('resendForm');
    const resendBtn = document.getElementById('resendBtn');
    
    if (resendForm && resendBtn) {
        resendForm.addEventListener('submit', function(e) {
            // Disable button and show loading state
            resendBtn.disabled = true;
            resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            
            // Re-enable button after 5 seconds to prevent permanent disable
            setTimeout(() => {
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>{{ __("Resend Verification Email") }}';
            }, 5000);
        });
    }
});
</script>

@include('footer')

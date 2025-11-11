@include('header')

<style> 

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
}

.form-floating {
    margin-bottom: 1.5rem;
}

.form-floating > .form-control {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem 0.75rem;
    height: auto;
    transition: all 0.3s ease;
}

.form-floating > .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-floating > label {
    color: #6c757d;
    font-weight: 500;
}

.btn-auth {

    background: linear-gradient(45deg, #17a2b8, #6bd098) !important;    border: none;
    border-radius: 12px;
    padding: 0.875rem 2rem;
    font-weight: 600;
    font-size: 1rem;
    color: white;
    width: 100%;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-auth:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
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
    text-align: center;
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

.reset-info {
    background: rgba(102, 126, 234, 0.1);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    text-align: center;
}

.reset-info i {
    font-size: 2.5rem;
    color: #667eea;
    margin-bottom: 1rem;
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
                        <h2><i class="fas fa-key me-2"></i>Reset Password</h2>
                        <p class="mb-0 mt-2 opacity-90">Enter your email to reset password</p>
                    </div>

                    <div class="auth-body">
                        <div id="toast-container" class="modern-toast-container"></div>

                        <div class="reset-info">
                            <i class="fas fa-envelope-open-text"></i>
                            <p class="mb-0 text-muted">Enter your email address and we'll send you a link to reset your password.</p>
                        </div>

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <div class="form-floating">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" required autocomplete="email" 
                                       autofocus placeholder="Email Address">
                                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                @error('email')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-auth">
                                <i class="fas fa-paper-plane me-2"></i>{{ __('Send Password Reset Link') }}
                            </button>
                        </form>

                        <div class="auth-links">
                            <a href="{{ route('login') }}">
                                <i class="fas fa-arrow-left me-1"></i>Back to Login
                            </a>
                            <div class="mt-2">
                                <span class="text-muted">Remember your password?</span>
                                <a href="{{ route('login') }}" class="ms-1">
                                    <i class="fas fa-sign-in-alt me-1"></i>Sign In
                                </a>
                            </div>
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
    @if (session('status'))
        showToast("{{ session('status') }}", 'success');
    @endif
});
</script>

@include('footer')

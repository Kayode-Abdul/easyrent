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

.auth-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.auth-header {
    background: linear-gradient(45deg, #17a2b8, #6bd098) !important;
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

.invitation-context {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border-left: 4px solid #17a2b8;
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
    border-color: #17a2b8;
    box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
}

.form-floating > label {
    color: #6c757d;
    font-weight: 500;
}

.btn-auth {
    background: linear-gradient(45deg, #17a2b8, #6bd098) !important;
    border: none;
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
    box-shadow: 0 10px 20px rgba(23, 162, 184, 0.3);
    background: linear-gradient(135deg, #138496 0%, #5a9f5a 100%);
}

.form-check {
    margin: 1.5rem 0;
}

.form-check-input:checked {
    background-color: #17a2b8;
    border-color: #17a2b8;
}

.auth-links {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e9ecef;
}

.auth-links a {
    color: #17a2b8;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.auth-links a:hover {
    color: #138496;
    text-decoration: underline;
}

.password-toggle-btn {
    position: absolute !important;
    right: 15px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    background: none !important;
    border: none !important;
    color: #6c757d !important;
    cursor: pointer !important;
    padding: 8px !important;
    z-index: 1000 !important;
    transition: color 0.3s ease !important;
    font-size: 16px !important;
    width: auto !important;
    height: auto !important;
    display: block !important;
    line-height: 1 !important;
}

.password-toggle-btn:hover {
    color: #17a2b8 !important;
}

.password-toggle-btn:focus {
    outline: none !important;
    color: #17a2b8 !important;
}

.form-floating.position-relative {
    position: relative !important;
}

.form-floating .password-toggle-btn {
    right: 12px !important;
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
    
    .invitation-context {
        padding: 1rem;
    }
}
</style>

<div class="pt-pad">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card">
                    <div class="auth-header">
                        <h2><i class="fas fa-sign-in-alt me-2"></i>Continue to Your Apartment</h2>
                        <p class="mb-0 mt-2 opacity-90">Sign in to complete your application</p>
                    </div>

                    <div class="auth-body">
                        <!-- Invitation Context Display -->
                        @if(isset($invitation) && $invitation)
                        <div class="invitation-context">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-home fa-2x text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-1 text-primary">{{ $invitation->apartment->property->prop_name }}</h6>
                                    <small class="text-muted">{{ $invitation->apartment->apartment_type }} • ₦{{ number_format($invitation->apartment->amount) }}/month</small>
                                </div>
                            </div>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>Your apartment application is waiting. Sign in to continue where you left off.</small>
                            </div>
                        </div>
                        @endif

                        <div id="toast-container" class="modern-toast-container"></div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <!-- Hidden field to preserve invitation context -->
                            @if(request('token'))
                                <input type="hidden" name="invitation_token" value="{{ request('token') }}">
                            @endif

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

                            <div class="form-floating position-relative">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="current-password" placeholder="Password">
                                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password')">
                                    <i class="fas fa-eye-slash" id="password-toggle-icon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <div class="form-check">
                                <input type="checkbox" name="remember" id="remember" class="form-check-input"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Remember Me
                                </label>
                            </div>

                            <button type="submit" class="btn btn-auth">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In & Continue
                            </button>
                        </form>

                        <div class="auth-links">
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">
                                    <i class="fas fa-key me-1"></i>Forgot Your Password?
                                </a>
                            @endif
                            <div class="mt-3">
                                <span class="text-muted">Don't have an account?</span>
                                <a href="{{ route('register') }}{{ request('token') ? '?token=' . request('token') : '' }}" class="ms-1">
                                    <i class="fas fa-user-plus me-1"></i>Create Account
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
// Password visibility toggle function
function togglePasswordVisibility(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(fieldId + '-toggle-icon');
    
    if (!passwordField || !toggleIcon) {
        return;
    }
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    }
}

// Show session messages as modern toasts
document.addEventListener('DOMContentLoaded', function() {
    @if (session('status'))
        showToast("{{ session('status') }}", 'success');
    @endif
    @if (session('message'))
        showToast("{{ session('message') }}", 'success');
    @endif
    @if (session('error'))
        showToast("{{ session('error') }}", 'error');
    @endif
    
    // Check for session expiry parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('expired') === '1') {
        showToast('Your session has expired. Please login again.', 'warning');
    }
    
    // Handle form submission
    const loginForm = document.querySelector('form[action*="login"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
            submitBtn.disabled = true;
        });
    }
});
</script> 

@include('footer')
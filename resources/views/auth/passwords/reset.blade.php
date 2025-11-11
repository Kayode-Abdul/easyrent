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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
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

.password-requirements {
    background: rgba(40, 167, 69, 0.1);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.password-requirements ul {
    margin: 0;
    padding-left: 1.2rem;
}

.password-requirements li {
    color: #28a745;
    margin-bottom: 0.25rem;
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
                        <h2><i class="fas fa-lock-open me-2"></i>Reset Password</h2>
                        <p class="mb-0 mt-2 opacity-90">Create your new password</p>
                    </div>

                    <div class="auth-body">
                        <div id="toast-container" class="modern-toast-container"></div>
                        
                        <div class="reset-info">
                            <i class="fas fa-shield-alt"></i>
                            <p class="mb-0 text-muted">Choose a strong password to secure your account.</p>
                        </div>

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="form-floating">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" 
                                       autofocus placeholder="Email Address" readonly>
                                <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                @error('email')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <div class="form-floating position-relative">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="new-password" placeholder="New Password">
                                <label for="password"><i class="fas fa-lock me-2"></i>New Password</label>
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password')">
                                    <i class="fas fa-eye-slash" id="password-toggle-icon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <div class="form-floating position-relative">
                                <input id="password-confirm" type="password" class="form-control" 
                                       name="password_confirmation" required autocomplete="new-password" 
                                       placeholder="Confirm New Password">
                                <label for="password-confirm"><i class="fas fa-lock me-2"></i>Confirm New Password</label>
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password-confirm')">
                                    <i class="fas fa-eye-slash" id="password-confirm-toggle-icon"></i>
                                </button>
                            </div>

                            <div class="password-requirements">
                                <strong><i class="fas fa-info-circle me-2"></i>Password Requirements:</strong>
                                <ul>
                                    <li>At least 8 characters long</li>
                                    <li>Mix of uppercase and lowercase letters</li>
                                    <li>At least one number</li>
                                    <li>At least one special character</li>
                                </ul>
                            </div>

                            <button type="submit" class="btn btn-auth">
                                <i class="fas fa-check me-2"></i>{{ __('Reset Password') }}
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

<style>
.password-toggle-btn {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 5px;
    z-index: 10;
    transition: color 0.3s ease;
}

.password-toggle-btn:hover {
    color: #667eea;
}

.password-toggle-btn:focus {
    outline: none;
    color: #667eea;
}
</style>

<script>
// Password visibility toggle function
function togglePasswordVisibility(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(fieldId + '-toggle-icon');
    
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
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            showToast("{{ $error }}", 'error');
        @endforeach
    @endif
});
</script>

@include('footer')

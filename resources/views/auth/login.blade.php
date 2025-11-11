
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


    background: linear-gradient(45deg, #17a2b8, #6bd098) !important;    padding: 2rem;
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
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
}

.form-check {
    margin: 1.5rem 0;
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
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

.alert-modern {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.alert-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.alert-danger {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    color: white;
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
                        <h2><i class="fas fa-sign-in-alt me-2"></i>Welcome Back</h2>
                        <p class="mb-0 mt-2 opacity-90">Sign in to your account</p>
                    </div>

                    <div class="auth-body">
                        <div id="toast-container" class="modern-toast-container"></div>

                        <form method="POST" action="{{ route('login') }}">
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

                            <div class="form-floating position-relative">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="current-password" placeholder="Password">
                                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password')">
                                    <i class="bi bi-eye-slash" id="password-toggle-icon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <div class="form-check">
                                <!--input class="form-check-input" type="checkbox" name="remember" id="remember" -->
                                <input type="checkbox" name="remember" id="remember" 
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Remember Me
                                </label>
                            </div>

                            <button type="submit" class="btn btn-auth">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
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
                                <a href="{{ route('register') }}" class="ms-1">
                                    <i class="fas fa-user-plus me-1"></i>Sign Up
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
    color: #667eea !important;
}

.password-toggle-btn:focus {
    outline: none !important;
    color: #667eea !important;
}

.form-floating.position-relative {
    position: relative !important;
}

.form-floating .password-toggle-btn {
    right: 12px !important;
}

/* Debug styles to make sure button is visible */
.password-toggle-btn {
    background-color: rgba(255, 0, 0, 0.1) !important; /* Temporary red background for debugging */
    border: 1px solid red !important; /* Temporary red border for debugging */
    min-width: 30px !important;
    min-height: 30px !important;
}

/* Ensure the parent container allows absolute positioning */
.form-floating {
    position: relative !important;
}
</style>

<script>
// Password visibility toggle function
function togglePasswordVisibility(fieldId) {
    console.log('Toggle called for:', fieldId);
    const passwordField = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(fieldId + '-toggle-icon');
    
    console.log('Password field:', passwordField);
    console.log('Toggle icon:', toggleIcon);
    
    if (!passwordField || !toggleIcon) {
        console.error('Elements not found');
        return;
    }
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
        console.log('Password shown');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
        console.log('Password hidden');
    }
}

// Show session messages as modern toasts and handle redirects
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    
    // Check if password toggle button exists
    const toggleBtn = document.querySelector('.password-toggle-btn');
    console.log('Toggle button found:', toggleBtn);
    
    // Check if password field exists
    const passwordField = document.getElementById('password');
    console.log('Password field found:', passwordField);
    
    // If button doesn't exist, create it manually
    if (passwordField && !toggleBtn) {
        console.log('Creating toggle button manually');
        const parent = passwordField.parentElement;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'password-toggle-btn';
        button.onclick = () => togglePasswordVisibility('password');
        button.innerHTML = '<i class="fas fa-eye-slash" id="password-toggle-icon"></i>';
        parent.appendChild(button);
        console.log('Toggle button created');
    }
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
    
    // Handle form submission and redirect after login
    const loginForm = document.querySelector('form[action*="login"]');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            // Store current redirect URL if it exists in sessionStorage
            const redirectUrl = sessionStorage.getItem('redirect_after_login');
            if (redirectUrl) {
                // Add redirect URL as hidden input
                const redirectInput = document.createElement('input');
                redirectInput.type = 'hidden';
                redirectInput.name = 'redirect_to';
                redirectInput.value = redirectUrl;
                this.appendChild(redirectInput);
                
                // Clear from sessionStorage
                sessionStorage.removeItem('redirect_after_login');
            }
        });
    }
});
</script> 

@include('footer')

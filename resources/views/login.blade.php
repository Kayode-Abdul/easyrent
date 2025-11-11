<!-- Header area start -->
@include('header')
<!-- Header area end -->
 
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Enhanced Login Section -->
<section class="login-section">
    <div class="login-container">
        <div class="login-wrapper">
            <!-- Left Side - Branding -->
            <div class="login-branding">
                <div class="branding-content">
                    <div class="logo-section">
                        <img src="/assets/images/logo.png" alt="EasyRent" class="login-logo">
                        <h1>EasyRent</h1>
                    </div>
                    <h2>Welcome Back!</h2>
                    <p>Sign in to access your property management dashboard and continue your journey with us.</p>
                    <div class="features-list">
                        <div class="feature-item">
                            <i class="fas fa-home"></i>
                            <span>Manage Properties</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users"></i>
                            <span>Connect with Tenants</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-chart-line"></i>
                            <span>Track Performance</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="login-form-section">
                <div class="form-container">
                    <div class="form-header">
                        <h3>Sign In</h3>
                        <p>Enter your credentials to access your account</p>
                    </div>

                    <!-- Messages -->
                    <div id="message">
                        @if (session('status'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                {{ session('status') }}
                            </div>
                        @endif
                        @if (session('message'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                {{ session('message') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ session('error') }}
                            </div>
                        @endif
                    </div>

                    <form method="post" id="loginForm" action="/login" class="enhanced-form">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        
                        <div class="input-group">
                            <div class="input-wrapper">
                                <i class="fas fa-envelope input-icon"></i>
                                <input name="email" class="form-input" type="email" placeholder="Email Address" required>
                                <label class="floating-label">Email Address</label>
                            </div>
                        </div>

                        <div class="input-group">
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input name="password" class="form-input" type="password" placeholder="Password" required>
                                <label class="floating-label">Password</label>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" name="remember">
                                <span class="checkmark"></span>
                                Remember me
                            </label>
                            <a href="{{ route('password.request') }}" class="forgot-link">Forgot Password?</a>
                        </div>

                        <button type="submit" class="login-btn">
                            <span class="btn-text">Sign In</span>
                            <i class="fas fa-arrow-right btn-icon"></i>
                        </button>

                        <div class="divider">
                            <span>or</span>
                        </div>

                        <div class="signup-link">
                            <p>Don't have an account? <a href="/register">Create Account</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
        <div class="row justify-content-center">

<style>
/* Enhanced Login Page Styles */
.login-section {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.login-container {
    width: 100%;
    max-width: 1200px;
}

.login-wrapper {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 600px;
}

.login-branding {
    background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
    color: white;
    padding: 60px 40px;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.login-branding::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
    animation: float 20s infinite linear;
}

@keyframes float {
    0% { transform: translate(0, 0) rotate(0deg); }
    100% { transform: translate(-50px, -50px) rotate(360deg); }
}

.branding-content {
    position: relative;
    z-index: 1;
}

.logo-section {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
}

.login-logo {
    width: 50px;
    height: 50px;
    margin-right: 15px;
}

.logo-section h1 {
    font-size: 28px;
    font-weight: 700;
    margin: 0;
}

.branding-content h2 {
    font-size: 32px;
    font-weight: 600;
    margin-bottom: 15px;
    line-height: 1.2;
}

.branding-content p {
    font-size: 16px;
    opacity: 0.9;
    margin-bottom: 40px;
    line-height: 1.6;
}

.features-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 16px;
}

.feature-item i {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    font-size: 12px;
}

.login-form-section {
    padding: 60px 40px;
    display: flex;
    align-items: center;
}

.form-container {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}

.form-header {
    text-align: center;
    margin-bottom: 40px;
}

.form-header h3 {
    font-size: 28px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-header p {
    color: #666;
    font-size: 14px;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.input-group {
    margin-bottom: 25px;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 16px;
    z-index: 2;
}

.form-input {
    width: 100%;
    padding: 16px 16px 16px 50px;
    border: 2px solid #e1e5e9;
    border-radius: 12px;
    font-size: 16px;
    background: #f8f9fa;
    transition: all 0.3s ease;
    outline: none;
}

.form-input:focus {
    border-color: #3e8189;
    background: white;
    box-shadow: 0 0 0 3px rgba(62, 129, 137, 0.1);
}

.form-input:focus + .floating-label,
.form-input:not(:placeholder-shown) + .floating-label {
    transform: translateY(-28px) scale(0.85);
    color: #3e8189;
}

.floating-label {
    position: absolute;
    left: 50px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 16px;
    pointer-events: none;
    transition: all 0.3s ease;
    background: white;
    padding: 0 5px;
}

.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    font-size: 16px;
    padding: 5px;
}

.password-toggle:hover {
    color: #3e8189;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 14px;
    color: #666;
}

.checkbox-wrapper input {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-radius: 4px;
    margin-right: 8px;
    position: relative;
    transition: all 0.3s ease;
}

.checkbox-wrapper input:checked + .checkmark {
    background: #3e8189;
    border-color: #3e8189;
}

.checkbox-wrapper input:checked + .checkmark::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.forgot-link {
    color: #3e8189;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.forgot-link:hover {
    text-decoration: underline;
}

.login-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 25px;
}

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(62, 129, 137, 0.3);
}

.login-btn:active {
    transform: translateY(0);
}

.btn-icon {
    transition: transform 0.3s ease;
}

.login-btn:hover .btn-icon {
    transform: translateX(5px);
}

.divider {
    text-align: center;
    margin: 25px 0;
    position: relative;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e1e5e9;
}

.divider span {
    background: white;
    padding: 0 15px;
    color: #999;
    font-size: 14px;
}

.signup-link {
    text-align: center;
}

.signup-link p {
    color: #666;
    font-size: 14px;
    margin: 0;
}

.signup-link a {
    color: #3e8189;
    text-decoration: none;
    font-weight: 600;
}

.signup-link a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .login-wrapper {
        grid-template-columns: 1fr;
        min-height: auto;
    }
    
    .login-branding {
        padding: 40px 30px;
        text-align: center;
    }
    
    .branding-content h2 {
        font-size: 24px;
    }
    
    .login-form-section {
        padding: 40px 30px;
    }
    
    .form-header h3 {
        font-size: 24px;
    }
}

@media (max-width: 480px) {
    .login-section {
        padding: 10px;
    }
    
    .login-branding,
    .login-form-section {
        padding: 30px 20px;
    }
}
</style>

<script>
function togglePassword() {
    const passwordInput = document.querySelector('input[name="password"]');
    const toggleIcon = document.querySelector('.password-toggle i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Form validation and enhancement
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const inputs = form.querySelectorAll('.form-input');
    
    // Add focus/blur effects
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
    
    // Form submission with loading state
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('.login-btn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnIcon = submitBtn.querySelector('.btn-icon');
        
        submitBtn.disabled = true;
        btnText.textContent = 'Signing In...';
        btnIcon.className = 'fas fa-spinner fa-spin btn-icon';
    });
});
</script>
        	<div class="col-md-10">
        		<div id="map" class="bg-white"></div>
        	</div>
        </div>
      </div>
    </section>
    
<script  src="assets/js/custom/login.js"></script>
@if (auth()->check())
    <script>window.location = '/dashboard';</script>
@endif
<!-- Footer area start -->
@include('footer')
<!-- Footer area end -->


<!-- Use Laravel Auth for login, and display errors from Auth if present. -->
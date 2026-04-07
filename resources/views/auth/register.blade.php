@include('header')

@php
$invitationToken = request('invitation_token');
$invitation = null;
$hasCompletedPayment = false;

if ($invitationToken) {
$invitation = \App\Models\ApartmentInvitation::where('invitation_token', $invitationToken)
->with(['apartment.property', 'landlord'])
->first();

if ($invitation) {
$hasCompletedPayment = \App\Models\Payment::where('payment_meta->invitation_token', $invitationToken)
->where('status', 'completed')
->whereNull('tenant_id')
->exists();
}
}
@endphp

<style>
    .navbar {
        display: none;
    }

    footer {
        display: none;

    }

    .pt-pad {
        margin-top: 0;
        margin-bottom: 0;
        padding-top: 90px;
        padding-bottom: 90px;
    }

    .auth-container {
        min-height: 100vh;
        background: linear-gradient(45deg, #17a2b8, #6bd098) !important;
        padding: 4rem 0;
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

        /* background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%) ; */
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

    .step-indicator {

        background: linear-gradient(45deg, #17a2b8, #6bd098) !important;
        color: white;
        padding: 1rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 2rem;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .form-floating {
        margin-bottom: 1.5rem;
    }

    .form-floating>.form-control,
    .form-floating>.form-select {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1rem 0.75rem;
        height: auto;
        transition: all 0.3s ease;
    }

    .form-floating>.form-control:focus,
    .form-floating>.form-select:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .form-floating>label {
        color: #6c757d;
        font-weight: 500;
    }

    .photo-upload {
        text-align: center;
        margin-bottom: 2rem;
    }

    .photo-preview {
        position: relative;
        display: inline-block;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .photo-preview:hover {
        transform: scale(1.05);
    }

    .photo-preview img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #28a745;
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }

    .photo-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(102, 126, 234, 0.8);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s ease;
        color: white;
        font-size: 1.5rem;
    }

    .photo-preview:hover .photo-overlay {
        opacity: 1;
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
    }

    .btn-auth:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    }

    .btn-secondary-auth {
        background: #6c757d;
        border: none;
        border-radius: 12px;
        padding: 0.875rem 2rem;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-secondary-auth:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }

    .social-auth-section {
        padding: 0;
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 1.5rem 0;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e9ecef;
    }

    .divider-text {
        padding: 0 1rem;
        color: #6c757d;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .btn-outline-danger:hover,
    .btn-outline-primary:hover,
    .btn-outline-dark:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .auth-links {
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e9ecef;
    }

    .auth-links a {
        color: #28a745;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .auth-links a:hover {
        color: #ffc107;
        text-decoration: underline;
    }

    /* Password toggle button styles */
    .password-toggle-btn {
        position: absolute !important;
        right: 15px !important;
        top: 38px !important;
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
        color: #28a745 !important;
    }

    .password-toggle-btn:focus {
        outline: none !important;
        color: #28a745 !important;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    /* Toast styles are now handled by the global modern-toasts.css file */

    @media (max-width: 768px) {
        .auth-container {
            padding: 3rem 1rem;
        }

        .auth-body {
            padding: 1.5rem;
        }

        .auth-header {
            padding: 1.5rem;
        }

        /* Mobile toast styles are handled by global CSS */
    }
</style>

<div class="pt-pad">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <!-- Logo above card -->
                <div class="text-center mb-4">
                    <a href="/"><img src="/assets/images/logo-small.png" alt="EasyRent Logo" style="width:80px;"></a>

                </div>

                <div class="auth-card">
                    <div class="auth-header">
                        <!-- <h2><i class="fas fa-user-plus me-2"></i>Create Account</h2>
                        <p class="mb-0 mt-2 opacity-90">Join our community today</p> -->
                    </div>

                    <div class="auth-body">

                        @if($hasCompletedPayment && $invitation)
                        <div class="alert alert-success border-0 mb-4"
                            style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fa-2x text-success me-3"></i>
                                <div>
                                    <h6 class="mb-1 text-success fw-semibold">Payment Completed!</h6>
                                    <p class="mb-0 small">You've successfully paid for your apartment at {{
                                        $invitation->apartment->property->prop_name }}. Please complete your
                                        registration below to finalize your booking.</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Social Authentication Buttons -->
                        <div class="social-auth-section mb-4">
                            <div class="text-center mb-3">
                                <p class="text-muted mb-3">Sign up with</p>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <button type="button" class="btn btn-outline-danger w-100"
                                        onclick="socialLogin('google')">
                                        <i class="fab fa-google me-1"></i> Google
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-outline-primary w-100"
                                        onclick="socialLogin('facebook')">
                                        <i class="fab fa-facebook-f me-1"></i> Facebook
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-outline-dark w-100"
                                        onclick="socialLogin('github')">
                                        <i class="fab fa-github me-1"></i> GitHub
                                    </button>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="divider">
                                    <span class="divider-text">OR</span>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data"
                            id="registerForm">
                            @csrf

                            <!-- Simplified Single Step Form -->
                            <div class="mb-3">
                                <label for="first_name" class="form-label"><i class="fas fa-user me-2"></i>First Name
                                    *</label>
                                <input id="first_name" type="text"
                                    class="form-control @error('first_name') is-invalid @enderror" name="first_name"
                                    value="{{ old('first_name', $invitationData['suggested_first_name'] ?? '') }}"
                                    required placeholder="John">
                                @error('first_name')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label"><i class="fas fa-user me-2"></i>Last Name
                                    *</label>
                                <input id="last_name" type="text"
                                    class="form-control @error('last_name') is-invalid @enderror" name="last_name"
                                    value="{{ old('last_name', $invitationData['suggested_last_name'] ?? '') }}"
                                    required placeholder="Doe">
                                @error('last_name')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label"><i class="fas fa-phone me-2"></i>Phone Number
                                    *</label>
                                <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror"
                                    name="phone" value="{{ old('phone', $invitationData['suggested_phone'] ?? '') }}"
                                    required placeholder="08123456789">
                                @error('phone')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i>Email Address
                                    *</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email', $invitationData['suggested_email'] ?? '') }}"
                                    required autocomplete="email" placeholder="johndoe@email.com">
                                @error('email')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                                @enderror
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="password" class="form-label"><i class="fas fa-lock me-2"></i>Password (min 8
                                    characters) *</label>
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    required autocomplete="new-password" placeholder="Password">
                                <button type="button" class="password-toggle-btn"
                                    onclick="togglePasswordVisibility('password')">
                                    <i class="bi bi-eye-slash" id="password-toggle-icon"></i>
                                </button>
                                @error('password')
                                <div class="invalid-feedback">
                                    <strong>{{ $message }}</strong>
                                </div>
                                @enderror
                            </div>

                            <div class="mb-3 position-relative">
                                <label for="password-confirm" class="form-label"><i class="fas fa-lock me-2"></i>Confirm
                                    Password *</label>
                                <input id="password-confirm" type="password" class="form-control"
                                    name="password_confirmation" required autocomplete="new-password"
                                    placeholder="Confirm Password">
                                <button type="button" class="password-toggle-btn"
                                    onclick="togglePasswordVisibility('password-confirm')">
                                    <i class="bi bi-eye-slash" id="password-confirm-toggle-icon"></i>
                                </button>
                            </div>

                            <div class="text-center mt-3 mb-2">
                                <small class="text-muted">* Required fields</small>
                            </div>

                            <button type="submit" class="btn btn-auth w-100">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </form>

                        <div class="auth-links">
                            <span class="text-muted">Already have an account?</span>
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
<script>
    // Password visibility toggle function
    function togglePasswordVisibility(fieldId) {
        const passwordField = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(fieldId + '-toggle-icon');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        }
    }

    // Social login function
    function socialLogin(provider) {
        // Check if Socialite is configured
        @if (!config('services.google.client_id') && !config('services.facebook.client_id') && !config('services.github.client_id'))
            showToast('Social authentication is not yet configured. Please use email registration.', 'info');
        return;
        @endif

        showToast(`Redirecting to ${provider.charAt(0).toUpperCase() + provider.slice(1)}...`, 'info');
        // Redirect to social auth route
        window.location.href = `/auth/${provider}/redirect`;
    }

    // Toast functions are now handled by the global modern-toasts.js file
    // Show server-side session messages as toast
    @if (session('status'))
        showToast("{{ session('status') }}", 'success');
    @endif
    @if (session('message'))
        showToast("{{ session('message') }}", 'success');
    @endif
    @if (session('error'))
        showToast("{{ session('error') }}", 'error');
    @endif
    @if (session('success'))
        showToast("{{ session('success') }}", 'success');
    @endif

    // Form validation
    document.getElementById('registerForm').addEventListener('submit', function (e) {
        const requiredFields = ['first_name', 'last_name', 'phone', 'email', 'password', 'password-confirm'];
        let missingFields = [];

        requiredFields.forEach(function (fieldId) {
            const field = document.getElementById(fieldId);
            if (field && !field.value.trim()) {
                field.classList.add('is-invalid');
                const label = field.previousElementSibling?.textContent || fieldId;
                missingFields.push(label.replace('*', '').trim());
            } else if (field) {
                field.classList.remove('is-invalid');
            }
        });

        if (missingFields.length > 0) {
            e.preventDefault();
            showToast(`Please fill in: ${missingFields.join(', ')}`, 'error');
            return false;
        }

        // Validate password match
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password-confirm').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            document.getElementById('password-confirm').classList.add('is-invalid');
            showToast('Password confirmation does not match', 'error');
            return false;
        }

        // Validate email format
        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            document.getElementById('email').classList.add('is-invalid');
            showToast('Please enter a valid email address', 'error');
            return false;
        }
    });

    // On page load
    document.addEventListener('DOMContentLoaded', function () {
        // Ensure toast container exists
        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'modern-toast-container';
            document.body.appendChild(container);
        }
    });
</script>

@include('footer')
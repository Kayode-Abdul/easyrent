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
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-gradient: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        --accent-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(255, 255, 255, 0.2);
        --text-primary: #1e293b;
        --text-muted: #64748b;
        --accent: #3b82f6;
    }

    body {
        font-family: 'Outfit', sans-serif;
        background: #f8fafc;
        overflow-x: hidden;
    }

    .navbar,
    footer {
        display: none !important;
    }

    .auth-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('{{ asset(' auth_background_premium_1772325387793.png') }}');
        background-size: cover;
        background-position: center;
        filter: brightness(0.5);
        z-index: -1;
    }

    .auth-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem 2rem;
    }

    .register-card-premium {
        background: var(--glass-bg);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid var(--glass-border);
        border-radius: 40px;
        box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.6);
        width: 100%;
        max-width: 600px;
        padding: 3.5rem;
        animation: slideIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(40px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .brand-logo {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .brand-logo img {
        width: 80px;
        filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.1));
    }

    .auth-title {
        color: var(--text-primary);
        font-weight: 700;
        font-size: 2.25rem;
        text-align: center;
        margin-bottom: 0.5rem;
        letter-spacing: -0.025em;
    }

    .auth-subtitle {
        color: var(--text-muted);
        text-align: center;
        margin-bottom: 2.5rem;
        font-size: 1.1rem;
    }

    .social-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 2.5rem;
    }

    .btn-social-premium {
        background: white;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        cursor: pointer;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.9rem;
        gap: 0.5rem;
    }

    .btn-social-premium:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 2rem 0;
        color: var(--text-muted);
        font-size: 0.875rem;
        font-weight: 500;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }

    .divider span {
        padding: 0 1.25rem;
    }

    .input-group-premium {
        margin-bottom: 1.25rem;
    }

    .input-group-premium label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        padding-left: 0.25rem;
    }

    .form-control-premium {
        background: rgba(255, 255, 255, 0.7);
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 0.85rem 1.25rem;
        font-size: 1rem;
        width: 100%;
        transition: all 0.3s;
        color: var(--text-primary);
    }

    .form-control-premium:focus {
        background: white;
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .custom-checkbox-wrapper {
        margin: 1.5rem 0;
    }

    .custom-checkbox {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.5);
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        transition: all 0.3s;
    }

    .custom-checkbox:hover {
        background: white;
        border-color: var(--accent);
    }

    .custom-checkbox input {
        width: 20px;
        height: 20px;
        border-radius: 6px;
        border: 2px solid #cbd5e1;
        cursor: pointer;
    }

    .artisan-panel {
        background: #f0f7ff;
        border: 1.5px solid #bfdbfe;
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.98);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .btn-premium-register {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 16px;
        padding: 1rem;
        width: 100%;
        font-weight: 600;
        font-size: 1.125rem;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .btn-premium-register:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    }

    .auth-footer {
        margin-top: 2.5rem;
        text-align: center;
        font-size: 1rem;
        color: var(--text-muted);
    }

    .auth-footer a {
        color: var(--accent);
        font-weight: 700;
        text-decoration: none;
    }

    .password-wrapper {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.5rem;
    }

    @media (max-width: 600px) {
        .register-card-premium {
            padding: 2rem;
            border-radius: 0;
        }

        .auth-wrapper {
            padding: 0;
        }
    }
</style>

<div class="auth-bg"></div>

<div class="auth-wrapper">
    <div class="register-card-premium">
        <div class="brand-logo">
            <a href="/"><img src="/assets/images/logo-small.png" alt="EasyRent Logo"></a>
        </div>

        <h1 class="auth-title">Create Account</h1>
        <p class="auth-subtitle">Join the most trusted property network.</p>

        @if($hasCompletedPayment && $invitation)
        <div class="alert alert-success border-0 mb-4"
            style="background: #ecfdf5; border: 1px solid #d1fae5; color: #065f46; border-radius: 16px; padding: 1rem;">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-check-circle fa-lg"></i>
                <div>
                    <h6 class="mb-0 fw-bold">Payment Completed!</h6>
                    <p class="mb-0 small">Finish registration for {{ $invitation->apartment->property->prop_name }}.</p>
                </div>
            </div>
        </div>
        @endif

        <div class="social-grid">
            <div class="btn-social-premium" onclick="socialLogin('google')">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18"> Google
            </div>
            <div class="btn-social-premium" onclick="socialLogin('facebook')">
                <i class="fab fa-facebook text-primary"></i> FB
            </div>
            <div class="btn-social-premium" onclick="socialLogin('github')">
                <i class="fab fa-github"></i> GitHub
            </div>
        </div>

        <div class="divider">
            <span>OR REGISTER WITH EMAIL</span>
        </div>

        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registerForm">
            @csrf
            <input type="hidden" name="role" id="user-role" value="1">

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group-premium">
                        <label>First Name</label>
                        <input type="text" name="first_name"
                            class="form-control-premium @error('first_name') is-invalid @enderror"
                            value="{{ old('first_name') }}" required placeholder="John">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group-premium">
                        <label>Last Name</label>
                        <input type="text" name="last_name"
                            class="form-control-premium @error('last_name') is-invalid @enderror"
                            value="{{ old('last_name') }}" required placeholder="Doe">
                    </div>
                </div>
            </div>

            <div class="input-group-premium">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control-premium @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" required placeholder="john@example.com">
            </div>

            <div class="input-group-premium">
                <label>Phone Number</label>
                <input type="tel" name="phone" class="form-control-premium @error('phone') is-invalid @enderror"
                    value="{{ old('phone') }}" required placeholder="+234...">
            </div>

            <div class="custom-checkbox-wrapper">
                <label class="custom-checkbox">
                    <input type="checkbox" name="is_artisan" id="is-artisan-checkbox" onchange="toggleArtisanFields()">
                    <span class="fw-semibold" style="color: var(--text-primary)">I am an Artisan / Service
                        Provider</span>
                </label>
            </div>

            <div id="artisan-fields" style="display: none;" class="artisan-panel">
                <div class="input-group-premium">
                    <label>Craft Category</label>
                    <select name="artisan_category_id" id="artisan-category" class="form-control-premium">
                        <option value="">Select your specialty</option>
                        @foreach(\App\Models\ComplaintCategory::all() as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group-premium mb-0">
                    <label>Service Description</label>
                    <textarea name="artisan_bio" class="form-control-premium" rows="2"
                        placeholder="Tell us about your services..."></textarea>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group-premium">
                        <label>Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="form-control-premium" required
                                placeholder="••••••••">
                            <button type="button" class="password-toggle"
                                onclick="togglePasswordVisibility('password')">
                                <i class="far fa-eye-slash" id="password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group-premium">
                        <label>Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password_confirmation" id="password-confirm"
                                class="form-control-premium" required placeholder="••••••••">
                            <button type="button" class="password-toggle"
                                onclick="togglePasswordVisibility('password-confirm')">
                                <i class="far fa-eye-slash" id="password-confirm-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-premium-register">
                Start Exploring <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="auth-footer">
            Already have an account?
            <a href="{{ route('login') }}">Sign In</a>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(fieldId) {
        const passwordField = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(fieldId + '-toggle-icon');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye-slash', 'far');
            toggleIcon.classList.add('fa-eye', 'fas');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye', 'fas');
            toggleIcon.classList.add('fa-eye-slash', 'far');
        }
    }

    function toggleArtisanFields() {
        const isArtisan = document.getElementById('is-artisan-checkbox').checked;
        const artisanFields = document.getElementById('artisan-fields');
        const artisanCategory = document.getElementById('artisan-category');
        const userRole = document.getElementById('user-role');
        const artisanRoleId = "{{ \App\Models\User::getRoleId('Artisan') }}";

        if (isArtisan) {
            artisanFields.style.display = 'block';
            artisanCategory.required = true;
            if (artisanRoleId) userRole.value = artisanRoleId;
        } else {
            artisanFields.style.display = 'none';
            artisanCategory.required = false;
            userRole.value = "1";
        }
    }

    function socialLogin(provider) {
        alert('Redirecting to ' + provider + '...');
        window.location.href = `/auth/${provider}/redirect`;
    }
</script>

<script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
@include('footer')
@include('header')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-gradient: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        --accent-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --glass-bg: rgba(255, 255, 255, 0.1);
        --glass-border: rgba(255, 255, 255, 0.2);
        --text-primary: #1e293b;
        --text-muted: #64748b;
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
        filter: brightness(0.6);
        z-index: -1;
    }

    .auth-bg::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at center, transparent 0%, rgba(0, 0, 0, 0.4) 100%);
    }

    .auth-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .auth-card-premium {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 30px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        width: 100%;
        max-width: 450px;
        padding: 3rem;
        animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .brand-logo {
        text-align: center;
        margin-bottom: 2rem;
    }

    .brand-logo img {
        width: 100px;
        filter: drop-shadow(0 10px 15px rgba(0, 0, 0, 0.1));
    }

    .auth-title {
        color: var(--text-primary);
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 0.5rem;
        text-align: center;
        letter-spacing: -0.025em;
    }

    .auth-subtitle {
        color: var(--text-muted);
        text-align: center;
        margin-bottom: 2.5rem;
        font-size: 1.1rem;
    }

    .input-group-premium {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .input-group-premium label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        padding-left: 0.5rem;
    }

    .input-group-premium .form-control {
        background: rgba(255, 255, 255, 0.5);
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 0.8rem 1.25rem;
        font-size: 1rem;
        color: var(--text-primary);
        transition: all 0.3s ease;
        height: auto !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .input-group-premium .form-control:focus {
        background: white;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .password-toggle {
        position: absolute;
        right: 1.25rem;
        bottom: 0.8rem;
        color: var(--text-muted);
        cursor: pointer;
        transition: color 0.3s;
        background: none;
        border: none;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .password-toggle:hover {
        color: var(--text-primary);
    }

    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        font-size: 0.875rem;
    }

    .custom-checkbox {
        display: flex;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }

    .custom-checkbox input {
        display: none;
    }

    .checkmark {
        width: 20px;
        height: 20px;
        border: 2px solid #cbd5e1;
        border-radius: 6px;
        margin-right: 10px;
        position: relative;
        transition: all 0.2s;
    }

    .custom-checkbox input:checked+.checkmark {
        background: #3b82f6;
        border-color: #3b82f6;
    }

    .custom-checkbox input:checked+.checkmark::after {
        content: '\2713';
        position: absolute;
        color: white;
        font-size: 12px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .forgot-pass {
        color: #3b82f6;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s;
    }

    .forgot-pass:hover {
        color: #2563eb;
        text-decoration: underline;
    }

    .btn-premium {
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
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        filter: brightness(1.1);
    }

    .btn-premium:active {
        transform: translateY(0);
    }

    .auth-footer {
        margin-top: 2.5rem;
        text-align: center;
        font-size: 0.95rem;
        color: var(--text-muted);
    }

    .auth-footer a {
        color: #3b82f6;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s;
    }

    .auth-footer a:hover {
        color: #2563eb;
        text-decoration: underline;
    }

    .alert-premium {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
        padding: 1rem;
        border-radius: 16px;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .invalid-feedback-premium {
        color: #dc2626;
        font-size: 0.75rem;
        margin-top: 0.375rem;
        padding-left: 0.5rem;
        font-weight: 500;
    }

    @media (max-width: 480px) {
        .auth-card-premium {
            padding: 2rem;
            border-radius: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.95);
        }

        .auth-wrapper {
            padding: 0;
        }
    }
</style>

<div class="auth-bg"></div>

<div class="auth-wrapper">
    <div class="auth-card-premium">
        <div class="brand-logo">
            <a href="/"><img src="/assets/images/logo-small.png" alt="EasyRent Logo"></a>
        </div>

        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-subtitle">Sign in to manage your spaces.</p>

        @if (session('status'))
        <div class="alert-premium">
            <i class="fas fa-check-circle"></i>
            {{ session('status') }}
        </div>
        @endif

        @if (session('error'))
        <div class="alert-premium" style="background: #fee2e2; color: #991b1b; border-color: #fecaca;">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="input-group-premium">
                <label for="email">Email Address</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                    value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Enter your email">
                @error('email')
                <div class="invalid-feedback-premium">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="input-group-premium">
                <label for="password">Password</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                    name="password" required autocomplete="current-password" placeholder="••••••••">
                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')">
                    <i class="far fa-eye-slash" id="password-toggle-icon"></i>
                </button>
                @error('password')
                <div class="invalid-feedback-premium">
                    {{ $message }}
                </div>
                @enderror
            </div>

            <div class="form-options">
                <label class="custom-checkbox">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <div class="checkmark"></div>
                    <span>Remember Me</span>
                </label>

                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-pass">Forgot Password?</a>
                @endif
            </div>

            <button type="submit" class="btn-premium">
                Sign In <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account?
            <a href="{{ route('register') }}">Create an account</a>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(fieldId) {
        const passwordField = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(fieldId + '-toggle-icon');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.remove('far');
            toggleIcon.classList.add('fa-eye');
            toggleIcon.classList.add('fas');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.remove('fas');
            toggleIcon.classList.add('fa-eye-slash');
            toggleIcon.classList.add('far');
        }
    }
</script>

<script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
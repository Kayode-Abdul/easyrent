@include('header')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-gradient: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        --accent: #3b82f6;
        --glass-bg: rgba(255, 255, 255, 0.85);
        --text-primary: #1e293b;
        --text-muted: #64748b;
    }

    body {
        font-family: 'Outfit', sans-serif;
        background: #f8fafc;
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
        padding: 2rem;
    }

    .confirm-card-premium {
        background: var(--glass-bg);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 40px;
        box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.5);
        width: 100%;
        max-width: 480px;
        padding: 4rem 3.5rem;
        text-align: center;
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
        margin-bottom: 2rem;
    }

    .brand-logo img {
        width: 80px;
    }

    .security-badge {
        width: 80px;
        height: 80px;
        background: #fff7ed;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        color: #f59e0b;
        font-size: 2rem;
        box-shadow: 0 10px 20px rgba(245, 158, 11, 0.1);
    }

    .auth-title {
        color: var(--text-primary);
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .auth-subtitle {
        color: var(--text-muted);
        line-height: 1.6;
        margin-bottom: 3rem;
        font-size: 1.05rem;
    }

    .input-group-premium {
        margin-bottom: 2rem;
        text-align: left;
    }

    .input-group-premium label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.6rem;
        padding-left: 0.25rem;
    }

    .password-wrapper {
        position: relative;
    }

    .form-control-premium {
        background: white;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 1rem 1.25rem;
        font-size: 1rem;
        width: 100%;
        transition: all 0.3s;
    }

    .form-control-premium:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
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

    .btn-premium-confirm {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 16px;
        padding: 1.1rem;
        width: 100%;
        font-weight: 600;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .btn-premium-confirm:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
        box-shadow: 0 15px 25px rgba(0, 0, 0, 0.15);
    }

    .auth-footer {
        margin-top: 2.5rem;
        padding-top: 2rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .auth-footer a {
        color: var(--text-muted);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }

    .auth-footer a:hover {
        color: var(--accent);
    }
</style>

<div class="auth-bg"></div>

<div class="auth-wrapper">
    <div class="confirm-card-premium">
        <div class="brand-logo">
            <a href="/"><img src="/assets/images/logo-small.png" alt="EasyRent Logo"></a>
        </div>

        <div class="security-badge">
            <i class="fas fa-user-shield"></i>
        </div>

        <h1 class="auth-title">Verify Identity</h1>
        <p class="auth-subtitle">
            Please confirm your password before continuing to this secure area.
        </p>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="input-group-premium">
                <label>Current Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password"
                        class="form-control-premium @error('password') is-invalid @enderror" required
                        placeholder="••••••••">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')">
                        <i class="far fa-eye-slash" id="password-toggle-icon"></i>
                    </button>
                </div>
                @error('password')
                <span class="text-danger small mt-2 d-block"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <button type="submit" class="btn-premium-confirm">
                Confirm & Continue
            </button>
        </form>

        <div class="auth-footer">
            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}">
                <i class="fas fa-key me-2"></i> Forgot Your Password?
            </a>
            @endif
            <a href="{{ route('login') }}">
                <i class="fas fa-arrow-left me-2"></i> Back to Login
            </a>
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
</script>

@include('footer')
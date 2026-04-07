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

    .reset-card-premium {
        background: var(--glass-bg);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 40px;
        box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.5);
        width: 100%;
        max-width: 480px;
        padding: 4rem 3.5rem;
        animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .brand-logo {
        text-align: center;
        margin-bottom: 2rem;
    }

    .brand-logo img {
        width: 80px;
    }

    .auth-title {
        color: var(--text-primary);
        font-weight: 700;
        font-size: 2rem;
        text-align: center;
        margin-bottom: 1rem;
    }

    .auth-subtitle {
        color: var(--text-muted);
        text-align: center;
        line-height: 1.6;
        margin-bottom: 3rem;
        font-size: 1.05rem;
    }

    .input-group-premium {
        margin-bottom: 2rem;
    }

    .input-group-premium label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.6rem;
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

    .btn-premium-reset {
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
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }

    .btn-premium-reset:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
        box-shadow: 0 15px 25px rgba(0, 0, 0, 0.15);
    }

    .auth-footer {
        margin-top: 2.5rem;
        text-align: center;
        font-size: 1rem;
        color: var(--text-muted);
        padding-top: 2rem;
        border-top: 1px solid #e2e8f0;
    }

    .auth-footer a {
        color: var(--accent);
        font-weight: 700;
        text-decoration: none;
    }
</style>

<div class="auth-bg"></div>

<div class="auth-wrapper">
    <div class="reset-card-premium">
        <div class="brand-logo">
            <a href="/"><img src="/assets/images/logo-small.png" alt="EasyRent Logo"></a>
        </div>

        <h1 class="auth-title">Reset Password</h1>
        <p class="auth-subtitle">
            Enter your email address and we'll send you a link to reset your password.
        </p>

        @if (session('status'))
        <div class="alert alert-success border-0 mb-4"
            style="background: #ecfdf5; color: #065f46; border-radius: 16px;">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="input-group-premium">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control-premium @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" required placeholder="your@email.com" autofocus>
                @error('email')
                <span class="text-danger small mt-2 d-block"><strong>{{ $message }}</strong></span>
                @enderror
            </div>

            <button type="submit" class="btn-premium-reset">
                Send Reset Link <i class="fas fa-paper-plane"></i>
            </button>
        </form>

        <div class="auth-footer">
            Remembered your password?
            <a href="{{ route('login') }}">Sign In</a>
        </div>
    </div>
</div>

@include('footer')
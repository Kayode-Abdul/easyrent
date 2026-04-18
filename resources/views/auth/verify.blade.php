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

    .verify-card-premium {
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
        animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideUp {
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
        margin-bottom: 2.5rem;
    }

    .brand-logo img {
        width: 80px;
    }

    .verify-icon-wrapper {
        width: 100px;
        height: 100px;
        background: #f0f7ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        color: var(--accent);
        font-size: 2.5rem;
        box-shadow: 0 15px 30px rgba(59, 130, 246, 0.1);
    }

    .auth-title {
        color: var(--text-primary);
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .auth-subtitle {
        color: var(--text-muted);
        line-height: 1.7;
        margin-bottom: 3rem;
        font-size: 1.05rem;
    }

    .btn-premium-verify {
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

    .btn-premium-verify:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
        box-shadow: 0 15px 25px rgba(0, 0, 0, 0.15);
    }

    .btn-premium-verify:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .auth-footer {
        margin-top: 2.5rem;
        padding-top: 2rem;
        border-top: 1px solid #e2e8f0;
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
    <div class="verify-card-premium">
        <div class="brand-logo">
            <a href="/"><img src="/assets/images/logo-small.png" alt="EasyRent Logo"></a>
        </div>

        <div class="verify-icon-wrapper">
            <i class="fas fa-paper-plane"></i>
        </div>

        <h1 class="auth-title">Verify Email</h1>
        <p class="auth-subtitle">
            A verification link was sent to your email. Please click the link to activate your account.
        </p>

        <form method="POST" action="{{ route('verification.resend') }}" id="resendForm">
            @csrf
            <button type="submit" class="btn-premium-verify" id="resendBtn">
                Resend Email <i class="fas fa-sync-alt"></i>
            </button>
        </form>

        <div class="auth-footer">
            <a href="{{ route('login') }}">
                <i class="fas fa-arrow-left me-2"></i> Back to Login
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if (session('resent'))
            alert("A fresh verification link has been sent to your email address.");
        @endif

        const resendForm = document.getElementById('resendForm');
        const resendBtn = document.getElementById('resendBtn');

        if (resendForm && resendBtn) {
            resendForm.addEventListener('submit', function (e) {
                resendBtn.disabled = true;
                resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

                setTimeout(() => {
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = 'Resend Email <i class="fas fa-sync-alt"></i>';
                }, 10000);
            });
        }
    });
</script>

@include('footer')
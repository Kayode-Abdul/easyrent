@extends('layout')

@section('title', 'Invitation Not Found')

@section('content')
<style>
.error-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    padding: 2rem 0;
}

.error-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    overflow: hidden;
    transition: all 0.3s ease;
}

.error-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

.error-icon {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.btn-modern {
    border-radius: 12px;
    padding: 0.875rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.reasons-list {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-radius: 12px;
    padding: 1.5rem;
    border-left: 4px solid #ffc107;
}

.reasons-list ul {
    margin-bottom: 0;
}

.reasons-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255, 193, 7, 0.2);
}

.reasons-list li:last-child {
    border-bottom: none;
}

.suggestions-card {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    border-radius: 12px;
    padding: 1.5rem;
    border-left: 4px solid #17a2b8;
}

@media (max-width: 768px) {
    .error-container {
        padding: 1rem;
        min-height: 70vh;
    }
    
    .error-card .card-body {
        padding: 2rem 1.5rem;
    }
    
    .error-icon {
        font-size: 3rem !important;
    }
    
    .reasons-list, .suggestions-card {
        padding: 1rem;
    }
}
</style>

<div class="container error-container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="error-card card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x error-icon mb-3"></i>
                        <h2 class="text-muted mb-3 fw-bold">Invitation Not Found</h2>
                        <p class="text-muted mb-4 lead">
                            We couldn't find the apartment invitation you're looking for. 
                            But don't worry - there are still ways to find great properties!
                        </p>
                    </div>
                    
                    <div class="reasons-list mb-4 text-start">
                        <h6 class="text-warning mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>Possible Reasons
                        </h6>
                        <ul class="list-unstyled">
                            <li>
                                <i class="fas fa-link me-2 text-warning"></i>
                                The invitation link is invalid or corrupted
                            </li>
                            <li>
                                <i class="fas fa-trash me-2 text-warning"></i>
                                The invitation has been removed by the landlord
                            </li>
                            <li>
                                <i class="fas fa-home me-2 text-warning"></i>
                                The apartment is no longer available
                            </li>
                            <li>
                                <i class="fas fa-clock me-2 text-warning"></i>
                                The invitation may have expired
                            </li>
                        </ul>
                    </div>
                    
                    <div class="suggestions-card mb-4 text-start">
                        <h6 class="text-info mb-3">
                            <i class="fas fa-lightbulb me-2"></i>What You Can Do
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-envelope me-2 text-info"></i>
                                Contact the landlord who sent you the link
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-search me-2 text-info"></i>
                                Browse available properties on our homepage
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-user-plus me-2 text-info"></i>
                                Create an account to get notified of new listings
                            </li>
                            <li class="mb-0">
                                <i class="fas fa-headset me-2 text-info"></i>
                                Contact our support team for assistance
                            </li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <a href="{{ url('/') }}" class="btn btn-primary btn-modern w-100">
                                    <i class="fas fa-search me-2"></i>Browse Properties
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('register') }}" class="btn btn-success btn-modern w-100">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </a>
                            </div>
                        </div>
                        
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-modern">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Existing Account
                        </a>
                    </div>
                    
                    <!-- Help Section -->
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-question-circle me-2"></i>Still Need Help?
                        </h6>
                        <p class="small text-muted mb-3">
                            Our support team is available to help you find the right property or resolve any issues.
                        </p>
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <p class="small mb-1">
                                    <i class="fas fa-envelope me-2"></i>
                                    <a href="mailto:support@easyrent.com" class="text-decoration-none">support@easyrent.com</a>
                                </p>
                                <p class="small mb-0">
                                    <i class="fas fa-phone me-2"></i>
                                    <a href="tel:+2348001234567" class="text-decoration-none">+234 800 EASYRENT</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
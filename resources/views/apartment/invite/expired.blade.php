@extends('layout')

@section('title', 'Invitation Expired')

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
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
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

.contact-info {
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-radius: 12px;
    padding: 1.5rem;
    border-left: 4px solid #2196f3;
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
    
    .contact-info {
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
                        <i class="fas fa-clock fa-4x error-icon mb-3"></i>
                        <h2 class="text-warning mb-3 fw-bold">Invitation Expired</h2>
                        <p class="text-muted mb-4 lead">
                            This apartment invitation has expired and is no longer valid. 
                            Don't worry - you can still get access to this amazing property!
                        </p>
                    </div>
                    
                    @if(isset($invitation) && $invitation->landlord)
                    <div class="contact-info mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-user-tie me-2"></i>Contact Your Landlord
                        </h6>
                        <div class="text-start">
                            <p class="mb-2">
                                <strong>{{ $invitation->landlord->first_name }} {{ $invitation->landlord->last_name }}</strong>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-envelope me-2 text-primary"></i>
                                <a href="mailto:{{ $invitation->landlord->email }}" class="text-decoration-none">
                                    {{ $invitation->landlord->email }}
                                </a>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-phone me-2 text-primary"></i>
                                <a href="tel:{{ $invitation->landlord->phone }}" class="text-decoration-none">
                                    {{ $invitation->landlord->phone }}
                                </a>
                            </p>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            <small>Contact them to request a new invitation link for this property.</small>
                        </div>
                    </div>
                    @endif
                    
                    <div class="d-grid gap-3">
                        @if(isset($invitation) && $invitation->landlord)
                        <a href="mailto:{{ $invitation->landlord->email }}?subject=New Apartment Invitation Request&body=Hi {{ $invitation->landlord->first_name }}, I would like to request a new invitation link for your property. The previous link has expired. Thank you!" 
                           class="btn btn-warning btn-modern">
                            <i class="fas fa-envelope me-2"></i>Email Landlord for New Link
                        </a>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <a href="{{ url('/') }}" class="btn btn-primary btn-modern w-100">
                                    <i class="fas fa-home me-2"></i>Browse Properties
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-modern w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login to Account
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Help Section -->
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-question-circle me-2"></i>Need Help?
                        </h6>
                        <p class="small text-muted mb-3">
                            If you're having trouble reaching the landlord, our support team is here to help.
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
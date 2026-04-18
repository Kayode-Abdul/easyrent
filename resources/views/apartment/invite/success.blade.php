@extends('layout')

@section('title', 'Payment Successful - Apartment Secured!')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Header -->
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-header bg-success text-white text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-check-circle fa-4x"></i>
                    </div>
                    <h2 class="mb-2">Payment Successful!</h2>
                    <p class="mb-0 fs-5">Your apartment has been secured</p>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h4 class="text-success mb-3">
                            <i class="fas fa-home me-2"></i>Welcome to Your New Home!
                        </h4>
                        <p class="lead text-muted">
                            Congratulations! Your payment has been processed successfully and your apartment has been assigned to you.
                        </p>
                    </div>

                    <!-- Apartment Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-building me-2"></i>Property Details
                                    </h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Property:</strong> {{ $invitation->apartment->property->prop_name }}</li>
                                        <li><strong>Type:</strong> {{ $invitation->apartment->apartment_type }}</li>
                                        <li><strong>Location:</strong> {{ $invitation->apartment->property->prop_address }}</li>
                                        <li><strong>Monthly Rent:</strong> {{ format_money($invitation->apartment->amount, ($invitation->apartment->property->currency->code ?? null)) }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">
                                        <i class="fas fa-calendar-alt me-2"></i>Lease Information
                                    </h6>
                                    <ul class="list-unstyled mb-0">
                                        <li><strong>Duration:</strong> 
    @php
        $duration = \App\Models\Duration::where('duration_months', $invitation->lease_duration)
            ->where('is_active', true)
            ->first();
        echo $duration ? $duration->name : $invitation->lease_duration . ' months';
    @endphp
</li>
                                        <li><strong>Move-in Date:</strong> {{ \Carbon\Carbon::parse($invitation->move_in_date)->format('M d, Y') }}</li>
                                        <li><strong>Lease End:</strong> {{ \Carbon\Carbon::parse($invitation->move_in_date)->addMonths($invitation->lease_duration)->format('M d, Y') }}</li>
                                        <li><strong>Total Paid:</strong> <span class="text-success fw-bold">{{ format_money($invitation->total_amount, ($invitation->apartment->property->currency->code ?? null)) }}</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="card border-primary mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-user-tie me-2"></i>Your Landlord
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Name:</strong> {{ $invitation->landlord->first_name }} {{ $invitation->landlord->last_name }}</p>
                                    <p class="mb-1"><strong>Email:</strong> 
                                        <a href="mailto:{{ $invitation->landlord->email }}" class="text-decoration-none">
                                            {{ $invitation->landlord->email }}
                                        </a>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Phone:</strong> 
                                        <a href="tel:{{ $invitation->landlord->phone }}" class="text-decoration-none">
                                            {{ $invitation->landlord->phone }}
                                        </a>
                                    </p>
                                    <p class="mb-0"><strong>Status:</strong> <span class="badge bg-success">Active Landlord</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="card border-info mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-list-check me-2"></i>Next Steps
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-info">Before Move-in:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>Contact your landlord to arrange key collection</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Schedule a property inspection</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Arrange utility connections (if needed)</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Plan your moving logistics</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-info">Important Reminders:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-info-circle text-info me-2"></i>Keep your payment receipt safe</li>
                                        <li><i class="fas fa-info-circle text-info me-2"></i>Review your lease agreement</li>
                                        <li><i class="fas fa-info-circle text-info me-2"></i>Note your lease end date</li>
                                        <li><i class="fas fa-info-circle text-info me-2"></i>Save landlord contact information</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="mailto:{{ $invitation->landlord->email }}" class="btn btn-outline-primary btn-lg w-100">
                                <i class="fas fa-envelope me-2"></i>Contact Landlord
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button onclick="window.print()" class="btn btn-outline-secondary btn-lg w-100">
                                <i class="fas fa-print me-2"></i>Print Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Confirmation Notice -->
            <div class="alert alert-info">
                <div class="d-flex align-items-center">
                    <i class="fas fa-envelope fa-2x text-info me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-1">Email Confirmations Sent</h6>
                        <p class="mb-0">
                            Confirmation emails have been sent to both you and your landlord with all the details of this transaction. 
                            Please check your email for the complete documentation.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Support Information -->
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="card-title">
                        <i class="fas fa-headset me-2"></i>Need Help?
                    </h6>
                    <p class="card-text">
                        If you have any questions or need assistance, our support team is here to help.
                    </p>
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>EasyRent Support:</strong></p>
                            <p class="mb-1">
                                <i class="fas fa-envelope me-2"></i>
                                <a href="mailto:support@easyrent.com" class="text-decoration-none">support@easyrent.com</a>
                            </p>
                            <p class="mb-0">
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

<style>
@media print {
    .btn, .alert, .card:last-child {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .bg-success, .bg-primary, .bg-info {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
}
</style>
@endsection
@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Success Card -->
            <div class="card border-success shadow-lg">
                <div class="card-body text-center py-5">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>

                    <!-- Success Message -->
                    <h2 class="card-title text-success mb-3">Payment Successful!</h2>
                    <p class="text-muted mb-4">Your payment has been processed successfully and your apartment has been assigned.</p>

                    <!-- Payment Details -->
                    <div class="alert alert-light border-left border-success" style="border-left-width: 4px;">
                        <div class="row text-left">
                            <div class="col-md-6 mb-3">
                                <strong>Transaction Reference:</strong><br>
                                <code class="text-success">{{ $payment->transaction_id }}</code>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Amount Paid:</strong><br>
                                <span class="text-success font-weight-bold">₦{{ number_format($payment->amount, 2) }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Duration:</strong><br>
                                {{ $payment->duration }} month{{ $payment->duration > 1 ? 's' : '' }}
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Payment Date:</strong><br>
                                {{ $payment->paid_at->format('M d, Y H:i A') }}
                            </div>
                        </div>
                    </div>

                    <!-- Apartment Details -->
                    @if($payment->apartment)
                    <div class="alert alert-info border-left border-info" style="border-left-width: 4px;">
                        <h5 class="text-left mb-3">Apartment Details</h5>
                        <div class="row text-left">
                            <div class="col-md-6 mb-2">
                                <strong>Apartment Type:</strong><br>
                                {{ $payment->apartment->apartment_type ?? 'N/A' }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Monthly Rent:</strong><br>
                                ₦{{ number_format($payment->apartment->amount, 2) }}
                            </div>
                            @if($payment->apartment->property)
                            <div class="col-12 mb-2">
                                <strong>Location:</strong><br>
                                {{ $payment->apartment->property->address ?? 'N/A' }}
                                @if($payment->apartment->property->state)
                                    , {{ $payment->apartment->property->state }}
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Landlord Contact -->
                    @if($payment->landlord)
                    <div class="alert alert-secondary border-left border-secondary" style="border-left-width: 4px;">
                        <h5 class="text-left mb-3">Landlord Information</h5>
                        <div class="row text-left">
                            <div class="col-md-6 mb-2">
                                <strong>Name:</strong><br>
                                {{ $payment->landlord->first_name }} {{ $payment->landlord->last_name }}
                            </div>
                            <div class="col-md-6 mb-2">
                                <strong>Email:</strong><br>
                                <a href="mailto:{{ $payment->landlord->email }}">{{ $payment->landlord->email }}</a>
                            </div>
                            @if($payment->landlord->phone)
                            <div class="col-md-6 mb-2">
                                <strong>Phone:</strong><br>
                                <a href="tel:{{ $payment->landlord->phone }}">{{ $payment->landlord->phone }}</a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Next Steps -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h5 class="mb-3">What's Next?</h5>
                        <ul class="text-left" style="line-height: 1.8;">
                            <li>Your apartment has been assigned to you</li>
                            <li>A confirmation email has been sent to your registered email address</li>
                            <li>The landlord has also been notified of your payment</li>
                            <li>You can view your apartment details in your dashboard</li>
                            <li>Contact the landlord to arrange move-in details</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-5">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg mr-2">
                            <i class="fas fa-home"></i> Go to Dashboard
                        </a>
                        <a href="{{ route('payment.download', $payment->id) }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-download"></i> Download Receipt
                        </a>
                    </div>
                </div>
            </div>

            <!-- Support Section -->
            <div class="mt-4 text-center text-muted">
                <p>
                    <small>
                        If you have any questions, please <a href="{{ route('contact') }}">contact our support team</a>
                        or email us at <a href="mailto:support@easyrent.com">support@easyrent.com</a>
                    </small>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 10px;
    }
    
    .alert {
        border-radius: 5px;
    }
    
    code {
        background-color: #f5f5f5;
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 0.9rem;
    }
</style>
@endsection

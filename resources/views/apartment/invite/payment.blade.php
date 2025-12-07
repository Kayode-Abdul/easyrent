@extends('layout')

@section('title', 'Complete Payment - ' . $invitation->apartment->property->prop_name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Payment Header -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>Complete Your Payment
                    </h4>
                    <p class="mb-0 opacity-75">Secure your apartment with payment</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-home me-2"></i>Apartment Details
                            </h6>
                            <ul class="list-unstyled">
                                <li><strong>Property:</strong> {{ $invitation->apartment->property->prop_name }}</li>
                                <li><strong>Type:</strong> {{ $invitation->apartment->apartment_type }}</li>
                                <li><strong>Location:</strong> {{ $invitation->apartment->property->prop_address }}</li>
                                <li><strong>Monthly Rent:</strong> ₦{{ number_format($invitation->apartment->amount) }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-calendar me-2"></i>Lease Details
                            </h6>
                            <ul class="list-unstyled">
                                <li><strong>Duration:</strong> {{ $invitation->lease_duration }} months</li>
                                <li><strong>Move-in Date:</strong> {{ \Carbon\Carbon::parse($invitation->move_in_date)->format('M d, Y') }}</li>
                                <li><strong>Total Amount:</strong> <span class="text-success fw-bold">₦{{ number_format($invitation->total_amount) }}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lock me-2"></i>Secure Payment
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    <!-- Payment Summary -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-receipt me-2"></i>Payment Summary
                            </h6>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Monthly Rent:</span>
                                        <span>₦{{ number_format($invitation->apartment->amount) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Duration:</span>
                                        <span>{{ $invitation->lease_duration }} months</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span>₦{{ number_format($invitation->total_amount) }}</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-success">Total Amount:</span>
                                        <span class="fw-bold text-success fs-5">₦{{ number_format($invitation->total_amount) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="fas fa-shield-alt fa-3x text-success mb-2"></i>
                                    <p class="small text-muted mb-0">SSL Encrypted</p>
                                    <p class="small text-muted">Secure Payment</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="mb-4">
                        <h6 class="mb-3 fw-semibold">
                            <i class="fas fa-credit-card me-2 text-primary"></i>Choose Payment Method
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border-primary payment-method-card" data-method="card" style="cursor: pointer; transition: all 0.3s ease;">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-3">
                                            <i class="fas fa-credit-card fa-3x text-primary"></i>
                                        </div>
                                        <h6 class="fw-semibold">Card Payment</h6>
                                        <p class="small text-muted mb-3">Visa, Mastercard, Verve</p>
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input" type="radio" name="payment_method" id="card_payment" value="card" checked>
                                            <label class="form-check-label ms-2 fw-semibold" for="card_payment">
                                                Select Card Payment
                                            </label>
                                        </div>
                                        <div class="mt-3">
                                            <span class="badge bg-success">Instant</span>
                                            <span class="badge bg-info">Secure</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-secondary payment-method-card" data-method="transfer" style="cursor: pointer; transition: all 0.3s ease;">
                                    <div class="card-body text-center p-4">
                                        <div class="mb-3">
                                            <i class="fas fa-university fa-3x text-secondary"></i>
                                        </div>
                                        <h6 class="fw-semibold">Bank Transfer</h6>
                                        <p class="small text-muted mb-3">Direct bank transfer</p>
                                        <div class="form-check d-flex justify-content-center">
                                            <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="transfer">
                                            <label class="form-check-label ms-2 fw-semibold" for="bank_transfer">
                                                Select Bank Transfer
                                            </label>
                                        </div>
                                        <div class="mt-3">
                                            <span class="badge bg-warning">1-2 Days</span>
                                            <span class="badge bg-info">Secure</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);">
                        <div class="form-check d-flex align-items-start">
                            <input class="form-check-input mt-1" type="checkbox" id="terms_agreement" required style="transform: scale(1.2);">
                            <label class="form-check-label ms-3" for="terms_agreement">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-shield-check text-info me-2"></i>
                                    <span class="fw-semibold">Agreement & Security</span>
                                </div>
                                <div class="small">
                                    I agree to the <a href="#" class="text-decoration-none fw-semibold">Terms and Conditions</a> and understand that this payment secures my apartment rental for the specified duration. This transaction is protected by bank-level security.
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Payment Button -->
                    <div class="d-grid mb-3">
                        <button type="button" class="btn btn-success btn-lg py-3" id="proceedPaymentBtn" onclick="processPayment()" 
                                style="border-radius: 12px; font-weight: 600; font-size: 18px;">
                            <i class="fas fa-lock me-2"></i>Pay ₦{{ number_format($invitation->total_amount) }} Securely
                        </button>
                    </div>

                    <!-- Security Features -->
                    <div class="row text-center mt-4">
                        <div class="col-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                                <small class="text-muted fw-semibold">SSL Encrypted</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fas fa-lock fa-2x text-success mb-2"></i>
                                <small class="text-muted fw-semibold">Bank Level Security</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <small class="text-muted fw-semibold">Verified Secure</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow mt-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-headset me-2"></i>Need Help?
                    </h6>
                    <p class="card-text">
                        If you have any questions about this payment or need assistance, please contact us:
                    </p>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>EasyRent Support:</strong></p>
                            <p class="mb-1"><i class="fas fa-envelope me-2"></i>support@easyrent.com</p>
                            <p class="mb-0"><i class="fas fa-phone me-2"></i>+234 800 EASYRENT</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Landlord Contact:</strong></p>
                            <p class="mb-1"><i class="fas fa-envelope me-2"></i>{{ $invitation->landlord->email }}</p>
                            <p class="mb-0"><i class="fas fa-phone me-2"></i>{{ $invitation->landlord->phone }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Mobile-First Payment Interface Enhancements */
@media (max-width: 768px) {
    .container {
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .card {
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
    
    .card-header {
        padding: 20px;
        border-radius: 16px 16px 0 0;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .payment-method-card {
        border-radius: 16px !important;
        transition: all 0.3s ease;
    }
    
    .payment-method-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .payment-method-card.selected {
        border-color: #007bff !important;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
    }
    
    .form-check-input {
        transform: scale(1.3);
    }
    
    .btn-lg {
        padding: 18px 24px;
        font-size: 18px;
        border-radius: 12px;
    }
    
    /* Touch-friendly spacing */
    .mb-4 {
        margin-bottom: 2rem !important;
    }
    
    /* Better alert styling */
    .alert {
        border-radius: 12px;
        padding: 20px;
    }
    
    /* Enhanced badge styling */
    .badge {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
    }
    
    /* Improved security icons */
    .fa-2x {
        font-size: 1.8em !important;
    }
    
    /* Better form styling */
    .form-check-label {
        font-size: 16px;
        line-height: 1.5;
    }
}

/* Enhanced hover effects for desktop */
@media (min-width: 769px) {
    .payment-method-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .btn:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
}

/* Payment method selection styling */
.payment-method-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
}

.payment-method-card.selected {
    border-color: #007bff;
    background: linear-gradient(135deg, rgba(0, 123, 255, 0.05) 0%, rgba(0, 123, 255, 0.1) 100%);
}

.payment-method-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Loading states */
.btn.loading {
    position: relative;
    color: transparent !important;
}

.btn.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    margin-left: -10px;
    margin-top: -10px;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Enhanced security section */
.security-features {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 20px;
    margin-top: 20px;
}

/* Better visual hierarchy */
.fw-semibold {
    font-weight: 600 !important;
}

/* Improved spacing for mobile */
@media (max-width: 576px) {
    .py-5 {
        padding-top: 2rem !important;
        padding-bottom: 2rem !important;
    }
    
    .row.g-3 > * {
        margin-bottom: 1rem;
    }
}

/* Enhanced form check styling */
.form-check {
    padding-left: 0;
}

.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

.form-check-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
}
</style>

<script>
function processPayment() {
    const termsChecked = document.getElementById('terms_agreement').checked;
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    const paymentBtn = document.getElementById('proceedPaymentBtn');
    
    if (!termsChecked) {
        alert('Please agree to the terms and conditions to proceed.');
        return;
    }
    
    // Disable button and show loading
    paymentBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Payment...';
    paymentBtn.disabled = true;
    
    // Create form and submit to payment gateway
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("pay") }}';
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add payment data
    const paymentData = {
        'payment_id': '{{ $payment->id }}',
        'amount': '{{ $invitation->total_amount }}',
        'email': '{{ auth()->user()->email }}',
        'payment_method': paymentMethod,
        'callback_url': '{{ route("apartment.invite.payment.callback", $invitation->invitation_token) }}',
        'metadata': JSON.stringify({
            'invitation_token': '{{ $invitation->invitation_token }}',
            'apartment_id': '{{ $invitation->apartment_id }}',
            'tenant_id': '{{ auth()->user()->user_id }}',
            'landlord_id': '{{ $invitation->landlord_id }}'
        })
    };
    
    for (const [key, value] of Object.entries(paymentData)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
}

// Enhanced payment method selection
document.addEventListener('DOMContentLoaded', function() {
    const paymentCards = document.querySelectorAll('.payment-method-card');
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    
    // Initialize selected state
    updatePaymentMethodSelection();
    
    // Handle card clicks
    paymentCards.forEach(card => {
        card.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                updatePaymentMethodSelection();
            }
        });
    });
    
    // Handle radio changes
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', updatePaymentMethodSelection);
    });
    
    function updatePaymentMethodSelection() {
        paymentCards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                card.classList.add('selected');
                card.classList.remove('border-secondary');
                card.classList.add('border-primary');
            } else {
                card.classList.remove('selected');
                card.classList.remove('border-primary');
                card.classList.add('border-secondary');
            }
        });
    }
    
    // Enhanced button interactions
    const proceedBtn = document.getElementById('proceedPaymentBtn');
    const termsCheckbox = document.getElementById('terms_agreement');
    
    // Enable/disable button based on terms agreement
    function updateButtonState() {
        if (termsCheckbox.checked) {
            proceedBtn.disabled = false;
            proceedBtn.classList.remove('btn-secondary');
            proceedBtn.classList.add('btn-success');
        } else {
            proceedBtn.disabled = true;
            proceedBtn.classList.remove('btn-success');
            proceedBtn.classList.add('btn-secondary');
        }
    }
    
    termsCheckbox.addEventListener('change', updateButtonState);
    updateButtonState(); // Initial state
});
</script>
@endsection
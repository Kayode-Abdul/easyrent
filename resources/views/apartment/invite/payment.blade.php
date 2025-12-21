@extends('layout')

@section('title', 'Complete Payment - ' . $invitation->apartment->property->prop_name)

@push('styles')
<link rel="stylesheet" href="{{ asset('public/assets/css/payment-calculation-mobile.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('public/assets/js/payment-calculation-enhanced.js') }}"></script>
@endpush

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
                                <li><strong>Duration:</strong> 
    @php
        $duration = \App\Models\Duration::where('duration_months', $invitation->lease_duration)
            ->where('is_active', true)
            ->first();
        echo $duration ? $duration->name : $invitation->lease_duration . ' months';
    @endphp
</li>
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
                    <div class="card bg-light mb-4 payment-summary-card" 
                         data-apartment-amount="{{ $invitation->apartment->amount }}" 
                         data-apartment-id="{{ $invitation->apartment->apartment_id }}"
                         data-pricing-type="{{ $invitation->apartment->getPricingType() }}">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-receipt me-2"></i>Payment Summary
                            </h6>
                            
                            <!-- Rental Duration Selection -->
                            <div class="mb-4">
                                <h6 class="mb-3 fw-semibold">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Select Rental Duration
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="duration_type" class="form-label">Duration Type</label>
                                        <select class="form-select" id="duration_type" name="duration_type" required>
                                            <option value="">Choose duration type...</option>
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly" selected>Monthly</option>
                                            <option value="quarterly">Quarterly (3 months)</option>
                                            <option value="semi_annually">Semi-Annually (6 months)</option>
                                            <option value="yearly">Yearly (12 months)</option>
                                            <option value="bi_annually">Bi-Annually (24 months)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="duration_quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" id="duration_quantity" name="duration_quantity" 
                                               value="{{ $invitation->lease_duration ?? 1 }}" min="1" max="999" required>
                                        <div class="form-text">Number of periods to rent</div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="calculate_rental_btn">
                                        <i class="fas fa-calculator me-2"></i>Calculate Total
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span id="rate_label">
                                            @if($invitation->apartment->getPricingType() === 'total')
                                                Total Rent:
                                            @else
                                                Monthly Rent:
                                            @endif
                                        </span>
                                        <span id="rate_amount">₦{{ number_format($invitation->apartment->amount) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Duration:</span>
                                        <span id="duration_display">
    @php
        $duration = \App\Models\Duration::where('duration_months', $invitation->lease_duration)
            ->where('is_active', true)
            ->first();
        echo $duration ? $duration->name : $invitation->lease_duration . ' months';
    @endphp
</span>
                                    </div>
                                    
                                    <!-- Pricing structure information -->
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Pricing Type:</span>
                                        <span class="text-info" id="pricing_type_display">
                                            {{ ucfirst($invitation->apartment->getPricingType()) }}
                                            @if($invitation->apartment->getPricingType() === 'total')
                                                <small class="text-muted">(Fixed amount)</small>
                                            @else
                                                <small class="text-muted">(Per month)</small>
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <!-- Calculation breakdown -->
                                    <div class="calculation-breakdown mb-2 p-2" id="calculation_breakdown" 
                                         style="background: rgba(0,123,255,0.05); border-radius: 6px; border-left: 3px solid #007bff;">
                                        <small class="text-muted d-block mb-1">Calculation:</small>
                                        <small class="d-flex justify-content-between" id="calculation_details">
                                            <span>₦{{ number_format($invitation->apartment->amount) }} × {{ $invitation->lease_duration }} months</span>
                                            <span>= ₦{{ number_format($invitation->total_amount) }}</span>
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span id="subtotal_amount">₦{{ number_format($invitation->total_amount) }}</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold text-success">Total Amount:</span>
                                        <span class="fw-bold text-success fs-5" id="total_amount">₦{{ number_format($invitation->total_amount) }}</span>
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
                        <button type="button" class="btn btn-success btn-lg py-3" id="proceedPaymentBtn" onclick="payWithPaystack()" 
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

<!-- Paystack Integration -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
// Generate a new reference each time to avoid duplicate transaction errors
function generateReference() {
    const timestamp = new Date().getTime();
    const random = Math.floor(Math.random() * 1000000);
    return 'easyrent_' + timestamp + '_' + random;
}

function payWithPaystack() {
    const termsChecked = document.getElementById('terms_agreement').checked;
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    const paymentBtn = document.getElementById('proceedPaymentBtn');
    
    if (!termsChecked) {
        showPaymentError('Please agree to the terms and conditions to proceed.');
        return;
    }
    
    // Disable button and show loading
    paymentBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Payment...';
    paymentBtn.disabled = true;
    
    // Hide any previous errors
    hidePaymentError();
    
    try {
        // Generate a fresh reference for this payment attempt
        const newReference = generateReference();
        
        // Get payment data - use calculated amount if available, otherwise fall back to invitation amount
        let email = @json(auth()->check() ? auth()->user()->email : ($invitation->prospect_email ?? null));
        let amount;
        
        // Check if we have a calculated amount from the enhanced rental calculation
        if (window.calculatedPaymentAmount && window.calculationDetails) {
            amount = window.calculatedPaymentAmount * 100; // Convert to kobo
            console.log('Using calculated amount:', {
                calculatedAmount: window.calculatedPaymentAmount,
                calculationMethod: window.calculationDetails.calculation_method,
                durationType: window.calculationDetails.duration_type,
                quantity: window.calculationDetails.quantity
            });
        } else {
            // Fall back to original invitation amount
            amount = @json($invitation->total_amount * 100); // Convert to kobo
            console.log('Using original invitation amount:', amount / 100);
        }
        
        // Debug logging
        console.log('Payment validation:', {
            email: email,
            amount: amount,
            isAuthenticated: @json(auth()->check()),
            prospectEmail: @json($invitation->prospect_email ?? null),
            hasCalculatedAmount: !!window.calculatedPaymentAmount
        });
        
        // Validate required fields
        if (!amount || amount <= 0) {
            showPaymentError('Invalid payment amount. Please refresh the page and try again.');
            resetPaymentButton(paymentBtn);
            return;
        }
        
        // For guest users, prompt for email if not available
        if (!email) {
            const guestEmail = prompt('Please enter your email address to proceed with payment:');
            if (!guestEmail || !guestEmail.includes('@')) {
                showPaymentError('A valid email address is required to proceed with payment.');
                resetPaymentButton(paymentBtn);
                return;
            }
            email = guestEmail;
        }
        
        const currency = 'NGN';
        const metadata = {
            invitation_token: @json($invitation->invitation_token),
            apartment_id: @json($invitation->apartment_id),
            tenant_id: @json(auth()->check() ? auth()->user()->user_id : ''),
            landlord_id: @json($invitation->landlord_id),
            payment_method: paymentMethod,
            transaction_type: 'apartment_invitation_payment'
        };
        
        // Debug: Log auth state and metadata
        console.log('Payment metadata debug:', {
            isAuthenticated: @json(auth()->check()),
            userId: @json(auth()->check() ? auth()->user()->user_id : null),
            userEmail: @json(auth()->check() ? auth()->user()->email : null),
            tenantIdInMetadata: metadata.tenant_id,
            finalMetadata: metadata
        });
        
        // Add enhanced calculation details to metadata if available
        if (window.calculationDetails) {
            metadata.enhanced_calculation = {
                duration_type: window.calculationDetails.duration_type,
                quantity: window.calculationDetails.quantity,
                calculation_method: window.calculationDetails.calculation_method,
                calculated_amount: window.calculatedPaymentAmount
            };
        }
        
        // Validate Paystack is loaded
        if (typeof PaystackPop === 'undefined') {
            throw new Error('Payment system not loaded. Please refresh the page and try again.');
        }
        
        // Validate Paystack public key
        const paystackKey = "{{ env('PAYSTACK_PUBLIC_KEY') }}";
        if (!paystackKey || paystackKey === '') {
            throw new Error('Payment system not configured. Please contact support.');
        }
        
        console.log('Initializing Paystack with:', {
            key: paystackKey.substring(0, 10) + '...',
            email: email,
            amount: amount,
            currency: currency,
            ref: newReference,
            hasEnhancedCalculation: !!window.calculationDetails
        });
        
        // Initialize Paystack
        const handler = PaystackPop.setup({
            key: paystackKey,
            email: email,
            amount: amount,
            currency: currency,
            ref: newReference,
            metadata: metadata,
            callback: function(response) {
                console.log('Payment successful:', response);
                showPaymentSuccess('Payment successful! Verifying transaction...');
                
                // Redirect to callback URL for verification
                window.location.href = "{{ route('payment.callback') }}?reference=" + response.reference;
            },
            onClose: function() {
                console.log('Payment modal closed by user');
                showPaymentInfo('Payment cancelled. You can try again when ready.');
                
                // Re-enable the button
                resetPaymentButton(paymentBtn);
            }
        });
        
        console.log('Opening Paystack payment modal...');
        // Open the payment modal
        handler.openIframe();
        
    } catch (error) {
        console.error('Payment initialization error:', error);
        showPaymentError('Error initializing payment: ' + error.message);
        
        // Re-enable the button
        resetPaymentButton(paymentBtn);
    }
}

// Payment feedback functions
function showPaymentError(message) {
    // Create or update error alert
    let errorAlert = document.getElementById('payment-error-alert');
    if (!errorAlert) {
        errorAlert = document.createElement('div');
        errorAlert.id = 'payment-error-alert';
        errorAlert.className = 'alert alert-danger mt-3';
        errorAlert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><span id="payment-error-message"></span>';
        
        const paymentCard = document.querySelector('.card.shadow .card-body');
        if (paymentCard) {
            paymentCard.appendChild(errorAlert);
        }
    }
    
    const messageSpan = document.getElementById('payment-error-message');
    if (messageSpan) {
        messageSpan.textContent = message;
    }
    
    errorAlert.style.display = 'block';
    errorAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function showPaymentSuccess(message) {
    // Create or update success alert
    let successAlert = document.getElementById('payment-success-alert');
    if (!successAlert) {
        successAlert = document.createElement('div');
        successAlert.id = 'payment-success-alert';
        successAlert.className = 'alert alert-success mt-3';
        successAlert.innerHTML = '<i class="fas fa-check-circle me-2"></i><span id="payment-success-message"></span>';
        
        const paymentCard = document.querySelector('.card.shadow .card-body');
        if (paymentCard) {
            paymentCard.appendChild(successAlert);
        }
    }
    
    const messageSpan = document.getElementById('payment-success-message');
    if (messageSpan) {
        messageSpan.textContent = message;
    }
    
    successAlert.style.display = 'block';
    hidePaymentError();
}

function showPaymentInfo(message) {
    // Create or update info alert
    let infoAlert = document.getElementById('payment-info-alert');
    if (!infoAlert) {
        infoAlert = document.createElement('div');
        infoAlert.id = 'payment-info-alert';
        infoAlert.className = 'alert alert-info mt-3';
        infoAlert.innerHTML = '<i class="fas fa-info-circle me-2"></i><span id="payment-info-message"></span>';
        
        const paymentCard = document.querySelector('.card.shadow .card-body');
        if (paymentCard) {
            paymentCard.appendChild(infoAlert);
        }
    }
    
    const messageSpan = document.getElementById('payment-info-message');
    if (messageSpan) {
        messageSpan.textContent = message;
    }
    
    infoAlert.style.display = 'block';
    hidePaymentError();
}

function hidePaymentError() {
    const errorAlert = document.getElementById('payment-error-alert');
    if (errorAlert) {
        errorAlert.style.display = 'none';
    }
}

function resetPaymentButton(paymentBtn) {
    paymentBtn.disabled = false;
    paymentBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay ₦{{ number_format($invitation->total_amount) }} Securely';
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
    
    // Enhanced rental calculation functionality
    const durationTypeSelect = document.getElementById('duration_type');
    const durationQuantityInput = document.getElementById('duration_quantity');
    const calculateBtn = document.getElementById('calculate_rental_btn');
    const apartmentId = document.querySelector('.payment-summary-card').dataset.apartmentId;
    
    // Load available rental options on page load
    loadApartmentRentalOptions();
    
    // Handle duration type changes
    durationTypeSelect.addEventListener('change', function() {
        updateDurationDisplay();
        if (this.value) {
            calculateBtn.disabled = false;
        }
    });
    
    // Handle quantity changes
    durationQuantityInput.addEventListener('input', function() {
        updateDurationDisplay();
    });
    
    // Handle calculate button click
    calculateBtn.addEventListener('click', function() {
        calculateRentalPayment();
    });
    
    // Auto-calculate when both fields are filled
    function autoCalculateIfReady() {
        if (durationTypeSelect.value && durationQuantityInput.value) {
            calculateRentalPayment();
        }
    }
    
    durationTypeSelect.addEventListener('change', autoCalculateIfReady);
    durationQuantityInput.addEventListener('input', debounce(autoCalculateIfReady, 500));
    
    function loadApartmentRentalOptions() {
        if (!apartmentId) return;
        
        fetch(`/api/apartment/${apartmentId}/rental-options`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateRentalOptionsUI(data);
                } else {
                    console.error('Failed to load rental options:', data.error);
                }
            })
            .catch(error => {
                console.error('Error loading rental options:', error);
            });
    }
    
    function updateRentalOptionsUI(data) {
        // Update duration type options based on available rates
        const availableOptions = data.available_options;
        const selectElement = durationTypeSelect;
        
        // Clear existing options except the first one
        while (selectElement.children.length > 1) {
            selectElement.removeChild(selectElement.lastChild);
        }
        
        // Add available options
        Object.keys(availableOptions).forEach(type => {
            const option = availableOptions[type];
            if (option.available) {
                const optionElement = document.createElement('option');
                optionElement.value = type;
                optionElement.textContent = `${capitalizeFirst(type.replace('_', ' '))} - ${option.formatted_rate}`;
                if (option.converted) {
                    optionElement.textContent += ' (calculated)';
                }
                selectElement.appendChild(optionElement);
            }
        });
        
        // Set default selection
        if (data.default_rental_type && availableOptions[data.default_rental_type]) {
            selectElement.value = data.default_rental_type;
            updateDurationDisplay();
        }
    }
    
    function calculateRentalPayment() {
        const durationType = durationTypeSelect.value;
        const quantity = parseInt(durationQuantityInput.value);
        
        if (!durationType || !quantity || quantity < 1) {
            showCalculationError('Please select a duration type and enter a valid quantity.');
            return;
        }
        
        // Show loading state
        calculateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Calculating...';
        calculateBtn.disabled = true;
        
        const requestData = {
            apartment_id: apartmentId,
            duration_type: durationType,
            quantity: quantity
        };
        
        fetch('/api/payment/calculate-rental', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePaymentSummary(data.calculation);
                hideCalculationError();
            } else {
                showCalculationError(data.error || 'Calculation failed');
            }
        })
        .catch(error => {
            console.error('Calculation error:', error);
            showCalculationError('Network error occurred. Please try again.');
        })
        .finally(() => {
            // Reset button state
            calculateBtn.innerHTML = '<i class="fas fa-calculator me-2"></i>Calculate Total';
            calculateBtn.disabled = false;
        });
    }
    
    function updatePaymentSummary(calculation) {
        // Update all the display elements
        document.getElementById('rate_label').textContent = `${capitalizeFirst(calculation.duration_type.replace('_', ' '))} Rate:`;
        document.getElementById('rate_amount').textContent = `₦${formatNumber(calculation.total_amount / calculation.quantity)}`;
        document.getElementById('duration_display').textContent = `${calculation.quantity} ${calculation.duration_type.replace('_', ' ')}`;
        document.getElementById('pricing_type_display').innerHTML = `Enhanced Calculation <small class="text-muted">(${calculation.calculation_method})</small>`;
        
        // Update calculation breakdown
        const breakdownElement = document.getElementById('calculation_details');
        breakdownElement.innerHTML = `
            <span>₦${formatNumber(calculation.total_amount / calculation.quantity)} × ${calculation.quantity} ${calculation.duration_type.replace('_', ' ')}</span>
            <span>= ${calculation.formatted_amount}</span>
        `;
        
        // Update totals
        document.getElementById('subtotal_amount').textContent = calculation.formatted_amount;
        document.getElementById('total_amount').textContent = calculation.formatted_amount;
        
        // Update the payment button
        const paymentBtn = document.getElementById('proceedPaymentBtn');
        paymentBtn.innerHTML = `<i class="fas fa-lock me-2"></i>Pay ${calculation.formatted_amount} Securely`;
        
        // Store the calculated amount for payment processing
        window.calculatedPaymentAmount = calculation.total_amount;
        window.calculationDetails = calculation;
    }
    
    function updateDurationDisplay() {
        const durationType = durationTypeSelect.value;
        const quantity = durationQuantityInput.value;
        
        if (durationType && quantity) {
            document.getElementById('duration_display').textContent = `${quantity} ${durationType.replace('_', ' ')}`;
        }
    }
    
    function showCalculationError(message) {
        let errorAlert = document.getElementById('calculation-error-alert');
        if (!errorAlert) {
            errorAlert = document.createElement('div');
            errorAlert.id = 'calculation-error-alert';
            errorAlert.className = 'alert alert-danger mt-3';
            errorAlert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i><span id="calculation-error-message"></span>';
            
            const summaryCard = document.querySelector('.payment-summary-card .card-body');
            if (summaryCard) {
                summaryCard.appendChild(errorAlert);
            }
        }
        
        const messageSpan = document.getElementById('calculation-error-message');
        if (messageSpan) {
            messageSpan.textContent = message;
        }
        
        errorAlert.style.display = 'block';
    }
    
    function hideCalculationError() {
        const errorAlert = document.getElementById('calculation-error-alert');
        if (errorAlert) {
            errorAlert.style.display = 'none';
        }
    }
    
    // Utility functions
    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    function formatNumber(num) {
        return new Intl.NumberFormat('en-NG').format(num);
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});
</script>
@endsection

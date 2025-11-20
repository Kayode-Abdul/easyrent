@include('header')

<div class="hero-wrap" style="background-image: url('/assets/images/bg_1.jpg'); height: 200px;">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
            <div class="col-md-9 text-center">
                <h1 class="mb-2 bread" style="color: white;">Rent Payment Request</h1>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="nc-icon nc-money-coins"></i> Payment Request Details</h3>
                    </div>
                    <div class="card-body p-4">
                        <!-- Invoice Details -->
                        <div class="invoice-details mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Tenant:</strong> {{ $invitation->tenant->first_name }} {{ $invitation->tenant->last_name }}</p>
                                    <p><strong>Email:</strong> {{ $invitation->tenant->email }}</p>
                                </div>
                                <div class="col-md-6 text-right">
                                    <h2 class="text-primary">₦{{ number_format($invitation->amount, 2) }}</h2>
                                    <p class="text-muted">Amount Due</p>
                                </div>
                            </div>
                            
                            @if($invitation->invoice_details && isset($invitation->invoice_details['message']))
                            <div class="alert alert-info mt-3">
                                <strong>Message:</strong> {{ $invitation->invoice_details['message'] }}
                            </div>
                            @endif
                        </div>

                        <hr>

                        <!-- Payment Form -->
                        <form action="{{ route('benefactor.payment.process', $invitation->token) }}" method="POST" id="paymentForm">
                            @csrf

                            <!-- Payment Type Selection -->
                            <div class="form-group">
                                <label class="font-weight-bold">How would you like to pay?</label>
                                
                                <div class="payment-type-option mb-3 p-3 border rounded" style="cursor: pointer;" onclick="selectPaymentType('one_time')">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="oneTime" name="payment_type" value="one_time" class="custom-control-input" required>
                                        <label class="custom-control-label" for="oneTime">
                                            <strong>One-Time Payment</strong>
                                            <p class="text-muted mb-0">Pay this invoice only</p>
                                        </label>
                                    </div>
                                </div>

                                <div class="payment-type-option mb-3 p-3 border rounded" style="cursor: pointer;" onclick="selectPaymentType('recurring')">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="recurring" name="payment_type" value="recurring" class="custom-control-input" required>
                                        <label class="custom-control-label" for="recurring">
                                            <strong>Recurring Payment (Recommended)</strong>
                                            <p class="text-muted mb-0">Automatically pay future rent</p>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Relationship Type -->
                            <div class="form-group">
                                <label for="relationship_type">Your Relationship to Tenant *</label>
                                <select name="relationship_type" id="relationship_type" class="form-control" required>
                                    <option value="">-- Select Relationship --</option>
                                    <option value="employer">Employer</option>
                                    <option value="parent">Parent</option>
                                    <option value="guardian">Guardian</option>
                                    <option value="sponsor">Sponsor</option>
                                    <option value="organization">Organization</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- Frequency Selection (shown only for recurring) -->
                            <div id="frequencySection" style="display: none;">
                                <div class="form-group">
                                    <label for="frequency">Payment Frequency</label>
                                    <select name="frequency" id="frequency" class="form-control">
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly (Every 3 months)</option>
                                        <option value="annually">Annually (Once a year)</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="payment_day_of_month">Preferred Payment Day of Month</label>
                                    <select name="payment_day_of_month" id="payment_day_of_month" class="form-control">
                                        <option value="">-- Select Day (Optional) --</option>
                                        @for($i = 1; $i <= 31; $i++)
                                            <option value="{{ $i }}">{{ $i }}{{ $i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')) }} of each month</option>
                                        @endfor
                                    </select>
                                    <small class="form-text text-muted">
                                        Choose when you'd like to be charged each month (e.g., on your payday)
                                    </small>
                                </div>
                            </div>

                            @if(!$isLoggedIn)
                            <!-- Guest Checkout Section -->
                            <div id="guestSection">
                                <hr>
                                <h5>Your Information</h5>
                                
                                <div class="form-group">
                                    <label for="full_name">Full Name *</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" class="form-control">
                                </div>

                                <!-- Account Creation Option (for recurring only) -->
                                <div id="accountCreationSection" style="display: none;">
                                    <div class="alert alert-warning">
                                        <i class="nc-icon nc-alert-circle-i"></i> Recurring payments require an account for security and management.
                                    </div>

                                    <div class="custom-control custom-checkbox mb-3">
                                        <input type="checkbox" class="custom-control-input" id="create_account" name="create_account" value="1">
                                        <label class="custom-control-label" for="create_account">
                                            Create an account to manage recurring payments
                                        </label>
                                    </div>

                                    <div id="passwordSection" style="display: none;">
                                        <div class="form-group">
                                            <label for="password">Password *</label>
                                            <input type="password" name="password" id="password" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label for="password_confirmation">Confirm Password *</label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <hr>

                            <!-- Submit Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="nc-icon nc-check-2"></i> Proceed to Payment
                                </button>
                            </div>

                            @if(!$isLoggedIn)
                            <div class="text-center mt-3">
                                <p class="text-muted">Already have an account? <a href="{{ route('login') }}?redirect={{ urlencode(route('benefactor.payment.show', $invitation->token)) }}">Sign In</a></p>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="text-center mt-4">
                    <p class="text-muted">
                        <i class="nc-icon nc-lock-circle-open"></i> Your payment is secure and encrypted
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function selectPaymentType(type) {
    document.getElementById('oneTime').checked = (type === 'one_time');
    document.getElementById('recurring').checked = (type === 'recurring');
    
    // Show/hide frequency section
    document.getElementById('frequencySection').style.display = (type === 'recurring') ? 'block' : 'none';
    
    @if(!$isLoggedIn)
    // Show/hide account creation for recurring
    document.getElementById('accountCreationSection').style.display = (type === 'recurring') ? 'block' : 'none';
    
    // If switching to one-time, uncheck create account
    if (type === 'one_time') {
        document.getElementById('create_account').checked = false;
        document.getElementById('passwordSection').style.display = 'none';
    }
    @endif
}

@if(!$isLoggedIn)
// Toggle password fields when create account is checked
document.getElementById('create_account').addEventListener('change', function() {
    document.getElementById('passwordSection').style.display = this.checked ? 'block' : 'none';
    
    // Make password required if creating account
    document.getElementById('password').required = this.checked;
    document.getElementById('password_confirmation').required = this.checked;
});
@endif

// Add visual feedback for payment type selection
document.querySelectorAll('.payment-type-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.payment-type-option').forEach(opt => {
            opt.classList.remove('border-primary', 'bg-light');
        });
        this.classList.add('border-primary', 'bg-light');
    });
});
</script>

<style>
.payment-type-option:hover {
    background-color: #f8f9fa;
    border-color: #007bff !important;
}

.payment-type-option.border-primary {
    border-width: 2px !important;
}
</style>

@include('footer')

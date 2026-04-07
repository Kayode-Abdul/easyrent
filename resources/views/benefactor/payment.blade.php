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

                        <!-- Display Validation Errors -->
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <!-- Display Success/Error Messages -->
                        @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                        @endif

                        @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif

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
                                            <strong>Recurring Payment</strong>
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
                            <hr>
                            
                            <!-- Authentication Options -->
                            <div class="alert alert-info border-left-info mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="nc-icon nc-bulb-63 fa-2x text-info me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Have an EasyRent Account?</h6>
                                        <p class="mb-0">Log in or sign up to track your payment history and manage recurring payments easily.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Login and Sign Up Buttons -->
                            <div class="row mb-3">
                                <div class="col-md-6 mb-2">
                                    <a href="{{ route('login') }}?redirect={{ urlencode(route('benefactor.payment.show', $invitation->token)) }}" class="btn btn-primary btn-block w-100">
                                        <i class="nc-icon nc-key-25"></i> Log In
                                    </a>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <a href="{{ route('register') }}?redirect={{ urlencode(route('benefactor.payment.show', $invitation->token)) }}" class="btn btn-success btn-block w-100">
                                        <i class="nc-icon nc-simple-add"></i> Sign Up
                                    </a>
                                </div>
                            </div>

                            <div class="text-center mb-4">
                                <span class="text-muted font-weight-bold">— OR —</span>
                            </div>

                            <!-- Guest Checkout Section -->
                            <div id="guestSection">
                                <h5>Continue as Guest</h5>
                                <p class="text-muted small mb-3">You can pay without creating an account</p>
                                
                                <div class="form-group">
                                    <label for="full_name">Full Name *</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" value="{{ old('full_name') }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $invitation->benefactor_email ?? '') }}" required>
                                    <small class="form-text text-muted">We'll send payment confirmation to this email</small>
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
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
    const passwordSection = document.getElementById('passwordSection');
    const passwordField = document.getElementById('password');
    const passwordConfirmField = document.getElementById('password_confirmation');
    
    if (this.checked) {
        passwordSection.style.display = 'block';
        passwordField.required = true;
        passwordConfirmField.required = true;
    } else {
        passwordSection.style.display = 'none';
        passwordField.required = false;
        passwordConfirmField.required = false;
        // Clear password values when unchecked
        passwordField.value = '';
        passwordConfirmField.value = '';
    }
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

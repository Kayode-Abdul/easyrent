@include('header')

<div class="hero-wrap" style="background-image: url('/assets/images/bg_1.jpg'); height: 200px;">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
            <div class="col-md-9 text-center">
                <h1 class="mb-2 bread" style="color: white;">Complete Payment</h1>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><i class="nc-icon nc-credit-card"></i> Payment Gateway</h3>
                    </div>
                    <div class="card-body p-4">
                        <!-- Payment Summary -->
                        <div class="payment-summary mb-4">
                            <h5 class="mb-3">Payment Summary</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Benefactor:</strong> {{ $payment->benefactor->full_name }}</p>
                                    <p><strong>Tenant:</strong> {{ $payment->tenant->first_name }} {{ $payment->tenant->last_name }}</p>
                                    <p><strong>Payment Type:</strong> {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</p>
                                    @if($payment->isRecurring())
                                        <p><strong>Frequency:</strong> {{ ucfirst($payment->frequency) }}</p>
                                    @endif
                                </div>
                                <div class="col-md-6 text-right">
                                    <h2 class="text-success">₦{{ number_format($payment->amount, 2) }}</h2>
                                    <p class="text-muted">Total Amount</p>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Payment Options -->
                        <div class="payment-options">
                            <h5 class="mb-3">Choose Payment Method</h5>
                            
                            <!-- Paystack Payment Button -->
                            <div class="payment-method mb-3">
                                <button type="button" class="btn btn-primary btn-lg btn-block" id="paystackBtn">
                                    <i class="nc-icon nc-credit-card"></i> Pay with Card (Paystack)
                                </button>
                            </div>

                            <!-- Bank Transfer Option -->
                            <div class="payment-method mb-3">
                                <button type="button" class="btn btn-outline-primary btn-lg btn-block" data-toggle="collapse" data-target="#bankTransferDetails">
                                    <i class="nc-icon nc-bank"></i> Pay via Bank Transfer
                                </button>
                                
                                <div id="bankTransferDetails" class="collapse mt-3">
                                    <div class="alert alert-info">
                                        <h6><strong>Bank Transfer Details:</strong></h6>
                                        <p class="mb-1"><strong>Bank Name:</strong> [Your Bank Name]</p>
                                        <p class="mb-1"><strong>Account Number:</strong> [Your Account Number]</p>
                                        <p class="mb-1"><strong>Account Name:</strong> [Your Account Name]</p>
                                        <p class="mb-1"><strong>Amount:</strong> ₦{{ number_format($payment->amount, 2) }}</p>
                                        <p class="mb-0"><strong>Reference:</strong> {{ $payment->id }}</p>
                                        <hr>
                                        <small class="text-muted">
                                            After making the transfer, please send proof of payment to support@example.com 
                                            with reference number: {{ $payment->id }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Security Notice -->
                        <div class="text-center">
                            <p class="text-muted">
                                <i class="nc-icon nc-lock-circle-open"></i> Your payment is secure and encrypted
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="text-center mt-4">
                    <p class="text-muted">
                        Need help? Contact support@example.com
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Paystack Integration Script -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('paystackBtn').addEventListener('click', function() {
    var paystackKey = '{{ config("services.paystack.public_key") }}';
    
    if (!paystackKey || paystackKey === '') {
        alert('Payment system not configured. Please contact support.');
        return;
    }
    
    var handler = PaystackPop.setup({
        key: paystackKey,
        email: '{{ $payment->benefactor->email }}',
        amount: {{ $payment->amount * 100 }}, // Amount in kobo
        currency: 'NGN',
        ref: 'BEN-{{ $payment->id }}-' + Math.floor((Math.random() * 1000000000) + 1),
        metadata: {
            payment_id: {{ $payment->id }},
            benefactor_id: {{ $payment->benefactor_id }},
            tenant_id: {{ $payment->tenant_id }},
            payment_type: '{{ $payment->payment_type }}',
            custom_fields: [
                {
                    display_name: "Payment Type",
                    variable_name: "payment_type",
                    value: "{{ ucfirst($payment->payment_type) }}"
                },
                {
                    display_name: "Benefactor",
                    variable_name: "benefactor_name",
                    value: "{{ $payment->benefactor->full_name }}"
                }
            ]
        },
        callback: function(response) {
            // Payment successful
            window.location.href = '{{ route("benefactor.payment.callback") }}?reference=' + response.reference;
        },
        onClose: function() {
            alert('Payment window closed. You can try again when ready.');
        }
    });
    handler.openIframe();
});
</script>

@include('footer')

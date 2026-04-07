@extends('layout')

@section('content')
<div class="container content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Payment for Proforma Invoice</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p><strong>Property:</strong> {{ $proforma->apartment->property->name ?? 'Property' }}</p>
                        <p><strong>Apartment:</strong> {{ $proforma->apartment->name ?? 'Apartment' }}</p>
                        <p><strong>Amount:</strong> ₦{{ number_format($proforma->total, 2) }}</p>
                        <p><strong>Invoice Number:</strong> {{ $proforma->transaction_id }}</p>
                        @if(isset($proforma->status))
                            <p><strong>Status:</strong> 
                                <span class="badge badge-{{ $proforma->status == 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($proforma->status) }}
                                </span>
                            </p>
                        @endif
                    </div>
                    
                    @if(!isset($proforma->status) || $proforma->status != 'paid')
                        <form id="paymentForm">
                            @csrf
                            <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                            <input type="hidden" name="orderID" value="{{ $proforma->id }}">
                            <input type="hidden" name="amount" value="{{ $proforma->total * 100 }}"> {{-- Amount in kobo --}}
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="currency" value="NGN">
                            <input type="hidden" name="metadata" value="{{ json_encode([
                                'proforma_id' => $proforma->id,
                                'tenant_id' => $proforma->tenant_id,
                                'landlord_id' => $proforma->user_id,
                                'apartment_id' => $proforma->apartment_id,
                                'transaction_type' => 'proforma_payment'
                            ]) }}">
                            <input type="hidden" name="reference" value="{{ Paystack::genTranxRef() }}" id="paymentReference">
                            
                            <div class="form-group row mt-4">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-success btn-lg" type="button" onclick="payWithPaystack()" id="payButton">
                                        <i class="fa fa-credit-card"></i> Pay Now
                                    </button>
                                    <p class="text-muted mt-3">
                                        <small>Click the button above to proceed with payment</small>
                                    </p>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-success text-center">
                            <i class="fa fa-check-circle fa-3x mb-3"></i>
                            <h4>Payment Completed</h4>
                            <p>This invoice has already been paid.</p>
                            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
// Generate a new reference each time to avoid duplicate transaction errors
function generateReference() {
    const timestamp = new Date().getTime();
    const random = Math.floor(Math.random() * 1000000);
    return 'PAY-' + timestamp + '-' + random;
}

function payWithPaystack() {
    // Disable the button to prevent double clicks
    const payButton = document.getElementById('payButton');
    if (payButton) {
        payButton.disabled = true;
        payButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
    }
    
    try {
        // Generate a fresh reference for this payment attempt
        const newReference = generateReference();
        document.getElementById('paymentReference').value = newReference;
        
        // Get form values
        const email = document.querySelector('input[name="email"]').value;
        const amount = document.querySelector('input[name="amount"]').value;
        const currency = document.querySelector('input[name="currency"]').value;
        const metadata = JSON.parse(document.querySelector('input[name="metadata"]').value);
        
        // Validate required fields
        if (!email || !amount) {
            if (typeof showToast === 'function') {
                showToast('Missing payment information. Please refresh the page and try again.', 'error');
            } else {
                alert('Missing payment information. Please refresh the page and try again.');
            }
            if (payButton) {
                payButton.disabled = false;
                payButton.innerHTML = '<i class="fa fa-credit-card"></i> Pay Now';
            }
            return;
        }
        
        // Initialize Paystack
        const handler = PaystackPop.setup({
            key: "{{ env('PAYSTACK_PUBLIC_KEY') }}",
            email: email,
            amount: amount,
            currency: currency,
            ref: newReference,
            metadata: metadata,
            callback: function(response) {
                // Payment successful
                if (typeof showToast === 'function') {
                    showToast('Payment successful! Verifying transaction...', 'success');
                }
                
                // Redirect to callback URL for verification
                window.location.href = "{{ route('payment.callback') }}?reference=" + response.reference;
            },
            onClose: function() {
                // User closed the payment modal
                if (typeof showToast === 'function') {
                    showToast('Payment cancelled. You can try again when ready.', 'warning');
                } else {
                    alert('Transaction was not completed, window closed.');
                }
                
                // Re-enable the button
                if (payButton) {
                    payButton.disabled = false;
                    payButton.innerHTML = '<i class="fa fa-credit-card"></i> Pay Now';
                }
            }
        });
        
        // Open the payment modal
        handler.openIframe();
        
    } catch (error) {
        console.error('Payment initialization error:', error);
        
        if (typeof showToast === 'function') {
            showToast('Error initializing payment. Please try again.', 'error');
        } else {
            alert('Error initializing payment. Please try again.');
        }
        
        // Re-enable the button
        if (payButton) {
            payButton.disabled = false;
            payButton.innerHTML = '<i class="fa fa-credit-card"></i> Pay Now';
        }
    }
}

// Ensure the button is always enabled when the page loads
document.addEventListener('DOMContentLoaded', function() {
    const payButton = document.getElementById('payButton');
    if (payButton) {
        payButton.disabled = false;
        payButton.style.display = 'inline-block';
        console.log('Pay button initialized and ready');
    }
});
</script>

<style>
#payButton {
    min-width: 200px;
    font-size: 1.1rem;
    padding: 12px 30px;
    transition: all 0.3s ease;
}

#payButton:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
}

#payButton:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
@endsection
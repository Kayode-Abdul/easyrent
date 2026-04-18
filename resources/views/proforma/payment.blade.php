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
                        <p><strong>Amount:</strong> {{ format_money($proforma->total, $proforma->currency) }}</p>
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
                        <input type="hidden" name="amount" value="{{ $proforma->total * 100 }}"> {{-- Amount in kobo
                        --}}
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="currency" value="{{ $proforma->currency->code ?? 'NGN' }}">
                        <input type="hidden" name="metadata" value="{{ json_encode([
                                'proforma_id' => $proforma->id,
                                'tenant_id' => $proforma->tenant_id,
                                'landlord_id' => $proforma->user_id,
                                'apartment_id' => $proforma->apartment_id,
                                'transaction_type' => 'proforma_payment'
                            ]) }}">
                        <input type="hidden" name="reference" value="{{ Paystack::genTranxRef() }}"
                            id="paymentReference">

                        <div class="form-group row mt-4">
                            <div class="col-md-12 text-center">
                                <h5 class="mb-3">Select Payment Method</h5>

                                <div class="payment-methods mb-4 d-flex justify-content-center flex-wrap"
                                    style="gap: 15px;">
                                    <!-- Paystack Option -->
                                    <div class="payment-method-option p-3 border rounded text-center cursor-pointer selected"
                                        onclick="selectPaymentMethod('paystack')" id="method-paystack">
                                        <i class="fa fa-credit-card fa-2x mb-2 text-primary"></i>
                                        <div class="font-weight-bold">Paystack</div>
                                        <small class="text-muted">Card, Transfer</small>
                                    </div>

                                    <!-- Flutterwave Option -->
                                    <div class="payment-method-option p-3 border rounded text-center cursor-pointer"
                                        onclick="selectPaymentMethod('flutterwave')" id="method-flutterwave">
                                        <i class="fa fa-credit-card fa-2x mb-2 text-warning"></i>
                                        <div class="font-weight-bold">Flutterwave</div>
                                        <small class="text-muted">Card, USSD</small>
                                    </div>

                                    <!-- Google Pay Option -->
                                    <div class="payment-method-option p-3 border rounded text-center cursor-pointer"
                                        onclick="selectPaymentMethod('googlepay')" id="method-googlepay">
                                        <i class="fab fa-google-pay fa-2x mb-2 text-dark"></i>
                                        <div class="font-weight-bold">Google Pay</div>
                                        <small class="text-muted">Fast & Secure</small>
                                    </div>
                                </div>

                                <input type="hidden" id="selectedPaymentMethod" value="paystack">

                                <button class="btn btn-success btn-lg" type="button" onclick="initiatePayment()"
                                    id="payButton">
                                    <i class="fa fa-lock"></i> Proceed to Pay
                                </button>
                                <p class="text-muted mt-3">
                                    <small>Click the button above to proceed securely</small>
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
<script src="https://checkout.flutterwave.com/v3.js"></script>
<script>
    // Generate a new reference each time to avoid duplicate transaction errors
    function generateReference() {
        const timestamp = new Date().getTime();
        const random = Math.floor(Math.random() * 1000000);
        return 'PAY-' + timestamp + '-' + random;
    }

    function selectPaymentMethod(method) {
        document.getElementById('selectedPaymentMethod').value = method;

        // Update UI styling
        document.querySelectorAll('.payment-method-option').forEach(el => {
            el.classList.remove('selected', 'border-primary', 'shadow-sm');
            el.style.backgroundColor = '#f8f9fa';
            el.style.borderColor = '#dee2e6';
        });

        const selectedEl = document.getElementById('method-' + method);
        selectedEl.classList.add('selected', 'border-primary', 'shadow-sm');
        selectedEl.style.backgroundColor = '#e8f4fd';
        selectedEl.style.borderColor = '#0d6efd';
    }

    function initiatePayment() {
        const method = document.getElementById('selectedPaymentMethod').value;

        // Add the gateway to metadata
        const metadataField = document.querySelector('input[name="metadata"]');
        if (metadataField) {
            let metadata = JSON.parse(metadataField.value);
            metadata.gateway = method === 'googlepay' ? 'flutterwave' : method;
            metadataField.value = JSON.stringify(metadata);
        }

        if (method === 'paystack') {
            payWithPaystack();
        } else if (method === 'flutterwave' || method === 'googlepay') {
            payWithFlutterwave(method);
        }
    }

    function payWithFlutterwave(method) {
        const payButton = document.getElementById('payButton');
        if (payButton) {
            payButton.disabled = true;
            payButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
        }

        try {
            const newReference = generateReference().replace('PAY-', 'FLW-');
            document.getElementById('paymentReference').value = newReference;

            const email = document.querySelector('input[name="email"]').value;
            const amountFull = document.querySelector('input[name="amount"]').value;
            const amountNaira = amountFull / 100; // Flutterwave expects Naira
            const currency = document.querySelector('input[name="currency"]').value;

            let metadata = JSON.parse(document.querySelector('input[name="metadata"]').value);
            metadata.custom_gateway = method;

            // Validate required fields
            if (!email || !amountNaira) {
                if (typeof showToast === 'function') {
                    showToast('Missing payment information. Please try again.', 'error');
                } else {
                    alert('Missing payment information. Please try again.');
                }
                if (payButton) {
                    payButton.disabled = false;
                    payButton.innerHTML = '<i class="fa fa-lock"></i> Proceed to Pay';
                }
                return;
            }

            FlutterwaveCheckout({
                public_key: "{{ config('flutterwave.public_key') }}",
                tx_ref: newReference,
                amount: amountNaira,
                currency: currency,
                payment_options: method === 'googlepay' ? "googlepay" : "card, ussd, banktransfer",
                customer: {
                    email: email,
                },
                meta: metadata,
                customizations: {
                    title: "EasyRent Payment",
                    description: "Proforma Invoice Payment",
                    logo: "{{ asset('assets/images/logo.png') }}",
                },
                callback: function (data) {
                    // Payment successful
                    if (typeof showToast === 'function') {
                        showToast('Payment successful! Verifying transaction...', 'success');
                    }
                    // Redirect to callback URL for verification
                    window.location.href = "{{ route('payment.callback') }}?reference=" + data.tx_ref + "&status=" + data.status + "&transaction_id=" + data.transaction_id;
                },
                onclose: function () {
                    if (typeof showToast === 'function') {
                        showToast('Payment cancelled. You can try again when ready.', 'warning');
                    } else {
                        alert('Transaction was not completed, window closed.');
                    }
                    if (payButton) {
                        payButton.disabled = false;
                        payButton.innerHTML = '<i class="fa fa-lock"></i> Proceed to Pay';
                    }
                }
            });
        } catch (error) {
            console.error('Payment initialization error:', error);
            if (typeof showToast === 'function') {
                showToast('Error initializing payment. Please try again.', 'error');
            } else {
                alert('Error initializing payment.');
            }
            if (payButton) {
                payButton.disabled = false;
                payButton.innerHTML = '<i class="fa fa-lock"></i> Proceed to Pay';
            }
        }
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
                callback: function (response) {
                    // Payment successful
                    if (typeof showToast === 'function') {
                        showToast('Payment successful! Verifying transaction...', 'success');
                    }

                    // Redirect to callback URL for verification
                    window.location.href = "{{ route('payment.callback') }}?reference=" + response.reference;
                },
                onClose: function () {
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
    document.addEventListener('DOMContentLoaded', function () {
        const payButton = document.getElementById('payButton');
        if (payButton) {
            payButton.disabled = false;
            payButton.style.display = 'inline-block';
            console.log('Pay button initialized and ready');
        }
    });
</script>

<style>
    .payment-method-option {
        min-width: 140px;
        background-color: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
    }

    .payment-method-option:hover {
        background-color: #e9ecef;
        transform: translateY(-2px);
    }

    .payment-method-option.selected {
        background-color: #e8f4fd !important;
        border-color: #0d6efd !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, .075);
    }

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
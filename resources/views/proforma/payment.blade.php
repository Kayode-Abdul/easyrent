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
                    </div>
                    
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
                        <input type="hidden" name="reference" value="{{ Paystack::genTranxRef() }}">
                        
                        <div class="form-group row mt-4">
                            <div class="col-md-12 text-center">
                                <button class="btn btn-success btn-lg" type="button" onclick="payWithPaystack()">
                                    <i class="fa fa-credit-card"></i> Pay Now
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
function payWithPaystack() {
    const handler = PaystackPop.setup({
        key: "{{ env('PAYSTACK_PUBLIC_KEY') }}",
        email: document.querySelector('input[name="email"]').value,
        amount: document.querySelector('input[name="amount"]').value,
        currency: document.querySelector('input[name="currency"]').value,
        ref: document.querySelector('input[name="reference"]').value,
        metadata: JSON.parse(document.querySelector('input[name="metadata"]').value),
        callback: function(response) {
            // Make an AJAX call to your server with the reference to verify the transaction
            window.location.href = "{{ route('payment.callback') }}?reference=" + response.reference;
        },
        onClose: function() {
            alert('Transaction was not completed, window closed.');
        }
    });
    handler.openIframe();
}
</script>
@endsection
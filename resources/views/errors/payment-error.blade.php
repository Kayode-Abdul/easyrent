@extends('layout')

@section('title', 'Payment Error')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card"></i>
                        Payment Processing Error
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>Payment could not be processed.</strong>
                        <p class="mb-0 mt-2">{{ $error_message ?? 'We encountered an issue processing your payment. Please try again or use an alternative payment method.' }}</p>
                    </div>

                    @if(isset($payment_details))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Payment Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Amount:</strong> {{ format_money($payment_details['amount'] ?? 0, ($payment_details['currency'] ?? null)) }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Reference:</strong> {{ $payment_details['reference'] ?? 'N/A' }}
                                </div>
                            </div>
                            @if(isset($payment_details['apartment_info']))
                            <div class="row mt-2">
                                <div class="col-12">
                                    <strong>Apartment:</strong> {{ $payment_details['apartment_info'] }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if(isset($recovery_options))
                    <div class="mt-4">
                        <h5>Recovery Options:</h5>
                        
                        @if($recovery_options['retry_payment'] ?? false)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Retry Payment:</strong> You can try processing the payment again.
                            @if(isset($recovery_options['retry_delay']))
                            <br><small>Please wait {{ $recovery_options['retry_delay'] }} seconds before retrying.</small>
                            @endif
                        </div>
                        @endif

                        @if($recovery_options['try_different_card'] ?? false)
                        <div class="alert alert-info">
                            <i class="fas fa-credit-card"></i>
                            <strong>Try Different Payment Method:</strong> Consider using a different card or payment method.
                        </div>
                        @endif

                        @if($recovery_options['contact_bank'] ?? false)
                        <div class="alert alert-info">
                            <i class="fas fa-phone"></i>
                            <strong>Contact Your Bank:</strong> Your bank may have declined the transaction. Please contact them for assistance.
                        </div>
                        @endif

                        @if($recovery_options['save_application'] ?? false)
                        <div class="alert alert-success">
                            <i class="fas fa-save"></i>
                            <strong>Application Saved:</strong> Your apartment application has been saved. You can complete payment later.
                        </div>
                        @endif
                    </div>
                    @endif

                    @if(isset($fallback_methods) && !empty($fallback_methods))
                    <div class="mt-4">
                        <h5>Alternative Payment Methods:</h5>
                        <div class="row">
                            @foreach($fallback_methods as $method => $details)
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $details['name'] }}</h6>
                                        <p class="card-text">
                                            <small class="text-muted">Processing Time: {{ $details['processing_time'] }}</small>
                                        </p>
                                        @if($details['available'])
                                        <button class="btn btn-outline-primary btn-sm" onclick="selectPaymentMethod('{{ $method }}')">
                                            Select This Method
                                        </button>
                                        @else
                                        <span class="badge bg-secondary">Currently Unavailable</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(isset($support_reference))
                    <div class="alert alert-light mt-4">
                        <strong>Support Reference:</strong> {{ $support_reference }}
                        <br>
                        <small class="text-muted">Please provide this reference when contacting support about this payment issue.</small>
                    </div>
                    @endif

                    <div class="mt-4 text-center">
                        @if(isset($retry_allowed) && $retry_allowed)
                        <button onclick="retryPayment()" class="btn btn-primary me-2" id="retryBtn">
                            <i class="fas fa-sync-alt"></i> Retry Payment
                        </button>
                        @endif
                        
                        @if(session('easyrent_invitation_token'))
                        <a href="{{ route('apartment.invite.show', session('easyrent_invitation_token')) }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Apartment
                        </a>
                        @endif
                        
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Go Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function retryPayment() {
    const retryBtn = document.getElementById('retryBtn');
    if (retryBtn) {
        retryBtn.disabled = true;
        retryBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        // Redirect back to payment page
        @if(isset($payment_url))
        window.location.href = '{{ $payment_url }}';
        @else
        location.reload();
        @endif
    }
}

function selectPaymentMethod(method) {
    // Handle alternative payment method selection
    console.log('Selected payment method:', method);
    // Implementation would depend on available payment methods
}

// Auto-enable retry button after delay if specified
@if(isset($recovery_options['retry_delay']))
let retryDelay = {{ $recovery_options['retry_delay'] }};
const retryBtn = document.getElementById('retryBtn');
if (retryBtn && retryDelay > 0) {
    retryBtn.disabled = true;
    let countdown = retryDelay;
    
    const updateButton = () => {
        if (countdown > 0) {
            retryBtn.innerHTML = `<i class="fas fa-clock"></i> Retry in ${countdown}s`;
            countdown--;
            setTimeout(updateButton, 1000);
        } else {
            retryBtn.disabled = false;
            retryBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Retry Payment';
        }
    };
    
    updateButton();
}
@endif
</script>
@endsection
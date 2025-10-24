@extends('layout')

@section('content')
<div class="container content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Payment Receipt</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fa fa-check-circle text-success" style="font-size: 64px;"></i>
                        <h3 class="mt-3">Payment Successful!</h3>
                        <p class="lead">Your payment has been processed successfully.</p>
                    </div>
                    
                    <div class="receipt-details p-3 border rounded">
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Transaction ID:</strong></div>
                            <div class="col-md-6">{{ $payment->transaction_id }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Amount Paid:</strong></div>
                            <div class="col-md-6">₦{{ number_format($payment->amount, 2) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Payment Date:</strong></div>
                            <div class="col-md-6">{{ $payment->paid_at->format('d M, Y H:i') }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Property:</strong></div>
                            <div class="col-md-6">{{ $payment->apartment->property->name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Apartment:</strong></div>
                            <div class="col-md-6">{{ $payment->apartment->name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Duration:</strong></div>
                            <div class="col-md-6">{{ $payment->duration }} month(s)</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><strong>Status:</strong></div>
                            <div class="col-md-6"><span class="badge bg-success">Successful</span></div>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="{{ route('payment.download', ['id' => $payment->id]) }}" class="btn btn-primary">
                            <i class="fa fa-download"></i> Download Receipt
                        </a>
                        <a href="{{ url('/dashboard') }}" class="btn btn-secondary ml-2">
                            <i class="fa fa-home"></i> Go to Dashboard
                        </a>
                    </div>
                    
                    <div class="mt-4 alert alert-info">
                        <p class="mb-0"><i class="fa fa-envelope"></i> A copy of this receipt has been sent to your email.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
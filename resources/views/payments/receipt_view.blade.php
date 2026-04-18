@extends('layout')

@section('content')
    <div class="container content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('billing.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                            <h5 class="card-title mb-0">Payment Receipt</h5>
                        </div>
                        {{-- The original content had a text-end div here, but it was incomplete and would cause syntax
                        errors.
                        Assuming the intent was to have the title and back button on the left, and potentially other
                        elements on the right.
                        For now, only the left-aligned elements are included as per the provided snippet. --}}
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h3 class="mt-2">Payment
                                {{ $payment->status === 'pending' ? 'Pending' : ($payment->status === 'failed' ? 'Failed' : 'Successful')}}!
                                <sup>
                                    <i class="fa {{ $payment->status === 'pending' ? 'fa-ban  text-warning' : ($payment->status === 'failed' ? 'fa-times-circle  text-danger' : 'fa-check-circle  text-success')}}"
                                        style="font-size: 64px;"></i>
                                </sup>
                            </h3>
                            <p class="lead">
                                {{ $payment->status === 'pending' ? 'Payment in Pending' : ($payment->status === 'failed' ? 'Payment Failed' : 'Your payment has been processed successfully.')}}
                            </p>
                        </div>

                        <div class="receipt-details p-3 border rounded">
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Transaction ID:</strong></div>
                                <div class="col-md-6">{{ $payment->transaction_id }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Amount Paid:</strong></div>
                                <div class="col-md-6">{{ format_money($payment->amount, $payment->currency->code ?? null) }}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Payment Date:</strong></div>
                                <div class="col-md-6">{{ $payment->paid_at ? $payment->paid_at->format('d M, Y H:i') :
        ($payment->created_at ? $payment->created_at->format('d M, Y H:i') : 'N/A') }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Payer:</strong></div>
                                <div class="col-md-6">{{ $payment->tenant->first_name ?? '' }} {{
        $payment->tenant->last_name ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Payee:</strong></div>
                                <div class="col-md-6">{{ $payment->landlord->first_name ?? '' }} {{
        $payment->landlord->last_name ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Property & Address:</strong></div>
                                <div class="col-md-6">
                                    {{ $payment->apartment->property->address ?? 'N/A' }},
                                    {{ $payment->apartment->property->lga ?? '' }},
                                    {{ $payment->apartment->property->state ?? '' }}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Property:</strong></div>
                                <div class="col-md-6">{{ $payment->apartment->property->getPropertyTypeName() ?? 'N/A' }} —
                                    {{ $payment->apartment->property->address ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Apartment:</strong></div>
                                <div class="col-md-6">{{ $payment->apartment->apartment_type ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Duration:</strong></div>
                                <div class="col-md-6">{{ $payment->duration }} month(s)</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Payment Method:</strong></div>
                                <div class="col-md-6">{{ ucfirst($payment->payment_method ?? 'N/A') }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Payment Reference:</strong></div>
                                <div class="col-md-6">{{ $payment->payment_reference ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Status:</strong></div>
                                <div class="col-md-6"><span
                                        class="badge {{ $payment->status === 'pending' ? 'bg-warning' : ($payment->status === 'failed' ? 'bg-danger text-white' : 'bg-success')}}">{{ ucfirst($payment->status) }}</span>
                                </div>
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

                        <div class="mt-4 text-center">
                            <h6 class="text-muted mb-3">Share Receipt Via:</h6>
                            <div class="d-flex justify-content-center gap-2">
                                @php
                                    $shareUrl = route('payment.receipt.reference', ['reference' => $payment->transaction_id]);
                                    $amountStr = format_money($payment->amount, $payment->currency->code ?? null);
                                    $propertyName = $payment->apartment->property->prop_name ?? 'Property';
                                    $shareText = "Hello! Here is my payment receipt for {$propertyName} (Ref: {$payment->transaction_id}). Amount: {$amountStr}. View it here: " . $shareUrl;
                                    
                                    $whatsappUrl = "https://wa.me/?text=" . urlencode($shareText);
                                    $emailUrl = "mailto:?subject=" . urlencode("Payment Receipt - " . $payment->transaction_id) . "&body=" . urlencode($shareText);
                                    $smsUrl = "sms:?body=" . urlencode($shareText);
                                @endphp
                                
                                <a href="{{ $whatsappUrl }}" target="_blank" class="btn btn-success btn-sm share-btn" title="Share on WhatsApp">
                                    <i class="fa fa-whatsapp"></i> WhatsApp
                                </a>
                                <a href="{{ $emailUrl }}" class="btn btn-info btn-sm share-btn" title="Share via Email">
                                    <i class="fa fa-envelope"></i> Email
                                </a>
                                <a href="{{ $smsUrl }}" class="btn btn-warning btn-sm share-btn" title="Share via SMS">
                                    <i class="fa fa-commenting"></i> SMS
                                </a>
                            </div>
                        </div>

                        <div class="mt-4 alert alert-info">
                            <p class="mb-0"><i class="fa fa-envelope"></i> A copy of this receipt has been sent to your
                                email.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
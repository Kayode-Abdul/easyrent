@extends('layout')


@section('content')
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<meta name="csrf-token" content="{{ csrf_token() }}"> -->
@php
    $landlord = $proforma->owner;
    $tenant = $proforma->tenant;
    $apartment = $proforma->apartment;
    $property = $apartment->property ?? null;
    // Ensure apartment amount is available or default to 0
    $apartmentAmount = $apartment->amount ?? 0;
@endphp

<div class="content" style="max-width:700px;margin:0 auto;font-family:sans-serif;">
    <h2 style="text-align:center;">Rent/Service Invoice (Estimated)</h2>
    
    @if(auth()->user()->user_id === $proforma->tenant_id)
        <div id="proforma-status" style="text-align:center;margin-bottom:15px;">
            @if($proforma->status === \App\Models\ProfomaReceipt::STATUS_NEW)
                <div style="background:#f8f9fa;padding:10px;border-radius:5px;margin-bottom:10px;">
                    <p style="margin:0;">Please review this proforma invoice and respond below.</p>
                </div>
            @elseif($proforma->status === \App\Models\ProfomaReceipt::STATUS_CONFIRMED)
                <div style="background:#d4edda;padding:10px;border-radius:5px;margin-bottom:10px;">
                    <p style="margin:0;color:#155724;">You have accepted this proforma invoice.</p>
                </div>
            @elseif($proforma->status === \App\Models\ProfomaReceipt::STATUS_REJECTED)
                <div style="background:#f8d7da;padding:10px;border-radius:5px;margin-bottom:10px;">
                    <p style="margin:0;color:#721c24;">You have rejected this proforma invoice.</p>
                </div>
            @endif
        </div>
    @endif
    
    <table style="width:100%;margin-bottom:20px;">
        <tr>
            <td>
                <strong>Landlord:</strong> {{ $landlord->first_name }} {{ $landlord->last_name }}<br>
                <strong>Address:</strong> {{ $property->address ?? 'N/A' }}<br>
                <strong>Phone:</strong> {{ $landlord->phone ?? 'N/A' }}<br>
                <strong>Email:</strong> {{ $landlord->email ?? 'N/A' }}
            </td>
            <td style="text-align:right;">
                <strong>Tenant:</strong> {{ $tenant->first_name }} {{ $tenant->last_name }}<br>
                <strong>Address:</strong> {{ $tenant->address ?? 'N/A' }}<br>
                <strong>Date:</strong> {{ $proforma->created_at ? $proforma->created_at->format('d/m/Y') : now()->format('d/m/Y') }}<br>
                <strong>Invoice No.:</strong> {{ $proforma->transaction_id }}
            </td>
        </tr>
    </table>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#f5f5f5;">
                <th style="border:1px solid #ccc;padding:8px;text-align:left;">Description</th>
                <th style="border:1px solid #ccc;padding:8px;text-align:right;">Amount (₦)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border:1px solid #ccc;padding:8px;">Monthly Rent @if($proforma->duration) ({{ $proforma->duration }} months) @endif</td>
                <td style="border:1px solid #ccc;padding:8px;text-align:right;">{{ number_format(($proforma->amount ?? $proforma->total / $proforma->duration), 2) }}</td>
            </tr>
            @if($proforma->security_deposit)
            <tr>
                <td style="border:1px solid #ccc;padding:8px;">Security Deposit</td>
                <td style="border:1px solid #ccc;padding:8px;text-align:right;">{{ number_format($proforma->security_deposit, 2) }}</td>
            </tr>
            @endif
            @if($proforma->water)
            <tr>
                <td style="border:1px solid #ccc;padding:8px;">Water</td>
                <td style="border:1px solid #ccc;padding:8px;text-align:right;">{{ number_format($proforma->water, 2) }}</td>
            </tr>
            @endif
            @if($proforma->internet)
            <tr>
                <td style="border:1px solid #ccc;padding:8px;">Internet</td>
                <td style="border:1px solid #ccc;padding:8px;text-align:right;">{{ number_format($proforma->internet, 2) }}</td>
            </tr>
            @endif
            @if($proforma->generator)
            <tr>
                <td style="border:1px solid #ccc;padding:8px;">Generator</td>
                <td style="border:1px solid #ccc;padding:8px;text-align:right;">{{ number_format($proforma->generator, 2) }}</td>
            </tr>
            @endif
            @if($proforma->other_charges_desc || $proforma->other_charges_amount)
            <tr>
                <td style="border:1px solid #ccc;padding:8px;">Other Charges: {{ $proforma->other_charges_desc }}</td>
                <td style="border:1px solid #ccc;padding:8px;text-align:right;">{{ number_format($proforma->other_charges_amount, 2) }}</td>
            </tr>
            @endif
            <tr style="font-weight:bold;background:#f5f5f5;">
                <td style="border:1px solid #ccc;padding:8px;">Total</td>
                <td style="border:1px solid #ccc;padding:8px;text-align:right;">{{ number_format($proforma->total, 2) }}</td>
            </tr>
        </tbody>
    </table>
    <p style="margin-top:20px;font-size:14px;color:#555;">
        <strong>Note:</strong> This is an estimate/statement before payment. Upon receipt of the payment, a formal receipt will be issued.
    </p>
    
    @if(auth()->user()->user_id === $proforma->tenant_id && $proforma->status !== \App\Models\ProfomaReceipt::STATUS_REJECTED)
        <div id="tenant-response-buttons" style="margin-top:30px;text-align:center;">
            @if($proforma->status === \App\Models\ProfomaReceipt::STATUS_NEW)
                <button id="accept-proforma" class="btn-accept" style="background:#28a745;color:white;border:none;padding:10px 20px;margin-right:10px;border-radius:5px;cursor:pointer;">
                    Accept Proforma
                </button>
                <button id="reject-proforma" class="btn-reject" style="background:#dc3545;color:white;border:none;padding:10px 20px;border-radius:5px;cursor:pointer;">
                    Reject Proforma
                </button>
            @elseif($proforma->status === \App\Models\ProfomaReceipt::STATUS_CONFIRMED)
                <p style="color:#28a745;font-weight:bold;">You have accepted this proforma.</p>
            @endif
        </div>
    @endif
</div>

<script>
$(document).ready(function() {
    $('#accept-proforma').on('click', function() {
        Swal.fire({
            title: 'Accept Proforma',
            text: 'Are you sure you want to accept this proforma invoice?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, accept it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("proforma.accept", ["id" => $proforma->id]) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Accepted!',
                                'You have accepted the proforma invoice. Redirecting to payment...',
                                'success'
                            ).then(() => {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message || 'Something went wrong.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Failed to process your request.',
                            'error'
                        );
                    }
                });
            }
        });
    });
    
    $('#reject-proforma').on('click', function() {
        Swal.fire({
            title: 'Reject Proforma',
            text: 'Are you sure you want to reject this proforma invoice?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, reject it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("proforma.reject", ["id" => $proforma->id]) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Rejected!',
                                'You have rejected the proforma invoice.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message || 'Something went wrong.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Failed to process your request.',
                            'error'
                        );
                    }
                });
            }
        });
    });
}); 
</script>
@endsection
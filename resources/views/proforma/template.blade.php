@extends('layout')


@section('content') 
@php
    $landlord = $proforma->owner;
    $tenant = $proforma->tenant;
    $apartment = $proforma->apartment;
    $property = $apartment->property ?? null;
    // Ensure apartment amount is available or default to 0
    $apartmentAmount = $apartment->amount ?? 0;
@endphp

<div class="content" style="max-width:700px;margin:90px auto;font-family:sans-serif;">
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
                <button id="reject-proforma" class="btn-reject" style="background:#dc3545;color:white;border:none;padding:10px 20px;margin-right:10px;border-radius:5px;cursor:pointer;">
                    Reject Proforma
                </button>
                <button id="invite-benefactor" class="btn-benefactor" style="background:#6f42c1;color:white;border:none;padding:10px 20px;border-radius:5px;cursor:pointer;">
                    <i class="fas fa-user-plus"></i> Invite Someone to Pay
                </button>
            @elseif($proforma->status === \App\Models\ProfomaReceipt::STATUS_CONFIRMED)
                @if($proforma->hasSuccessfulPayment())
                    @php $completedPayment = $proforma->getSuccessfulPayment(); @endphp
                    <div style="background:#d4edda;padding:15px;border-radius:5px;margin-bottom:10px;">
                        <p style="margin:0;color:#155724;font-weight:bold;">✅ Payment Completed</p>
                        <p style="margin:5px 0 0 0;color:#155724;font-size:14px;">
                            Payment Reference: {{ $completedPayment->payment_reference ?? $completedPayment->transaction_id }}
                        </p>
                        <p style="margin:5px 0 0 0;color:#155724;font-size:14px;">
                            Amount Paid: ₦{{ number_format($completedPayment->amount, 2) }}
                        </p>
                    </div>
                @else
                    <div style="background:#fff3cd;padding:15px;border-radius:5px;margin-bottom:10px;">
                        <p style="margin:0;color:#856404;font-weight:bold;">⏳ Payment Pending</p>
                        <p style="margin:5px 0 0 0;color:#856404;font-size:14px;">
                            You have accepted this proforma. Please proceed to complete payment.
                        </p>
                    </div>
                    
                    <div style="text-align:center;margin-top:20px;">
                        <a href="{{ route('proforma.payment.form', ['id' => $proforma->id]) }}" 
                           class="btn-payment" 
                           style="background:#007bff;color:white;border:none;padding:12px 30px;border-radius:5px;text-decoration:none;display:inline-block;font-weight:bold;transition:background-color 0.3s ease;">
                            💳 Proceed to Payment
                        </a>
                        
                        @php $failedPayments = $proforma->payments()->where('status', \App\Models\Payment::STATUS_FAILED); @endphp
                        @if($failedPayments->count() > 0)
                            <p style="margin-top:10px;color:#dc3545;font-size:12px;">
                                Previous payment attempts: {{ $failedPayments->count() }} failed
                            </p>
                        @endif
                    </div>
                @endif
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

    $('#invite-benefactor').on('click', function() {
        Swal.fire({
            title: 'Invite Someone to Pay',
            html: `
                <div style="text-align:left;">
                    <p style="margin-bottom:15px;">Invite a benefactor (employer, parent, sponsor, etc.) to pay this proforma on your behalf.</p>
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Benefactor Email *</label>
                    <input type="email" id="benefactor-email" class="swal2-input" placeholder="benefactor@example.com" style="width:100%;margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:bold;">Message (Optional)</label>
                    <textarea id="benefactor-message" class="swal2-textarea" placeholder="Add a personal message..." style="width:100%;height:80px;"></textarea>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#6f42c1',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Send Invitation',
            preConfirm: () => {
                const email = document.getElementById('benefactor-email').value;
                const message = document.getElementById('benefactor-message').value;
                
                if (!email) {
                    Swal.showValidationMessage('Please enter benefactor email');
                    return false;
                }
                
                // Basic email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    Swal.showValidationMessage('Please enter a valid email address');
                    return false;
                }
                
                return { email: email, message: message };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("tenant.invite.benefactor") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        benefactor_email: result.value.email,
                        proforma_id: {{ $proforma->id }},
                        amount: {{ $proforma->total }},
                        message: result.value.message
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Invitation Sent!',
                                'The benefactor will receive an email with payment instructions.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message || 'Failed to send invitation.',
                                'error'
                            );
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Failed to send invitation.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire(
                            'Error!',
                            errorMsg,
                            'error'
                        );
                    }
                });
            }
        });
    });
}); 

// Add hover effect for payment button
$('.btn-payment').hover(
    function() {
        $(this).css('background-color', '#0056b3');
    },
    function() {
        $(this).css('background-color', '#007bff');
    }
);// Add hover effect for payment button
$(document).ready(function() {
    $('.btn-payment').hover(
        function() {
            $(this).css('background-color', '#0056b3');
        },
        function() {
            $(this).css('background-color', '#007bff');
        }
    );
});
</script>

<style>
.btn-payment:hover {
    background-color: #0056b3 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,123,255,0.3);
}

.btn-payment:active {
    transform: translateY(0);
}
</style>
@endsection

<!--<function_calls>-->
<!--<invoke name="strReplace">-->
<!--<parameter name="oldStr">        return response()->json([-->
<!--            'success' => true,-->
<!--            'message' => 'Proforma rejected successfully!'-->
<!--        ]);-->
<!--    }-->
<!--}turn response()->json([-->
<!--            'success' => true,-->
<!--            'message' => 'Proforma rejected successfully!'-->
<!--        ]);-->
<!--    }-->

<!--    /**-->
<!--     * Check payment status for a proforma-->
<!--     */-->
<!--    public function checkPaymentStatus($id)-->
<!--    {-->
<!--        $proforma = ProfomaReceipt::findOrFail($id);-->
<!--        $user = Auth::user();-->
        
<!--        // Check if user has permission to view this proforma-->
<!--        if ($user->user_id !== $proforma->user_id && $user->user_id !== $proforma->tenant_id) {-->
<!--            return response()->json([-->
<!--                'success' => false,-->
<!--                'message' => 'Unauthorized'-->
<!--            ], 403);-->
<!--        }-->
        
<!--        $hasPayment = $proforma->hasSuccessfulPayment();-->
<!--        $payment = $proforma->getSuccessfulPayment();-->
        
<!--        return response()->json([-->
<!--            'success' => true,-->
<!--            'has_payment' => $hasPayment,-->
<!--            'payment' => $payment ? [-->
<!--                'reference' => $payment->payment_reference ?? $payment->transaction_id,-->
<!--                'amount' => $payment->amount,-->
<!--                'status' => $payment->status,-->
<!--                'paid_at' => $payment->paid_at-->
<!--            ] : null-->
<!--        ]);-->
<!--    }-->
<!--}-->
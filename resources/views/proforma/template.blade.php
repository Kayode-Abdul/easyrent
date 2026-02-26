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
                <td style="border:1px solid #ccc;padding:8px;">
                    @if($proforma->apartment && $proforma->apartment->getPricingType() === 'total')
                        Total Rent @if($proforma->duration) ({{ $proforma->duration }} months) @endif
                    @else
                        Monthly Rent @if($proforma->duration) ({{ $proforma->duration }} months) @endif
                    @endif
                </td>
                <td style="border:1px solid #ccc;padding:8px;text-align:right;">{{ number_format(($proforma->amount ?? optional($proforma->apartment)->amount ?? 0), 2) }}</td>
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
        <strong>Note:</strong> This is an estimate/statement before payment. Upon receipt of the payment, a formal receipt w
    
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
        // First, generate the payment link
        $.ajax({
            url: '{{ route("tenant.generate.benefactor.link") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                proforma_id: {{ $proforma->id }},
                amount: {{ $proforma->total }}
            },
            success: function(response) {
                if (response.success && response.payment_link) {
                    showInviteModal(response.payment_link, response.invitation_token);
                } else {
                    Swal.fire('Error!', 'Couldn\'t generate payment link.', 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Failed to generate payment link. request a new proforma from your landlord', 'error');
            }
        });
    });

    function showInviteModal(paymentLink, invitationToken) {
        const amount = '₦{{ number_format($proforma->total, 2) }}';
        const propertyName = {!! json_encode($proforma->property->apartment->address ?? "Property") !!};
        const tenantName = {!! json_encode(auth()->user()->first_name . ' ' . auth()->user()->last_name) !!};
        
        Swal.fire({
            title: 'Share Payment Request',
            html: `
                <div style="text-align:center;">
                    <p style="margin-bottom:30px;color:#6c757d;font-size:16px;">Choose how to share this payment request with your benefactor</p>
                    
                    <!-- Share Options as Clickable Icons -->
                    <div style="display:flex;justify-content:center;gap:20px;margin-bottom:30px;flex-wrap:wrap;">
                        
                        <!-- WhatsApp Icon -->
                        <div class="share-option" data-method="whatsapp" style="text-align:center;cursor:pointer;padding:20px;border-radius:15px;background:#f8f9fa;border:2px solid #e9ecef;transition:all 0.3s ease;min-width:120px;">
                            <div style="font-size:48px;color:#25D366;margin-bottom:10px;">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div style="font-weight:bold;color:#333;margin-bottom:5px;">WhatsApp</div>
                            <div style="font-size:12px;color:#6c757d;">Send via WhatsApp</div>
                        </div>
                        
                        <!-- Email Icon -->
                        <div class="share-option" data-method="email" style="text-align:center;cursor:pointer;padding:20px;border-radius:15px;background:#f8f9fa;border:2px solid #e9ecef;transition:all 0.3s ease;min-width:120px;">
                            <div style="font-size:48px;color:#dc3545;margin-bottom:10px;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div style="font-weight:bold;color:#333;margin-bottom:5px;">Email</div>
                            <div style="font-size:12px;color:#6c757d;">Send via Email</div>
                        </div>
                        
                        <!-- SMS Icon -->
                        <div class="share-option" data-method="sms" style="text-align:center;cursor:pointer;padding:20px;border-radius:15px;background:#f8f9fa;border:2px solid #e9ecef;transition:all 0.3s ease;min-width:120px;">
                            <div style="font-size:48px;color:#007bff;margin-bottom:10px;">
                                <i class="fas fa-sms"></i>
                            </div>
                            <div style="font-weight:bold;color:#333;margin-bottom:5px;">SMS</div>
                            <div style="font-size:12px;color:#6c757d;">Send via SMS</div>
                        </div>
                        
                        <!-- Copy Link Icon -->
                        <div class="share-option" data-method="copy" style="text-align:center;cursor:pointer;padding:20px;border-radius:15px;background:#f8f9fa;border:2px solid #e9ecef;transition:all 0.3s ease;min-width:120px;">
                            <div style="font-size:48px;color:#6f42c1;margin-bottom:10px;">
                                <i class="fas fa-link"></i>
                            </div>
                            <div style="font-weight:bold;color:#333;margin-bottom:5px;">Copy Link</div>
                            <div style="font-size:12px;color:#6c757d;">Copy & Share</div>
                        </div>
                        
                    </div>
                    
                    <!-- Payment Details -->
                    <div style="background:#f8f9fa;padding:20px;border-radius:10px;margin-bottom:20px;text-align:left;">
                        <h6 style="margin:0 0 15px 0;color:#333;font-size:16px;text-align:center;">
                            <i class="fas fa-info-circle" style="margin-right:8px;color:#007bff;"></i>Payment Details
                        </h6>
                        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                            <span style="color:#6c757d;">Amount:</span>
                            <span style="font-weight:bold;color:#333;">${amount}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                            <span style="color:#6c757d;">Property:</span>
                            <span style="font-weight:bold;color:#333;">${propertyName}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="color:#6c757d;">Tenant:</span>
                            <span style="font-weight:bold;color:#333;">${tenantName}</span>
                        </div>
                    </div>
                    
                    <input type="hidden" id="payment-link-hidden" value="${paymentLink}">
                    <input type="hidden" id="invitation-token-hidden" value="${invitationToken}">
                </div>
            `,
            icon: null,
            width: '600px',
            showCancelButton: true,
            showConfirmButton: false,
            cancelButtonColor: '#6c757d',
            cancelButtonText: 'Close',
            didOpen: () => {
                // Add hover effects and click handlers for share options
                document.querySelectorAll('.share-option').forEach(option => {
                    // Hover effects
                    option.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateY(-5px)';
                        this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
                        this.style.borderColor = '#007bff';
                    });
                    
                    option.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateY(0)';
                        this.style.boxShadow = 'none';
                        this.style.borderColor = '#e9ecef';
                    });
                    
                    // Click handlers
                    option.addEventListener('click', function() {
                        const method = this.getAttribute('data-method');
                        const paymentLink = document.getElementById('payment-link-hidden').value;
                        
                        if (method === 'whatsapp') {
                            handleWhatsAppShare(paymentLink, amount, propertyName, tenantName);
                        } else if (method === 'email') {
                            handleEmailShare(paymentLink, amount, propertyName, tenantName);
                        } else if (method === 'sms') {
                            handleSMSShare(paymentLink, amount, propertyName, tenantName);
                        } else if (method === 'copy') {
                            handleCopyLink(paymentLink);
                        }
                    });
                });
            }
        });
    }
    
    // Handle WhatsApp sharing - Direct open like social media share
    function handleWhatsAppShare(paymentLink, amount, propertyName, tenantName) {
        // Construct WhatsApp message
        let whatsappText = `🏠 *Payment Request from ${tenantName}*\n\n`;
        whatsappText += `📍 Property: ${propertyName}\n`;
        whatsappText += `💰 Amount: ${amount}\n\n`;
        whatsappText += `Please click this link to make payment:\n\n${paymentLink}\n\n`;
        whatsappText += `Thank you! 🙏`;
        
        // Open WhatsApp immediately without phone number (user selects recipient in WhatsApp)
        const whatsappLink = `https://wa.me/?text=${encodeURIComponent(whatsappText)}`;
        window.open(whatsappLink, '_blank');
        
        // Close the main modal
        Swal.close();
    }
    
    // Handle Email sharing - Opens email client like social media share
    function handleEmailShare(paymentLink, amount, propertyName, tenantName) {
        // Construct email subject and body
        const subject = encodeURIComponent(`Payment Request from ${tenantName}`);
        const body = encodeURIComponent(
            `Hello,\n\n` +
            `I need your help with a payment request.\n\n` +
            `Property: ${propertyName}\n` +
            `Amount: ${amount}\n\n` +
            `Please click this link to make payment:\n${paymentLink}\n\n` +
            `Thank you!`
        );
        
        // Open default email client (user selects recipient in email app)
        window.location.href = `mailto:?subject=${subject}&body=${body}`;
        
        // Close the main modal
        Swal.close();
    }
    
    // Handle SMS sharing - Direct open like social media share
    function handleSMSShare(paymentLink, amount, propertyName, tenantName) {
        // Construct SMS message (keep it concise for SMS)
        let smsText = `Payment Request from ${tenantName}\n`;
        smsText += `Amount: ${amount}\n`;
        smsText += `Property: ${propertyName}\n`;
        smsText += `Pay here: ${paymentLink}`;
        
        // Open SMS app immediately without phone number (user selects recipient in SMS app)
        window.location.href = `sms:?&body=${encodeURIComponent(smsText)}`;
        
        // Close the main modal
        Swal.close();
    }
    
    // Handle Copy Link
    function handleCopyLink(paymentLink) {
        // Create a temporary input element to copy the link
        const tempInput = document.createElement('input');
        tempInput.value = paymentLink;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        
        Swal.fire({
            title: 'Link Copied!',
            html: `
                <div style="text-align:left;">
                    <p style="margin-bottom:15px;">The payment link has been copied to your clipboard:</p>
                    <div style="background:#f8f9fa;padding:15px;border-radius:5px;word-break:break-all;font-family:monospace;font-size:12px;border:1px solid #e9ecef;">
                        ${paymentLink}
                    </div>
                    <p style="margin-top:15px;color:#6c757d;font-size:14px;">
                        <i class="fas fa-info-circle"></i> You can now paste this link in any messaging app, social media, or email to share with your benefactor.
                    </p>
                </div>
            `,
            icon: 'success',
            confirmButtonText: 'Got it!',
            confirmButtonColor: '#28a745'
        });
    }
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

/* Enhanced styles for benefactor invitation modal */
.share-option {
    transition: all 0.3s ease !important;
}

.share-option:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    border-color: #007bff !important;
}

.share-option:active {
    transform: translateY(-2px) !important;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .swal2-popup {
        width: 95% !important;
        margin: 0 !important;
    }
    
    .share-option {
        min-width: 100px !important;
        padding: 15px !important;
    }
    
    .share-option div:first-child {
        font-size: 36px !important;
    }
}

/* WhatsApp specific color */
.share-option[data-method="whatsapp"]:hover {
    border-color: #25D366 !important;
}

/* Email specific color */
.share-option[data-method="email"]:hover {
    border-color: #dc3545 !important;
}

/* SMS specific color */
.share-option[data-method="sms"]:hover {
    border-color: #007bff !important;
}

/* Copy link specific color */
.share-option[data-method="copy"]:hover {
    border-color: #6f42c1 !important;
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
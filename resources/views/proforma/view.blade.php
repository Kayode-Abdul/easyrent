<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Proforma - {{ optional(optional($proforma->apartment)->property)->prop_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #333; margin: 0; }
        .section { margin-bottom: 20px; }
        .section h2 { color: #444; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .details { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .property-details { margin-top: 30px; }
        .amount { font-size: 24px; color: #2c3e50; margin: 20px 0; }
        .footer { margin-top: 40px; text-align: center; color: #666; }
        @media print { body { padding: 0; } .container { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rent Proforma</h1>
            <p>Transaction ID: {{ $proforma->transaction_id }}</p>
            <p>Date: {{ optional($proforma->created_at)->format('F d, Y') }}</p>
        </div>

        <div class="details">
            <div class="section">
                <h2>From (Landlord)</h2>
                <p>
                    {{ optional($proforma->owner)->username }}<br>
                    {{ optional($proforma->owner)->email }}<br>
                    {{ optional($proforma->owner)->phone }}
                </p>
            </div>

            <div class="section">
                <h2>To (Tenant)</h2>
                <p>
                    {{ optional($proforma->tenant)->username }}<br>
                    {{ optional($proforma->tenant)->email }}<br>
                    {{ optional($proforma->tenant)->phone }}
                </p>
            </div>
        </div>

        <div class="section property-details">
            <h2>Property Details</h2>
            <p>
                <strong>Property ID:</strong> {{ optional(optional($proforma->apartment)->property)->prop_id }}<br>
                <strong>Apartment Type:</strong> {{ optional($proforma->apartment)->apartment_type }}<br>
                <strong>Duration:</strong> {{ $proforma->duration ? $proforma->duration . ' months' : 'N/A' }}<br>
                <strong>Monthly Rent:</strong> 
                @if(optional($proforma->apartment)->amount)
                    ₦{{ number_format($proforma->apartment->amount, 2) }}
                @else
                    N/A
                @endif
            </p>

            <div class="amount">
                <strong>Total Amount:</strong>
                @if(optional($proforma->apartment)->amount && $proforma->duration)
                    ₦{{ number_format($proforma->apartment->amount * $proforma->duration, 2) }}
                @else
                    N/A
                @endif
            </div>
        </div>

        <!-- Status and Action Buttons -->
        <div class="section">
            @if($proforma->status == \App\Models\ProfomaReceipt::STATUS_NEW)
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <p style="color: #0d6efd; font-weight: bold;">This proforma invoice is awaiting your response.</p>
                </div>
                <div style="display: flex; justify-content: center; gap: 20px; margin: 30px 0;">
                    <button id="acceptBtn" style="background-color: #198754; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Accept Proforma</button>
                    <button id="rejectBtn" style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Reject Proforma</button>
                </div>
            @elseif($proforma->status == \App\Models\ProfomaReceipt::STATUS_CONFIRMED)
                <div style="background-color: #d1e7dd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <p style="color: #0f5132; font-weight: bold;">You have accepted this proforma invoice.</p>
                </div>
            @elseif($proforma->status == \App\Models\ProfomaReceipt::STATUS_REJECTED)
                <div style="background-color: #f8d7da; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <p style="color: #842029; font-weight: bold;">You have rejected this proforma invoice.</p>
                </div>
            @endif
        </div>

        <div class="footer">
            <p>This is a computer-generated document. No signature is required.</p>
            <p>Generated on {{ config('app.name') }} - {{ now()->format('F d, Y h:i A') }}</p>
        </div>
    </div>

    <!-- SweetAlert2 for confirmations -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Accept button handler
            const acceptBtn = document.getElementById('acceptBtn');
            if (acceptBtn) {
                acceptBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Accept Proforma',
                        text: 'Are you sure you want to accept this proforma invoice?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, accept it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Send AJAX request to accept endpoint
                            fetch('/proforma/{{ $proforma->id }}/accept', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire(
                                        'Accepted!',
                                        'The proforma has been accepted successfully.',
                                        'success'
                                    ).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        data.message || 'Something went wrong.',
                                        'error'
                                    );
                                }
                            })
                            .catch(error => {
                                Swal.fire(
                                    'Error!',
                                    'There was an error processing your request.',
                                    'error'
                                );
                            });
                        }
                    });
                });
            }

            // Reject button handler
            const rejectBtn = document.getElementById('rejectBtn');
            if (rejectBtn) {
                rejectBtn.addEventListener('click', function() {
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
                            // Send AJAX request to reject endpoint
                            fetch('/proforma/{{ $proforma->id }}/reject', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire(
                                        'Rejected!',
                                        'The proforma has been rejected successfully.',
                                        'success'
                                    ).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        data.message || 'Something went wrong.',
                                        'error'
                                    );
                                }
                            })
                            .catch(error => {
                                Swal.fire(
                                    'Error!',
                                    'There was an error processing your request.',
                                    'error'
                                );
                            });
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
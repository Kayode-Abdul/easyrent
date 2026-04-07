@include('header')

<div class="hero-wrap" style="background-image: url('/assets/images/bg_1.jpg'); height: 100px;">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text align-items-center justify-content-center">
            <div class="col-md-9 text-center">
                <h1 class="mb-2 bread" style="color: white;">Rent Payment Request</h1>
            </div>
        </div>
    </div>
</div>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary">
                    <h4 class="mb-0  text-white">Payment Request Approval</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>{{ $invitation->tenant->first_name }} {{ $invitation->tenant->last_name }}</strong> 
                        has requested you to pay their rent.
                    </div>

                    <div class="payment-details mb-4">
                        <h5 class="mb-3">Payment Details</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Tenant Name:</th>
                                <td>{{ $invitation->tenant->first_name }} {{ $invitation->tenant->last_name }}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{{ $invitation->tenant->email }}</td>
                            </tr>
                            <tr>
                                <th>Amount:</th>
                                <td><strong class="text-success">₦{{ number_format($invitation->amount, 2) }}</strong></td>
                            </tr>
                            @if($invitation->invoice_details)
                                @php
                                    $details = is_array($invitation->invoice_details) ? $invitation->invoice_details : json_decode($invitation->invoice_details, true);
                                @endphp
                                @if(isset($details['property_name']))
                                    <tr>
                                        <th>Property:</th>
                                        <td>{{ $details['property_name'] }}</td>
                                    </tr>
                                @endif
                                @if(isset($details['apartment_name']))
                                    <tr>
                                        <th>Apartment:</th>
                                        <td>{{ $details['apartment_name'] }}</td>
                                    </tr>
                                @endif
                            @endif
                            <tr>
                                <th>Request Date:</th>
                                <td>{{ $invitation->created_at->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Expires:</th>
                                <td>{{ $invitation->expires_at->format('M d, Y') }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="approval-actions">
                        <h5 class="mb-3">Your Decision</h5>
                        <p class="text-muted">
                            Please review the payment request above. If you agree to pay, click "Approve & Continue to Payment". 
                            If you cannot or do not wish to pay, you can decline with an optional reason.
                        </p>

                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <form action="{{ route('benefactor.payment.approve', $invitation->token) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="fas fa-check-circle"></i> Approve & Continue to Payment
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn btn-outline-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#declineModal">
                                    <i class="fas fa-times-circle"></i> Decline Request
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Decline Modal -->
<div class="modal fade" id="declineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('benefactor.payment.decline', $invitation->token) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Decline Payment Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to decline this payment request?</p>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason (Optional)</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" 
                            placeholder="Let the tenant know why you're declining..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Decline Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('footer')


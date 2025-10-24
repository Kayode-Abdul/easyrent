@extends('layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Earnings Summary -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h4>KSh {{ number_format($summary['total_earned']) }}</h4>
                            <p class="mb-0">Total Earned</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h4>KSh {{ number_format($summary['total_paid']) }}</h4>
                            <p class="mb-0">Total Paid</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h4>KSh {{ number_format($summary['pending_payment']) }}</h4>
                            <p class="mb-0">Pending Payment</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h4>{{ $summary['total_referrals'] }}</h4>
                            <p class="mb-0">Total Referrals</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Payment History</h5>
                    <div>
                        <button class="btn btn-outline-primary" onclick="requestPayment()" 
                                {{ $summary['pending_payment'] < 1000 ? 'disabled' : '' }}>
                            <i class="fas fa-money-bill-wave"></i> Request Payment
                        </button>
                        <a href="{{ route('marketer.dashboard') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($summary['pending_payment'] >= 1000)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Payment Available!</strong> You have KSh {{ number_format($summary['pending_payment']) }} 
                            ready for payment. Click "Request Payment" to initiate the process.
                        </div>
                    @elseif($summary['pending_payment'] > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Minimum Payment:</strong> You need at least KSh 1,000 to request a payment. 
                            Current pending amount: KSh {{ number_format($summary['pending_payment']) }}
                        </div>
                    @endif

                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Payment Date</th>
                                        <th>Reference</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Bank Details</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>
                                                <strong>{{ $payment->created_at->format('M d, Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $payment->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <code>{{ $payment->payment_reference }}</code>
                                            </td>
                                            <td>
                                                <strong class="text-success">KSh {{ number_format($payment->amount) }}</strong>
                                            </td>
                                            <td>
                                                @switch($payment->payment_method)
                                                    @case('bank_transfer')
                                                        <span class="badge badge-primary">Bank Transfer</span>
                                                        @break
                                                    @case('mobile_money')
                                                        <span class="badge badge-success">Mobile Money</span>
                                                        @break
                                                    @case('paypal')
                                                        <span class="badge badge-info">PayPal</span>
                                                        @break
                                                    @default
                                                        <span class="badge badge-secondary">{{ ucfirst($payment->payment_method) }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $payment->bank_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $payment->account_number }}</small>
                                                    <br>
                                                    <small class="text-muted">{{ $payment->account_name }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @switch($payment->status)
                                                    @case('pending')
                                                        <span class="badge badge-warning">Pending</span>
                                                        @break
                                                    @case('processing')
                                                        <span class="badge badge-info">Processing</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge badge-success">Completed</span>
                                                        @break
                                                    @case('failed')
                                                        <span class="badge badge-danger">Failed</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge badge-secondary">Cancelled</span>
                                                        @break
                                                @endswitch
                                                
                                                @if($payment->processed_at)
                                                    <br>
                                                    <small class="text-muted">{{ $payment->processed_at->format('M d, Y') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewPaymentDetails({{ $payment->id }})" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    @if($payment->status === 'pending')
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="cancelPayment({{ $payment->id }})" title="Cancel">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($payment->status === 'failed')
                                                        <button class="btn btn-sm btn-outline-info" 
                                                                onclick="retryPayment({{ $payment->id }})" title="Retry">
                                                            <i class="fas fa-redo"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $payments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
                            <h5>No Payments Yet</h5>
                            <p class="text-muted">
                                Once you start earning commissions, your payment history will appear here.
                            </p>
                            <div class="mt-3">
                                <a href="{{ route('marketer.campaigns.index') }}" class="btn btn-primary mr-2">
                                    <i class="fas fa-bullhorn"></i> View Campaigns
                                </a>
                                <a href="{{ route('marketer.referrals.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-users"></i> View Referrals
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Rewards -->
            @if($pendingRewards->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Pending Commission Rewards</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Landlord</th>
                                        <th>Registration Date</th>
                                        <th>Commission</th>
                                        <th>Status</th>
                                        <th>Campaign</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingRewards as $reward)
                                        <tr>
                                            <td>{{ $reward->landlord->name }}</td>
                                            <td>{{ $reward->calculation_date ? $reward->calculation_date->format('M d, Y') : 'N/A' }}</td>
                                            <td><strong>KSh {{ number_format($reward->commission_amount ?? $reward->amount ?? 0) }}</strong></td>
                                            <td>
                                                <span class="badge badge-warning">{{ ucfirst($reward->status) }}</span>
                                            </td>
                                            <td>
                                                @if($reward->referral && $reward->referral->campaign)
                                                    {{ $reward->referral->campaign->name }}
                                                @else
                                                    <span class="text-muted">Direct Referral</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Payment Request Modal -->
<div class="modal fade" id="paymentRequestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Payment</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('marketer.payments.request') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Amount to be paid:</strong> KSh {{ number_format($summary['pending_payment']) }}
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money (M-Pesa)</option>
                        </select>
                    </div>
                    
                    <div id="bankDetails" style="display: none;">
                        <h6>Bank Account Details</h6>
                        <div class="form-group">
                            <label for="bank_name">Bank Name</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                   value="{{ auth()->user()->bank_name }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="account_number">Account Number</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" 
                                   value="{{ auth()->user()->account_number }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="account_name">Account Name</label>
                            <input type="text" class="form-control" id="account_name" name="account_name" 
                                   value="{{ auth()->user()->account_name }}" readonly>
                        </div>
                    </div>
                    
                    <div id="mobileDetails" style="display: none;">
                        <div class="form-group">
                            <label for="mobile_number">M-Pesa Number</label>
                            <input type="text" class="form-control" id="mobile_number" name="mobile_number" 
                                   placeholder="0700000000" value="{{ auth()->user()->phone }}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Additional Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Any special instructions or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Payment Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="paymentDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('payment_method').addEventListener('change', function() {
    const bankDetails = document.getElementById('bankDetails');
    const mobileDetails = document.getElementById('mobileDetails');
    
    if (this.value === 'bank_transfer') {
        bankDetails.style.display = 'block';
        mobileDetails.style.display = 'none';
    } else if (this.value === 'mobile_money') {
        bankDetails.style.display = 'none';
        mobileDetails.style.display = 'block';
    } else {
        bankDetails.style.display = 'none';
        mobileDetails.style.display = 'none';
    }
});

function requestPayment() {
    $('#paymentRequestModal').modal('show');
}

function viewPaymentDetails(paymentId) {
    $('#paymentDetailsModal').modal('show');
    
    fetch(`/marketer/payments/${paymentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const payment = data.payment;
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Payment Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Reference:</strong></td>
                                    <td><code>${payment.payment_reference}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td><strong class="text-success">KSh ${payment.amount.toLocaleString()}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Method:</strong></td>
                                    <td>${payment.payment_method.replace('_', ' ').toUpperCase()}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge badge-${getPaymentStatusColor(payment.status)}">${payment.status.toUpperCase()}</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Account Details</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Bank:</strong></td>
                                    <td>${payment.bank_name}</td>
                                </tr>
                                <tr>
                                    <td><strong>Account:</strong></td>
                                    <td>${payment.account_number}</td>
                                </tr>
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>${payment.account_name}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    ${payment.notes ? `
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2">Notes</h6>
                                <p>${payment.notes}</p>
                            </div>
                        </div>
                    ` : ''}
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2">Timeline</h6>
                            <ul class="list-unstyled">
                                <li><strong>Requested:</strong> ${new Date(payment.created_at).toLocaleString()}</li>
                                ${payment.processed_at ? `<li><strong>Processed:</strong> ${new Date(payment.processed_at).toLocaleString()}</li>` : ''}
                                ${payment.processed_by ? `<li><strong>Processed By:</strong> Admin</li>` : ''}
                            </ul>
                        </div>
                    </div>
                `;
                document.getElementById('paymentDetailsContent').innerHTML = content;
            } else {
                document.getElementById('paymentDetailsContent').innerHTML = 
                    '<p class="text-danger">Error loading payment details</p>';
            }
        })
        .catch(error => {
            document.getElementById('paymentDetailsContent').innerHTML = 
                '<p class="text-danger">Error loading payment details</p>';
        });
}

function getPaymentStatusColor(status) {
    switch(status) {
        case 'pending': return 'warning';
        case 'processing': return 'info';
        case 'completed': return 'success';
        case 'failed': return 'danger';
        case 'cancelled': return 'secondary';
        default: return 'secondary';
    }
}

function cancelPayment(paymentId) {
    if (confirm('Are you sure you want to cancel this payment request?')) {
        fetch(`/marketer/payments/${paymentId}/cancel`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Error cancelling payment');
            }
        });
    }
}

function retryPayment(paymentId) {
    if (confirm('Are you sure you want to retry this payment?')) {
        fetch(`/marketer/payments/${paymentId}/retry`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Error retrying payment');
            }
        });
    }
}
</script>
@endsection

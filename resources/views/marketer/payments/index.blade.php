@extends('layout')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-12">
            <!-- Earnings Summary -->
            @php
                // Transform Eloquent collections into format the component expects
                $earnedCurrencies = $summary['total_earned']->map(function($s) {
                    return [
                        'code' => $s->currency ? $s->currency->code : 'NGN',
                        'symbol' => $s->currency ? $s->currency->symbol : '₦',
                        'amount' => $s->total,
                    ];
                })->toArray();
                $paidCurrencies = $summary['total_paid']->map(function($s) {
                    return [
                        'code' => $s->currency ? $s->currency->code : 'NGN',
                        'symbol' => $s->currency ? $s->currency->symbol : '₦',
                        'amount' => $s->total,
                    ];
                })->toArray();
                $pendingCurrencies = $summary['pending_payment']->map(function($s) {
                    return [
                        'code' => $s->currency ? $s->currency->code : 'NGN',
                        'symbol' => $s->currency ? $s->currency->symbol : '₦',
                        'amount' => $s->total,
                    ];
                })->toArray();
            @endphp
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <x-currency-carousel :currencies="$earnedCurrencies" :decimals="0" id="marketer-earned" />
                            <p class="mb-0 mt-1">Total Earned</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <x-currency-carousel :currencies="$paidCurrencies" :decimals="0" id="marketer-paid" />
                            <p class="mb-0 mt-1">Total Paid</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <x-currency-carousel :currencies="$pendingCurrencies" :decimals="0" id="marketer-pending" />
                            <p class="mb-0 mt-1">Pending Payment</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <p class="cc-figure" style="font-weight:700; font-size:clamp(1rem,1.6vw,1.4rem); margin:0;">{{ $summary['total_referrals'] }}</p>
                            <p class="mb-0 mt-1">Total Referrals</p>
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
                                {{ !collect($summary['pending_payment'])->filter(function($s) { return $s->total >= 1000; })->isNotEmpty() ? 'disabled' : '' }}>
                            <i class="fa fa-money-bill-wave"></i> Request Payment
                        </button>
                        <a href="{{ route('marketer.dashboard') }}" class="btn btn-secondary ml-2">
                            <i class="fa fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $canRequest = collect($summary['pending_payment'])->filter(function($s) { return $s->total >= 1000; })->isNotEmpty();
                        $totalPendingAll = $summary['pending_payment']->sum('total');
                    @endphp
                    @if($canRequest)
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>Payment Available!</strong> You have 
                            @foreach($summary['pending_payment'] as $stat)
                                @if($stat->total >= 1000)
                                    <span class="badge badge-success">{{ $stat->currency ? $stat->currency->symbol : '' }} {{ number_format($stat->total) }}</span>
                                @endif
                            @endforeach
                            ready for payment. Click "Request Payment" to initiate the process.
                        </div>
                    @elseif($totalPendingAll > 0)
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>Minimum Payment:</strong> You need at least 1,000 in a single currency to request a payment. 
                            Current pending amounts: 
                            @foreach($summary['pending_payment'] as $stat)
                                <span class="badge badge-secondary">{{ $stat->currency ? $stat->currency->symbol : '' }} {{ number_format($stat->total) }}</span>
                            @endforeach
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
                                        <th>Type</th>
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
                                                <strong class="text-success">{{ format_money($payment->total_amount, $payment->currency_id) }}</strong>
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
                                                @if($payment->commission_tier)
                                                    <span class="badge badge-info">{{ ucwords(str_replace('_', ' ', $payment->commission_tier)) }} Commission</span>
                                                @else
                                                    <span class="badge badge-secondary">Withdrawal Request</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $payment->payment_details['bank_name'] ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $payment->payment_details['account_number'] ?? '' }}</small>
                                                    <br>
                                                    <small class="text-muted">{{ $payment->payment_details['account_name'] ?? '' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @switch($payment->payment_status)
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
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    
                                                    @if($payment->payment_status === 'pending')
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="cancelPayment({{ $payment->id }})" title="Cancel">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($payment->payment_status === 'failed')
                                                        <button class="btn btn-sm btn-outline-info" 
                                                                onclick="retryPayment({{ $payment->id }})" title="Retry">
                                                            <i class="fa fa-redo"></i>
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
                            <i class="fa fa-wallet fa-3x text-muted mb-3"></i>
                            <h5>No Payments Yet</h5>
                            <p class="text-muted">
                                Once you start earning commissions, your payment history will appear here.
                            </p>
                            <div class="mt-3">
                                <a href="{{ route('marketer.campaigns.index') }}" class="btn btn-primary mr-2">
                                    <i class="fa fa-bullhorn"></i> View Campaigns
                                </a>
                                <a href="{{ route('marketer.referrals.index') }}" class="btn btn-outline-primary">
                                    <i class="fa fa-users"></i> View Referrals
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
                                            <td>{{ $reward->referral && $reward->referral->referred ? $reward->referral->referred->name : 'Unknown Landlord' }}</td>
                                            <td>{{ $reward->calculation_date ? $reward->calculation_date->format('M d, Y') : 'N/A' }}</td>
                                            <td><strong>{{ $reward->currency ? $reward->currency->symbol : '' }} {{ number_format($reward->commission_amount ?? $reward->amount ?? 0) }}</strong></td>
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
                    <div class="form-group">
                        <label for="currency_id">Select Currency to Withdraw</label>
                        <select class="form-control" id="currency_id" name="currency_id" required onchange="updateAmountDisplay()">
                            <option value="">Select Currency</option>
                            @foreach($summary['pending_payment'] as $stat)
                                @if($stat->total >= 1000)
                                    <option value="{{ $stat->currency_id }}" data-symbol="{{ $stat->currency ? $stat->currency->symbol : '' }}" data-total="{{ number_format($stat->total) }}">
                                        {{ $stat->currency ? $stat->currency->code : 'Unknown' }} ({{ $stat->currency ? $stat->currency->symbol : '' }}{{ number_format($stat->total) }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="alert alert-info" id="amountDisplayWrapper" style="display: none;">
                        <strong>Amount to be paid:</strong> 
                        <span id="amountDisplayValue"></span>
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
                                   value="{{ auth()->user()->bank_account_number }}" readonly>
                        </div>
                        <div class="form-group">
                            <label for="account_name">Account Name</label>
                            <input type="text" class="form-control" id="account_name" name="account_name" 
                                   value="{{ auth()->user()->bank_account_name }}" readonly>
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
                        <i class="fa fa-paper-plane"></i> Submit Payment Request
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

function updateAmountDisplay() {
    const currencySelect = document.getElementById('currency_id');
    const displayWrapper = document.getElementById('amountDisplayWrapper');
    const displayValue = document.getElementById('amountDisplayValue');
    
    if (currencySelect.value) {
        const selectedOption = currencySelect.options[currencySelect.selectedIndex];
        const symbol = selectedOption.getAttribute('data-symbol');
        const total = selectedOption.getAttribute('data-total');
        
        displayValue.textContent = symbol + ' ' + total;
        displayWrapper.style.display = 'block';
    } else {
        displayWrapper.style.display = 'none';
    }
}

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
                                    <td><strong class="text-success">${payment.currency ? payment.currency.symbol : (window.currencySymbol || '₦')}${payment.total_amount.toLocaleString()}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Method:</strong></td>
                                    <td>${payment.payment_method.replace('_', ' ').toUpperCase()}</td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>${payment.commission_tier ? payment.commission_tier.replace('_', ' ').toUpperCase() + ' COMMISSION' : 'WITHDRAWAL REQUEST'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge badge-${getPaymentStatusColor(payment.payment_status)}">${payment.payment_status.toUpperCase()}</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Account Details</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Bank:</strong></td>
                                    <td>${payment.payment_details && payment.payment_details.bank_name ? payment.payment_details.bank_name : 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Account:</strong></td>
                                    <td>${payment.payment_details && payment.payment_details.account_number ? payment.payment_details.account_number : ''}</td>
                                </tr>
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>${payment.payment_details && payment.payment_details.account_name ? payment.payment_details.account_name : ''}</td>
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

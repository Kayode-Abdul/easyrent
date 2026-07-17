@extends('layout')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fa fa-credit-card text-primary me-2"></i>
                                Commission Payments
                            </h4>
                            <p class="text-muted mb-0">Manage marketer withdrawal requests and payments</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.marketers.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Paid</h6>
                            <h3 class="mb-0">{{ format_money($stats['total_paid'] ?? 0) }}</h3>
                            <small class="opacity-75">Completed payments</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fa fa-check-circle fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Pending Amount</h6>
                            <h3 class="mb-0">{{ format_money($stats['pending_amount'] ?? 0) }}</h3>
                            <small class="opacity-75">Awaiting processing</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fa fa-clock fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Failed Amount</h6>
                            <h3 class="mb-0">{{ format_money($stats['failed_amount'] ?? 0) }}</h3>
                            <small class="opacity-75">Failed transactions</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fa fa-times-circle fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.marketers.payments') }}" class="row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2 mb-md-0">
                    <select name="method" class="form-select" onchange="this.form.submit()">
                        <option value="">All Payment Methods</option>
                        <option value="bank_transfer" {{ request('method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="mobile_money" {{ request('method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                        <option value="paypal" {{ request('method') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('admin.marketers.payments') }}" class="btn btn-outline-secondary">Reset Filters</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-body p-0">
            @if($payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Marketer</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Account Details</th>
                                <th>Status</th>
                                <th>Date Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td><code>{{ $payment->payment_reference }}</code></td>
                                    <td>
                                        <div>
                                            <strong>{{ $payment->marketer->first_name ?? 'N/A' }} {{ $payment->marketer->last_name ?? '' }}</strong>
                                            <br><small class="text-muted">{{ $payment->marketer->email ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">{{ format_money($payment->total_amount, $payment->currency_id) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                                    </td>
                                    <td>
                                        @if($payment->payment_method === 'bank_transfer' && isset($payment->payment_details['bank_name']))
                                            <small>
                                                <strong>Bank:</strong> {{ $payment->payment_details['bank_name'] }}<br>
                                                <strong>Acct:</strong> {{ $payment->payment_details['account_number'] }}<br>
                                                <strong>Name:</strong> {{ $payment->payment_details['account_name'] }}
                                            </small>
                                        @elseif($payment->payment_method === 'mobile_money' && isset($payment->payment_details['mobile_number']))
                                            <small>
                                                <strong>Mobile:</strong> {{ $payment->payment_details['mobile_number'] }}
                                            </small>
                                        @else
                                            <small class="text-muted">N/A</small>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($payment->payment_status)
                                            @case('pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                                @break
                                            @case('processing')
                                                <span class="badge bg-info">Processing</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">Completed</span>
                                                @break
                                            @case('failed')
                                                <span class="badge bg-danger">Failed</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-secondary">Cancelled</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ ucfirst($payment->payment_status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <small>{{ $payment->created_at->format('M d, Y h:i A') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($payment->payment_status === 'pending')
                                                <form action="{{ route('admin.marketers.payments.process', $payment) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-info" title="Mark as Processing" onclick="return confirm('Mark this payment as processing?')">
                                                        <i class="fa fa-cogs"></i> Process
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($payment->payment_status === 'processing')
                                                <button type="button" class="btn btn-outline-success" title="Mark as Completed" onclick="completePayment({{ $payment->id }})">
                                                    <i class="fa fa-check"></i> Complete
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
                <div class="d-flex justify-content-center p-3">
                    {{ $payments->appends(request()->query())->links() }}
                </div>
            @else
                <div class="p-5 text-center text-muted">
                    <i class="fa fa-credit-card fa-3x mb-3 d-block opacity-25"></i>
                    <h6>No Payments Found</h6>
                    <p class="small">There are no commission payments matching your criteria.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Complete Payment Modal -->
<div class="modal fade" id="completePaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complete Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="completePaymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Please enter the transaction reference or receipt number from your payment gateway/bank to mark this payment as completed.</p>
                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">Transaction ID / Reference <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Complete Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function completePayment(paymentId) {
        const form = document.getElementById('completePaymentForm');
        form.action = `/admin/marketers/payments/${paymentId}/complete`;
        const modal = new bootstrap.Modal(document.getElementById('completePaymentModal'));
        modal.show();
    }
</script>
@endsection

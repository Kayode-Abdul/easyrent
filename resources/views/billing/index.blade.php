@extends('layout')

<!-- section('title', 'Billing & Payments') -->

@section('content')
<div class="content">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-custom shadow-sm">
                <div class="d-flex align-items-center mb-3">
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary me-3">
                        <i class="fafa-arrow-left"></i> Back
                    </a>
                    <div>
                        <h4 class="mb-1">
                            <i class="fafa-credit-card text-primary me-2"></i>
                            Billing & Payments
                        </h4>
                        <p class=" mb-0">View your payment history and pending bills</p>
                    </div>
                </div>
                <div>
                    <span class="badge bg-success fs-6">
                        <i class="fafa-check-circle me-1"></i>
                        Account Active
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Paid</h6>
                            <h3 class="mb-0">₦{{ number_format($totalPaid, 2) }}</h3>
                            <small class="opacity-75">All time payments</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fafa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Pending Amount</h6>
                            <h3 class="mb-0">₦{{ number_format($totalPending, 2) }}</h3>
                            <small class="opacity-75">Outstanding bills</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fafa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Payment History -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fafa-history text-success me-2"></i>
                        Payment History
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($payments && $payments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 datatable" id="billing-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                <tr class="clickable-row" data-href="{{ route('payment.receipt', $payment->id) }}"
                                    style="cursor: pointer;">
                                    <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="fw-medium">Payment #{{ $payment->id }}</div>
                                        <small class="text-muted">
                                            @if($payment->tenant_id == auth()->user()->user_id)
                                            Rent Payment (as Tenant)
                                            @else
                                            Rent Payment Received (as Landlord)
                                            @endif
                                        </small>
                                    </td>
                                    <td class="fw-bold text-success">₦{{ number_format($payment->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ ucfirst($payment->status) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-4 text-center text-muted">
                        <i class="fafa-receipt fa-3x mb-3 d-block opacity-25"></i>
                        <h6>No Payment History</h6>
                        <p class="small">Your payment history will appear here once you make payments</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Bills -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fafa-exclamation-triangle text-warning me-2"></i>
                        Pending Bills
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($pendingPayments->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($pendingPayments as $pending)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">#{{ $pending->id }} -
                                    {{ $pending->apartment?->property?->address ?? 'Rent Payment' }}
                                </h6>
                                <span class="badge bg-warning text-dark">Pending</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">₦{{ number_format($pending->amount, 2) }}</span>
                                <a href="{{ route('proforma.view', $pending->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fafa-credit-card me-1"></i> Pay Now
                                </a>
                            </div>
                            <small class="text-muted d-block mt-1">Due: {{ (isset($pending->due_date) &&
                                $pending->due_date
                                instanceof \Carbon\Carbon) ?
                                $pending->due_date->format('M d, Y') : 'N/A' }}</small>
                        </div>
                        @endforeach
                        <div class="mt-3">
                            {{ $pendingPayments->links() }}
                        </div>
                    </div>
                    @else
                    <div class="p-4 text-center text-muted">
                        <i class="fafa-check-circle fa-3x mb-3 d-block opacity-25"></i>
                        <h6>No Pending Bills</h6>
                        <p class="small">You're all caught up!</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function () {
        $('#billing-table').DataTable({
            "order": [[0, "desc"]], // Sort by date column (index 0)
            "pageLength": 10,
            "responsive": true,
            "language": {
                "search": "Search payments:",
                "paginate": {
                    "next": '<i class="fafa-chevron-right"></i>',
                    "previous": '<i class="fafa-chevron-left"></i>'
                }
            }
        });

        $(".clickable-row").click(function () {
            window.location = $(this).data("href");
        });
    });
</script>
@endpush
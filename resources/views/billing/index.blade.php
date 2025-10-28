@extends('layout')

<!-- section('title', 'Billing & Payments') -->

@section('content')
<div class="content">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fas fa-credit-card text-primary me-2"></i>
                                Billing & Payments
                            </h4>
                            <p class="text-muted mb-0">View your payment history and pending bills</p>
                        </div>
                        <div>
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-check-circle me-1"></i>
                                Account Active
                            </span>
                        </div>
                    </div>
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
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
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
                            <i class="fas fa-clock fa-2x opacity-75"></i>
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
                        <i class="fas fa-history text-success me-2"></i>
                        Payment History
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(config('app.debug'))
                        <div class="alert alert-info">
                            <strong>Debug Info:</strong><br>
                            User ID: {{ auth()->user()->user_id }}<br>
                            Total Payments Found: {{ $payments->count() }}<br>
                            Total Paid Amount: ₦{{ number_format($totalPaid, 2) }}
                        </div>
                    @endif
                    
                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
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
                                        <tr>
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
                            <i class="fas fa-receipt fa-3x mb-3 d-block opacity-25"></i>
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
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Pending Bills
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($pendingBookings->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($pendingBookings as $booking)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Booking #{{ $booking->id }}</h6>
                                            <small class="text-muted">Due: {{ $booking->created_at->format('M d, Y') }}</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-warning">₦{{ number_format($booking->amount, 2) }}</div>
                                            <button class="btn btn-sm btn-outline-primary mt-1">Pay Now</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-check-circle fa-3x mb-3 d-block opacity-25"></i>
                            <h6>No Pending Bills</h6>
                            <p class="small">You're all caught up!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layout')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4 class="card-title">Property Payments Ledger</h4>
                            <p class="card-category">Real-time payment history for your managed units</p>
                        </div>
                        <a href="{{ route('property-manager.dashboard') }}" class="btn btn-outline-secondary btn-round btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px; background: #fff9f6;">
                <div class="card-body">
                    <form method="GET" class="row align-items-end">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <div class="form-group mb-0">
                                <label for="property_id" class="text-secondary font-weight-bold" style="font-size: 0.85rem;">Property Address</label>
                                <select class="form-control" name="property_id" id="property_id" style="border-color: #ef8157; background-color: #fff;">
                                    <option value="">All Managed Properties</option>
                                    @foreach($managedProperties as $property)
                                        <option value="{{ $property->property_id }}" {{ request('property_id') == $property->property_id ? 'selected' : '' }}>
                                            {{ $property->address }} ({{ $property->property_id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3 col-sm-6 mb-2">
                            <div class="form-group mb-0">
                                <label for="status" class="text-secondary font-weight-bold" style="font-size: 0.85rem;">Payment Status</label>
                                <select class="form-control" name="status" id="status" style="border-color: #ef8157; background-color: #fff;">
                                    <option value="">All Statuses</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6 mb-2">
                            <div class="form-group mb-0">
                                <label for="date_from" class="text-secondary font-weight-bold" style="font-size: 0.85rem;">From Date</label>
                                <input type="date" class="form-control" name="date_from" id="date_from" value="{{ request('date_from') }}" style="border-color: #ef8157; background-color: #fff;">
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6 mb-2">
                            <div class="form-group mb-0">
                                <label for="date_to" class="text-secondary font-weight-bold" style="font-size: 0.85rem;">To Date</label>
                                <input type="date" class="form-control" name="date_to" id="date_to" value="{{ request('date_to') }}" style="border-color: #ef8157; background-color: #fff;">
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-12 mb-2">
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary btn-round btn-block mr-2" style="background-color: #ef8157; border-color: #ef8157;">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('property-manager.payments') }}" class="btn btn-outline-secondary btn-round btn-block mt-0 d-flex align-items-center justify-content-center">
                                    <i class="fa fa-refresh"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Ledger List -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header border-0 bg-transparent">
                    <h5 class="card-title text-dark font-weight-bold mb-0">Transaction List ({{ $payments->total() }})</h5>
                </div>
                <div class="card-body">
                    @if($payments->isEmpty())
                        <div class="text-center py-5">
                            <i class="nc-icon nc-paper-2 text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                            <h4 class="mt-3 text-muted">No payments found</h4>
                            <p class="text-secondary">Try adjusting your filters or check back later.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead style="color: #ef8157; font-weight: bold;">
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Tenant</th>
                                        <th>Property & Apartment</th>
                                        <th>Amount</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold text-dark">{{ $payment->transaction_id ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($payment->tenant)
                                                    <div>
                                                        <strong>{{ $payment->tenant->first_name }} {{ $payment->tenant->last_name }}</strong><br>
                                                        <small class="text-muted">{{ $payment->tenant->email }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Anonymous</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="font-weight-bold text-primary">{{ $payment->apartment->property->address ?? 'N/A' }}</span><br>
                                                    <span class="badge badge-pill badge-light">{{ $payment->apartment->apartment_type ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold text-success" style="font-size: 1.05rem;">
                                                    {{ format_money($payment->amount, $payment->currency) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info px-2 py-1">
                                                    {{ $payment->duration }} {{ Str::plural('Month', $payment->duration) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-pill px-3 py-1 text-uppercase font-weight-bold badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ $payment->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-muted" style="font-size: 0.85rem;">
                                                    <i class="fa fa-calendar-o mr-1"></i>
                                                    {{ $payment->created_at ? $payment->created_at->format('M j, Y h:i A') : 'N/A' }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($payments->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $payments->appends(request()->query())->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

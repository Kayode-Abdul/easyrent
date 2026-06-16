@extends('layout')

@section('title', 'Payment & Commission Breakdown')

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
                                <i class="fa fa-money-bill-wave text-primary me-2"></i>
                                Payment & Commission Breakdown
                            </h4>
                            <p class="text-muted mb-0">Trace rent payments back to their commission splits</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <form action="{{ route('payments.breakdown') }}" method="GET" class="d-flex align-items-center">
                        <div class="input-group" style="max-width: 400px;">
                            <input type="text" name="search" class="form-control" placeholder="Search by apartment name..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-primary"><i class="fa fa-search"></i> Search</button>
                        </div>
                        @if(request('search'))
                            <a href="{{ route('payments.breakdown') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                        @endif
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rent Payment</th>
                                    <th>Apartment</th>
                                    <th>Date</th>
                                    <th>Commission Split Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>
                                            <h6 class="mb-0 text-success fw-bold">
                                                {{ $payment->currency->symbol ?? '₦' }}{{ number_format($payment->amount, 2) }}
                                            </h6>
                                            <small class="text-muted">Payment ID: #{{ $payment->id }}</small>
                                        </td>
                                        <td>
                                            @if($payment->apartment)
                                                <strong>{{ $payment->apartment->name ?? 'N/A' }}</strong>
                                                <div class="small text-muted">{{ $payment->apartment->property->name ?? 'N/A' }}</div>
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $payment->created_at->format('M d, Y H:i') }}
                                        </td>
                                        <td>
                                            @if($payment->commissionPayments->isEmpty())
                                                <span class="text-muted fst-italic">No commission records</span>
                                            @else
                                                <ul class="list-group list-group-flush small">
                                                    @foreach($payment->commissionPayments as $cp)
                                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1 border-0">
                                                            <span>
                                                                <span class="badge bg-{{ $cp->commission_tier == 'super_marketer' ? 'info' : ($cp->commission_tier == 'marketer' ? 'success' : 'primary') }} me-1">
                                                                    {{ ucfirst(str_replace('_', ' ', $cp->commission_tier)) }}
                                                                </span>
                                                                @if($cp->marketer)
                                                                    {{ $cp->marketer->first_name }} {{ $cp->marketer->last_name }}
                                                                @else
                                                                    <em>Unknown User</em>
                                                                @endif
                                                            </span>
                                                            <strong class="text-dark">
                                                                {{ $payment->currency->symbol ?? '₦' }}{{ number_format($cp->total_amount, 2) }}
                                                                <small class="text-muted">({{ $cp->regional_rate_applied }}%)</small>
                                                            </strong>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fa fa-folder-open fa-3x mb-3 opacity-25"></i>
                                            <p class="mb-0">No rent payments with commission splits found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($payments->hasPages())
                    <div class="card-footer bg-white border-top">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

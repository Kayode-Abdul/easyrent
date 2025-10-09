@extends('layout')

@section('content')
<body>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Payment History</h4>
                </div>
                <div class="card-body">
                    @if($payments->isEmpty())
                        <div class="alert alert-info">
                            No payment records found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Apartment</th>
                                        @if(auth()->user()->role === 1)
                                            <th>Tenant</th>
                                        @else
                                            <th>Landlord</th>
                                        @endif
                                        <th>Amount</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->transaction_id }}</td>
                                            <td>
                                                @if($payment->apartment)
                                                    <a href="{{ url('/dashboard/apartment/'.$payment->apartment_id) }}">
                                                        {{ $payment->apartment->apartment_type }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Unknown Apartment</span>
                                                @endif
                                            </td>
                                            @if(auth()->user()->role === 1)
                                                <td>{{ $payment->tenant->first_name }} {{ $payment->tenant->last_name }}</td>
                                            @else
                                                <td>{{ $payment->landlord->first_name }} {{ $payment->landlord->last_name }}</td>
                                            @endif
                                            <td>{{ $payment->getFormattedAmount() }}</td>
                                            <td>{{ $payment->duration }} {{ Str::plural('Month', $payment->duration) }}</td>
                                            <td>
                                                <span class="badge {{ $payment->getStatusBadgeClass() }}">
                                                    {{ $payment->getFormattedStatus() }}
                                                </span>
                                            </td>
                                            <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="viewReceipt('{{ $payment->transaction_id }}')">
                                                    <i class="fa fa-file-text"></i> Receipt
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            {{ $payments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewReceipt(transactionId) {
    window.open('/dashboard/payments/' + transactionId + '/receipt', '_blank');
}
</script>
@endsection

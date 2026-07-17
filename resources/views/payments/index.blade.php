@extends('layout')

@section('content')

    <div class="content mt-5">
        @if(isset($totalsByCurrency) && count($totalsByCurrency) > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Total Received</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-sm-6">
                                <div class="card card-stats bg-light mb-3">
                                    <div class="card-body text-center">
                                        <x-currency-carousel :currencies="$totalsByCurrency" id="carousel-total-received" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

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
                                <table class="table datatable" id="payments-table">
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
                                                        <a href="{{ url('/dashboard/apartment/' . $payment->apartment_id) }}">
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
                                                    <a href="{{ route('payment.receipt.reference', ['reference' => $payment->transaction_id]) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fa fa-download"></i> View Receipt
                                                    </a>
                                                    @if(auth()->user()->role === 7)
                                                        <button type="button" onclick="viewCommissionBreakdown({{ $payment->id }})"
                                                            class="btn btn-sm btn-warning mt-1">
                                                            <i class="fa fa-eye"></i> Commissions
                                                        </button>
                                                    @endif
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
        <!-- Commission Breakdown Modal -->
        <div class="modal fade" id="commissionBreakdownModal" tabindex="-1" role="dialog" aria-labelledby="commissionBreakdownModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="commissionBreakdownModalLabel">Commission Breakdown</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="commissionBreakdownContent">
                        <div class="text-center py-4" id="commissionBreakdownLoader">
                            <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                            <p class="mt-2">Loading breakdown...</p>
                        </div>
                        <div id="commissionBreakdownData" style="display: none;">
                            <div class="row text-center mb-4 border-bottom pb-3">
                                <div class="col-md-4 border-right">
                                    <h5 class="card-title text-info mb-1" id="cbRentAmount">--</h5>
                                    <p class="card-category text-muted mb-0">Total Rent Paid</p>
                                </div>
                                <div class="col-md-4 border-right">
                                    <h5 class="card-title text-warning mb-1" id="cbPlatformFee">--</h5>
                                    <p class="card-category text-muted mb-0">Platform Fee (2.5%)</p>
                                </div>
                                <div class="col-md-4">
                                    <h5 class="card-title text-success mb-1" id="cbCompanyRetained">--</h5>
                                    <p class="card-category text-muted mb-0">Company Retained</p>
                                </div>
                            </div>
                            
                            <h6 class="font-weight-bold mb-3">Marketer Distribution</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Tier</th>
                                            <th>Recipient</th>
                                            <th>Rate Applied</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cbMarketersTable">
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between mt-2 font-weight-bold">
                                <span>Total Distributed:</span>
                                <span id="cbTotalDistributed" class="text-success">--</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewReceipt(transactionId) {
            window.open('/dashboard/payments/' + transactionId + '/receipt', '_blank');
        }

        function viewCommissionBreakdown(paymentId) {
            $('#commissionBreakdownModal').modal('show');
            $('#commissionBreakdownLoader').show();
            $('#commissionBreakdownData').hide();
            $('#cbMarketersTable').empty();

            $.ajax({
                url: '/dashboard/payments/' + paymentId + '/commissions',
                type: 'GET',
                success: function(response) {
                    if(response.success) {
                        $('#cbRentAmount').text(response.payment.amount_formatted);
                        $('#cbPlatformFee').text(response.breakdown.totalPlatformFee);
                        $('#cbCompanyRetained').text(response.breakdown.companyRetained);
                        $('#cbTotalDistributed').text(response.breakdown.totalDistributed);

                        if(response.commissions.length > 0) {
                            response.commissions.forEach(function(c) {
                                $('#cbMarketersTable').append(`
                                    <tr>
                                        <td>${c.tier}</td>
                                        <td>${c.recipient}</td>
                                        <td>${c.rate}</td>
                                        <td>${c.amount}</td>
                                        <td><span class="badge badge-info">${c.status}</span></td>
                                    </tr>
                                `);
                            });
                        } else {
                            $('#cbMarketersTable').append('<tr><td colspan="5" class="text-center">No marketer commissions distributed for this payment.</td></tr>');
                        }

                        $('#commissionBreakdownLoader').hide();
                        $('#commissionBreakdownData').show();
                    }
                },
                error: function() {
                    $('#commissionBreakdownLoader').html('<p class="text-danger mt-2">Error loading data. Please try again.</p>');
                }
            });
        }
    </script>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#payments-table').DataTable({
                "order": [[6, "desc"]], // Sort by date column
                "pageLength": 25,
                "responsive": true
            });
        });
    </script>
@endpush
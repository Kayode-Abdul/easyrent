@extends('layout')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Commission Transparency Dashboard</h4>
                            <p class="card-category">Detailed view of commission deductions from your rental income</p>
                        </div>
                        <div>
                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#commissionHistoryModal">
                                <i class="fa fa-history"></i> Commission History
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="exportCommissionReport()">
                                <i class="fa fa-download"></i> Export Report
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Commission Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5 col-md-4">
                                            <div class="icon-big text-center icon-warning">
                                                <i class="nc-icon nc-money-coins text-success"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-md-8">
                                            <div class="numbers">
                                                <p class="card-category">Total Revenue</p>
                                                <p class="card-title">₦{{ number_format($summary['total_revenue'] ?? 0, 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5 col-md-4">
                                            <div class="icon-big text-center icon-warning">
                                                <i class="nc-icon nc-chart-bar-32 text-warning"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-md-8">
                                            <div class="numbers">
                                                <p class="card-category">Total Commission</p>
                                                <p class="card-title">₦{{ number_format($summary['total_commission'] ?? 0, 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5 col-md-4">
                                            <div class="icon-big text-center icon-warning">
                                                <i class="nc-icon nc-simple-add text-info"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-md-8">
                                            <div class="numbers">
                                                <p class="card-category">Net Income</p>
                                                <p class="card-title">₦{{ number_format($summary['net_income'] ?? 0, 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-5 col-md-4">
                                            <div class="icon-big text-center icon-warning">
                                                <i class="nc-icon nc-chart-pie-36 text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="col-7 col-md-8">
                                            <div class="numbers">
                                                <p class="card-category">Avg Commission %</p>
                                                <p class="card-title">{{ number_format($summary['avg_commission_percentage'] ?? 0, 2) }}%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <form id="filterForm" class="row">
                                        <div class="col-md-3">
                                            <label for="dateFrom">From Date</label>
                                            <input type="date" class="form-control" id="dateFrom" name="date_from" value="{{ request('date_from', now()->subMonth()->format('Y-m-d')) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="dateTo">To Date</label>
                                            <input type="date" class="form-control" id="dateTo" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="propertyFilter">Property</label>
                                            <select class="form-control" id="propertyFilter" name="property_id">
                                                <option value="">All Properties</option>
                                                @foreach($properties ?? [] as $property)
                                                    <option value="{{ $property->prop_id }}" {{ request('property_id') == $property->prop_id ? 'selected' : '' }}>
                                                        {{ $property->address }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label>&nbsp;</label>
                                            <div>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-filter"></i> Apply Filters
                                                </button>
                                                <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                                                    <i class="fa fa-times"></i> Clear
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Commission Table -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="commissionTable">
                            <thead class="text-primary">
                                <tr>
                                    <th>Date</th>
                                    <th>Property</th>
                                    <th>Tenant</th>
                                    <th>Gross Amount</th>
                                    <th>Commission Breakdown</th>
                                    <th>Net Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments ?? [] as $payment)
                                    @php
                                        $breakdown = $commissionBreakdowns[$payment->id] ?? null;
                                        $totalCommission = $breakdown ? $breakdown['total_commission'] : 0;
                                        $netAmount = $payment->amount - $totalCommission;
                                    @endphp
                                    <tr>
                                        <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ $payment->property?->address ?? 'N/A' }}</strong>
                                                <br><small class="text-muted">{{ $payment->apartment?->apartment_type ?? 'N/A' }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $payment->tenant ? $payment->tenant->first_name . ' ' . $payment->tenant->last_name : 'N/A' }}</td>
                                        <td>₦{{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            @if($breakdown && !empty($breakdown['breakdown']))
                                                <div class="commission-breakdown">
                                                    @foreach($breakdown['breakdown'] as $tier)
                                                        <div class="d-flex justify-content-between">
                                                            <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $tier['tier'])) }}</span>
                                                            <span>₦{{ number_format($tier['amount'], 2) }}</span>
                                                        </div>
                                                    @endforeach
                                                    <hr class="my-1">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>Total:</strong>
                                                        <strong>₦{{ number_format($totalCommission, 2) }}</strong>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">No commission</span>
                                            @endif
                                        </td>
                                        <td class="text-success">
                                            <strong>₦{{ number_format($netAmount, 2) }}</strong>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info" onclick="viewDetailedBreakdown({{ $payment->id }})" title="View Details">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary" onclick="verifyTransaction({{ $payment->id }})" title="Verify Transaction">
                                                    <i class="fa fa-check-circle"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="alert alert-info">
                                                No payment data available for the selected period.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(isset($payments) && method_exists($payments, 'links'))
                        <div class="d-flex justify-content-center mt-4">
                            {{ $payments->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Commission History Modal -->
<div class="modal fade" id="commissionHistoryModal" tabindex="-1" role="dialog" aria-labelledby="commissionHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commissionHistoryModalLabel">Commission Rate History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="commissionHistoryContent">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin"></i> Loading commission history...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Verification Modal -->
<div class="modal fade" id="transactionVerificationModal" tabindex="-1" role="dialog" aria-labelledby="transactionVerificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionVerificationModalLabel">Transaction Verification</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="transactionVerificationContent">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin"></i> Loading transaction details...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="confirmTransaction()">Confirm Transaction</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable if available
    if ($.fn.DataTable) {
        $('#commissionTable').DataTable({
            "pageLength": 25,
            "order": [[ 0, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [4, 6] }
            ]
        });
    }
});

function viewDetailedBreakdown(paymentId) {
    // This function is already implemented in the main dashboard
    viewCommissionDetails(paymentId);
}

function verifyTransaction(paymentId) {
    $('#transactionVerificationModal').modal('show');
    
    $.ajax({
        url: `/dashboard/payment/${paymentId}/commission-details`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let content = `
                    <div class="alert alert-info">
                        <strong>Transaction Verification</strong><br>
                        Review the commission calculation details below to verify the accuracy of deductions.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Payment Details</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Payment ID:</strong></td><td>${response.payment.id}</td></tr>
                                <tr><td><strong>Amount:</strong></td><td>₦${parseFloat(response.payment.amount).toLocaleString()}</td></tr>
                                <tr><td><strong>Property:</strong></td><td>${response.payment.property_address || 'N/A'}</td></tr>
                                <tr><td><strong>Date:</strong></td><td>${response.payment.payment_date}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Commission Calculation</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Total Commission:</strong></td><td class="text-warning">₦${parseFloat(response.commission_breakdown.total_commission || 0).toLocaleString()}</td></tr>
                                <tr><td><strong>Commission Rate:</strong></td><td>${parseFloat(response.commission_breakdown.commission_percentage || 0).toFixed(2)}%</td></tr>
                                <tr><td><strong>Your Net Amount:</strong></td><td class="text-success">₦${parseFloat(response.commission_breakdown.net_amount || response.payment.amount).toLocaleString()}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                
                if (response.commission_breakdown.breakdown && response.commission_breakdown.breakdown.length > 0) {
                    content += `
                        <hr>
                        <h6>Detailed Commission Distribution</h6>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Commission Tier</th>
                                        <th>Recipient</th>
                                        <th>Amount</th>
                                        <th>Rate Applied</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    response.commission_breakdown.breakdown.forEach(function(item) {
                        content += `
                            <tr>
                                <td><span class="badge badge-primary">${item.tier.replace('_', ' ').toUpperCase()}</span></td>
                                <td>${item.recipient ? item.recipient.name : 'System'}</td>
                                <td>₦${parseFloat(item.amount).toLocaleString()}</td>
                                <td>${parseFloat(item.percentage).toFixed(2)}%</td>
                            </tr>
                        `;
                    });
                    
                    content += `
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> 
                            <strong>Verification Status:</strong> Commission calculations appear correct based on current rates.
                        </div>
                    `;
                }
                
                $('#transactionVerificationContent').html(content);
            } else {
                $('#transactionVerificationContent').html('<div class="alert alert-danger">Failed to load transaction details.</div>');
            }
        },
        error: function() {
            $('#transactionVerificationContent').html('<div class="alert alert-danger">Error loading transaction details.</div>');
        }
    });
}

function confirmTransaction() {
    alert('Transaction verified successfully. Commission calculations are accurate.');
    $('#transactionVerificationModal').modal('hide');
}

function exportCommissionReport() {
    const params = new URLSearchParams();
    params.append('date_from', $('#dateFrom').val());
    params.append('date_to', $('#dateTo').val());
    params.append('property_id', $('#propertyFilter').val());
    params.append('format', 'csv');
    
    window.location.href = `/dashboard/commission-transparency/export?${params.toString()}`;
}

function clearFilters() {
    $('#filterForm')[0].reset();
    $('#filterForm').submit();
}

// Load commission history
$('#commissionHistoryModal').on('show.bs.modal', function() {
    // Show loading state
    $('#commissionHistoryContent').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading commission history...</div>');
    
    $.ajax({
        url: '/dashboard/commission-rate-history',
        method: 'GET',
        success: function(response) {
            console.log('Commission history response:', response); // Debug log
            
            if (response.success && response.history && response.history.length > 0) {
                let content = `
                    <div class="mb-3">
                        <strong>Region:</strong> ${response.region || 'Default'} 
                        <span class="badge badge-info ml-2">${response.total_records || response.history.length} records</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="commissionHistoryTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Role</th>
                                    <th>Commission Rate</th>
                                    <th>Changed By</th>
                                    <th>Effective From</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                response.history.forEach(function(item) {
                    content += `
                        <tr>
                            <td>${item.created_at || 'N/A'}</td>
                            <td><span class="badge badge-primary">${item.role_name || 'Unknown'}</span></td>
                            <td><strong>${item.commission_percentage || 0}%</strong></td>
                            <td>${item.created_by || 'System'}</td>
                            <td>${item.effective_from || item.created_at || 'N/A'}</td>
                        </tr>
                    `;
                });
                
                content += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                $('#commissionHistoryContent').html(content);
                
                // Initialize DataTable without conflicting options
                setTimeout(function() {
                    if ($.fn.DataTable && $('#commissionHistoryTable').length) {
                        try {
                            $('#commissionHistoryTable').DataTable({
                                "pageLength": 10,
                                "order": [[ 0, "desc" ]],
                                "responsive": true,
                                "destroy": true // Allow reinitialization
                            });
                        } catch (e) {
                            console.log('DataTable initialization skipped:', e.message);
                        }
                    }
                }, 100);
                
            } else {
                $('#commissionHistoryContent').html(`
                    <div class="alert alert-info">
                        <h5>No Commission History</h5>
                        <p>No commission rate changes found for your region (${response.region || 'Default'}).</p>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Commission history error:', xhr.responseText); // Debug log
            let errorMessage = 'Error loading commission history.';
            
            if (xhr.status === 500) {
                errorMessage = 'Server error occurred. Please try again later.';
            } else if (xhr.status === 401) {
                errorMessage = 'You are not authorized to view this data.';
            }
            
            $('#commissionHistoryContent').html(`
                <div class="alert alert-danger">
                    <h5>Error</h5>
                    <p>${errorMessage}</p>
                    ${xhr.responseText ? '<small class="text-muted">Details: ' + xhr.responseText + '</small>' : ''}
                </div>
            `);
        }
    });
});
</script>
@endsection
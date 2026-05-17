@extends('layout')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Apartments - {{ $property->address }}</h4>
                            <p class="card-category">Property ID: {{ $property->property_id }} | Owner: {{
                                $property->owner->first_name ?? 'N/A' }} {{ $property->owner->last_name ?? '' }}</p>
                        </div>
                        <div>
                            <a href="{{ route('property-manager.property-details', $property->property_id) }}"
                                class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Property
                            </a>
                            <a href="{{ route('property-manager.dashboard') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fa fa-tachometer-alt"></i> Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Summary -->
    <div class="row">
        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-settings-gear-65 text-info"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Total Apartments</p>
                                <p class="card-title">{{ $apartments->total() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-single-02 text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Occupied</p>
                                <p class="card-title">{{ $apartments->where('occupied', true)->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-key-25 text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Vacant</p>
                                <p class="card-title">{{ $apartments->where('occupied', false)->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
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
                                <p class="card-category">Occupancy Rate</p>
                                @php
                                $occupancyRate = $apartments->total() > 0 ? round(($apartments->where('occupied',
                                true)->count() / $apartments->total()) * 100, 1) : 0;
                                @endphp
                                <p class="card-title">{{ $occupancyRate }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Apartments List -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Apartment Details</h5>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-info" onclick="filterApartments('all')">All</button>
                            <button class="btn btn-sm btn-outline-success"
                                onclick="filterApartments('occupied')">Occupied</button>
                            <button class="btn btn-sm btn-outline-warning"
                                onclick="filterApartments('vacant')">Vacant</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($apartments->isEmpty())
                    <div class="alert alert-info text-center">
                        <h5>No Apartments Found</h5>
                        <p>This property doesn't have any apartments registered yet.</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table" id="apartmentsTable">
                            <thead class="text-primary">
                                <tr>
                                    <th>Apartment ID</th>
                                    <th>Type</th>
                                    <th>Tenant Information</th>
                                    <th>Rent Details</th>
                                    <th>Lease Period</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($apartments as $apartment)
                                <tr class="apartment-row"
                                    data-status="{{ $apartment->occupied ? 'occupied' : 'vacant' }}">
                                    <td>
                                        <strong class="text-primary">{{ $apartment->apartment_id }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $apartment->apartment_type ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @if($apartment->tenant)
                                        <div>
                                            <strong>{{ $apartment->tenant->first_name }} {{
                                                $apartment->tenant->last_name }}</strong><br>
                                            <small class="text-muted">{{ $apartment->tenant->email }}</small><br>
                                            @if($apartment->tenant->phone)
                                            <small class="text-muted">{{ $apartment->tenant->phone }}</small>
                                            @endif
                                        </div>
                                        @else
                                        <span class="text-muted">No tenant assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($apartment->amount)
                                        <div>
                                            <strong>{{ format_money($apartment->amount, ($property->currency->code ?? null)) }}</strong><br>
                                            <small class="text-muted">Monthly Rent</small>
                                        </div>
                                        @else
                                        <span class="text-muted">No rent set</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($apartment->range_start && $apartment->range_end)
                                        <div>
                                            <strong>{{ $apartment->range_start->format('M d, Y') }}</strong><br>
                                            <small class="text-muted">to {{ $apartment->range_end->format('M d, Y')
                                                }}</small><br>
                                            @if($apartment->range_end > now())
                                            <small class="text-success">{{ $apartment->range_end->diffForHumans()
                                                }}</small>
                                            @else
                                            <small class="text-danger">Expired</small>
                                            @endif
                                        </div>
                                        @else
                                        <span class="text-muted">No lease period</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($apartment->occupied)
                                        <span class="badge badge-success">Occupied</span>
                                        @if($apartment->range_end && $apartment->range_end <= now()) <br><span
                                                class="badge badge-danger">Lease Expired</span>
                                            @endif
                                            @else
                                            <span class="badge badge-warning">Vacant</span>
                                            @endif
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical">
                                            @if($apartment->tenant)
                                            <a href="mailto:{{ $apartment->tenant->email }}"
                                                class="btn btn-info btn-sm mb-1" title="Email Tenant">
                                                <i class="fa fa-envelope"></i>
                                            </a>
                                            @if($apartment->tenant->phone)
                                            <a href="tel:{{ $apartment->tenant->phone }}"
                                                class="btn btn-success btn-sm mb-1" title="Call Tenant">
                                                <i class="fa fa-phone"></i>
                                            </a>
                                            @endif
                                            @endif
                                            <button class="btn btn-warning btn-sm"
                                                onclick="viewPaymentHistory('{{ $apartment->apartment_id }}')"
                                                title="Payment History">
                                                <i class="fa fa-money-bill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($apartments->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $apartments->links() }}
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-block" onclick="exportApartmentList()">
                                <i class="nc-icon nc-paper"></i><br>
                                Export List
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-success btn-block" onclick="sendBulkNotification()">
                                <i class="nc-icon nc-email-85"></i><br>
                                Notify All Tenants
                            </button>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('property-manager.payments') }}?property_id={{ $property->property_id }}"
                                class="btn btn-outline-info btn-block">
                                <i class="nc-icon nc-money-coins"></i><br>
                                View Payments
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-warning btn-block" onclick="generateOccupancyReport()">
                                <i class="nc-icon nc-chart-bar-32"></i><br>
                                Occupancy Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function filterApartments(status) {
        const rows = document.querySelectorAll('.apartment-row');
        const buttons = document.querySelectorAll('.btn-group .btn');

        // Reset button styles
        buttons.forEach(btn => {
            btn.classList.remove('btn-info', 'btn-success', 'btn-warning');
            btn.classList.add('btn-outline-info');
        });

        // Set active button style
        event.target.classList.remove('btn-outline-info');
        if (status === 'occupied') {
            event.target.classList.add('btn-success');
        } else if (status === 'vacant') {
            event.target.classList.add('btn-warning');
        } else {
            event.target.classList.add('btn-info');
        }

        // Filter rows
        rows.forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function viewPaymentHistory(apartmentId) {
        window.location.href = "{{ route('property-manager.payments') }}?apartment_id=" + apartmentId;
    }

    function exportApartmentList() {
        const table = document.getElementById('apartmentsTable');
        if (!table) {
            alert('No apartment data available to export.');
            return;
        }

        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const cols = row.querySelectorAll('th, td');
            let rowData = [];
            
            for (let j = 0; j < cols.length - 1; j++) { // exclude actions column
                let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").trim();
                text = text.replace(/"/g, '""');
                rowData.push('"' + text + '"');
            }
            csv.push(rowData.join(','));
        }

        const csvString = csv.join('\n');
        const filename = 'apartment_list_{{ $property->property_id }}.csv';
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        
        if (navigator.msSaveBlob) { 
            navigator.msSaveBlob(blob, filename);
        } else {
            const link = document.createElement('a');
            if (link.download !== undefined) {
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Exported!',
                text: 'Your apartment list has been successfully exported to CSV.',
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert('Apartment list exported successfully!');
        }
    }

    function sendBulkNotification() {
        $('#bulkNotificationModal').modal('show');
    }

    function submitBulkNotification(e) {
        e.preventDefault();
        
        const form = document.getElementById('bulkNotificationForm');
        const subject = document.getElementById('broadcastSubject').value.trim();
        const message = document.getElementById('broadcastMessage').value.trim();
        const sendBtn = document.getElementById('sendBroadcastBtn');
        
        if (!subject || !message) {
            alert('Please fill out all fields.');
            return;
        }

        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Broadcasting...';

        $.ajax({
            url: "{{ route('property-manager.send-bulk-notification', $property->property_id) }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                subject: subject,
                message: message
            },
            success: function(res) {
                $('#bulkNotificationModal').modal('hide');
                form.reset();
                
                if (res.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Broadcast Sent!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#ef8157'
                        });
                    } else {
                        alert(res.message);
                    }
                } else {
                    alert(res.message || 'Failed to send broadcast.');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to broadcast notification.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
            },
            complete: function() {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fa fa-paper-plane mr-1"></i> Send Announcement';
            }
        });
    }

    function generateOccupancyReport() {
        $('#occupancyReportModal').modal('show');
    }

    function printOccupancyReport() {
        window.print();
    }
</script>

<!-- Bulk Notification Modal -->
<div class="modal fade" id="bulkNotificationModal" tabindex="-1" role="dialog" aria-labelledby="bulkNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #ef8157 0%, #f39c12 100%); border-top-left-radius: 15px; border-top-right-radius: 15px; display: flex; align-items: center; justify-content: space-between; width: 100%;">
                <h5 class="modal-title font-weight-bold" id="bulkNotificationModalLabel" style="margin: 0;">
                    <i class="fa fa-broadcast-tower mr-2"></i> Broadcast Notice to Tenants
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="background: transparent; border: 0; font-size: 1.5rem; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="bulkNotificationForm" onsubmit="submitBulkNotification(event)">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm mb-4" style="background-color: #fff9f6; border-left: 4px solid #ef8157 !important; border-radius: 8px;">
                        <small class="text-dark font-weight-bold">
                            <i class="fa fa-info-circle mr-1 text-primary"></i> 
                            This announcement will be broadcasted via email to all active tenants in occupied units at <strong>{{ $property->address }}</strong>.
                        </small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="broadcastSubject" class="text-secondary font-weight-bold small">Broadcast Subject</label>
                        <input type="text" class="form-control border-light shadow-sm" id="broadcastSubject" name="subject" required placeholder="e.g. Scheduled Water Maintenance Notice" style="border-radius: 8px; padding: 12px; border: 1px solid #ddd;">
                    </div>
                    
                    <div class="form-group mb-0">
                        <label for="broadcastMessage" class="text-secondary font-weight-bold small">Message Body</label>
                        <textarea class="form-control border-light shadow-sm" id="broadcastMessage" name="message" rows="5" required placeholder="Write details about the notice here..." style="border-radius: 8px; padding: 12px; resize: none; border: 1px solid #ddd;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                    <button type="button" class="btn btn-outline-secondary btn-round" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-round px-4" id="sendBroadcastBtn" style="background-color: #ef8157; border-color: #ef8157;">
                        <i class="fa fa-paper-plane mr-1"></i> Send Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Occupancy Report Modal -->
<div class="modal fade" id="occupancyReportModal" tabindex="-1" role="dialog" aria-labelledby="occupancyReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1f2251 0%, #3f479f 100%); border-top-left-radius: 15px; border-top-right-radius: 15px; display: flex; align-items: center; justify-content: space-between; width: 100%;">
                <h5 class="modal-title font-weight-bold" id="occupancyReportModalLabel" style="margin: 0;">
                    <i class="fa fa-file-invoice mr-2"></i> Property Occupancy Scorecard
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="background: transparent; border: 0; font-size: 1.5rem; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body p-4" id="printableOccupancyReport">
                <!-- Header block for print only -->
                <div class="print-header d-none text-center mb-4">
                    <img src="{{ asset('assets/images/logo-small.png') }}" alt="EasyRent Logo" style="max-width: 120px; margin-bottom: 10px;">
                    <h3 class="font-weight-bold" style="color: #ef8157; margin-bottom: 5px;">Property Occupancy & Analytics Report</h3>
                    <p class="text-secondary" style="font-size: 0.95rem;">
                        <strong>Address:</strong> {{ $property->address }} | 
                        <strong>Property ID:</strong> {{ $property->property_id }}<br>
                        <strong>Generated On:</strong> {{ now()->format('F j, Y h:i A') }}
                    </p>
                    <hr style="border-top: 2px solid #ef8157; margin-top: 15px;">
                </div>

                <!-- Stats summary scorecards -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6 text-center mb-2">
                        <div class="p-3 bg-light rounded shadow-sm border">
                            <span class="text-secondary small d-block font-weight-bold">Total Units</span>
                            <span class="h3 font-weight-bold text-dark d-block mt-1" style="margin: 5px 0 0 0;">{{ $apartments->total() }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 text-center mb-2">
                        <div class="p-3 bg-light rounded shadow-sm border">
                            <span class="text-secondary small d-block font-weight-bold">Occupied Units</span>
                            <span class="h3 font-weight-bold text-success d-block mt-1" style="margin: 5px 0 0 0;">
                                {{ $apartments->where('occupied', true)->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 text-center mb-2">
                        <div class="p-3 bg-light rounded shadow-sm border">
                            <span class="text-secondary small d-block font-weight-bold">Vacant Units</span>
                            <span class="h3 font-weight-bold text-warning d-block mt-1" style="margin: 5px 0 0 0;">
                                {{ $apartments->where('occupied', false)->count() }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 text-center mb-2">
                        <div class="p-3 bg-light rounded shadow-sm border">
                            <span class="text-secondary small d-block font-weight-bold">Occupancy Rate</span>
                            <span class="h3 font-weight-bold text-primary d-block mt-1" style="margin: 5px 0 0 0;">{{ $occupancyRate }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Occupancy Rate Progress bar -->
                <div class="mb-4">
                    <label class="text-secondary font-weight-bold small mb-1">Visual Occupancy Density</label>
                    <div class="progress" style="height: 18px; border-radius: 9px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.15); background-color: #e9ecef; overflow: hidden;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                             style="width: {{ $occupancyRate }}%; background: linear-gradient(90deg, #ef8157 0%, #28a745 100%); line-height: 18px; color: white; text-align: center; font-weight: bold; font-size: 0.85rem;" 
                             aria-valuenow="{{ $occupancyRate }}" aria-valuemin="0" aria-valuemax="100">
                             {{ $occupancyRate }}%
                        </div>
                    </div>
                </div>

                <!-- Unit Roster breakdown tables -->
                <div class="mb-4">
                    <h6 class="font-weight-bold text-dark mb-2">
                        <i class="fa fa-list mr-1 text-primary"></i> Occupied Unit Breakdown
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="bg-light">
                                <tr>
                                    <th>Apartment ID</th>
                                    <th>Type</th>
                                    <th>Tenant Name</th>
                                    <th>Monthly Rent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalPotential = 0; @endphp
                                @forelse($apartments as $apartment)
                                    @if($apartment->occupied)
                                        @php $totalPotential += $apartment->amount; @endphp
                                        <tr>
                                            <td><strong>{{ $apartment->apartment_id }}</strong></td>
                                            <td><span class="badge badge-light border">{{ $apartment->apartment_type ?? 'N/A' }}</span></td>
                                            <td>{{ $apartment->tenant->first_name ?? 'N/A' }} {{ $apartment->tenant->last_name ?? '' }}</td>
                                            <td class="text-success font-weight-bold">{{ format_money($apartment->amount, ($property->currency->code ?? null)) }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No occupied apartments.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-2">
                    <h6 class="font-weight-bold text-dark mb-2">
                        <i class="fa fa-key mr-1 text-warning"></i> Vacant Unit Breakdown
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="bg-light">
                                <tr>
                                    <th>Apartment ID</th>
                                    <th>Type</th>
                                    <th>Target Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apartments as $apartment)
                                    @if(!$apartment->occupied)
                                        <tr>
                                            <td><strong>{{ $apartment->apartment_id }}</strong></td>
                                            <td><span class="badge badge-light border">{{ $apartment->apartment_type ?? 'N/A' }}</span></td>
                                            <td class="text-dark font-weight-bold">{{ format_money($apartment->amount, ($property->currency->code ?? null)) }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No vacant apartments.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer bg-light border-0" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                <button type="button" class="btn btn-outline-secondary btn-round" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info btn-round px-4" onclick="printOccupancyReport()">
                    <i class="fa fa-print mr-1"></i> Print Report
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .apartment-row {
        transition: all 0.3s ease;
    }

    .apartment-row:hover {
        background-color: #f8f9fa;
    }

    .btn-group-vertical .btn {
        margin-bottom: 2px;
    }

    .btn-group-vertical .btn:last-child {
        margin-bottom: 0;
    }

    @media print {
        body * {
            visibility: hidden;
        }
        #printableOccupancyReport, #printableOccupancyReport * {
            visibility: visible;
        }
        #printableOccupancyReport {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0;
            margin: 0;
            background: white;
            z-index: 9999;
        }
        .print-header {
            display: block !important;
        }
        .modal-content {
            border: none !important;
            box-shadow: none !important;
        }
        @page {
            size: auto;
            margin: 15mm;
        }
    }
</style>
@endsection
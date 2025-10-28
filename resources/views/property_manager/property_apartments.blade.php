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
                            <p class="card-category">Property ID: {{ $property->prop_id }} | Owner: {{ $property->owner->first_name ?? 'N/A' }} {{ $property->owner->last_name ?? '' }}</p>
                        </div>
                        <div>
                            <a href="{{ route('property-manager.property-details', $property->prop_id) }}" class="btn btn-outline-secondary btn-sm">
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
                                <i class="nc-icon nc-home-gear text-info"></i>
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
                                    $occupancyRate = $apartments->total() > 0 ? round(($apartments->where('occupied', true)->count() / $apartments->total()) * 100, 1) : 0;
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
                            <button class="btn btn-sm btn-outline-success" onclick="filterApartments('occupied')">Occupied</button>
                            <button class="btn btn-sm btn-outline-warning" onclick="filterApartments('vacant')">Vacant</button>
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
                                        <tr class="apartment-row" data-status="{{ $apartment->occupied ? 'occupied' : 'vacant' }}">
                                            <td>
                                                <strong class="text-primary">{{ $apartment->apartment_id }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $apartment->apartment_type ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($apartment->tenant)
                                                    <div>
                                                        <strong>{{ $apartment->tenant->first_name }} {{ $apartment->tenant->last_name }}</strong><br>
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
                                                        <strong>₦{{ number_format($apartment->amount, 2) }}</strong><br>
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
                                                        <small class="text-muted">to {{ $apartment->range_end->format('M d, Y') }}</small><br>
                                                        @if($apartment->range_end > now())
                                                            <small class="text-success">{{ $apartment->range_end->diffForHumans() }}</small>
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
                                                    @if($apartment->range_end && $apartment->range_end <= now())
                                                        <br><span class="badge badge-danger">Lease Expired</span>
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
                            <a href="{{ route('property-manager.payments') }}?property_id={{ $property->prop_id }}" class="btn btn-outline-info btn-block">
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
    alert('Payment history for apartment ' + apartmentId + ' - Feature coming soon!');
}

function exportApartmentList() {
    alert('Export apartment list - Feature coming soon!');
}

function sendBulkNotification() {
    if (confirm('Send notification to all tenants in this property?')) {
        alert('Bulk notification feature coming soon!');
    }
}

function generateOccupancyReport() {
    alert('Generate occupancy report - Feature coming soon!');
}
</script>

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
</style>
@endsection
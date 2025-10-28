@extends('layout')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Managed Properties</h4>
                            <p class="card-category">All properties assigned to you for management</p>
                        </div>
                        <a href="{{ route('property-manager.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Filter Properties</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="search">Search</label>
                                <input type="text" class="form-control" name="search" id="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Property ID, Address, Location...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="property_type">Property Type</label>
                                <select class="form-control" name="property_type" id="property_type">
                                    <option value="">All Types</option>
                                    <option value="1" {{ request('property_type') == '1' ? 'selected' : '' }}>Mansion</option>
                                    <option value="2" {{ request('property_type') == '2' ? 'selected' : '' }}>Duplex</option>
                                    <option value="3" {{ request('property_type') == '3' ? 'selected' : '' }}>Flat</option>
                                    <option value="4" {{ request('property_type') == '4' ? 'selected' : '' }}>Terrace</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" name="status" id="status">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="state">State</label>
                                <select class="form-control" name="state" id="state">
                                    <option value="">All States</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>
                                            {{ $state }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary mr-2">
                                        <i class="fa fa-search"></i> Filter
                                    </button>
                                    <a href="{{ route('property-manager.managed-properties') }}" class="btn btn-outline-secondary">
                                        <i class="fa fa-refresh"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Properties List -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Properties ({{ $properties->total() }} total)</h5>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-info" onclick="toggleView('grid')">
                                <i class="fa fa-th"></i> Grid
                            </button>
                            <button class="btn btn-sm btn-info" onclick="toggleView('table')">
                                <i class="fa fa-list"></i> Table
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($properties->isEmpty())
                        <div class="alert alert-info text-center">
                            <h5>No Properties Found</h5>
                            <p>No properties match your current filters.</p>
                            <a href="{{ route('property-manager.managed-properties') }}" class="btn btn-info">
                                Clear Filters
                            </a>
                        </div>
                    @else
                        <!-- Table View (Default) -->
                        <div id="tableView" class="table-responsive">
                            <table class="table">
                                <thead class="text-primary">
                                    <tr>
                                        <th>Property Details</th>
                                        <th>Owner</th>
                                        <th>Type & Status</th>
                                        <th>Apartments</th>
                                        <th>Occupancy</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($properties as $property)
                                        @php
                                            $totalApartments = $property->apartments->count();
                                            $occupiedApartments = $property->apartments->where('occupied', true)->count();
                                            $occupancyRate = $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100, 1) : 0;
                                            
                                            $propertyTypes = [
                                                1 => 'Mansion',
                                                2 => 'Duplex', 
                                                3 => 'Flat',
                                                4 => 'Terrace'
                                            ];
                                        @endphp
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $property->prop_id }}</strong><br>
                                                    <span class="text-primary">{{ $property->address }}</span><br>
                                                    <small class="text-muted">{{ $property->lga }}, {{ $property->state }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($property->owner)
                                                    <div>
                                                        <strong>{{ $property->owner->first_name }} {{ $property->owner->last_name }}</strong><br>
                                                        <small class="text-muted">{{ $property->owner->email }}</small><br>
                                                        <small class="text-muted">{{ $property->owner->phone ?? 'No phone' }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Owner not found</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    {{ $propertyTypes[$property->prop_type] ?? 'Other' }}
                                                </span><br>
                                                @if($property->status)
                                                    <span class="badge badge-{{ $property->status === 'approved' ? 'success' : ($property->status === 'pending' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($property->status) }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">No Status</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <h5 class="mb-1">{{ $totalApartments }}</h5>
                                                    <small class="text-muted">Total Units</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-center">
                                                    <div class="progress mb-2" style="height: 20px;">
                                                        <div class="progress-bar bg-{{ $occupancyRate >= 80 ? 'success' : ($occupancyRate >= 50 ? 'warning' : 'danger') }}" 
                                                             role="progressbar" 
                                                             style="width: {{ $occupancyRate }}%"
                                                             aria-valuenow="{{ $occupancyRate }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            {{ $occupancyRate }}%
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">{{ $occupiedApartments }}/{{ $totalApartments }} occupied</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group-vertical">
                                                    <a href="{{ route('property-manager.property-details', $property->prop_id) }}" 
                                                       class="btn btn-info btn-sm mb-1" title="View Details">
                                                        <i class="fa fa-eye"></i> Details
                                                    </a>
                                                    <a href="{{ route('property-manager.property-apartments', $property->prop_id) }}" 
                                                       class="btn btn-success btn-sm mb-1" title="View Apartments">
                                                        <i class="fa fa-home"></i> Apartments
                                                    </a>
                                                    <button class="btn btn-warning btn-sm" 
                                                            onclick="alert('Contact owner: {{ $property->owner->email ?? 'No email' }}')" 
                                                            title="Contact Owner">
                                                        <i class="fa fa-envelope"></i> Contact
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Grid View (Hidden by default) -->
                        <div id="gridView" class="row" style="display: none;">
                            @foreach($properties as $property)
                                @php
                                    $totalApartments = $property->apartments->count();
                                    $occupiedApartments = $property->apartments->where('occupied', true)->count();
                                    $occupancyRate = $totalApartments > 0 ? round(($occupiedApartments / $totalApartments) * 100, 1) : 0;
                                    
                                    $propertyTypes = [
                                        1 => 'Mansion',
                                        2 => 'Duplex', 
                                        3 => 'Flat',
                                        4 => 'Terrace'
                                    ];
                                @endphp
                                <div class="col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="card-title mb-0">{{ $property->prop_id }}</h6>
                                                <span class="badge badge-primary">{{ $propertyTypes[$property->prop_type] ?? 'Other' }}</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h6 class="text-primary">{{ $property->address }}</h6>
                                            <p class="text-muted mb-2">{{ $property->lga }}, {{ $property->state }}</p>
                                            
                                            @if($property->owner)
                                                <p class="mb-2">
                                                    <strong>Owner:</strong><br>
                                                    {{ $property->owner->first_name }} {{ $property->owner->last_name }}
                                                </p>
                                            @endif
                                            
                                            <div class="row text-center mb-3">
                                                <div class="col-6">
                                                    <h5>{{ $totalApartments }}</h5>
                                                    <small class="text-muted">Total Units</small>
                                                </div>
                                                <div class="col-6">
                                                    <h5>{{ $occupancyRate }}%</h5>
                                                    <small class="text-muted">Occupied</small>
                                                </div>
                                            </div>
                                            
                                            <div class="progress mb-3" style="height: 10px;">
                                                <div class="progress-bar bg-{{ $occupancyRate >= 80 ? 'success' : ($occupancyRate >= 50 ? 'warning' : 'danger') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $occupancyRate }}%">
                                                </div>
                                            </div>
                                            
                                            <div class="btn-group btn-group-sm w-100">
                                                <a href="{{ route('property-manager.property-details', $property->prop_id) }}" 
                                                   class="btn btn-info">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('property-manager.property-apartments', $property->prop_id) }}" 
                                                   class="btn btn-success">
                                                    <i class="fa fa-home"></i>
                                                </a>
                                                <button class="btn btn-warning" 
                                                        onclick="alert('Contact: {{ $property->owner->email ?? 'No email' }}')">
                                                    <i class="fa fa-envelope"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($properties->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $properties->appends(request()->query())->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleView(viewType) {
    const tableView = document.getElementById('tableView');
    const gridView = document.getElementById('gridView');
    const buttons = document.querySelectorAll('.btn-group .btn');
    
    buttons.forEach(btn => btn.classList.remove('btn-info'));
    buttons.forEach(btn => btn.classList.add('btn-outline-info'));
    
    if (viewType === 'grid') {
        tableView.style.display = 'none';
        gridView.style.display = 'flex';
        event.target.classList.remove('btn-outline-info');
        event.target.classList.add('btn-info');
    } else {
        tableView.style.display = 'block';
        gridView.style.display = 'none';
        event.target.classList.remove('btn-outline-info');
        event.target.classList.add('btn-info');
    }
}
</script>
@endsection
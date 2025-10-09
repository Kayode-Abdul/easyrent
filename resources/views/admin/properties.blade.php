@extends('admin.layouts.app')

@section('content')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">
                                <i class="nc-icon nc-istanbul"></i> Property Oversight Dashboard
                            </h4>
                            <p class="card-category">Comprehensive property monitoring and management</p>
                        </div>
                        <div class="col-md-4">
                            <a href="/dashboard" class="btn btn-info btn-sm float-right">
                                <i class="nc-icon nc-minimal-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Statistics -->
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-istanbul text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Total Properties</p>
                                <p class="card-title">{{ number_format($stats['total_properties']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-calendar text-success"></i>
                        {{ $stats['properties_added_today'] }} added today
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-check-2 text-info"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Occupied Units</p>
                                <p class="card-title">{{ number_format($stats['occupied_apartments']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-arrow-up text-success"></i>
                        {{ round(($stats['occupied_apartments'] / max($stats['occupied_apartments'] + $stats['vacant_apartments'], 1)) * 100, 1) }}% occupancy
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-time-alarm text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Vacant Units</p>
                                <p class="card-title">{{ number_format($stats['vacant_apartments']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-home text-warning"></i>
                        Available for rent
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5 col-md-4">
                            <div class="icon-big text-center icon-warning">
                                <i class="nc-icon nc-chart-pie-35 text-primary"></i>
                            </div>
                        </div>
                        <div class="col-7 col-md-8">
                            <div class="numbers">
                                <p class="card-category">Total Units</p>
                                <p class="card-title">{{ number_format($stats['occupied_apartments'] + $stats['vacant_apartments']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="stats">
                        <i class="fa fa-building text-primary"></i>
                        All apartments
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Analytics Charts -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="nc-icon nc-chart-bar-32"></i> Property Distribution by Location</h5>
                    <p class="card-category">Top 10 locations with most properties</p>
                </div>
                <div class="card-body">
                    <canvas id="locationChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="nc-icon nc-chart-pie-36"></i> Property Categories</h5>
                    <p class="card-category">Distribution by property type</p>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" width="200" height="200"></canvas>
                </div>
                <div class="card-footer">
                    <div class="legend">
                        @if(isset($stats['properties_by_category']))
                            @foreach($stats['properties_by_category'] as $category => $count)
                                <i class="fa fa-circle text-primary"></i> {{ ucfirst($category) }} ({{ $count }})
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Management Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="nc-icon nc-settings"></i> Property Management Tools</h5>
                    <p class="card-category">Quick actions for property administration</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-success btn-block" onclick="bulkAction('approve')">
                                <i class="nc-icon nc-check-2"></i><br>
                                Bulk Approve Properties
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-warning btn-block" onclick="bulkAction('featured')">
                                <i class="nc-icon nc-diamond"></i><br>
                                Mark as Featured
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-info btn-block" onclick="exportProperties()">
                                <i class="nc-icon nc-paper"></i><br>
                                Export Properties
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary btn-block" onclick="generateReport()">
                                <i class="nc-icon nc-chart-bar-32"></i><br>
                                Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Properties Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">All Properties</h5>
                    <p class="card-category">Comprehensive property listing with management options</p>
                </div>
                <div class="card-body">
                    <!-- Search and Filter -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="propertySearch" placeholder="Search properties...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="locationFilter">
                                <option value="">All Locations</option>
                                @if(isset($stats['properties_by_location']))
                                    @foreach($stats['properties_by_location'] as $location => $count)
                                        <option value="{{ $location }}">{{ $location }} ({{ $count }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="categoryFilter">
                                <option value="">All Categories</option>
                                @if(isset($stats['properties_by_category']))
                                    @foreach($stats['properties_by_category'] as $category => $count)
                                        <option value="{{ $category }}">{{ ucfirst($category) }} ({{ $count }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary btn-block" onclick="applyFilters()">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="text-primary">
                                <th>
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>Property</th>
                                <th>Owner</th>
                                <th>Location</th>
                                <th>Category</th>
                                <th>Apartments</th>
                                <th>Occupancy</th>
                                <th>Added</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </thead>
                            <tbody>
                                @foreach($properties as $property)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="property-checkbox" value="{{ $property->prop_id }}">
                                        </td>
                                        <td>
                                            <div class="property-info">
                                                <strong>{{ $property->title ?? 'Property #' . $property->prop_id }}</strong>
                                                <br>
                                                <small class="text-muted">ID: {{ $property->prop_id }}</small>
                                                @if($property->price)
                                                    <br><small class="text-success">₦{{ number_format($property->price) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($property->user)
                                                <strong>{{ $property->user->first_name }} {{ $property->user->last_name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $property->user->email_address }}</small>
                                            @else
                                                <span class="text-muted">No owner</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $property->location ?? $property->state }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ ucfirst($property->category ?? 'General') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $property->apartments_count ?? 0 }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $total = $property->apartments_count ?? 0;
                                                $occupied = $property->apartments->where('occupied', true)->count();
                                                $occupancyRate = $total > 0 ? round(($occupied / $total) * 100) : 0;
                                            @endphp
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $occupancyRate ?>%">
                                                    {{ $occupancyRate }}%
                                                </div>
                                            </div>
                                            <small>{{ $occupied }}/{{ $total }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $property->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-success">Active</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="/dashboard/property/{{ $property->prop_id }}" class="btn btn-sm btn-info" title="View">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="/dashboard/property/{{ $property->prop_id }}/edit" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" onclick="deleteProperty({{ $property->prop_id }})" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="row">
                        <div class="col-md-12">
                            {{ $properties->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Location Chart
const locationCtx = document.getElementById('locationChart').getContext('2d');
new Chart(locationCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($stats['properties_by_location']->toArray() ?? [])) !!},
        datasets: [{
            label: 'Properties',
            data: {!! json_encode(array_values($stats['properties_by_location']->toArray() ?? [])) !!},
            backgroundColor: '#51cbce',
            borderColor: '#51cbce',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_keys($stats['properties_by_category']->toArray() ?? [])) !!},
        datasets: [{
            data: {!! json_encode(array_values($stats['properties_by_category']->toArray() ?? [])) !!},
            backgroundColor: [
                '#51cbce',
                '#fbc658',
                '#ef8157',
                '#6bd098',
                '#e14eca'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Management Functions
function bulkAction(action) {
    const checkedBoxes = document.querySelectorAll('.property-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select properties first');
        return;
    }
    
    const propertyIds = Array.from(checkedBoxes).map(box => box.value);
    showNotification(`${action} action will be implemented for ${propertyIds.length} properties`, 'info');
}

function exportProperties() {
    showNotification('Property export feature will be available soon!', 'info');
}

function generateReport() {
    showNotification('Report generation feature will be available soon!', 'info');
}

function deleteProperty(propId) {
    if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
        showNotification('Delete functionality will be implemented', 'warning');
    }
}

function applyFilters() {
    const search = document.getElementById('propertySearch').value.toLowerCase();
    const location = document.getElementById('locationFilter').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value.toLowerCase();
    
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const propertyText = row.cells[1].textContent.toLowerCase();
        const propertyLocation = row.cells[3].textContent.toLowerCase();
        const propertyCategory = row.cells[4].textContent.toLowerCase();
        
        const matchesSearch = !search || propertyText.includes(search);
        const matchesLocation = !location || propertyLocation.includes(location);
        const matchesCategory = !category || propertyCategory.includes(category);
        
        if (matchesSearch && matchesLocation && matchesCategory) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Select All functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.property-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification && notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}
</script>

<style>
.progress {
    background-color: #f4f3ef;
}

.property-info strong {
    display: block;
    margin-bottom: 2px;
}

.card-stats:hover {
    transform: translateY(-2px);
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.btn:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.table td {
    vertical-align: middle;
}
</style>

@endsection

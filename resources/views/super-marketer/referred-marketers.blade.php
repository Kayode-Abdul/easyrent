@extends('layout')

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
                                <i class="fas fa-users text-primary me-2"></i>
                                Referred Marketers
                            </h4>
                            <p class="text-muted mb-0">Manage and track performance of marketers in your network</p>
                        </div>
                        <div>
                            <a href="{{ route('super-marketer.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Marketers
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $summary['total'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Marketers
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $summary['active'] }}
                            </div>
                            <div class="text-xs text-success">
                                {{ $summary['active_percentage'] }}% of total
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Approval
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $summary['pending'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Inactive
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $summary['inactive'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('super-marketer.referred-marketers') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Name or email...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="region" class="form-label">Region</label>
                            <select class="form-select" id="region" name="region">
                                <option value="">All Regions</option>
                                <option value="Lagos" {{ request('region') === 'Lagos' ? 'selected' : '' }}>Lagos</option>
                                <option value="Abuja" {{ request('region') === 'Abuja' ? 'selected' : '' }}>Abuja</option>
                                <option value="Port Harcourt" {{ request('region') === 'Port Harcourt' ? 'selected' : '' }}>Port Harcourt</option>
                                <option value="Kano" {{ request('region') === 'Kano' ? 'selected' : '' }}>Kano</option>
                                <option value="Ibadan" {{ request('region') === 'Ibadan' ? 'selected' : '' }}>Ibadan</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                Filter
                            </button>
                            <a href="{{ route('super-marketer.referred-marketers') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Marketers List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Referred Marketers ({{ $marketers->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($marketers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Marketer</th>
                                        <th>Contact</th>
                                        <th>Region</th>
                                        <th>Status</th>
                                        <th>Performance</th>
                                        <th>Commission</th>
                                        <th>Join Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($marketers as $marketer)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        @if($marketer->photo)
                                                            <img src="{{ asset('storage/' . $marketer->photo) }}" 
                                                                 alt="{{ $marketer->first_name }}" 
                                                                 class="rounded-circle" width="40" height="40">
                                                        @else
                                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                                 style="width: 40px; height: 40px;">
                                                                <span class="text-white font-weight-bold">
                                                                    {{ substr($marketer->first_name, 0, 1) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold">
                                                            {{ $marketer->first_name }} {{ $marketer->last_name }}
                                                        </div>
                                                        @if($marketer->marketerProfile && $marketer->marketerProfile->business_name)
                                                            <div class="text-muted small">
                                                                {{ $marketer->marketerProfile->business_name }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>{{ $marketer->email }}</div>
                                                @if($marketer->phone)
                                                    <div class="text-muted small">{{ $marketer->phone }}</div>
                                                @endif
                                            </td>
                                            <td>{{ $marketer->state ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    $marketer->marketer_status === 'active' ? 'success' : 
                                                    ($marketer->marketer_status === 'pending' ? 'warning' : 'danger') 
                                                }}">
                                                    {{ ucfirst($marketer->marketer_status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div><strong>{{ $marketer->performance_metrics['total_referrals'] }}</strong> referrals</div>
                                                    <div class="text-success">{{ $marketer->performance_metrics['conversion_rate'] }}% conversion</div>
                                                    <div class="text-muted">{{ $marketer->performance_metrics['this_month_referrals'] }} this month</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div class="font-weight-bold">₦{{ number_format($marketer->performance_metrics['total_commission'], 2) }}</div>
                                                    <div class="text-muted">Total earned</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    {{ $marketer->created_at->format('M d, Y') }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('super-marketer.marketer.performance', $marketer->user_id) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="View Performance">
                                                        <i class="fas fa-chart-line"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            onclick="viewMarketerDetails({{ $marketer->user_id }})" 
                                                            title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                Showing {{ $marketers->firstItem() }} to {{ $marketers->lastItem() }} of {{ $marketers->total() }} results
                            </div>
                            <div>
                                {{ $marketers->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-4x text-muted mb-4"></i>
                            <h5 class="text-muted">No Marketers Found</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'status', 'region']))
                                    No marketers match your current filters. Try adjusting your search criteria.
                                @else
                                    You haven't referred any marketers yet. Start building your network!
                                @endif
                            </p>
                            @if(!request()->hasAny(['search', 'status', 'region']))
                                <a href="{{ route('super-marketer.dashboard') }}" class="btn btn-primary">
                                    <i class="fas fa-link me-1"></i>
                                    Generate Referral Link
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Marketer Details Modal -->
<div class="modal fade" id="marketerDetailsModal" tabindex="-1" aria-labelledby="marketerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="marketerDetailsModalLabel">
                    <i class="fas fa-user me-2"></i>
                    Marketer Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="marketerDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="viewPerformanceBtn">
                    <i class="fas fa-chart-line me-1"></i>
                    View Performance
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewMarketerDetails(marketerId) {
    const modal = new bootstrap.Modal(document.getElementById('marketerDetailsModal'));
    const content = document.getElementById('marketerDetailsContent');
    const performanceBtn = document.getElementById('viewPerformanceBtn');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch marketer details (you would implement this endpoint)
    fetch(`/super-marketer/marketer/${marketerId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = generateMarketerDetailsHTML(data.marketer);
                performanceBtn.onclick = () => {
                    window.location.href = `/super-marketer/marketer/${marketerId}/performance`;
                };
            } else {
                content.innerHTML = '<div class="alert alert-danger">Error loading marketer details.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-danger">Error loading marketer details.</div>';
        });
}

function generateMarketerDetailsHTML(marketer) {
    return `
        <div class="row">
            <div class="col-md-4 text-center">
                ${marketer.photo ? 
                    `<img src="/storage/${marketer.photo}" alt="${marketer.first_name}" class="rounded-circle mb-3" width="100" height="100">` :
                    `<div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px;">
                        <span class="text-white h3">${marketer.first_name.charAt(0)}</span>
                    </div>`
                }
                <h5>${marketer.first_name} ${marketer.last_name}</h5>
                <span class="badge bg-${marketer.marketer_status === 'active' ? 'success' : 'warning'}">
                    ${marketer.marketer_status.charAt(0).toUpperCase() + marketer.marketer_status.slice(1)}
                </span>
            </div>
            <div class="col-md-8">
                <h6>Contact Information</h6>
                <p><strong>Email:</strong> ${marketer.email}</p>
                <p><strong>Phone:</strong> ${marketer.phone || 'N/A'}</p>
                <p><strong>Region:</strong> ${marketer.state || 'N/A'}</p>
                
                <h6 class="mt-4">Performance Summary</h6>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary">${marketer.performance.total_referrals}</h4>
                            <small class="text-muted">Total Referrals</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-success">₦${marketer.performance.total_commission.toLocaleString()}</h4>
                            <small class="text-muted">Total Commission</small>
                        </div>
                    </div>
                </div>
                
                <h6 class="mt-4">Business Information</h6>
                ${marketer.marketer_profile ? `
                    <p><strong>Business Name:</strong> ${marketer.marketer_profile.business_name || 'N/A'}</p>
                    <p><strong>Business Type:</strong> ${marketer.marketer_profile.business_type || 'N/A'}</p>
                    <p><strong>Experience:</strong> ${marketer.marketer_profile.years_of_experience || 'N/A'} years</p>
                ` : '<p class="text-muted">No business profile available</p>'}
            </div>
        </div>
    `;
}
</script>
@endpush

@push('head')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    color: #6c757d;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endpush
@endsection
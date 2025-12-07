@extends('layouts.admin')

@section('title', 'Regional Manager Management')

@push('styles')
<style>
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        transition: transform 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .stats-card.green {
        background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
    }
    
    .stats-card.orange {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .stats-card.blue {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .stats-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 16px;
    }
    
    .stats-value {
        font-size: 36px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    
    .stats-label {
        font-size: 14px;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .manager-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .manager-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    
    .manager-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 20px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .manager-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
        color: white;
        box-shadow: 0 4px 12px rgba(62, 129, 137, 0.3);
    }
    
    .manager-body {
        padding: 20px;
    }
    
    .region-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        margin: 4px;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        color: #1976d2;
        border: 1px solid #90caf9;
    }
    
    .action-btn-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .action-btn {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }
    
    .action-btn.view {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .action-btn.view:hover {
        background: #1976d2;
        color: white;
    }
    
    .action-btn.edit {
        background: #fff3e0;
        color: #f57c00;
    }
    
    .action-btn.edit:hover {
        background: #f57c00;
        color: white;
    }
    
    .action-btn.add {
        background: #e8f5e9;
        color: #388e3c;
    }
    
    .action-btn.add:hover {
        background: #388e3c;
        color: white;
    }
    
    .action-btn.delete {
        background: #ffebee;
        color: #d32f2f;
    }
    
    .action-btn.delete:hover {
        background: #d32f2f;
        color: white;
    }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 24px;
    }
    
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 16px;
        margin-bottom: 32px;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
    }
    
    .page-header h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    
    .page-header p {
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    
    .empty-state-icon {
        font-size: 64px;
        color: #dee2e6;
        margin-bottom: 20px;
    }
    
    .empty-state h3 {
        color: #6c757d;
        margin-bottom: 12px;
    }
    
    .empty-state p {
        color: #adb5bd;
    }
</style>
@endpush

@section('content')
<div class="content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fa fa-users-cog me-3"></i>Regional Manager Management</h1>
                <p>Manage regional managers and their assigned territories</p>
            </div>
            <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#bulkAssignModal">
                <i class="fa fa-plus-circle me-2"></i>Bulk Assign Regions
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon">
                    <i class="fa fa-users"></i>
                </div>
                <div class="stats-value">{{ $regionalManagers->total() }}</div>
                <div class="stats-label">Total Managers</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card green">
                <div class="stats-icon">
                    <i class="fa fa-map-marker"></i>
                </div>
                <div class="stats-value">{{ $allRegions->count() }}</div>
                <div class="stats-label">Total Regions</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card orange">
                <div class="stats-icon">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div class="stats-value">
                    {{ $regionalManagers->sum(function($m) { return $m->regionalScopes->count(); }) }}
                </div>
                <div class="stats-label">Active Assignments</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card blue">
                <div class="stats-icon">
                    <i class="fa fa-chart-line"></i>
                </div>
                <div class="stats-value">
                    {{ $regionalManagers->count() > 0 ? number_format($regionalManagers->sum(function($m) { return $m->regionalScopes->count(); }) / $regionalManagers->count(), 1) : 0 }}
                </div>
                <div class="stats-label">Avg Regions/Manager</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label for="search" class="form-label fw-bold">
                    <i class="fa fa-search me-2"></i>Search Manager
                </label>
                <input type="text" class="form-control form-control-lg" id="search" name="search" 
                       value="{{ $search }}" placeholder="Search by name or email...">
            </div>
            <div class="col-md-5">
                <label for="region" class="form-label fw-bold">
                    <i class="fa fa-filter me-2"></i>Filter by Region
                </label>
                <select class="form-select form-select-lg" id="region" name="region">
                    <option value="">All Regions</option>
                    @foreach($allRegions as $regionOption)
                        <option value="{{ e($regionOption) }}" {{ $region == $regionOption ? 'selected' : '' }}>
                            {{ e($regionOption) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="d-grid gap-2 w-100">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-search"></i> Apply
                    </button>
                    <a href="{{ route('admin.regional-managers.index') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-redo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Bulk Selection Info -->
    <div id="bulkSelectionInfo" class="alert alert-info d-none mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fa fa-info-circle me-2"></i>
                <strong><span id="selectedCount">0</span> manager(s) selected</strong>
            </div>
            <div id="selectedManagers"></div>
        </div>
    </div>

    <!-- Regional Managers Grid -->
    @if($regionalManagers->count() > 0)
        <div class="row">
            @foreach($regionalManagers as $manager)
                @php
                    $scopes = $manager->getFormattedRegionalScopes();
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="manager-card">
                        <div class="manager-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <input type="checkbox" class="form-check-input me-3 manager-checkbox" 
                                           value="{{ $manager->user_id }}" style="width: 20px; height: 20px;">
                                    <div class="manager-avatar">
                                        {{ strtoupper(substr($manager->first_name, 0, 1)) }}{{ strtoupper(substr($manager->last_name, 0, 1)) }}
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-0">{{ $manager->first_name }} {{ $manager->last_name }}</h5>
                                        <small class="text-muted">ID: {{ $manager->user_id }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="manager-body">
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">
                                    <i class="fa fa-envelope me-1"></i>Email
                                </small>
                                <div class="text-truncate">{{ $manager->email }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block mb-2">
                                    <i class="fa fa-map-marker-alt me-1"></i>Assigned Regions ({{ $scopes->count() }})
                                </small>
                                @if($scopes->count() > 0)
                                    <div class="d-flex flex-wrap">
                                        @foreach($scopes->take(4) as $scope)
                                            <span class="region-badge">
                                                {{ $scope->state }}{{ $scope->lga ? ' / ' . $scope->lga : '' }}
                                            </span>
                                        @endforeach
                                        @if($scopes->count() > 4)
                                            <span class="region-badge">
                                                +{{ $scopes->count() - 4 }} more
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">No regions assigned</span>
                                @endif
                            </div>
                            
                            <div class="action-btn-group">
                                <button class="action-btn view" 
                                        onclick="window.location='{{ route('admin.regional-managers.show', $manager) }}'"
                                        title="View Details">
                                    <i class="fa fa-eye me-1"></i>View
                                </button>
                                <button class="action-btn edit" 
                                        onclick="editManagerRegions({{ $manager->user_id }}, '{{ $manager->first_name }} {{ $manager->last_name }}')"
                                        title="Edit Regions">
                                    <i class="fa fa-edit me-1"></i>Edit
                                </button>
                                <button class="action-btn add" 
                                        onclick="window.location='{{ route('admin.regional-managers.assign-regions', $manager) }}'"
                                        title="Add Regions">
                                    <i class="fa fa-plus me-1"></i>Add
                                </button>
                                <button class="action-btn delete" 
                                        onclick="confirmRemoveRegionalManagerRole({{ $manager->user_id }}, '{{ $manager->first_name }} {{ $manager->last_name }}')"
                                        title="Remove Role">
                                    <i class="fa fa-trash me-1"></i>Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $regionalManagers->links() }}
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fa fa-users-slash"></i>
                    </div>
                    <h3>No Regional Managers Found</h3>
                    <p>{{ $search || $region ? 'Try adjusting your filters' : 'Start by assigning the Regional Manager role to users' }}</p>
                    @if($search || $region)
                        <a href="{{ route('admin.regional-managers.index') }}" class="btn btn-primary mt-3">
                            <i class="fa fa-redo me-2"></i>Clear Filters
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Keep all existing modals and scripts from the original file -->
@include('admin.regional-managers.partials.modals')
@include('admin.regional-managers.partials.scripts')

@endSection

@push('scripts')
<script>
// Update selected count
function updateSelectedCount() {
    const selectedCheckboxes = document.querySelectorAll('.manager-checkbox:checked');
    const count = selectedCheckboxes.length;
    const bulkInfo = document.getElementById('bulkSelectionInfo');
    const countSpan = document.getElementById('selectedCount');
    
    if (count > 0) {
        bulkInfo.classList.remove('d-none');
        countSpan.textContent = count;
    } else {
        bulkInfo.classList.add('d-none');
    }
}

// Handle checkbox changes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('manager-checkbox')) {
        updateSelectedCount();
    }
});

// Select all functionality
const selectAllCheckbox = document.getElementById('selectAll');
if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.manager-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });
}
</script>
@endpush
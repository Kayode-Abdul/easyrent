@extends('layouts.admin')

@section('title', 'Regional Manager Management')

@push('styles')
<style>
    
    .stats-mini-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: transform 0.3s ease;
        border-left: 4px solid #3e8189;
    }
    
    .stats-mini-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }
    
    .stats-mini-card .icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content-center;
        color: white;
        font-size: 20px;
    }
    
    .stats-mini-card .value {
        font-size: 28px;
        font-weight: 700;
        color: #333;
    }
    
    .stats-mini-card .label {
        font-size: 13px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .manager-avatar-enhanced {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 18px;
        box-shadow: 0 4px 12px rgba(62, 129, 137, 0.3);
    }
    
    .region-badge-enhanced {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        color: #1976d2;
        border: 1px solid #90caf9;
        margin: 2px;
    }
    
    .action-btn-enhanced {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        transition: all 0.2s ease;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
</style>
@endpush

@section('content')
<div class="content">
    <!-- Enhanced Page Header -->
    <div class="page-header-custom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-2">Regional Manager Management</h3>
                <p class="mb-0 opacity-90">Manage regional managers and their assigned territories</p>
            </div>
            <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#bulkAssignModal">
                <i class="fa fa-plus-circle me-2"></i>Bulk Assign Regions
            </button>
        </div>
    </div>

    <!-- Stats Mini Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-mini-card">
                <div class="d-flex align-items-center">
                    <div class="icon me-3">
                        <i class="fa fa-users"></i>
                    </div>
                    <div>
                        <div class="value">{{ $regionalManagers->total() }}</div>
                        <div class="label">Total Managers</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-mini-card" style="border-left-color: #51cbce;">
                <div class="d-flex align-items-center">
                    <div class="icon me-3" style="background: linear-gradient(135deg, #51cbce 0%, #3e8189 100%);">
                        <i class="fa fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <div class="value">{{ $allRegions->count() }}</div>
                        <div class="label">Total Regions</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-mini-card" style="border-left-color: #f5576c;">
                <div class="d-flex align-items-center">
                    <div class="icon me-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="value">{{ $regionalManagers->sum(function($m) { return $m->regionalScopes->count(); }) }}</div>
                        <div class="label">Active Assignments</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-mini-card" style="border-left-color: #00f2fe;">
                <div class="d-flex align-items-center">
                    <div class="icon me-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fa fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="value">{{ $regionalManagers->count() > 0 ? number_format($regionalManagers->sum(function($m) { return $m->regionalScopes->count(); }) / $regionalManagers->count(), 1) : 0 }}</div>
                        <div class="label">Avg per Manager</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ $search }}" placeholder="Name or email...">
                        </div>
                        <div class="col-md-4">
                            <label for="region" class="form-label">Filter by Region</label>
                            <select class="form-select" id="region" name="region">
                                <option value="">All Regions</option>
                                @foreach($allRegions as $regionOption)
                                    <option value="{{ e($regionOption) }}" {{ $region == $regionOption ? 'selected' : '' }}>
                                        {{ e($regionOption) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fa fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.regional-managers.index') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Regional Managers List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Regional Managers ({{ $regionalManagers->total() }})</h5>
                </div>
                <div class="card-body">
                    @if($regionalManagers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Assigned Regions</th>
                                        <th>Total Scopes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($regionalManagers as $manager)
                                        @php
                                            $scopes = $manager->getFormattedRegionalScopes();
                                        @endphp
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input manager-checkbox" 
                                                       value="{{ $manager->user_id }}">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="manager-avatar-enhanced me-3">
                                                        {{ strtoupper(substr($manager->first_name, 0, 1)) }}{{ strtoupper(substr($manager->last_name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">{{ $manager->first_name }} {{ $manager->last_name }}</div>
                                                        <small class="text-muted">ID: {{ $manager->user_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $manager->email }}</td>
                                            <td>
                                                @if($scopes->count() > 0)
                                                    @foreach($scopes->take(3) as $scope)
                                                        <span class="region-badge-enhanced">
                                                            <i class="fa fa-map-pin me-1"></i>{{ $scope->state }}{{ $scope->lga ? ' / ' . $scope->lga : '' }}
                                                        </span>
                                                    @endforeach
                                                    @if($scopes->count() > 3)
                                                        <span class="badge bg-secondary">+{{ $scopes->count() - 3 }} more</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No regions assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $scopes->count() > 0 ? 'success' : 'warning' }}">
                                                    {{ $scopes->count() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.regional-managers.show', $manager) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="View Manager Details & Roles">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="editManagerRegions({{ $manager->user_id }}, '{{ $manager->first_name }} {{ $manager->last_name }}')"
                                                            title="Edit Assigned Regions">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <a href="{{ route('admin.regional-managers.assign-regions', $manager) }}" 
                                                       class="btn btn-sm btn-outline-success" title="Add New Regions">
                                                        <i class="fa fa-plus"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmRemoveRegionalManagerRole({{ $manager->user_id }}, '{{ $manager->first_name }} {{ $manager->last_name }}')"
                                                            title="Remove Regional Manager Role">
                                                        <i class="fa fa-user-times"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Showing {{ $regionalManagers->firstItem() }} to {{ $regionalManagers->lastItem() }} 
                                of {{ $regionalManagers->total() }} results
                            </div>
                            {{ $regionalManagers->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fa fa-users fa-3x text-muted mb-3"></i>
                            <h5>No Regional Managers Found</h5>
                            <p class="text-muted">No regional managers match your current filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Assign Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.regional-managers.bulk-assign') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Assign Regions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Selected Regional Managers</label>
                        <div id="selectedManagers" class="border rounded p-2 bg-light">
                            <em class="text-muted">Select regional managers from the list above</em>
                        </div>
                        <input type="hidden" name="regional_manager_ids" id="selectedManagerIds">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">States to Assign <span class="text-danger">*</span></label>
                        <div id="statesContainer">
                            <div class="state-group mb-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <select name="states[]" class="form-select state-select" required>
                                            <option value="">Select State</option>
                                            <option value="Lagos">Lagos</option>
                                            <option value="Abuja">Abuja</option>
                                            <option value="Kano">Kano</option>
                                            <option value="Rivers">Rivers</option>
                                            <option value="Oyo">Oyo</option>
                                            <option value="Kaduna">Kaduna</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <select name="lgas[]" class="form-select lga-select">
                                            <option value="">All LGAs</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger remove-state" disabled>
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addStateBtn">
                            <i class="fa fa-plus"></i> Add Another State
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Regions</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Manager Regions Modal -->
<div class="modal fade" id="editManagerRegionsModal" tabindex="-1" aria-labelledby="editManagerRegionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editManagerRegionsModalLabel">Edit Assigned Regions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Managing regions for: <strong id="editManagerName"></strong></p>
                <div id="assignedRegionsList">
                    <!-- Regions will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveRegionChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Regional Manager Role Modal -->
<div class="modal fade" id="removeRegionalManagerRoleModal" tabindex="-1" aria-labelledby="removeRegionalManagerRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeRegionalManagerRoleModalLabel">Remove Regional Manager Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>Warning!</strong> This will completely remove the Regional Manager role from this user.
                </div>
                <p>Are you sure you want to remove the Regional Manager role from <strong id="managerNameToRemoveRole"></strong>?</p>
                <p class="text-danger">This action will:</p>
                <ul class="text-danger">
                    <li>Remove all assigned regions</li>
                    <li>Remove Regional Manager role</li>
                    <li>Revoke all regional management permissions</li>
                    <li>This action cannot be undone</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="removeRegionalManagerRoleForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Remove Regional Manager Role</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}
</style>
@endpush

@push('scripts')
<script>
// Debug function to check if Bootstrap is loaded
function checkBootstrap() {
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap 5 is not loaded. Falling back to jQuery modal.');
        return false;
    }
    return true;
}

// Fallback modal function for older Bootstrap/jQuery
function showModal(modalId) {
    console.log('Attempting to show modal:', modalId);
    
    const modalElement = document.getElementById(modalId);
    if (!modalElement) {
        console.error('Modal element not found:', modalId);
        alert(`Modal ${modalId} not found. Please refresh the page.`);
        return;
    }
    
    if (checkBootstrap()) {
        console.log('Using Bootstrap 5 modal');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    } else if (typeof $ !== 'undefined') {
        console.log('Using jQuery modal');
        $('#' + modalId).modal('show');
    } else {
        console.error('No modal system available');
        alert('Modal system not available. Please refresh the page.');
    }
}
// LGA options for each state (guard global)
window.lgaOptions = Object.assign({}, window.lgaOptions || {}, {
    'Lagos': ['Ikeja', 'Victoria Island', 'Lekki', 'Surulere', 'Yaba', 'Apapa', 'Ikoyi'],
    'Abuja': ['Garki', 'Wuse', 'Maitama', 'Asokoro', 'Gwarinpa', 'Kubwa'],
    'Kano': ['Kano Municipal', 'Fagge', 'Dala', 'Gwale', 'Tarauni'],
    'Rivers': ['Port Harcourt', 'Obio-Akpor', 'Eleme', 'Ikwerre', 'Oyigbo'],
    'Oyo': ['Ibadan North', 'Ibadan South-West', 'Egbeda', 'Akinyele', 'Lagelu'],
    'Kaduna': ['Kaduna North', 'Kaduna South', 'Chikun', 'Igabi', 'Kajuru']
});

// Handle select all checkbox
const selectAllElement = document.getElementById('selectAll');
if (selectAllElement) {
    selectAllElement.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.manager-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedManagers();
    });
} else {
    console.warn('selectAll element not found');
}

// Handle individual checkboxes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('manager-checkbox')) {
        updateSelectedManagers();
    }
});

// Update selected managers display
function updateSelectedManagers() {
    const selectedCheckboxes = document.querySelectorAll('.manager-checkbox:checked');
    const selectedContainer = document.getElementById('selectedManagers');
    const selectedIds = document.getElementById('selectedManagerIds');
    
    if (selectedCheckboxes.length === 0) {
        selectedContainer.innerHTML = '<em class="text-muted">Select regional managers from the list above</em>';
        selectedIds.value = '';
        return;
    }
    
    const ids = [];
    const names = [];
    
    selectedCheckboxes.forEach(checkbox => {
        ids.push(checkbox.value);
        const row = checkbox.closest('tr');
        const nameCell = row.querySelector('td:nth-child(2) .fw-bold');
        names.push(nameCell.textContent.trim());
    });
    
    selectedIds.value = ids.join(',');
    selectedContainer.innerHTML = names.map(name => 
        `<span class="badge bg-primary me-1">${name}</span>`
    ).join('');
}

// Handle state selection change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('state-select')) {
        const lgaSelect = e.target.closest('.state-group').querySelector('.lga-select');
        const selectedState = e.target.value;
        
        // Clear LGA options
        lgaSelect.innerHTML = '<option value="">All LGAs</option>';
        
        // Add LGA options for selected state
        if (selectedState && window.lgaOptions[selectedState]) {
            window.lgaOptions[selectedState].forEach(lga => {
                const option = document.createElement('option');
                option.value = lga;
                option.textContent = lga;
                lgaSelect.appendChild(option);
            });
        }
    }
});

// Add new state group
const addStateBtnElement = document.getElementById('addStateBtn');
if (addStateBtnElement) {
    addStateBtnElement.addEventListener('click', function() {
        const container = document.getElementById('statesContainer');
        const newGroup = document.querySelector('.state-group').cloneNode(true);
        
        // Reset values
        newGroup.querySelector('.state-select').value = '';
        newGroup.querySelector('.lga-select').innerHTML = '<option value="">All LGAs</option>';
        
        // Enable remove button
        const removeBtn = newGroup.querySelector('.remove-state');
        removeBtn.disabled = false;
        removeBtn.addEventListener('click', function() {
            newGroup.remove();
        });
        
        container.appendChild(newGroup);
    });
} else {
    console.warn('addStateBtn element not found');
}

// Remove state group
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-state') || e.target.closest('.remove-state')) {
        const stateGroup = e.target.closest('.state-group');
        if (stateGroup && !e.target.disabled) {
            stateGroup.remove();
        }
    }
});

// Add click handlers for action buttons with error handling
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing Regional Manager Management');
    
    // Test if all required elements exist
    const requiredElements = [
        'selectAll',
        'selectedManagers', 
        'selectedManagerIds',
        'addStateBtn',
        'statesContainer',
        'editManagerRegionsModal',
        'removeRegionalManagerRoleModal',
        'removeRegionalManagerRoleForm',
        'managerNameToRemoveRole',
        'editManagerName',
        'assignedRegionsList'
    ];
    
    const missingElements = requiredElements.filter(id => !document.getElementById(id));
    if (missingElements.length > 0) {
        console.error('Missing required elements:', missingElements);
        console.log('Available elements:', requiredElements.filter(id => document.getElementById(id)));
    } else {
        console.log('All required elements found');
    }
    
    // Test if action buttons exist
    const actionButtons = document.querySelectorAll('[onclick*="editRegionalManager"], [onclick*="confirmRemoveAllScopes"]');
    console.log('Found action buttons:', actionButtons.length);
    
    // Add form submission handlers with validation
    const forms = document.querySelectorAll('form');
    forms.forEach((form, index) => {
        console.log(`Form ${index + 1}:`, form.id || 'unnamed', form.action);
        
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
                
                // Re-enable after 5 seconds to prevent permanent disable
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 5000);
            }
        });
    });
    
    // Test Bootstrap availability
    console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
    console.log('jQuery available:', typeof $ !== 'undefined');
    
    console.log('Regional Manager Management page initialized successfully');
});

// Edit manager regions - show assigned regions with ability to remove
function editManagerRegions(managerId, managerName) {
    console.log('Edit regions button clicked for manager:', managerId);
    
    try {
        // Check if CSRF token exists
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token meta tag not found');
            alert('CSRF token not found. Please refresh the page.');
            return;
        }
        
        document.getElementById('editManagerName').textContent = managerName;
        
        // Load assigned regions via AJAX
        fetch(`/admin/regional-managers/${managerId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayAssignedRegions(data.regions, managerId);
                    showModal('editManagerRegionsModal');
                } else {
                    alert('Failed to load manager regions: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error loading regions:', error);
                console.error('Full error details:', {
                    message: error.message,
                    stack: error.stack,
                    managerId: managerId,
                    url: `/admin/regional-managers/${managerId}`
                });
                alert('Error loading regions: ' + error.message + '. Please check the console for details.');
            });
    } catch (error) {
        console.error('Error in editManagerRegions:', error);
        alert('Error opening edit regions modal. Please check the console for details.');
    }
}

// Display assigned regions with remove buttons
function displayAssignedRegions(regions, managerId) {
    const container = document.getElementById('assignedRegionsList');
    
    if (!regions || regions.length === 0) {
        container.innerHTML = '<p class="text-muted">No regions assigned to this manager.</p>';
        return;
    }
    
    let html = '<div class="row">';
    regions.forEach(region => {
        html += `
            <div class="col-md-6 mb-2">
                <div class="card">
                    <div class="card-body p-2 d-flex justify-content-between align-items-center">
                        <span>
                            <strong>${region.state}</strong>
                            ${region.lga ? ` / ${region.lga}` : ' (All LGAs)'}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="removeRegion(${region.id}, ${managerId})"
                                title="Remove this region">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}

// Remove a specific region
function removeRegion(regionId, managerId) {
    if (confirm('Are you sure you want to remove this region assignment?')) {
        fetch(`/admin/regional-managers/${managerId}/remove-scope`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ scope_id: regionId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If not JSON, it's likely a redirect response (success)
                return { success: true };
            }
        })
        .then(data => {
            if (data.success) {
                // Reload the regions list
                editManagerRegions(managerId, document.getElementById('editManagerName').textContent);
                // Show success message
                alert('Region removed successfully');
            } else {
                alert('Failed to remove region: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error removing region:', error);
            alert('Error removing region. Please try again.');
        });
    }
}

// Save region changes (placeholder for future functionality)
function saveRegionChanges() {
    // Close modal and refresh page to show updated data
    document.querySelector('#editManagerRegionsModal .btn-close').click();
    window.location.reload();
}

// Confirm remove regional manager role entirely
function confirmRemoveRegionalManagerRole(managerId, managerName) {
    console.log('Remove regional manager role button clicked for manager:', managerId);
    
    try {
        document.getElementById('managerNameToRemoveRole').textContent = managerName;
        document.getElementById('removeRegionalManagerRoleForm').action = 
            `/admin/regional-managers/${managerId}/remove-role`;
        
        console.log('Remove role form action set to:', document.getElementById('removeRegionalManagerRoleForm').action);
        
        showModal('removeRegionalManagerRoleModal');
    } catch (error) {
        console.error('Error in confirmRemoveRegionalManagerRole:', error);
        alert('Error opening remove role confirmation modal. Please check the console for details.');
    }
}

// Update bulk assign form when modal opens
const bulkAssignModalElement = document.getElementById('bulkAssignModal');
if (bulkAssignModalElement) {
    bulkAssignModalElement.addEventListener('show.bs.modal', function() {
        updateSelectedManagers();
    });
    
    // Alternative event listener for older Bootstrap
    if (typeof $ !== 'undefined') {
        $('#bulkAssignModal').on('show.bs.modal', function() {
            updateSelectedManagers();
        });
    }
} else {
    console.warn('bulkAssignModal element not found');
}


</script>
@endpush
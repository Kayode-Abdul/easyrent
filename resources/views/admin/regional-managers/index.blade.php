@extends('layout')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Regional Manager Management</h2>
                <div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkAssignModal">
                        <i class="fas fa-users"></i> Bulk Assign Regions
                    </button>
                </div>
            </div>

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
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.regional-managers.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
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
                                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <span class="text-white fw-bold">
                                                            {{ strtoupper(substr($manager->first_name, 0, 1)) }}{{ strtoupper(substr($manager->last_name, 0, 1)) }}
                                                        </span>
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
                                                        <span class="badge bg-info me-1 mb-1">
                                                            {{ $scope->state }}{{ $scope->lga ? ' / ' . $scope->lga : ' (All)' }}
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
                                                       class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="editRegionalManager({{ $manager->user_id }}, '{{ $manager->first_name }}', '{{ $manager->last_name }}', '{{ $manager->email }}')"
                                                            title="Edit Regional Manager">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="{{ route('admin.regional-managers.assign-regions', $manager) }}" 
                                                       class="btn btn-sm btn-outline-success" title="Assign Regions">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmRemoveAllScopes({{ $manager->user_id }}, '{{ $manager->first_name }} {{ $manager->last_name }}')"
                                                            title="Remove All Regions">
                                                        <i class="fas fa-trash"></i>
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
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
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
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addStateBtn">
                            <i class="fas fa-plus"></i> Add Another State
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

@push('modals')
<!-- Remove All Scopes Modal -->
<div class="modal fade" id="removeAllScopesModal" tabindex="-1" aria-labelledby="removeAllScopesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeAllScopesModalLabel">Confirm Remove All Regions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove all assigned regions from <strong id="managerNameToRemove"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="removeAllScopesForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Remove All Regions</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Regional Manager Modal -->
<div class="modal fade" id="editRegionalManagerModal" tabindex="-1" aria-labelledby="editRegionalManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRegionalManagerModalLabel">Edit Regional Manager</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editRegionalManagerForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <div class="mb-3">
                        <label for="edit_first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Regional Manager</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

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
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.manager-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateSelectedManagers();
});

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
document.getElementById('addStateBtn').addEventListener('click', function() {
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

// Remove state group
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-state') || e.target.closest('.remove-state')) {
        const stateGroup = e.target.closest('.state-group');
        if (stateGroup && !e.target.disabled) {
            stateGroup.remove();
        }
    }
});

// Confirm remove all scopes
function confirmRemoveAllScopes(managerId, managerName) {
    document.getElementById('managerNameToRemove').textContent = managerName;
    document.getElementById('removeAllScopesForm').action = 
        `{{ route('admin.regional-managers.index') }}/${managerId}/remove-all-scopes`;
    
    const modal = new bootstrap.Modal(document.getElementById('removeAllScopesModal'));
    modal.show();
}

// Update bulk assign form when modal opens
document.getElementById('bulkAssignModal').addEventListener('show.bs.modal', function() {
    updateSelectedManagers();
});

// Edit regional manager function
function editRegionalManager(userId, firstName, lastName, email) {
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_first_name').value = firstName;
    document.getElementById('edit_last_name').value = lastName;
    document.getElementById('edit_email').value = email;
    
    const modal = new bootstrap.Modal(document.getElementById('editRegionalManagerModal'));
    modal.show();
}
</script>
@endpush
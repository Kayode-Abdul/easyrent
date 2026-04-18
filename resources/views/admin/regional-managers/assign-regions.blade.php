@extends('layouts.admin')
@section('content')
<div class="content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.regional-managers.index') }}">Regional Managers</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.regional-managers.show', $regionalManager) }}">
                                {{ $regionalManager->first_name }} {{ $regionalManager->last_name }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Assign Regions</li>
                    </ol>
                </nav>
                <h2>Assign Regions to Regional Manager</h2>
            </div>

            <!-- Manager Info Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                            <span class="text-white fw-bold">
                                {{ strtoupper(substr(e($regionalManager->first_name), 0, 1)) }}{{ strtoupper(substr(e($regionalManager->last_name), 0, 1)) }}
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ e($regionalManager->first_name) }} {{ e($regionalManager->last_name) }}</h5>
                            <p class="text-muted mb-0">{{ e($regionalManager->email) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Assignments -->
            @if($currentScopes->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Current Regional Assignments</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($currentScopes as $scope)
                                <div class="col-md-6 mb-2">
                                    <span class="badge bg-info me-1">
                                        {{ $scope->state }}{{ $scope->lga ? ' / ' . $scope->lga : ' (All LGAs)' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Assignment Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add New Regional Assignments</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.regional-managers.store-assignments', $regionalManager) }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label">Regional Assignments <span class="text-danger">*</span></label>
                            <p class="text-muted small">Add one or more state/LGA assignments for this regional manager.</p>
                            
                            <div id="assignmentsContainer">
                                <div class="assignment-group mb-3 p-3 border rounded">
                                        <div class="col-md-4">
                                            <label class="form-label">Country <span class="text-danger">*</span></label>
                                            <select name="countries[]" class="form-select country-select" required>
                                                <option value="">Select Country</option>
                                                @foreach($availableCountries as $country)
                                                    <option value="{{ e($country) }}">{{ e($country) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">State <span class="text-danger">*</span></label>
                                            <select name="states[]" class="form-select state-select" required disabled>
                                                <option value="">Select State</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">LGA (Optional)</label>
                                            <select name="lgas[]" class="form-select lga-select" disabled>
                                                <option value="">All LGAs in State</option>
                                            </select>
                                            <small class="text-muted">Leave empty for entire state</small>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-danger remove-assignment" disabled>
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-sm" id="addAssignmentBtn">
                                <i class="fa fa-plus"></i> Add Another Assignment
                            </button>
                        </div>

                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i>
                            <strong>Note:</strong> 
                            <ul class="mb-0 mt-2">
                                <li>Selecting a state without an LGA gives access to the entire state</li>
                                <li>Selecting a specific LGA limits access to that LGA only</li>
                                <li>You can assign multiple states and LGAs to the same manager</li>
                                <li>Duplicate assignments will be automatically handled</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.regional-managers.show', $regionalManager) }}" 
                               class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Back to Details
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i> Assign Regions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-md {
    width: 60px;
    height: 60px;
}

.assignment-group {
    background-color: #f8f9fa;
}
</style>
@endpush

@push('scripts')
// Location data (guard global)
window.statesByCountry = @json($statesByCountry);
window.lgaOptions = @json($availableLgas);

// Handle country selection change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('country-select')) {
        const group = e.target.closest('.assignment-group');
        const stateSelect = group.querySelector('.state-select');
        const lgaSelect = group.querySelector('.lga-select');
        const selectedCountry = e.target.value;
        
        // Reset and disable sub-selectors
        stateSelect.innerHTML = '<option value="">Select State</option>';
        stateSelect.disabled = !selectedCountry;
        lgaSelect.innerHTML = '<option value="">All LGAs in State</option>';
        lgaSelect.disabled = true;
        
        // Add state options for selected country
        if (selectedCountry && window.statesByCountry[selectedCountry]) {
            window.statesByCountry[selectedCountry].forEach(state => {
                const option = document.createElement('option');
                option.value = state.name;
                option.textContent = state.name;
                stateSelect.appendChild(option);
            });
        }
    }

    if (e.target.classList.contains('state-select')) {
        const group = e.target.closest('.assignment-group');
        const lgaSelect = group.querySelector('.lga-select');
        const selectedState = e.target.value;
        
        // Clear and reset LGA options
        lgaSelect.innerHTML = '<option value="">All LGAs in State</option>';
        lgaSelect.disabled = !selectedState;
        
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

// Add new assignment group
document.getElementById('addAssignmentBtn').addEventListener('click', function() {
    const container = document.getElementById('assignmentsContainer');
    const newGroup = document.querySelector('.assignment-group').cloneNode(true);
    
    // Reset values
    newGroup.querySelector('.country-select').value = '';
    newGroup.querySelector('.state-select').innerHTML = '<option value="">Select State</option>';
    newGroup.querySelector('.state-select').disabled = true;
    newGroup.querySelector('.lga-select').innerHTML = '<option value="">All LGAs in State</option>';
    newGroup.querySelector('.lga-select').disabled = true;
    
    // Enable remove button
    const removeBtn = newGroup.querySelector('.remove-assignment');
    removeBtn.disabled = false;
    removeBtn.addEventListener('click', function() {
        newGroup.remove();
        updateRemoveButtons();
    });
    
    container.appendChild(newGroup);
    updateRemoveButtons();
});

// Remove assignment group
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-assignment') || e.target.closest('.remove-assignment')) {
        const assignmentGroup = e.target.closest('.assignment-group');
        if (assignmentGroup && !e.target.disabled) {
            assignmentGroup.remove();
            updateRemoveButtons();
        }
    }
});

// Update remove button states
function updateRemoveButtons() {
    const groups = document.querySelectorAll('.assignment-group');
    groups.forEach((group, index) => {
        const removeBtn = group.querySelector('.remove-assignment');
        removeBtn.disabled = groups.length === 1;
    });
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const stateSelects = document.querySelectorAll('.state-select');
    let hasValidAssignment = false;
    
    stateSelects.forEach(select => {
        if (select.value) {
            hasValidAssignment = true;
        }
    });
    
    if (!hasValidAssignment) {
        e.preventDefault();
        alert('Please select at least one state to assign.');
        return false;
    }
});

// Initialize remove button states
updateRemoveButtons();
</script>
@endpush
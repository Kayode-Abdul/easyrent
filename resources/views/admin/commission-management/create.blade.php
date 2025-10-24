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
                                <i class="fas fa-plus text-primary me-2"></i>
                                Create New Commission Rate
                            </h4>
                            <p class="text-muted mb-0">Add a new commission rate configuration</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.commission-management.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Commission Rate Configuration</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.commission-management.store') }}">
                        @csrf

                        <!-- Scenario Selection -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Region <span class="text-danger">*</span></label>
                                <select class="form-select @error('region') is-invalid @enderror" name="region" required>
                                    <option value="">Select Region</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region }}" {{ old('region') === $region ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $region)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('region')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Property Management <span class="text-danger">*</span></label>
                                <select class="form-select @error('property_management_status') is-invalid @enderror" 
                                        name="property_management_status" 
                                        required
                                        onchange="updateDefaultRates()">
                                    <option value="">Select Status</option>
                                    @foreach($propertyManagementStatuses as $status)
                                        <option value="{{ $status }}" {{ old('property_management_status') === $status ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            @if($status === 'unmanaged') (5% Total) @else (2.5% Total) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('property_management_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Hierarchy Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('hierarchy_status') is-invalid @enderror" 
                                        name="hierarchy_status" 
                                        required
                                        onchange="toggleSuperMarketerField(); updateDefaultRates();">
                                    <option value="">Select Status</option>
                                    @foreach($hierarchyStatuses as $status)
                                        <option value="{{ $status }}" {{ old('hierarchy_status') === $status ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('hierarchy_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <!-- Commission Rates -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Total Commission Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('total_commission_rate') is-invalid @enderror" 
                                       name="total_commission_rate" 
                                       value="{{ old('total_commission_rate') }}" 
                                       step="0.001" 
                                       min="0" 
                                       max="100" 
                                       required
                                       onchange="updateRemainingRate()">
                                @error('total_commission_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Remaining Rate</label>
                                <input type="text" class="form-control" id="remainingRate" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6" id="superMarketerField" style="display: none;">
                                <label class="form-label">Super Marketer Rate (%)</label>
                                <input type="number" 
                                       class="form-control @error('super_marketer_rate') is-invalid @enderror" 
                                       name="super_marketer_rate" 
                                       value="{{ old('super_marketer_rate') }}" 
                                       step="0.001" 
                                       min="0" 
                                       max="100"
                                       onchange="updateRemainingRate()">
                                @error('super_marketer_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Marketer Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('marketer_rate') is-invalid @enderror" 
                                       name="marketer_rate" 
                                       value="{{ old('marketer_rate') }}" 
                                       step="0.001" 
                                       min="0" 
                                       max="100" 
                                       required
                                       onchange="updateRemainingRate()">
                                @error('marketer_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Regional Manager Rate (%)</label>
                                <input type="number" 
                                       class="form-control @error('regional_manager_rate') is-invalid @enderror" 
                                       name="regional_manager_rate" 
                                       value="{{ old('regional_manager_rate') }}" 
                                       step="0.001" 
                                       min="0" 
                                       max="100"
                                       onchange="updateRemainingRate()">
                                @error('regional_manager_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('company_rate') is-invalid @enderror" 
                                       name="company_rate" 
                                       value="{{ old('company_rate') }}" 
                                       step="0.001" 
                                       min="0" 
                                       max="100" 
                                       required
                                       onchange="updateRemainingRate()">
                                @error('company_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Optional description for this commission rate">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @error('rates_sum')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        @error('combination')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.commission-management.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Commission Rate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Default Rate Templates -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Default Rate Templates</h6>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="loadTemplate('unmanaged_without_super')">
                        Unmanaged - No Super Marketer
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="loadTemplate('unmanaged_with_super')">
                        Unmanaged - With Super Marketer
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm w-100 mb-2" onclick="loadTemplate('managed_without_super')">
                        Managed - No Super Marketer
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm w-100 mb-2" onclick="loadTemplate('managed_with_super')">
                        Managed - With Super Marketer
                    </button>
                </div>
            </div>

            <!-- Rate Validation -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Rate Validation</h6>
                </div>
                <div class="card-body">
                    <div id="rateValidation">
                        <div class="text-center text-muted">
                            <i class="fas fa-calculator fa-2x mb-2 d-block"></i>
                            <small>Enter rates to see validation</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const templates = {
    'unmanaged_without_super': {
        total_commission_rate: 5.000,
        super_marketer_rate: null,
        marketer_rate: 1.500,
        regional_manager_rate: 0.250,
        company_rate: 3.250
    },
    'unmanaged_with_super': {
        total_commission_rate: 5.000,
        super_marketer_rate: 0.500,
        marketer_rate: 1.000,
        regional_manager_rate: 0.250,
        company_rate: 3.250
    },
    'managed_without_super': {
        total_commission_rate: 2.500,
        super_marketer_rate: null,
        marketer_rate: 0.750,
        regional_manager_rate: 0.100,
        company_rate: 1.650
    },
    'managed_with_super': {
        total_commission_rate: 2.500,
        super_marketer_rate: 0.250,
        marketer_rate: 0.500,
        regional_manager_rate: 0.100,
        company_rate: 1.650
    }
};

function toggleSuperMarketerField() {
    const hierarchyStatus = document.querySelector('[name="hierarchy_status"]').value;
    const superMarketerField = document.getElementById('superMarketerField');
    const superMarketerInput = document.querySelector('[name="super_marketer_rate"]');
    
    if (hierarchyStatus === 'with_super_marketer') {
        superMarketerField.style.display = 'block';
        superMarketerField.classList.remove('col-md-6');
        superMarketerField.classList.add('col-md-6');
    } else {
        superMarketerField.style.display = 'none';
        superMarketerInput.value = '';
    }
    
    updateRemainingRate();
}

function updateDefaultRates() {
    const propertyStatus = document.querySelector('[name="property_management_status"]').value;
    const hierarchyStatus = document.querySelector('[name="hierarchy_status"]').value;
    
    if (propertyStatus && hierarchyStatus) {
        const templateKey = `${propertyStatus}_${hierarchyStatus}`;
        if (templates[templateKey]) {
            loadTemplate(templateKey);
        }
    }
}

function loadTemplate(templateKey) {
    const template = templates[templateKey];
    if (!template) return;
    
    document.querySelector('[name="total_commission_rate"]').value = template.total_commission_rate;
    document.querySelector('[name="marketer_rate"]').value = template.marketer_rate;
    document.querySelector('[name="regional_manager_rate"]').value = template.regional_manager_rate || '';
    document.querySelector('[name="company_rate"]').value = template.company_rate;
    
    const superMarketerInput = document.querySelector('[name="super_marketer_rate"]');
    if (superMarketerInput) {
        superMarketerInput.value = template.super_marketer_rate || '';
    }
    
    updateRemainingRate();
}

function updateRemainingRate() {
    const totalRate = parseFloat(document.querySelector('[name="total_commission_rate"]').value) || 0;
    const superMarketerRate = parseFloat(document.querySelector('[name="super_marketer_rate"]')?.value) || 0;
    const marketerRate = parseFloat(document.querySelector('[name="marketer_rate"]').value) || 0;
    const regionalManagerRate = parseFloat(document.querySelector('[name="regional_manager_rate"]').value) || 0;
    const companyRate = parseFloat(document.querySelector('[name="company_rate"]').value) || 0;
    
    const usedRate = superMarketerRate + marketerRate + regionalManagerRate + companyRate;
    const remainingRate = totalRate - usedRate;
    
    document.getElementById('remainingRate').value = remainingRate.toFixed(3) + '%';
    
    // Update validation
    updateValidation(totalRate, usedRate);
}

function updateValidation(totalRate, usedRate) {
    const validationDiv = document.getElementById('rateValidation');
    const difference = Math.abs(totalRate - usedRate);
    
    let html = '';
    let alertClass = '';
    
    if (difference < 0.001) {
        alertClass = 'alert-success';
        html = `
            <div class="alert ${alertClass}">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Valid!</strong> Rates sum correctly.
            </div>
        `;
    } else {
        alertClass = 'alert-danger';
        html = `
            <div class="alert ${alertClass}">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Invalid!</strong> Rates don't sum to total.
                <br><small>Difference: ${difference.toFixed(3)}%</small>
            </div>
        `;
    }
    
    html += `
        <div class="small">
            <div class="d-flex justify-content-between">
                <span>Total Rate:</span>
                <span>${totalRate.toFixed(3)}%</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Used Rate:</span>
                <span>${usedRate.toFixed(3)}%</span>
            </div>
        </div>
    `;
    
    validationDiv.innerHTML = html;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleSuperMarketerField();
    updateRemainingRate();
});
</script>
@endsection
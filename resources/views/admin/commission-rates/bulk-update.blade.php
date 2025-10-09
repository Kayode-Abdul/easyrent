@extends('layout')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Bulk Update Commission Rates</h4>
                    <a href="{{ route('admin.commission-rates.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
                
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Instructions -->
                    <div class="alert alert-info">
                        <h6><i class="fa fa-info-circle"></i> Bulk Update Instructions</h6>
                        <ul class="mb-0">
                            <li>Add or modify commission rates for multiple regions and roles</li>
                            <li>Existing active rates will be automatically deactivated when new rates are applied</li>
                            <li>All rates will use the same effective date unless individually specified</li>
                            <li>Total commission rates per region should not exceed 2.5%</li>
                            <li>Click "Add Rate" to add more rate configurations</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('admin.commission-rates.bulk-update.process') }}" id="bulkUpdateForm">
                        @csrf
                        
                        <!-- Global Settings -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Global Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="effective_from_global" class="form-label">Global Effective Date</label>
                                            <input type="datetime-local" 
                                                   name="effective_from_global" 
                                                   id="effective_from_global" 
                                                   class="form-control" 
                                                   value="{{ old('effective_from_global', now()->format('Y-m-d\TH:i')) }}">
                                            <small class="form-text text-muted">This date will be used for all rates unless individually overridden</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Rates Overview -->
                        @if($currentRates->count() > 0)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Current Active Rates by Region</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($currentRates as $region => $rates)
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-left-primary">
                                                    <div class="card-body">
                                                        <h6 class="card-title">{{ $region }}</h6>
                                                        @foreach($rates as $rate)
                                                            <div class="d-flex justify-content-between">
                                                                <span class="badge badge-info">{{ $rate->role->name ?? 'Unknown' }}</span>
                                                                <span class="badge badge-success">{{ number_format($rate->commission_percentage, 2) }}%</span>
                                                            </div>
                                                        @endforeach
                                                        <hr class="my-2">
                                                        <small class="text-muted">
                                                            Total: {{ number_format($rates->sum('commission_percentage'), 2) }}%
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Rate Configuration -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Rate Configuration</h5>
                                <button type="button" class="btn btn-success btn-sm" onclick="addRateRow()">
                                    <i class="fa fa-plus"></i> Add Rate
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="ratesContainer">
                                    <!-- Initial rate row -->
                                    <div class="rate-row border rounded p-3 mb-3">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-label">Region <span class="text-danger">*</span></label>
                                                    <select name="rates[0][region]" class="form-control region-select" required>
                                                        <option value="">Select Region</option>
                                                        @foreach($regions as $region)
                                                            <option value="{{ $region }}">{{ $region }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                                    <select name="rates[0][role_id]" class="form-control role-select" required>
                                                        <option value="">Select Role</option>
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label class="form-label">Rate % <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               name="rates[0][commission_percentage]" 
                                                               class="form-control commission-input" 
                                                               step="0.01" 
                                                               min="0" 
                                                               max="100" 
                                                               required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-label">Effective From</label>
                                                    <input type="datetime-local" 
                                                           name="rates[0][effective_from]" 
                                                           class="form-control">
                                                    <small class="form-text text-muted">Leave blank to use global date</small>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeRateRow(this)">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Validation Summary -->
                                <div id="validationSummary" class="alert alert-warning" style="display: none;">
                                    <h6><i class="fa fa-exclamation-triangle"></i> Validation Warnings</h6>
                                    <ul id="validationList"></ul>
                                </div>

                                <!-- Submit Actions -->
                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fa fa-save"></i> Update Commission Rates
                                    </button>
                                    <button type="button" class="btn btn-info btn-lg ml-2" onclick="validateRates()">
                                        <i class="fa fa-check"></i> Validate Rates
                                    </button>
                                    <a href="{{ route('admin.commission-rates.index') }}" class="btn btn-secondary btn-lg ml-2">
                                        <i class="fa fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let rateRowIndex = 1;

function addRateRow() {
    const container = document.getElementById('ratesContainer');
    const newRow = document.createElement('div');
    newRow.className = 'rate-row border rounded p-3 mb-3';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Region <span class="text-danger">*</span></label>
                    <select name="rates[${rateRowIndex}][region]" class="form-control region-select" required>
                        <option value="">Select Region</option>
                        @foreach($regions as $region)
                            <option value="{{ $region }}">{{ $region }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="rates[${rateRowIndex}][role_id]" class="form-control role-select" required>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label class="form-label">Rate % <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" 
                               name="rates[${rateRowIndex}][commission_percentage]" 
                               class="form-control commission-input" 
                               step="0.01" 
                               min="0" 
                               max="100" 
                               required>
                        <div class="input-group-append">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Effective From</label>
                    <input type="datetime-local" 
                           name="rates[${rateRowIndex}][effective_from]" 
                           class="form-control">
                    <small class="form-text text-muted">Leave blank to use global date</small>
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeRateRow(this)">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(newRow);
    rateRowIndex++;
}

function removeRateRow(button) {
    const rateRow = button.closest('.rate-row');
    const container = document.getElementById('ratesContainer');
    
    // Don't allow removing the last row
    if (container.children.length > 1) {
        rateRow.remove();
    } else {
        alert('At least one rate configuration is required.');
    }
}

function validateRates() {
    const rateRows = document.querySelectorAll('.rate-row');
    const regionTotals = {};
    const warnings = [];
    
    // Calculate totals by region
    rateRows.forEach(row => {
        const region = row.querySelector('.region-select').value;
        const percentage = parseFloat(row.querySelector('.commission-input').value) || 0;
        
        if (region && percentage > 0) {
            if (!regionTotals[region]) {
                regionTotals[region] = 0;
            }
            regionTotals[region] += percentage;
        }
    });
    
    // Check for violations
    Object.keys(regionTotals).forEach(region => {
        if (regionTotals[region] > 2.5) {
            warnings.push(`${region}: Total commission rate ${regionTotals[region].toFixed(2)}% exceeds 2.5% limit`);
        }
    });
    
    // Display validation results
    const validationSummary = document.getElementById('validationSummary');
    const validationList = document.getElementById('validationList');
    
    if (warnings.length > 0) {
        validationList.innerHTML = warnings.map(warning => `<li>${warning}</li>`).join('');
        validationSummary.style.display = 'block';
        validationSummary.className = 'alert alert-danger';
        validationSummary.querySelector('h6').innerHTML = '<i class="fa fa-exclamation-triangle"></i> Validation Errors';
    } else {
        validationList.innerHTML = '<li>All commission rates are within acceptable limits</li>';
        validationSummary.style.display = 'block';
        validationSummary.className = 'alert alert-success';
        validationSummary.querySelector('h6').innerHTML = '<i class="fa fa-check"></i> Validation Passed';
    }
}

// Auto-validate on input change
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('commission-input')) {
            // Auto-validate when commission percentages change
            setTimeout(validateRates, 500);
        }
    });
    
    // Prevent form submission if validation fails
    document.getElementById('bulkUpdateForm').addEventListener('submit', function(e) {
        validateRates();
        
        const validationSummary = document.getElementById('validationSummary');
        if (validationSummary.classList.contains('alert-danger')) {
            e.preventDefault();
            alert('Please fix validation errors before submitting.');
            validationSummary.scrollIntoView({ behavior: 'smooth' });
        }
    });
});
</script>
@endsection
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
                                <i class="fas fa-edit text-primary me-2"></i>
                                Edit Commission Rate
                            </h4>
                            <p class="text-muted mb-0">
                                {{ ucfirst(str_replace('_', ' ', $commissionRate->region)) }} - 
                                {{ ucfirst(str_replace('_', ' ', $commissionRate->property_management_status)) }} - 
                                {{ ucfirst(str_replace('_', ' ', $commissionRate->hierarchy_status)) }}
                            </p>
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
                    <h6 class="mb-0">Commission Rate Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.commission-management.update', $commissionRate) }}">
                        @csrf
                        @method('PUT')

                        <!-- Scenario Information (Read-only) -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Region</label>
                                <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $commissionRate->region)) }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Property Management</label>
                                <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $commissionRate->property_management_status)) }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Hierarchy Status</label>
                                <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $commissionRate->hierarchy_status)) }}" readonly>
                            </div>
                        </div>

                        <hr>

                        <!-- Commission Rates -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Total Commission Rate (%)</label>
                                <input type="number" 
                                       class="form-control @error('total_commission_rate') is-invalid @enderror" 
                                       name="total_commission_rate" 
                                       value="{{ old('total_commission_rate', $commissionRate->total_commission_rate) }}" 
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
                            @if($commissionRate->hierarchy_status === 'with_super_marketer')
                            <div class="col-md-6">
                                <label class="form-label">Super Marketer Rate (%)</label>
                                <input type="number" 
                                       class="form-control @error('super_marketer_rate') is-invalid @enderror" 
                                       name="super_marketer_rate" 
                                       value="{{ old('super_marketer_rate', $commissionRate->super_marketer_rate) }}" 
                                       step="0.001" 
                                       min="0" 
                                       max="100"
                                       onchange="updateRemainingRate()">
                                @error('super_marketer_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @else
                            <input type="hidden" name="super_marketer_rate" value="">
                            @endif
                            
                            <div class="col-md-6">
                                <label class="form-label">Marketer Rate (%)</label>
                                <input type="number" 
                                       class="form-control @error('marketer_rate') is-invalid @enderror" 
                                       name="marketer_rate" 
                                       value="{{ old('marketer_rate', $commissionRate->marketer_rate) }}" 
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
                                       value="{{ old('regional_manager_rate', $commissionRate->regional_manager_rate) }}" 
                                       step="0.001" 
                                       min="0" 
                                       max="100"
                                       onchange="updateRemainingRate()">
                                @error('regional_manager_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company Rate (%)</label>
                                <input type="number" 
                                       class="form-control @error('company_rate') is-invalid @enderror" 
                                       name="company_rate" 
                                       value="{{ old('company_rate', $commissionRate->company_rate) }}" 
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
                                      rows="3">{{ old('description', $commissionRate->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @error('rates_sum')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.commission-management.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Commission Rate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Rate Validation -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Rate Validation</h6>
                </div>
                <div class="card-body">
                    <div id="rateValidation">
                        <div class="text-center text-muted">
                            <i class="fas fa-calculator fa-2x mb-2 d-block"></i>
                            <small>Modify rates to see validation</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commission Preview -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Commission Preview</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Sample Rent Amount</label>
                        <input type="number" class="form-control" id="sampleRent" value="100000" min="0" onchange="updatePreview()">
                    </div>
                    <div id="commissionPreview">
                        <!-- Preview will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
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
    
    // Update preview
    updatePreview();
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

function updatePreview() {
    const sampleRent = parseFloat(document.getElementById('sampleRent').value) || 0;
    const totalRate = parseFloat(document.querySelector('[name="total_commission_rate"]').value) || 0;
    const superMarketerRate = parseFloat(document.querySelector('[name="super_marketer_rate"]')?.value) || 0;
    const marketerRate = parseFloat(document.querySelector('[name="marketer_rate"]').value) || 0;
    const regionalManagerRate = parseFloat(document.querySelector('[name="regional_manager_rate"]').value) || 0;
    const companyRate = parseFloat(document.querySelector('[name="company_rate"]').value) || 0;
    
    if (sampleRent <= 0) return;
    
    const totalCommission = (sampleRent * totalRate) / 100;
    const superMarketerCommission = (sampleRent * superMarketerRate) / 100;
    const marketerCommission = (sampleRent * marketerRate) / 100;
    const regionalManagerCommission = (sampleRent * regionalManagerRate) / 100;
    const companyCommission = (sampleRent * companyRate) / 100;
    
    let html = `
        <div class="small">
            <div class="d-flex justify-content-between mb-1">
                <span><strong>Total Commission:</strong></span>
                <span><strong>₦${totalCommission.toLocaleString()}</strong></span>
            </div>
    `;
    
    if (superMarketerCommission > 0) {
        html += `
            <div class="d-flex justify-content-between">
                <span>Super Marketer:</span>
                <span>₦${superMarketerCommission.toLocaleString()}</span>
            </div>
        `;
    }
    
    html += `
            <div class="d-flex justify-content-between">
                <span>Marketer:</span>
                <span>₦${marketerCommission.toLocaleString()}</span>
            </div>
    `;
    
    if (regionalManagerCommission > 0) {
        html += `
            <div class="d-flex justify-content-between">
                <span>Regional Manager:</span>
                <span>₦${regionalManagerCommission.toLocaleString()}</span>
            </div>
        `;
    }
    
    html += `
            <div class="d-flex justify-content-between">
                <span>Company:</span>
                <span>₦${companyCommission.toLocaleString()}</span>
            </div>
        </div>
    `;
    
    document.getElementById('commissionPreview').innerHTML = html;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateRemainingRate();
});
</script>
@endsection
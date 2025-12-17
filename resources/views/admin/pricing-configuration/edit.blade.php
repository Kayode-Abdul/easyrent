@extends('layouts.admin')

@section('title', 'Edit Pricing Configuration')

@push('styles')
<style>
    .page-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 32px;
        border-radius: 16px;
        margin-bottom: 32px;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
    }
    
    .apartment-info-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        border-left: 4px solid #3e8189;
    }
    
    .pricing-type-option {
        border: 2px solid #dee2e6;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .pricing-type-option:hover {
        border-color: #3e8189;
        background: #f8f9fa;
    }
    
    .pricing-type-option.selected {
        border-color: #3e8189;
        background: linear-gradient(135deg, #e8f4f5 0%, #d1ecf1 100%);
    }
    
    .pricing-type-option .icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        margin-bottom: 12px;
    }
    
    .config-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .preview-section {
        background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .calculation-result {
        background: white;
        border-radius: 8px;
        padding: 16px;
        margin-top: 12px;
        border-left: 4px solid #4caf50;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #3e8189;
        box-shadow: 0 0 0 0.2rem rgba(62, 129, 137, 0.25);
    }
</style>
@endpush

@section('content')
<div class="content">
    <!-- Page Header -->
    <div class="page-header-custom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2"><i class="fa fa-edit me-3"></i>Edit Pricing Configuration</h1>
                <p class="mb-0 opacity-90">Configure pricing type and calculation rules for apartment</p>
            </div>
            <a href="{{ route('admin.pricing-configuration.index') }}" class="btn btn-outline-light btn-lg">
                <i class="fa fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Apartment Information -->
            <div class="apartment-info-card">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fa fa-home me-2"></i>Apartment Information</h5>
                        <p class="mb-1"><strong>Apartment ID:</strong> {{ $apartment->apartment_id }}</p>
                        <p class="mb-1"><strong>Property:</strong> {{ $apartment->property->property_name ?? 'Unknown Property' }}</p>
                        <p class="mb-1"><strong>Type:</strong> {{ $apartment->apartment_type }}</p>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fa fa-money-bill me-2"></i>Current Settings</h5>
                        <p class="mb-1"><strong>Current Amount:</strong> ₦{{ number_format($apartment->amount, 2) }}</p>
                        <p class="mb-1"><strong>Current Type:</strong> 
                            <span class="badge bg-{{ $apartment->getPricingType() == 'total' ? 'success' : 'primary' }}">
                                {{ ucfirst($apartment->getPricingType()) }}
                            </span>
                        </p>
                        <p class="mb-1"><strong>Configuration:</strong> 
                            @if($apartment->price_configuration)
                                <span class="text-success"><i class="fa fa-check-circle"></i> Custom</span>
                            @else
                                <span class="text-warning"><i class="fa fa-exclamation-triangle"></i> Default</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Configuration Form -->
            <form method="POST" action="{{ route('admin.pricing-configuration.update', $apartment) }}" id="pricingForm">
                @csrf
                @method('PUT')
                
                <div class="config-section">
                    <h5 class="mb-4"><i class="fa fa-cogs me-2"></i>Pricing Configuration</h5>
                    
                    <!-- Amount Field -->
                    <div class="mb-4">
                        <label for="amount" class="form-label">Base Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                   id="amount" name="amount" value="{{ old('amount', $apartment->amount) }}" 
                                   step="0.01" min="0" required>
                        </div>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">The base amount for this apartment</div>
                    </div>

                    <!-- Pricing Type Selection -->
                    <div class="mb-4">
                        <label class="form-label">Pricing Type <span class="text-danger">*</span></label>
                        
                        <div class="pricing-type-option {{ old('pricing_type', $apartment->getPricingType()) == 'total' ? 'selected' : '' }}" 
                             onclick="selectPricingType('total')">
                            <input type="radio" name="pricing_type" value="total" id="pricing_total" 
                                   {{ old('pricing_type', $apartment->getPricingType()) == 'total' ? 'checked' : '' }} 
                                   style="display: none;" required>
                            <div class="d-flex align-items-start">
                                <div class="icon me-3">
                                    <i class="fa fa-calculator"></i>
                                </div>
                                <div>
                                    <h6 class="mb-2">Total Amount</h6>
                                    <p class="mb-0 text-muted">
                                        The amount represents the complete rental cost. 
                                        Duration will not affect the total payment.
                                    </p>
                                    <small class="text-success">
                                        <i class="fa fa-info-circle"></i> 
                                        Example: ₦500,000 for any rental duration
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="pricing-type-option {{ old('pricing_type', $apartment->getPricingType()) == 'monthly' ? 'selected' : '' }}" 
                             onclick="selectPricingType('monthly')">
                            <input type="radio" name="pricing_type" value="monthly" id="pricing_monthly" 
                                   {{ old('pricing_type', $apartment->getPricingType()) == 'monthly' ? 'checked' : '' }} 
                                   style="display: none;" required>
                            <div class="d-flex align-items-start">
                                <div class="icon me-3">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <div>
                                    <h6 class="mb-2">Monthly Amount</h6>
                                    <p class="mb-0 text-muted">
                                        The amount represents monthly rent. 
                                        Total payment = Amount × Rental Duration.
                                    </p>
                                    <small class="text-primary">
                                        <i class="fa fa-info-circle"></i> 
                                        Example: ₦50,000/month × 12 months = ₦600,000
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        @error('pricing_type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Advanced Configuration -->
                    <div class="mb-4">
                        <h6 class="mb-3">Advanced Configuration (Optional)</h6>
                        
                        <div class="mb-3">
                            <label for="config_description" class="form-label">Configuration Description</label>
                            <textarea class="form-control @error('price_configuration.description') is-invalid @enderror" 
                                      id="config_description" name="price_configuration[description]" rows="3"
                                      placeholder="Optional description for this pricing configuration...">{{ old('price_configuration.description', $currentConfig['description'] ?? '') }}</textarea>
                            @error('price_configuration.description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="config_base_amount" class="form-label">Override Base Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₦</span>
                                    <input type="number" class="form-control @error('price_configuration.base_amount') is-invalid @enderror" 
                                           id="config_base_amount" name="price_configuration[base_amount]" 
                                           value="{{ old('price_configuration.base_amount', $currentConfig['base_amount'] ?? '') }}" 
                                           step="0.01" min="0">
                                </div>
                                @error('price_configuration.base_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave empty to use the main amount field</div>
                            </div>
                            <div class="col-md-6">
                                <label for="config_multiplier" class="form-label">Custom Multiplier</label>
                                <input type="number" class="form-control @error('price_configuration.multiplier') is-invalid @enderror" 
                                       id="config_multiplier" name="price_configuration[multiplier]" 
                                       value="{{ old('price_configuration.multiplier', $currentConfig['multiplier'] ?? '') }}" 
                                       step="0.01" min="0">
                                @error('price_configuration.multiplier')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Custom multiplier for calculations</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="config-section">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.pricing-configuration.index') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-times me-2"></i>Cancel
                        </a>
                        <div>
                            <button type="button" class="btn btn-outline-info me-2" onclick="showPreview()">
                                <i class="fa fa-eye me-2"></i>Preview Calculation
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-2"></i>Save Configuration
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <!-- Preview Section -->
            <div class="config-section">
                <h5 class="mb-3"><i class="fa fa-calculator me-2"></i>Calculation Preview</h5>
                
                <div class="mb-3">
                    <label for="preview_duration" class="form-label">Rental Duration (Months)</label>
                    <input type="number" class="form-control" id="preview_duration" value="12" min="1" max="60">
                </div>
                
                <button type="button" class="btn btn-outline-primary w-100 mb-3" onclick="calculatePreview()">
                    <i class="fa fa-calculator me-2"></i>Calculate Preview
                </button>
                
                <div id="previewResults" style="display: none;">
                    <div class="preview-section">
                        <h6>Calculation Results</h6>
                        <div id="calculationDetails"></div>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="config-section">
                <h5 class="mb-3"><i class="fa fa-question-circle me-2"></i>Help & Guidelines</h5>
                
                <div class="accordion" id="helpAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapseOne" aria-expanded="false">
                                Pricing Types Explained
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <strong>Total Amount:</strong> Use when the amount represents the complete rental cost regardless of duration.<br><br>
                                <strong>Monthly Amount:</strong> Use when the amount represents monthly rent that should be multiplied by the rental duration.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapseTwo" aria-expanded="false">
                                Advanced Configuration
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <strong>Override Base Amount:</strong> Use a different amount for calculations than the main amount field.<br><br>
                                <strong>Custom Multiplier:</strong> Apply a custom multiplier to the calculation (advanced use only).
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapseThree" aria-expanded="false">
                                Audit Trail
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                All changes to pricing configurations are logged for audit purposes. You can view the complete audit trail from the main pricing configuration page.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select pricing type
function selectPricingType(type) {
    // Remove selected class from all options
    document.querySelectorAll('.pricing-type-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Add selected class to clicked option
    event.currentTarget.classList.add('selected');
    
    // Check the radio button
    document.getElementById('pricing_' + type).checked = true;
}

// Calculate preview
function calculatePreview() {
    const amount = document.getElementById('amount').value;
    const pricingType = document.querySelector('input[name="pricing_type"]:checked')?.value;
    const duration = document.getElementById('preview_duration').value;
    
    if (!amount || !pricingType || !duration) {
        alert('Please fill in all required fields first');
        return;
    }
    
    // Show loading
    const resultsDiv = document.getElementById('previewResults');
    const detailsDiv = document.getElementById('calculationDetails');
    detailsDiv.innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Calculating...</div>';
    resultsDiv.style.display = 'block';
    
    // Make AJAX request
    fetch('{{ route("admin.pricing-configuration.preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            apartment_id: '{{ $apartment->apartment_id }}',
            pricing_type: pricingType,
            amount: parseFloat(amount),
            duration: parseInt(duration)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const calc = data.calculation;
            let html = `
                <div class="calculation-result">
                    <div class="row mb-2">
                        <div class="col-6"><strong>Base Amount:</strong></div>
                        <div class="col-6">₦${parseFloat(calc.base_amount).toLocaleString()}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Duration:</strong></div>
                        <div class="col-6">${calc.duration} months</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Pricing Type:</strong></div>
                        <div class="col-6">${calc.pricing_type}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Method:</strong></div>
                        <div class="col-6">${calc.calculation_method}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6"><strong>Total Amount:</strong></div>
                        <div class="col-6"><strong class="text-success">₦${parseFloat(calc.total_amount).toLocaleString()}</strong></div>
                    </div>
                </div>
            `;
            
            if (calc.calculation_steps && calc.calculation_steps.length > 0) {
                html += '<div class="mt-3"><strong>Calculation Steps:</strong><ul class="mt-2">';
                calc.calculation_steps.forEach(step => {
                    html += `<li>${step}</li>`;
                });
                html += '</ul></div>';
            }
            
            detailsDiv.innerHTML = html;
        } else {
            detailsDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        detailsDiv.innerHTML = '<div class="alert alert-danger">Failed to calculate preview</div>';
    });
}

// Show preview modal (alternative implementation)
function showPreview() {
    calculatePreview();
}

// Form validation
document.getElementById('pricingForm').addEventListener('submit', function(e) {
    const amount = document.getElementById('amount').value;
    const pricingType = document.querySelector('input[name="pricing_type"]:checked');
    
    if (!amount || parseFloat(amount) < 0) {
        e.preventDefault();
        alert('Please enter a valid amount');
        return;
    }
    
    if (!pricingType) {
        e.preventDefault();
        alert('Please select a pricing type');
        return;
    }
    
    // Disable submit button to prevent double submission
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Saving...';
});

// Auto-calculate preview when values change
document.getElementById('amount').addEventListener('input', function() {
    if (document.getElementById('previewResults').style.display !== 'none') {
        setTimeout(calculatePreview, 500); // Debounce
    }
});

document.addEventListener('change', function(e) {
    if (e.target.name === 'pricing_type' && document.getElementById('previewResults').style.display !== 'none') {
        calculatePreview();
    }
});

document.getElementById('preview_duration').addEventListener('input', function() {
    if (document.getElementById('previewResults').style.display !== 'none') {
        setTimeout(calculatePreview, 500); // Debounce
    }
});
</script>
@endpush
@extends('layouts.admin')

@section('title', 'Pricing Configuration Management')

@push('styles')
<style>
    .page-header-custom {
        background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        color: white;
        padding: 32px;
        border-radius: 16px;
        margin-bottom: 32px;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
    }

    .stats-mini-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease;
        border-left: 4px solid #1e7e34;
    }

    .stats-mini-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }

    .stats-mini-card .icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
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

    .pricing-type-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
        text-transform: uppercase;
    }

    .pricing-type-total {
        background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
        color: #2e7d32;
        border: 1px solid #81c784;
    }

    .pricing-type-monthly {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        color: #1976d2;
        border: 1px solid #90caf9;
    }

    .amount-display {
        font-weight: 600;
        color: #2e7d32;
        font-size: 16px;
    }

    .config-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-left: 8px;
    }

    .config-indicator.configured {
        background: #4caf50;
    }

    .config-indicator.not-configured {
        background: #ff9800;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .preview-modal .calculation-step {
        background: #f8f9fa;
        border-left: 4px solid #1e7e34;
        padding: 12px;
        margin: 8px 0;
        border-radius: 4px;
    }
</style>
@endpush

@section('content')
<div class="content">
    <!-- Enhanced Page Header -->
    <div class="page-header-custom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2"><i class="fa fa-cogs me-3"></i>Pricing Configuration Management</h1>
                <p class="mb-0 opacity-90">Manage apartment pricing types and calculation rules</p>
            </div>
            <div>
                <button class="btn btn-light btn-lg me-2" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                    <i class="fa fa-edit me-2"></i>Bulk Update
                </button>
                <a href="{{ route('admin.pricing-configuration.audit-trail') }}" class="btn btn-outline-light btn-lg">
                    <i class="fa fa-history me-2"></i>Audit Trail
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Mini Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-mini-card">
                <div class="d-flex align-items-center">
                    <div class="icon me-3">
                        <i class="fa fa-home"></i>
                    </div>
                    <div>
                        <div class="value">{{ $stats['total_apartments'] }}</div>
                        <div class="label">Total Apartments</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-mini-card" style="border-left-color: #4caf50;">
                <div class="d-flex align-items-center">
                    <div class="icon me-3" style="background: linear-gradient(135deg, #4caf50 0%, #81c784 100%);">
                        <i class="fa fa-calculator"></i>
                    </div>
                    <div>
                        <div class="value">{{ $stats['total_pricing'] }}</div>
                        <div class="label">Total Pricing</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-mini-card" style="border-left-color: #2196f3;">
                <div class="d-flex align-items-center">
                    <div class="icon me-3" style="background: linear-gradient(135deg, #2196f3 0%, #64b5f6 100%);">
                        <i class="fa fa-calendar"></i>
                    </div>
                    <div>
                        <div class="value">{{ $stats['monthly_pricing'] }}</div>
                        <div class="label">Monthly Pricing</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-mini-card" style="border-left-color: #ff9800;">
                <div class="d-flex align-items-center">
                    <div class="icon me-3" style="background: linear-gradient(135deg, #ff9800 0%, #ffb74d 100%);">
                        <i class="fa fa-cog"></i>
                    </div>
                    <div>
                        <div class="value">{{ $stats['configured_apartments'] }}</div>
                        <div class="label">Configured</div>
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
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ $search }}"
                                placeholder="Apartment ID or Property name...">
                        </div>
                        <div class="col-md-3">
                            <label for="pricing_type" class="form-label">Pricing Type</label>
                            <select class="form-select" id="pricing_type" name="pricing_type">
                                <option value="">All Types</option>
                                @foreach($pricingTypes as $type)
                                <option value="{{ $type }}" {{ $pricingType==$type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="property_id" class="form-label">Property</label>
                            <select class="form-select" id="property_id" name="property_id">
                                <option value="">All Properties</option>
                                @foreach($properties as $property)
                                <option value="{{ $property->property_id }}" {{ $propertyId==$property->property_id ?
                                    'selected' : '' }}>
                                    {{ $property->property_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fa fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.pricing-configuration.index') }}"
                                class="btn btn-outline-secondary">
                                <i class="fa fa-times"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Apartments List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Apartment Pricing Configurations ({{ $apartments->total() }})</h5>
                </div>
                <div class="card-body">
                    @if($apartments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Apartment</th>
                                    <th>Property</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Pricing Type</th>
                                    <th>Configuration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($apartments as $apartment)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input apartment-checkbox"
                                            value="{{ $apartment->apartment_id }}">
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $apartment->apartment_id }}</div>
                                        <small class="text-muted">ID: {{ $apartment->apartment_id }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $apartment->property->property_name ?? 'Unknown' }}
                                        </div>
                                        <small class="text-muted">ID: {{ $apartment->property_id }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $apartment->apartment_type }}</span>
                                    </td>
                                    <td>
                                        <div class="amount-display">{{ format_money($apartment->amount, $apartment->property->currency ?? null) }}</div>
                                    </td>
                                    <td>
                                        <span
                                            class="pricing-type-badge pricing-type-{{ $apartment->getPricingType() }}">
                                            {{ ucfirst($apartment->getPricingType()) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($apartment->price_configuration)
                                        <span class="text-success">
                                            <i class="fa fa-check-circle"></i> Configured
                                        </span>
                                        <span class="config-indicator configured"
                                            title="Has custom configuration"></span>
                                        @else
                                        <span class="text-warning">
                                            <i class="fa fa-exclamation-triangle"></i> Default
                                        </span>
                                        <span class="config-indicator not-configured"
                                            title="Using default configuration"></span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.pricing-configuration.edit', $apartment) }}"
                                                class="btn btn-sm btn-outline-primary" title="Edit Configuration">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="showPreview('{{ $apartment->apartment_id }}', '{{ $apartment->getPricingType() }}', {{ $apartment->amount }})"
                                                title="Preview Calculation">
                                                <i class="fa fa-calculator"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                onclick="showApartmentDetails('{{ $apartment->apartment_id }}')"
                                                title="View Details">
                                                <i class="fa fa-eye"></i>
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
                            Showing {{ $apartments->firstItem() }} to {{ $apartments->lastItem() }}
                            of {{ $apartments->total() }} results
                        </div>
                        {{ $apartments->appends(request()->query())->links() }}
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fa fa-home fa-3x text-muted mb-3"></i>
                        <h5>No Apartments Found</h5>
                        <p class="text-muted">No apartments match your current filters.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.pricing-configuration.bulk-update') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Update Pricing Configuration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Selected Apartments</label>
                        <div id="selectedApartments" class="border rounded p-2 bg-light">
                            <em class="text-muted">Select apartments from the list above</em>
                        </div>
                        <input type="hidden" name="apartment_ids" id="selectedApartmentIds">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pricing Type <span class="text-danger">*</span></label>
                        <select name="pricing_type" class="form-select" required>
                            <option value="">Select Pricing Type</option>
                            <option value="total">Total Amount</option>
                            <option value="monthly">Monthly Amount</option>
                        </select>
                        <div class="form-text">
                            <strong>Total:</strong> Amount represents the complete rental cost<br>
                            <strong>Monthly:</strong> Amount will be multiplied by rental duration
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="update_amount" id="updateAmount">
                            <label class="form-check-label" for="updateAmount">
                                Update Amount
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="amountField" style="display: none;">
                        <label class="form-label">New Amount</label>
                        <div class="input-group">
                            <span class="input-group-text currency-symbol-preview">${window.currencySymbol}</span>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Configuration Description (Optional)</label>
                        <textarea name="price_configuration[description]" class="form-control" rows="3"
                            placeholder="Optional description for this pricing configuration..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Selected Apartments</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pricing Calculation Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Rental Duration (Months)</label>
                        <input type="number" id="previewDuration" class="form-control" value="12" min="1" max="60">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="button" class="btn btn-primary" onclick="calculatePreview()">
                            <i class="fa fa-calculator"></i> Calculate
                        </button>
                    </div>
                </div>

                <div id="previewResults" style="display: none;">
                    <h6>Calculation Results</h6>
                    <div id="calculationDetails"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentPreviewApartment = null;
    let currentPreviewType = null;
    let currentPreviewAmount = null;

    // Handle select all checkbox
    document.getElementById('selectAll').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('.apartment-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedApartments();
    });

    // Handle individual checkboxes
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('apartment-checkbox')) {
            updateSelectedApartments();
        }
    });

    // Update selected apartments display
    function updateSelectedApartments() {
        const selectedCheckboxes = document.querySelectorAll('.apartment-checkbox:checked');
        const selectedContainer = document.getElementById('selectedApartments');
        const selectedIds = document.getElementById('selectedApartmentIds');

        if (selectedCheckboxes.length === 0) {
            selectedContainer.innerHTML = '<em class="text-muted">Select apartments from the list above</em>';
            selectedIds.value = '';
            return;
        }

        const ids = [];
        const apartmentIds = [];

        selectedCheckboxes.forEach(checkbox => {
            ids.push(checkbox.value);
            apartmentIds.push(checkbox.value);
        });

        selectedIds.value = ids.join(',');
        selectedContainer.innerHTML = apartmentIds.map(id =>
            `<span class="badge bg-primary me-1">Apartment ${id}</span>`
        ).join('');
    }

    // Handle update amount checkbox
    document.getElementById('updateAmount').addEventListener('change', function () {
        const amountField = document.getElementById('amountField');
        if (this.checked) {
            amountField.style.display = 'block';
            amountField.querySelector('input').required = true;
        } else {
            amountField.style.display = 'none';
            amountField.querySelector('input').required = false;
        }
    });

    // Show preview modal
    function showPreview(apartmentId, pricingType, amount) {
        currentPreviewApartment = apartmentId;
        currentPreviewType = pricingType;
        currentPreviewAmount = amount;

        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    }

    // Calculate preview
    function calculatePreview() {
        if (!currentPreviewApartment) return;

        const duration = document.getElementById('previewDuration').value;

        if (!duration || duration < 1) {
            alert('Please enter a valid duration');
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
                apartment_id: currentPreviewApartment,
                pricing_type: currentPreviewType,
                amount: currentPreviewAmount,
                duration: parseInt(duration)
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const calc = data.calculation;
                    let html = `
                <div class="calculation-step">
                    <strong>Apartment:</strong> ${data.apartment.id} (${data.apartment.property_name})
                </div>
                <div class="calculation-step">
                    <strong>Base Amount:</strong> ${data.apartment.currency_symbol || window.currencySymbol}${parseFloat(calc.base_amount).toLocaleString()}
                </div>
                <div class="calculation-step">
                    <strong>Duration:</strong> ${calc.duration} months
                </div>
                <div class="calculation-step">
                    <strong>Pricing Type:</strong> ${calc.pricing_type}
                </div>
                <div class="calculation-step">
                    <strong>Calculation Method:</strong> ${calc.calculation_method}
                </div>
            `;

                    if (calc.calculation_steps && calc.calculation_steps.length > 0) {
                        html += '<div class="calculation-step"><strong>Steps:</strong><ul>';
                        calc.calculation_steps.forEach(step => {
                            html += `<li>${step}</li>`;
                        });
                        html += '</ul></div>';
                    }

                    html += `
                <div class="calculation-step bg-success text-white">
                    <strong>Total Amount: ${data.apartment.currency_symbol || window.currencySymbol}${parseFloat(calc.total_amount).toLocaleString()}</strong>
                </div>
            `;

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

    // Show apartment details
    function showApartmentDetails(apartmentId) {
        // Make AJAX request to get apartment data
        fetch(`{{ route('admin.pricing-configuration.apartment-data') }}?apartment_id=${apartmentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const apartment = data.apartment;
                    let configHtml = 'No custom configuration';

                    if (apartment.price_configuration) {
                        configHtml = '<pre>' + JSON.stringify(apartment.price_configuration, null, 2) + '</pre>';
                    }

                    const detailsHtml = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Apartment ID:</strong> ${apartment.id}<br>
                        <strong>Property:</strong> ${apartment.property_name}<br>
                        <strong>Type:</strong> ${apartment.apartment_type}<br>
                    </div>
                    <div class="col-md-6">
                        <strong>Amount:</strong> ${apartment.currency_symbol || window.currencySymbol}${parseFloat(apartment.amount).toLocaleString()}<br>
                        <strong>Pricing Type:</strong> ${apartment.pricing_type}<br>
                    </div>
                </div>
                <hr>
                <strong>Configuration:</strong><br>
                ${configHtml}
            `;

                    // Show in a simple alert for now (could be enhanced with a proper modal)
                    const modal = document.createElement('div');
                    modal.innerHTML = `
                <div class="modal fade" id="detailsModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Apartment Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">${detailsHtml}</div>
                        </div>
                    </div>
                </div>
            `;
                    document.body.appendChild(modal);

                    const bootstrapModal = new bootstrap.Modal(document.getElementById('detailsModal'));
                    bootstrapModal.show();

                    // Clean up modal after hiding
                    document.getElementById('detailsModal').addEventListener('hidden.bs.modal', function () {
                        document.body.removeChild(modal);
                    });
                } else {
                    alert('Failed to load apartment details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load apartment details');
            });
    }

    // Form submission handlers
    document.addEventListener('DOMContentLoaded', function () {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
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
    });
</script>
@endpush
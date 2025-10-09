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
                                <i class="fas fa-calculator text-primary me-2"></i>
                                Commission Management
                            </h4>
                            <p class="text-muted mb-0">Manage commission rates for different property management scenarios</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.commission-management.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add New Rate
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Calculator -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-calculator text-info me-2"></i>
                        Commission Calculator
                    </h6>
                </div>
                <div class="card-body">
                    <form id="commissionCalculatorForm" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Rent Amount</label>
                            <input type="number" class="form-control" id="rentAmount" placeholder="Enter rent amount" step="0.01" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Region</label>
                            <select class="form-select" id="calculatorRegion">
                                @foreach($regions as $region)
                                    <option value="{{ $region }}">{{ ucfirst(str_replace('_', ' ', $region)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Property Management</label>
                            <select class="form-select" id="propertyManagement">
                                <option value="unmanaged">Unmanaged (5%)</option>
                                <option value="managed">Managed (2.5%)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Hierarchy Status</label>
                            <select class="form-select" id="hierarchyStatus">
                                <option value="without_super_marketer">Without Super Marketer</option>
                                <option value="with_super_marketer">With Super Marketer</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-info w-100" onclick="calculateCommission()">
                                <i class="fas fa-calculator me-1"></i>Calculate
                            </button>
                        </div>
                    </form>
                    <div id="calculationResult" class="mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Rates by Region -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-table text-success me-2"></i>
                        Commission Rates by Region
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Region</th>
                                    <th>Property Management</th>
                                    <th>Hierarchy</th>
                                    <th>Super Marketer</th>
                                    <th>Marketer</th>
                                    <th>Regional Manager</th>
                                    <th>Company</th>
                                    <th>Total</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($commissionRates as $region => $regionRates)
                                    @foreach($regionRates as $propertyStatus => $propertyRates)
                                        @foreach($propertyRates as $hierarchyStatus => $rates)
                                            @foreach($rates as $rate)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $region)) }}</span>
                                                    </td>
                                                    <td>
                                                        @if($propertyStatus === 'managed')
                                                            <span class="badge bg-info">Managed</span>
                                                        @else
                                                            <span class="badge bg-secondary">Unmanaged</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($hierarchyStatus === 'with_super_marketer')
                                                            <span class="badge bg-success">With Super Marketer</span>
                                                        @else
                                                            <span class="badge bg-warning">Without Super Marketer</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $rate->super_marketer_rate ? number_format($rate->super_marketer_rate, 3) . '%' : 'N/A' }}</td>
                                                    <td>{{ number_format($rate->marketer_rate, 3) }}%</td>
                                                    <td>{{ $rate->regional_manager_rate ? number_format($rate->regional_manager_rate, 3) . '%' : 'N/A' }}</td>
                                                    <td>{{ number_format($rate->company_rate, 3) }}%</td>
                                                    <td><strong>{{ number_format($rate->total_commission_rate, 3) }}%</strong></td>
                                                    <td>
                                                        @if($rate->last_updated_at)
                                                            <small>{{ $rate->last_updated_at->format('M d, Y H:i') }}</small>
                                                            @if($rate->updater)
                                                                <br><small class="text-muted">by {{ $rate->updater->first_name }}</small>
                                                            @endif
                                                        @else
                                                            <small class="text-muted">Not updated</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('admin.commission-management.edit', $rate) }}" 
                                                               class="btn btn-outline-primary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button class="btn btn-outline-danger" 
                                                                    onclick="deleteRate({{ $rate->id }})" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                            <h6>No Commission Rates Found</h6>
                                            <p>Start by adding commission rates for different scenarios</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this commission rate? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function calculateCommission() {
    const rentAmount = document.getElementById('rentAmount').value;
    const region = document.getElementById('calculatorRegion').value;
    const propertyManagement = document.getElementById('propertyManagement').value;
    const hierarchyStatus = document.getElementById('hierarchyStatus').value;
    
    if (!rentAmount || rentAmount <= 0) {
        alert('Please enter a valid rent amount');
        return;
    }
    
    $.ajax({
        url: '{{ route("admin.commission-management.breakdown") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            rent_amount: rentAmount,
            region: region,
            property_management_status: propertyManagement,
            hierarchy_status: hierarchyStatus
        },
        success: function(response) {
            if (response.success) {
                displayCalculationResult(response.breakdown, rentAmount);
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('Failed to calculate commission');
        }
    });
}

function displayCalculationResult(breakdown, rentAmount) {
    const resultDiv = document.getElementById('calculationResult');
    
    let html = `
        <div class="alert alert-info">
            <h6 class="mb-3"><i class="fas fa-calculator me-2"></i>Commission Breakdown for ₦${parseFloat(rentAmount).toLocaleString()}</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>Total Commission:</strong></td>
                                <td class="text-end"><strong>₦${breakdown.total_commission.toLocaleString()}</strong></td>
                            </tr>
    `;
    
    if (breakdown.super_marketer_commission > 0) {
        html += `
            <tr>
                <td>Super Marketer (${breakdown.rates.super_marketer_rate}%):</td>
                <td class="text-end">₦${breakdown.super_marketer_commission.toLocaleString()}</td>
            </tr>
        `;
    }
    
    html += `
                            <tr>
                                <td>Marketer (${breakdown.rates.marketer_rate}%):</td>
                                <td class="text-end">₦${breakdown.marketer_commission.toLocaleString()}</td>
                            </tr>
    `;
    
    if (breakdown.regional_manager_commission > 0) {
        html += `
            <tr>
                <td>Regional Manager (${breakdown.rates.regional_manager_rate}%):</td>
                <td class="text-end">₦${breakdown.regional_manager_commission.toLocaleString()}</td>
            </tr>
        `;
    }
    
    html += `
                            <tr>
                                <td>Company (${breakdown.rates.company_rate}%):</td>
                                <td class="text-end">₦${breakdown.company_commission.toLocaleString()}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center">
                        <div class="display-6 text-primary">₦${breakdown.total_commission.toLocaleString()}</div>
                        <small class="text-muted">Total Commission (${breakdown.rates.total_rate}%)</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    resultDiv.innerHTML = html;
    resultDiv.style.display = 'block';
}

function deleteRate(rateId) {
    document.getElementById('deleteForm').action = `/admin/commission-management/rate/${rateId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection
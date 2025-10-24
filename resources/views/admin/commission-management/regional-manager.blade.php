@extends('layout')

@section('title', 'Regional Commission Manager')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fas fa-map-marked-alt text-primary me-2"></i>
                                Regional Commission Manager
                            </h4>
                            <p class="text-muted mb-0">Select a region and manage all commission rates for different scenarios</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.commission-management.index') }}" class="btn btn-secondary">
                                <i class="fas fa-table me-2"></i>View All Rates
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Region Selector -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-globe text-info me-2"></i>
                        Select Region/State
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Choose Region/State</label>
                            <select class="form-select" id="regionSelector" onchange="loadRegionRates()">
                                <option value="">Select a region...</option>
                                <option value="default">Default (Fallback)</option>
                                <option value="lagos">Lagos State</option>
                                <option value="abuja">Abuja (FCT)</option>
                                <option value="kano">Kano State</option>
                                <option value="rivers">Rivers State</option>
                                <option value="ogun">Ogun State</option>
                                <option value="anambra">Anambra State</option>
                                <option value="kaduna">Kaduna State</option>
                                <option value="oyo">Oyo State</option>
                                <option value="plateau">Plateau State</option>
                                <option value="delta">Delta State</option>
                                <option value="edo">Edo State</option>
                                <option value="enugu">Enugu State</option>
                                <option value="imo">Imo State</option>
                                <option value="abia">Abia State</option>
                                <option value="akwa_ibom">Akwa Ibom State</option>
                                <option value="bayelsa">Bayelsa State</option>
                                <option value="cross_river">Cross River State</option>
                                <option value="ebonyi">Ebonyi State</option>
                                <option value="ekiti">Ekiti State</option>
                                <option value="gombe">Gombe State</option>
                                <option value="ondo">Ondo State</option>
                                <option value="osun">Osun State</option>
                                <option value="taraba">Taraba State</option>
                                <option value="adamawa">Adamawa State</option>
                                <option value="bauchi">Bauchi State</option>
                                <option value="benue">Benue State</option>
                                <option value="borno">Borno State</option>
                                <option value="jigawa">Jigawa State</option>
                                <option value="kebbi">Kebbi State</option>
                                <option value="kogi">Kogi State</option>
                                <option value="kwara">Kwara State</option>
                                <option value="nasarawa">Nasarawa State</option>
                                <option value="niger">Niger State</option>
                                <option value="sokoto">Sokoto State</option>
                                <option value="yobe">Yobe State</option>
                                <option value="zamfara">Zamfara State</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quick Actions</label>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success" onclick="copyFromDefault()" id="copyDefaultBtn" disabled>
                                    <i class="fas fa-copy me-1"></i>Copy from Default
                                </button>
                                <button class="btn btn-primary" onclick="createNewRegion()" id="createNewBtn" disabled>
                                    <i class="fas fa-plus me-1"></i>Create New
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Commission Calculator</label>
                            <div class="input-group">
                                <span class="input-group-text">₦</span>
                                <input type="number" class="form-control" id="calculatorRent" placeholder="Enter rent amount" value="100000">
                                <button class="btn btn-outline-info" onclick="calculateForRegion()">
                                    <i class="fas fa-calculator"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Region Commission Rates -->
    <div id="regionRatesContainer" style="display: none;">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>
                            Commission Rates for <span id="selectedRegionName"></span>
                        </h6>
                        <button class="btn btn-light btn-sm" onclick="saveAllRates()">
                            <i class="fas fa-save me-1"></i>Save All Changes
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="regionalRatesForm">
                            <div class="row">
                                <!-- Unmanaged Properties -->
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-home me-2"></i>Unmanaged Properties (5% Total)
                                    </h6>
                                    
                                    <!-- Without Super Marketer -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <small class="fw-bold">Without Super Marketer</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small">Marketer (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="unmanaged_without_marketer_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('unmanaged_without')">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small">Regional Mgr (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="unmanaged_without_regional_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('unmanaged_without')">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small">Company (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="unmanaged_without_company_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('unmanaged_without')">
                                                </div>
                                                <div class="col-12">
                                                    <div class="alert alert-info py-1 px-2 small">
                                                        Total: <span id="unmanaged_without_total">0.000%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- With Super Marketer -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <small class="fw-bold">With Super Marketer</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small">Super Marketer (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="unmanaged_with_super_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('unmanaged_with')">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small">Marketer (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="unmanaged_with_marketer_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('unmanaged_with')">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small">Regional Mgr (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="unmanaged_with_regional_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('unmanaged_with')">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small">Company (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="unmanaged_with_company_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('unmanaged_with')">
                                                </div>
                                                <div class="col-12">
                                                    <div class="alert alert-info py-1 px-2 small">
                                                        Total: <span id="unmanaged_with_total">0.000%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Managed Properties -->
                                <div class="col-md-6">
                                    <h6 class="text-success mb-3">
                                        <i class="fas fa-building me-2"></i>Managed Properties (2.5% Total)
                                    </h6>
                                    
                                    <!-- Without Super Marketer -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <small class="fw-bold">Without Super Marketer</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small">Marketer (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="managed_without_marketer_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('managed_without')">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small">Regional Mgr (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="managed_without_regional_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('managed_without')">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small">Company (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="managed_without_company_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('managed_without')">
                                                </div>
                                                <div class="col-12">
                                                    <div class="alert alert-info py-1 px-2 small">
                                                        Total: <span id="managed_without_total">0.000%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- With Super Marketer -->
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <small class="fw-bold">With Super Marketer</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small">Super Marketer (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="managed_with_super_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('managed_with')">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small">Marketer (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="managed_with_marketer_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('managed_with')">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small">Regional Mgr (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="managed_with_regional_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('managed_with')">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small">Company (%)</label>
                                                    <input type="number" class="form-control form-control-sm" 
                                                           name="managed_with_company_rate" 
                                                           step="0.001" min="0" max="100"
                                                           onchange="updateTotals('managed_with')">
                                                </div>
                                                <div class="col-12">
                                                    <div class="alert alert-info py-1 px-2 small">
                                                        Total: <span id="managed_with_total">0.000%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commission Preview -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-calculator me-2"></i>
                            Commission Preview
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="commissionPreview">
                            <div class="text-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                Enter rent amount and modify rates to see commission breakdown
                            </div>
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
let currentRegion = '';
let regionRates = {};

function loadRegionRates() {
    const region = document.getElementById('regionSelector').value;
    if (!region) {
        document.getElementById('regionRatesContainer').style.display = 'none';
        return;
    }
    
    currentRegion = region;
    document.getElementById('selectedRegionName').textContent = 
        document.getElementById('regionSelector').selectedOptions[0].text;
    
    // Enable buttons
    document.getElementById('copyDefaultBtn').disabled = false;
    document.getElementById('createNewBtn').disabled = false;
    
    // Load existing rates for this region
    fetch(`/admin/commission-management/region/${region}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateRates(data.rates);
                document.getElementById('regionRatesContainer').style.display = 'block';
            } else {
                // Region doesn't exist, show empty form
                populateRates({});
                document.getElementById('regionRatesContainer').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading region rates:', error);
            // Show empty form anyway
            populateRates({});
            document.getElementById('regionRatesContainer').style.display = 'block';
        });
}

function populateRates(rates) {
    // Default values
    const defaults = {
        unmanaged_without: { marketer: 1.500, regional: 0.250, company: 3.250 },
        unmanaged_with: { super: 0.500, marketer: 1.000, regional: 0.250, company: 3.250 },
        managed_without: { marketer: 0.750, regional: 0.100, company: 1.650 },
        managed_with: { super: 0.250, marketer: 0.500, regional: 0.100, company: 1.650 }
    };
    
    // Populate unmanaged without super marketer
    document.querySelector('[name="unmanaged_without_marketer_rate"]').value = 
        rates.unmanaged_without?.marketer_rate || defaults.unmanaged_without.marketer;
    document.querySelector('[name="unmanaged_without_regional_rate"]').value = 
        rates.unmanaged_without?.regional_manager_rate || defaults.unmanaged_without.regional;
    document.querySelector('[name="unmanaged_without_company_rate"]').value = 
        rates.unmanaged_without?.company_rate || defaults.unmanaged_without.company;
    
    // Populate unmanaged with super marketer
    document.querySelector('[name="unmanaged_with_super_rate"]').value = 
        rates.unmanaged_with?.super_marketer_rate || defaults.unmanaged_with.super;
    document.querySelector('[name="unmanaged_with_marketer_rate"]').value = 
        rates.unmanaged_with?.marketer_rate || defaults.unmanaged_with.marketer;
    document.querySelector('[name="unmanaged_with_regional_rate"]').value = 
        rates.unmanaged_with?.regional_manager_rate || defaults.unmanaged_with.regional;
    document.querySelector('[name="unmanaged_with_company_rate"]').value = 
        rates.unmanaged_with?.company_rate || defaults.unmanaged_with.company;
    
    // Populate managed without super marketer
    document.querySelector('[name="managed_without_marketer_rate"]').value = 
        rates.managed_without?.marketer_rate || defaults.managed_without.marketer;
    document.querySelector('[name="managed_without_regional_rate"]').value = 
        rates.managed_without?.regional_manager_rate || defaults.managed_without.regional;
    document.querySelector('[name="managed_without_company_rate"]').value = 
        rates.managed_without?.company_rate || defaults.managed_without.company;
    
    // Populate managed with super marketer
    document.querySelector('[name="managed_with_super_rate"]').value = 
        rates.managed_with?.super_marketer_rate || defaults.managed_with.super;
    document.querySelector('[name="managed_with_marketer_rate"]').value = 
        rates.managed_with?.marketer_rate || defaults.managed_with.marketer;
    document.querySelector('[name="managed_with_regional_rate"]').value = 
        rates.managed_with?.regional_manager_rate || defaults.managed_with.regional;
    document.querySelector('[name="managed_with_company_rate"]').value = 
        rates.managed_with?.company_rate || defaults.managed_with.company;
    
    // Update totals
    updateTotals('unmanaged_without');
    updateTotals('unmanaged_with');
    updateTotals('managed_without');
    updateTotals('managed_with');
    
    // Update preview
    calculateForRegion();
}

function updateTotals(scenario) {
    let total = 0;
    
    if (scenario === 'unmanaged_without') {
        total += parseFloat(document.querySelector('[name="unmanaged_without_marketer_rate"]').value) || 0;
        total += parseFloat(document.querySelector('[name="unmanaged_without_regional_rate"]').value) || 0;
        total += parseFloat(document.querySelector('[name="unmanaged_without_company_rate"]').value) || 0;
    } else if (scenario === 'unmanaged_with') {
        total += parseFloat(document.querySelector('[name="unmanaged_with_super_rate"]').value) || 0;
        total += parseFloat(document.querySelector('[name="unmanaged_with_marketer_rate"]').value) || 0;
        total += parseFloat(document.querySelector('[name="unmanaged_with_regional_rate"]').value) || 0;
        total += parseFloat(document.querySelector('[name="unmanaged_with_company_rate"]').value) || 0;
    } else if (scenario === 'managed_without') {
        total += parseFloat(document.querySelector('[name="managed_without_marketer_rate"]').value) || 0;
        total += parseFloat(document.querySelector('[name="managed_without_regional_rate"]').value) || 0;
        total += parseFloat(document.querySelector('[name="managed_without_company_rate"]').value) || 0;
    } else if (scenario === 'managed_with') {
        total += parseFloat(document.querySelector('[name="managed_with_super_rate"]').value) || 0;
        total += parseFloat(document.querySelector('[name="managed_with_marketer_rate"]').value) || 0;
        total += parseFloat(document.querySelector('[name="managed_with_regional_rate"]').value) || 0;
        total += parseFloat(document.querySelector('[name="managed_with_company_rate"]').value) || 0;
    }
    
    document.getElementById(scenario + '_total').textContent = total.toFixed(3) + '%';
    
    // Update color based on expected total
    const expectedTotal = scenario.startsWith('unmanaged') ? 5.0 : 2.5;
    const totalElement = document.getElementById(scenario + '_total').parentElement;
    
    if (Math.abs(total - expectedTotal) < 0.001) {
        totalElement.className = 'alert alert-success py-1 px-2 small';
    } else {
        totalElement.className = 'alert alert-warning py-1 px-2 small';
    }
    
    // Update preview
    calculateForRegion();
}

function calculateForRegion() {
    const rentAmount = parseFloat(document.getElementById('calculatorRent').value) || 0;
    if (rentAmount <= 0) return;
    
    const scenarios = [
        { name: 'Unmanaged - No Super Marketer', prefix: 'unmanaged_without' },
        { name: 'Unmanaged - With Super Marketer', prefix: 'unmanaged_with' },
        { name: 'Managed - No Super Marketer', prefix: 'managed_without' },
        { name: 'Managed - With Super Marketer', prefix: 'managed_with' }
    ];
    
    let html = '<div class="row">';
    
    scenarios.forEach(scenario => {
        let breakdown = '';
        let total = 0;
        
        if (scenario.prefix === 'unmanaged_without') {
            const marketer = parseFloat(document.querySelector('[name="unmanaged_without_marketer_rate"]').value) || 0;
            const regional = parseFloat(document.querySelector('[name="unmanaged_without_regional_rate"]').value) || 0;
            const company = parseFloat(document.querySelector('[name="unmanaged_without_company_rate"]').value) || 0;
            
            breakdown = `
                <div>Marketer: ₦${((rentAmount * marketer) / 100).toLocaleString()}</div>
                <div>Regional Mgr: ₦${((rentAmount * regional) / 100).toLocaleString()}</div>
                <div>Company: ₦${((rentAmount * company) / 100).toLocaleString()}</div>
            `;
            total = marketer + regional + company;
        } else if (scenario.prefix === 'unmanaged_with') {
            const super_m = parseFloat(document.querySelector('[name="unmanaged_with_super_rate"]').value) || 0;
            const marketer = parseFloat(document.querySelector('[name="unmanaged_with_marketer_rate"]').value) || 0;
            const regional = parseFloat(document.querySelector('[name="unmanaged_with_regional_rate"]').value) || 0;
            const company = parseFloat(document.querySelector('[name="unmanaged_with_company_rate"]').value) || 0;
            
            breakdown = `
                <div>Super Marketer: ₦${((rentAmount * super_m) / 100).toLocaleString()}</div>
                <div>Marketer: ₦${((rentAmount * marketer) / 100).toLocaleString()}</div>
                <div>Regional Mgr: ₦${((rentAmount * regional) / 100).toLocaleString()}</div>
                <div>Company: ₦${((rentAmount * company) / 100).toLocaleString()}</div>
            `;
            total = super_m + marketer + regional + company;
        } else if (scenario.prefix === 'managed_without') {
            const marketer = parseFloat(document.querySelector('[name="managed_without_marketer_rate"]').value) || 0;
            const regional = parseFloat(document.querySelector('[name="managed_without_regional_rate"]').value) || 0;
            const company = parseFloat(document.querySelector('[name="managed_without_company_rate"]').value) || 0;
            
            breakdown = `
                <div>Marketer: ₦${((rentAmount * marketer) / 100).toLocaleString()}</div>
                <div>Regional Mgr: ₦${((rentAmount * regional) / 100).toLocaleString()}</div>
                <div>Company: ₦${((rentAmount * company) / 100).toLocaleString()}</div>
            `;
            total = marketer + regional + company;
        } else if (scenario.prefix === 'managed_with') {
            const super_m = parseFloat(document.querySelector('[name="managed_with_super_rate"]').value) || 0;
            const marketer = parseFloat(document.querySelector('[name="managed_with_marketer_rate"]').value) || 0;
            const regional = parseFloat(document.querySelector('[name="managed_with_regional_rate"]').value) || 0;
            const company = parseFloat(document.querySelector('[name="managed_with_company_rate"]').value) || 0;
            
            breakdown = `
                <div>Super Marketer: ₦${((rentAmount * super_m) / 100).toLocaleString()}</div>
                <div>Marketer: ₦${((rentAmount * marketer) / 100).toLocaleString()}</div>
                <div>Regional Mgr: ₦${((rentAmount * regional) / 100).toLocaleString()}</div>
                <div>Company: ₦${((rentAmount * company) / 100).toLocaleString()}</div>
            `;
            total = super_m + marketer + regional + company;
        }
        
        html += `
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-light">
                        <small class="fw-bold">${scenario.name}</small>
                    </div>
                    <div class="card-body">
                        <div class="h5 text-primary">₦${((rentAmount * total) / 100).toLocaleString()}</div>
                        <div class="small text-muted">${breakdown}</div>
                        <div class="small"><strong>Total: ${total.toFixed(3)}%</strong></div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    document.getElementById('commissionPreview').innerHTML = html;
}

function copyFromDefault() {
    if (currentRegion === 'default') {
        alert('You are already viewing the default region');
        return;
    }
    
    fetch('/admin/commission-management/region/default')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateRates(data.rates);
                alert('Default rates copied successfully!');
            }
        })
        .catch(error => {
            console.error('Error copying default rates:', error);
            alert('Failed to copy default rates');
        });
}

function createNewRegion() {
    // Populate with standard default values
    populateRates({});
    alert('New region template loaded. Modify the rates and click "Save All Changes"');
}

function saveAllRates() {
    if (!currentRegion) {
        alert('Please select a region first');
        return;
    }
    
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('region', currentRegion);
    
    // Collect all rate data
    const scenarios = [
        {
            property_management_status: 'unmanaged',
            hierarchy_status: 'without_super_marketer',
            super_marketer_rate: null,
            marketer_rate: document.querySelector('[name="unmanaged_without_marketer_rate"]').value,
            regional_manager_rate: document.querySelector('[name="unmanaged_without_regional_rate"]').value,
            company_rate: document.querySelector('[name="unmanaged_without_company_rate"]').value,
            total_commission_rate: 5.0
        },
        {
            property_management_status: 'unmanaged',
            hierarchy_status: 'with_super_marketer',
            super_marketer_rate: document.querySelector('[name="unmanaged_with_super_rate"]').value,
            marketer_rate: document.querySelector('[name="unmanaged_with_marketer_rate"]').value,
            regional_manager_rate: document.querySelector('[name="unmanaged_with_regional_rate"]').value,
            company_rate: document.querySelector('[name="unmanaged_with_company_rate"]').value,
            total_commission_rate: 5.0
        },
        {
            property_management_status: 'managed',
            hierarchy_status: 'without_super_marketer',
            super_marketer_rate: null,
            marketer_rate: document.querySelector('[name="managed_without_marketer_rate"]').value,
            regional_manager_rate: document.querySelector('[name="managed_without_regional_rate"]').value,
            company_rate: document.querySelector('[name="managed_without_company_rate"]').value,
            total_commission_rate: 2.5
        },
        {
            property_management_status: 'managed',
            hierarchy_status: 'with_super_marketer',
            super_marketer_rate: document.querySelector('[name="managed_with_super_rate"]').value,
            marketer_rate: document.querySelector('[name="managed_with_marketer_rate"]').value,
            regional_manager_rate: document.querySelector('[name="managed_with_regional_rate"]').value,
            company_rate: document.querySelector('[name="managed_with_company_rate"]').value,
            total_commission_rate: 2.5
        }
    ];
    
    formData.append('scenarios', JSON.stringify(scenarios));
    
    fetch('/admin/commission-management/region/bulk-save', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('All commission rates saved successfully!');
        } else {
            alert('Error saving rates: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error saving rates:', error);
        alert('Failed to save commission rates');
    });
}
</script>
@endsection
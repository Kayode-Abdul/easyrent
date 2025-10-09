@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Create New Campaign</h5>
                    <a href="{{ route('marketer.campaigns.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Campaigns
                    </a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('marketer.campaigns.store') }}">
                        @csrf
                        
                        <!-- Campaign Basic Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3">Campaign Information</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Campaign Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required
                                           placeholder="e.g., Summer Landlord Drive 2024">
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="campaign_type">Campaign Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('campaign_type') is-invalid @enderror" 
                                            id="campaign_type" name="campaign_type" required>
                                        <option value="">Select Campaign Type</option>
                                        <option value="link" {{ old('campaign_type') === 'link' ? 'selected' : '' }}>
                                            Referral Link Only
                                        </option>
                                        <option value="qr_code" {{ old('campaign_type') === 'qr_code' ? 'selected' : '' }}>
                                            QR Code + Link
                                        </option>
                                    </select>
                                    <small class="form-text text-muted">
                                        QR codes are great for print materials and physical marketing
                                    </small>
                                    @error('campaign_type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Campaign Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3"
                                              placeholder="Describe your campaign strategy and target approach">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Target Audience -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Target Audience</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="target_audience">Primary Target <span class="text-danger">*</span></label>
                                    <select class="form-control @error('target_audience') is-invalid @enderror" 
                                            id="target_audience" name="target_audience" required>
                                        <option value="">Select Target Audience</option>
                                        <option value="landlords" {{ old('target_audience') === 'landlords' ? 'selected' : '' }}>
                                            Landlords
                                        </option>
                                        <option value="property_owners" {{ old('target_audience') === 'property_owners' ? 'selected' : '' }}>
                                            Property Owners
                                        </option>
                                        <option value="real_estate_investors" {{ old('target_audience') === 'real_estate_investors' ? 'selected' : '' }}>
                                            Real Estate Investors
                                        </option>
                                        <option value="property_managers" {{ old('target_audience') === 'property_managers' ? 'selected' : '' }}>
                                            Property Managers
                                        </option>
                                    </select>
                                    @error('target_audience')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="budget">Campaign Budget (KSh)</label>
                                    <input type="number" class="form-control @error('budget') is-invalid @enderror" 
                                           id="budget" name="budget" value="{{ old('budget') }}" 
                                           min="0" step="1000" placeholder="e.g., 50000">
                                    <small class="form-text text-muted">
                                        Optional: Set a budget for tracking your marketing investment
                                    </small>
                                    @error('budget')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campaign Duration -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Campaign Duration</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}">
                                    <small class="form-text text-muted">
                                        Leave blank to start immediately
                                    </small>
                                    @error('start_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" value="{{ old('end_date') }}"
                                           min="{{ date('Y-m-d') }}">
                                    <small class="form-text text-muted">
                                        Leave blank for an ongoing campaign
                                    </small>
                                    @error('end_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Campaign Preview -->
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Campaign Preview</h6>
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Your Referral Information</h6>
                                    <p><strong>Your Referral Code:</strong> <code>{{ auth()->user()->referral_code }}</code></p>
                                    <p><strong>Commission Rate:</strong> {{ auth()->user()->commission_rate }}% per successful referral</p>
                                    <p class="mb-0"><strong>Referral Link Preview:</strong> 
                                        <span id="linkPreview">{{ url('/register?ref=' . auth()->user()->referral_code . '&campaign=') }}[CAMPAIGN-CODE]</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- QR Code Information -->
                        <div class="row" id="qrInfo" style="display: none;">
                            <div class="col-md-12">
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-qrcode"></i> QR Code Features</h6>
                                    <ul class="mb-0">
                                        <li>QR code will be automatically generated after campaign creation</li>
                                        <li>High-resolution PNG format suitable for print</li>
                                        <li>Downloadable for use in flyers, business cards, and advertisements</li>
                                        <li>Tracks scans and conversions separately from link clicks</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-rocket"></i> Create Campaign
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const campaignTypeSelect = document.getElementById('campaign_type');
    const qrInfo = document.getElementById('qrInfo');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    // Show/hide QR code information
    campaignTypeSelect.addEventListener('change', function() {
        if (this.value === 'qr_code') {
            qrInfo.style.display = 'block';
        } else {
            qrInfo.style.display = 'none';
        }
    });
    
    // Set minimum end date when start date changes
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    });
    
    // Trigger initial check for QR code info
    if (campaignTypeSelect.value === 'qr_code') {
        qrInfo.style.display = 'block';
    }
});
</script>
@endsection

@extends('layout')


@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Edit Commission Rate</h4>
                    <div>
                        <a href="{{ route('admin.commission-rates.show', $commissionRate) }}" class="btn btn-info btn-sm">
                            <i class="fa fa-eye"></i> View Details
                        </a>
                        <a href="{{ route('admin.commission-rates.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
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

                    <!-- Current Rate Info -->
                    <div class="alert alert-light border">
                        <h6><i class="fa fa-info-circle"></i> Current Rate Information</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Region:</strong><br>
                                <span class="badge badge-primary">{{ $commissionRate->region }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Role:</strong><br>
                                <span class="badge badge-info">{{ $commissionRate->role->name ?? 'Unknown' }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Current Rate:</strong><br>
                                <span class="badge badge-success">{{ number_format($commissionRate->commission_percentage, 2) }}%</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Status:</strong><br>
                                @if($commissionRate->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.commission-rates.update', $commissionRate) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="region" class="form-label">Region <span class="text-danger">*</span></label>
                                    <select name="region" id="region" class="form-control @error('region') is-invalid @enderror" required>
                                        <option value="">Select Region</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region }}" {{ (old('region', $commissionRate->region) == $region) ? 'selected' : '' }}>
                                                {{ $region }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('region')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select name="role_id" id="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ (old('role_id', $commissionRate->role_id) == $role->id) ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="commission_percentage" class="form-label">Commission Percentage <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" 
                                               name="commission_percentage" 
                                               id="commission_percentage" 
                                               class="form-control @error('commission_percentage') is-invalid @enderror" 
                                               value="{{ old('commission_percentage', $commissionRate->commission_percentage) }}" 
                                               step="0.01" 
                                               min="0" 
                                               max="100" 
                                               required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        @error('commission_percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">Enter percentage value (e.g., 2.5 for 2.5%)</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active" class="form-label">Status</label>
                                    <select name="is_active" id="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                        <option value="1" {{ old('is_active', $commissionRate->is_active) == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('is_active', $commissionRate->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="effective_from" class="form-label">Effective From</label>
                                    <input type="datetime-local" 
                                           name="effective_from" 
                                           id="effective_from" 
                                           class="form-control @error('effective_from') is-invalid @enderror" 
                                           value="{{ old('effective_from', $commissionRate->effective_from ? $commissionRate->effective_from->format('Y-m-d\TH:i') : '') }}">
                                    @error('effective_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="effective_until" class="form-label">Effective Until</label>
                                    <input type="datetime-local" 
                                           name="effective_until" 
                                           id="effective_until" 
                                           class="form-control @error('effective_until') is-invalid @enderror" 
                                           value="{{ old('effective_until', $commissionRate->effective_until ? $commissionRate->effective_until->format('Y-m-d\TH:i') : '') }}">
                                    @error('effective_until')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Leave blank for ongoing rate</small>
                                </div>
                            </div>
                        </div>

                        <!-- Rate Validation Info -->
                        <div class="alert alert-warning">
                            <h6><i class="fa fa-exclamation-triangle"></i> Important Notes</h6>
                            <ul class="mb-0">
                                <li>Changes to commission rates will affect future calculations only</li>
                                <li>Historical commission calculations remain unchanged</li>
                                <li>Deactivating a rate will prevent it from being used for new calculations</li>
                                <li>Total commission rates across all roles should not exceed 2.5%</li>
                            </ul>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Update Commission Rate
                            </button>
                            <a href="{{ route('admin.commission-rates.show', $commissionRate) }}" class="btn btn-info ml-2">
                                <i class="fa fa-eye"></i> View Details
                            </a>
                            <a href="{{ route('admin.commission-rates.index') }}" class="btn btn-secondary ml-2">
                                <i class="fa fa-times"></i> Cancel
                            </a>
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
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate and validate commission percentage
    const commissionInput = document.getElementById('commission_percentage');
    
    function validateCommissionRate() {
        const percentage = parseFloat(commissionInput.value);
        if (percentage > 100) {
            commissionInput.setCustomValidity('Commission percentage cannot exceed 100%');
        } else if (percentage < 0) {
            commissionInput.setCustomValidity('Commission percentage cannot be negative');
        } else {
            commissionInput.setCustomValidity('');
        }
    }
    
    commissionInput.addEventListener('input', validateCommissionRate);
    
    // Validate effective dates
    const effectiveFrom = document.getElementById('effective_from');
    const effectiveUntil = document.getElementById('effective_until');
    
    function validateDates() {
        if (effectiveFrom.value && effectiveUntil.value) {
            const fromDate = new Date(effectiveFrom.value);
            const untilDate = new Date(effectiveUntil.value);
            
            if (untilDate <= fromDate) {
                effectiveUntil.setCustomValidity('End date must be after start date');
            } else {
                effectiveUntil.setCustomValidity('');
            }
        } else {
            effectiveUntil.setCustomValidity('');
        }
    }
    
    effectiveFrom.addEventListener('change', validateDates);
    effectiveUntil.addEventListener('change', validateDates);
});
</script>
@endsection
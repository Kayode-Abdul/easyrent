@extends('layout')


@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Create New Commission Rate</h4>
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

                    <form method="POST" action="{{ route('admin.commission-rates.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="region" class="form-label">Region <span class="text-danger">*</span></label>
                                    <select name="region" id="region" class="form-control @error('region') is-invalid @enderror" required>
                                        <option value="">Select Region</option>
                                        @foreach($regions as $region)
                                            <option value="{{ e($region) }}" {{ old('region') == $region ? 'selected' : '' }}>
                                                {{ e($region) }}
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
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
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
                                               value="{{ old('commission_percentage') }}" 
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
                                    <label for="effective_from" class="form-label">Effective From</label>
                                    <input type="datetime-local" 
                                           name="effective_from" 
                                           id="effective_from" 
                                           class="form-control @error('effective_from') is-invalid @enderror" 
                                           value="{{ old('effective_from', now()->format('Y-m-d\TH:i')) }}">
                                    @error('effective_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Leave blank to start immediately</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="effective_until" class="form-label">Effective Until</label>
                                    <input type="datetime-local" 
                                           name="effective_until" 
                                           id="effective_until" 
                                           class="form-control @error('effective_until') is-invalid @enderror" 
                                           value="{{ old('effective_until') }}">
                                    @error('effective_until')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Leave blank for ongoing rate</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" 
                                               name="replace_existing" 
                                               id="replace_existing" 
                                               class="form-check-input" 
                                               value="1" 
                                               {{ old('replace_existing') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="replace_existing">
                                            Replace existing active rate
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Check this to automatically deactivate any existing active rate for this region and role</small>
                                </div>
                            </div>
                        </div>

                        <!-- Rate Validation Info -->
                        <div class="alert alert-info">
                            <h6><i class="fa fa-info-circle"></i> Commission Rate Guidelines</h6>
                            <ul class="mb-0">
                                <li>Total commission rates across all roles should not exceed 2.5%</li>
                                <li>Regional rates override default system rates</li>
                                <li>New rates will be validated against existing rates for the region</li>
                                <li>Historical rates are preserved for audit purposes</li>
                            </ul>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Create Commission Rate
                            </button>
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
    const regionSelect = document.getElementById('region');
    const roleSelect = document.getElementById('role_id');
    
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
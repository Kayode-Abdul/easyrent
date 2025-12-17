@include('header')

<style>
.rental-types-container {
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 15px;
    background-color: #f8f9fc;
}

.rental-types-container .form-check {
    margin-bottom: 15px;
    padding: 10px;
    border: 1px solid #e3e6f0;
    border-radius: 6px;
    background-color: white;
}

.rental-types-container .form-check:last-child {
    margin-bottom: 0;
}

.rental-types-container .form-check-label {
    font-weight: 500;
    color: #5a5c69;
}

.rate-input-group {
    margin-left: 25px;
}

.input-group-text {
    background-color: #f8f9fc;
    border-color: #d1d3e2;
    color: #5a5c69;
    font-weight: 500;
}

.form-check-input:checked {
    background-color: #5a67d8;
    border-color: #5a67d8;
}

.form-check-input:focus {
    border-color: #5a67d8;
    box-shadow: 0 0 0 0.2rem rgba(90, 103, 216, 0.25);
}
</style>

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Edit Apartment</h4>
                        <a href="{{ url('/dashboard/apartment/'.$apartment->apartment_id) }}" class="btn btn-primary btn-round">
                            <i class="fa fa-arrow-left"></i> Back to Details
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="editApartmentForm">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="apartmentType">Apartment Type</label>
                                    <input type="text" class="form-control" name="apartmentType" id="apartmentType" value="{{ $apartment->apartment_type }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="tenantId">Tenant ID</label>
                                    <input type="text" class="form-control" name="tenantId" id="tenantId" value="{{ $apartment->tenant_id }}">
                                </div>
                                <div class="form-group">
                                    <label for="duration">Duration</label>
                                    <select class="form-control" name="duration" id="duration" required>
                                        <option value="1" {{ $apartment->duration == 1 ? 'selected' : '' }}>Monthly</option>
                                        <option value="3" {{ $apartment->duration == 3 ? 'selected' : '' }}>Quarterly</option>
                                        <option value="6" {{ $apartment->duration == 6 ? 'selected' : '' }}>Semi-Annual</option>
                                        <option value="12" {{ $apartment->duration == 12 ? 'selected' : '' }}>Annual</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fromRange">Start Date</label>
                                    <input type="date" class="form-control" name="fromRange" id="fromRange" value="{{ $apartment->range_start ? date('Y-m-d', strtotime($apartment->range_start)) : '' }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="toRange">End Date</label>
                                    <input type="date" class="form-control" name="toRange" id="toRange" value="{{ $apartment->range_end ? date('Y-m-d', strtotime($apartment->range_end)) : '' }}" required>
                                </div>
                                <div class="form-group">
                                    <label for="amount">Price</label>
                                    <input type="text" class="form-control" name="amount" id="amount" value="{{ $apartment->amount }}" required>
                                </div>
                                
                                <!-- Rental Duration Configuration -->
                                <div class="form-group">
                                    <label>Supported Rental Types</label>
                                    <div class="rental-types-container">
                                        @php
                                            $supportedTypes = $apartment->getSupportedRentalTypes();
                                            $allRates = $apartment->getAllRates();
                                        @endphp
                                        
                                        <div class="form-check">
                                            <input class="form-check-input rental-type-checkbox" type="checkbox" 
                                                   id="hourly_rental" name="rental_types[]" value="hourly"
                                                   {{ in_array('hourly', $supportedTypes) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="hourly_rental">
                                                Hourly Rental
                                            </label>
                                            <div class="rate-input-group mt-2" style="{{ in_array('hourly', $supportedTypes) ? '' : 'display: none;' }}">
                                                <div class="input-group">
                                                    <span class="input-group-text">₦</span>
                                                    <input type="number" class="form-control" name="hourly_rate" 
                                                           placeholder="Hourly rate" step="0.01" min="0"
                                                           value="{{ $allRates['hourly'] ?? '' }}">
                                                    <span class="input-group-text">per hour</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input rental-type-checkbox" type="checkbox" 
                                                   id="daily_rental" name="rental_types[]" value="daily"
                                                   {{ in_array('daily', $supportedTypes) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="daily_rental">
                                                Daily Rental
                                            </label>
                                            <div class="rate-input-group mt-2" style="{{ in_array('daily', $supportedTypes) ? '' : 'display: none;' }}">
                                                <div class="input-group">
                                                    <span class="input-group-text">₦</span>
                                                    <input type="number" class="form-control" name="daily_rate" 
                                                           placeholder="Daily rate" step="0.01" min="0"
                                                           value="{{ $allRates['daily'] ?? '' }}">
                                                    <span class="input-group-text">per day</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input rental-type-checkbox" type="checkbox" 
                                                   id="weekly_rental" name="rental_types[]" value="weekly"
                                                   {{ in_array('weekly', $supportedTypes) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="weekly_rental">
                                                Weekly Rental
                                            </label>
                                            <div class="rate-input-group mt-2" style="{{ in_array('weekly', $supportedTypes) ? '' : 'display: none;' }}">
                                                <div class="input-group">
                                                    <span class="input-group-text">₦</span>
                                                    <input type="number" class="form-control" name="weekly_rate" 
                                                           placeholder="Weekly rate" step="0.01" min="0"
                                                           value="{{ $allRates['weekly'] ?? '' }}">
                                                    <span class="input-group-text">per week</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input rental-type-checkbox" type="checkbox" 
                                                   id="monthly_rental" name="rental_types[]" value="monthly"
                                                   {{ in_array('monthly', $supportedTypes) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="monthly_rental">
                                                Monthly Rental
                                            </label>
                                            <div class="rate-input-group mt-2" style="{{ in_array('monthly', $supportedTypes) ? '' : 'display: none;' }}">
                                                <div class="input-group">
                                                    <span class="input-group-text">₦</span>
                                                    <input type="number" class="form-control" name="monthly_rate" 
                                                           placeholder="Monthly rate" step="0.01" min="0"
                                                           value="{{ $allRates['monthly'] ?? $apartment->amount }}">
                                                    <span class="input-group-text">per month</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input rental-type-checkbox" type="checkbox" 
                                                   id="yearly_rental" name="rental_types[]" value="yearly"
                                                   {{ in_array('yearly', $supportedTypes) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="yearly_rental">
                                                Yearly Rental
                                            </label>
                                            <div class="rate-input-group mt-2" style="{{ in_array('yearly', $supportedTypes) ? '' : 'display: none;' }}">
                                                <div class="input-group">
                                                    <span class="input-group-text">₦</span>
                                                    <input type="number" class="form-control" name="yearly_rate" 
                                                           placeholder="Yearly rate" step="0.01" min="0"
                                                           value="{{ $allRates['yearly'] ?? '' }}">
                                                    <span class="input-group-text">per year</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="default_rental_type">Default Rental Type</label>
                                    <select class="form-control" name="default_rental_type" id="default_rental_type">
                                        <option value="hourly" {{ $apartment->getDefaultRentalType() == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                        <option value="daily" {{ $apartment->getDefaultRentalType() == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ $apartment->getDefaultRentalType() == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ $apartment->getDefaultRentalType() == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="yearly" {{ $apartment->getDefaultRentalType() == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="occupied">Occupied Status</label>
                                    <select class="form-control" name="occupied" id="occupied">
                                        <option value="0" {{ !$apartment->occupied ? 'selected' : '' }}>Vacant</option>
                                        <option value="1" {{ $apartment->occupied ? 'selected' : '' }}>Occupied</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div id="updateMessage"></div>
                                <button type="submit" class="btn btn-primary btn-round">
                                    <i class="fa fa-save"></i> Update Apartment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculateEndDate(startDate, duration) {
    if (!startDate || !duration) return '';
    const date = new Date(startDate);
    date.setMonth(date.getMonth() + parseInt(duration));
    return date.toISOString().split('T')[0];
}

$(document).ready(function() {
    // Auto-update end date when start date or duration changes
    $('#fromRange').on('change', function() {
        const startDate = $(this).val();
        const duration = $('#duration').val();
        if (startDate && duration) {
            $('#toRange').val(calculateEndDate(startDate, duration));
        }
    });
    $('#duration').on('change', function() {
        const duration = $(this).val();
        const startDate = $('#fromRange').val();
        if (startDate && duration) {
            $('#toRange').val(calculateEndDate(startDate, duration));
        }
    });

    // Handle rental type checkbox changes
    $('.rental-type-checkbox').on('change', function() {
        const checkbox = $(this);
        const rateInputGroup = checkbox.closest('.form-check').find('.rate-input-group');
        const rateInput = rateInputGroup.find('input[type="number"]');
        
        if (checkbox.is(':checked')) {
            rateInputGroup.show();
            rateInput.prop('required', true);
        } else {
            rateInputGroup.hide();
            rateInput.prop('required', false);
            rateInput.val('');
        }
        
        updateDefaultRentalTypeOptions();
    });

    // Update default rental type options based on selected rental types
    function updateDefaultRentalTypeOptions() {
        const selectedTypes = [];
        $('.rental-type-checkbox:checked').each(function() {
            selectedTypes.push($(this).val());
        });
        
        const defaultSelect = $('#default_rental_type');
        const currentValue = defaultSelect.val();
        
        // Clear and repopulate options
        defaultSelect.empty();
        
        selectedTypes.forEach(function(type) {
            const label = type.charAt(0).toUpperCase() + type.slice(1);
            defaultSelect.append(`<option value="${type}">${label}</option>`);
        });
        
        // Restore previous selection if still valid
        if (selectedTypes.includes(currentValue)) {
            defaultSelect.val(currentValue);
        } else if (selectedTypes.length > 0) {
            defaultSelect.val(selectedTypes[0]);
        }
    }

    // Initialize default rental type options
    updateDefaultRentalTypeOptions();

    // Existing AJAX form submit logic
    $('#editApartmentForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = form.serialize();
        $.ajax({
            url: '/dashboard/apartment/{{ $apartment->apartment_id }}',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function(data) {
                if (data.success) {
                    $('#updateMessage').html('<div class="alert alert-success">' + data.messages + '</div>');
                    setTimeout(function() {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = '/dashboard/apartment/{{ $apartment->apartment_id }}';
                        }
                    }, 1500);
                } else {
                    $('#updateMessage').html('<div class="alert alert-danger">' + data.messages + '</div>');
                }
            },
            error: function(xhr) {
                $('#updateMessage').html('<div class="alert alert-danger">An error occurred while updating the apartment. Please try again.</div>');
            }
        });
    });
});
</script>

@include('footer')

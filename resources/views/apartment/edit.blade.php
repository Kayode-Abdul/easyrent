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

    .image-container {
        position: relative;
        transition: transform 0.2s;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e9ecef;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .image-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    .delete-image-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 10;
    }
    .delete-image-btn:hover {
        background: #dc3545;
    }
    
    /* Modern Upload Zone */
    .upload-zone {
        border: 2px dashed #3e8189;
        border-radius: 15px;
        padding: 40px 20px;
        text-align: center;
        background: #f8fbff;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }
    .upload-zone:hover, .upload-zone.dragover {
        background: #eff7f8;
        border-color: #51cbce;
    }
    .upload-zone i {
        font-size: 48px;
        color: #3e8189;
        margin-bottom: 15px;
    }
    .upload-zone h5 {
        margin-bottom: 5px;
        color: #333;
    }
    .upload-zone p {
        color: #777;
        font-size: 14px;
    }
    
    /* Preview Container */
    .preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    .preview-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 10px;
        overflow: hidden;
        border: 2px solid #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .preview-item .remove-preview {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(0,0,0,0.5);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .preview-item .remove-preview:hover {
        background: #000;
    }
    #imageGallery .card {
        border: none;
        background: none;
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
                                    <label for="tenantId">Tenant User ID</label>
                                    <input type="text" class="form-control" name="tenantId" id="tenantId" value="{{ $apartment->tenant_id }}" placeholder="Enter tenant ID or leave empty if vacant">
                                </div>
                                <div class="form-group">
                                    <label for="duration">Duration</label>
                                    <select class="form-control" name="duration" id="duration" required>
                                        @foreach($durationOptions as $durationValue => $durationName)
                                            <option value="{{ $durationValue }}" {{ $apartment->duration == $durationValue ? 'selected' : '' }}>
                                                {{ $durationName }}
                                            </option>
                                        @endforeach
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
                                                    <span class="input-group-text">{{ $apartment->property->currency->symbol ?? format_money(0)->getSymbol() }}</span>
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
                                                    <span class="input-group-text">{{ $apartment->property->currency->symbol ?? format_money(0)->getSymbol() }}</span>
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
                                                    <span class="input-group-text">{{ $apartment->property->currency->symbol ?? format_money(0)->getSymbol() }}</span>
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
                                                    <span class="input-group-text">{{ $apartment->property->currency->symbol ?? format_money(0)->getSymbol() }}</span>
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
                                                    <span class="input-group-text">{{ $apartment->property->currency->symbol ?? format_money(0)->getSymbol() }}</span>
                                                    <input type="number" class="form-control" name="yearly_rate" 
                                                           placeholder="Yearly rate" step="0.01" min="0"
                                                           value="{{ $allRates['yearly'] ?? '' }}">
                                                    <span class="input-group-text">per year</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Additional Duration Types -->
                                        <div class="form-check">
                                            <input class="form-check-input rental-type-checkbox" type="checkbox" 
                                                   id="quarterly_rental" name="rental_types[]" value="quarterly"
                                                   {{ in_array('quarterly', $supportedTypes) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="quarterly_rental">
                                                Quarterly Rental (3 months)
                                            </label>
                                            <div class="rate-input-group mt-2" style="{{ in_array('quarterly', $supportedTypes) ? '' : 'display: none;' }}">
                                                <div class="input-group">
                                                    <span class="input-group-text">{{ $apartment->property->currency->symbol ?? format_money(0)->getSymbol() }}</span>
                                                    <input type="number" class="form-control" name="quarterly_rate" 
                                                           placeholder="Quarterly rate (auto-calculated from monthly)" step="0.01" min="0" readonly
                                                           value="{{ isset($allRates['quarterly']) ? $allRates['quarterly'] : (isset($allRates['monthly']) ? $allRates['monthly'] * 3 : '') }}">
                                                    <span class="input-group-text">per quarter</span>
                                                </div>
                                                <small class="text-muted">Auto-calculated as 3 × monthly rate</small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input rental-type-checkbox" type="checkbox" 
                                                   id="semi_annually_rental" name="rental_types[]" value="semi_annually"
                                                   {{ in_array('semi_annually', $supportedTypes) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="semi_annually_rental">
                                                Semi-Annual Rental (6 months)
                                            </label>
                                            <div class="rate-input-group mt-2" style="{{ in_array('semi_annually', $supportedTypes) ? '' : 'display: none;' }}">
                                                <div class="input-group">
                                                    <span class="input-group-text">{{ $apartment->property->currency->symbol ?? format_money(0)->getSymbol() }}</span>
                                                    <input type="number" class="form-control" name="semi_annually_rate" 
                                                           placeholder="Semi-annual rate (auto-calculated from monthly)" step="0.01" min="0" readonly
                                                           value="{{ isset($allRates['semi_annually']) ? $allRates['semi_annually'] : (isset($allRates['monthly']) ? $allRates['monthly'] * 6 : '') }}">
                                                    <span class="input-group-text">per 6 months</span>
                                                </div>
                                                <small class="text-muted">Auto-calculated as 6 × monthly rate</small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input rental-type-checkbox" type="checkbox" 
                                                   id="bi_annually_rental" name="rental_types[]" value="bi_annually"
                                                   {{ in_array('bi_annually', $supportedTypes) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="bi_annually_rental">
                                                Bi-Annual Rental (24 months)
                                            </label>
                                            <div class="rate-input-group mt-2" style="{{ in_array('bi_annually', $supportedTypes) ? '' : 'display: none;' }}">
                                                <div class="input-group">
                                                    <span class="input-group-text">{{ $apartment->property->currency->symbol ?? format_money(0)->getSymbol() }}</span>
                                                    <input type="number" class="form-control" name="bi_annually_rate" 
                                                           placeholder="Bi-annual rate (auto-calculated from monthly)" step="0.01" min="0" readonly
                                                           value="{{ isset($allRates['bi_annually']) ? $allRates['bi_annually'] : (isset($allRates['monthly']) ? $allRates['monthly'] * 24 : '') }}">
                                                    <span class="input-group-text">per 24 months</span>
                                                </div>
                                                <small class="text-muted">Auto-calculated as 24 × monthly rate</small>
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
                                        <option value="quarterly" {{ $apartment->getDefaultRentalType() == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                        <option value="semi_annually" {{ $apartment->getDefaultRentalType() == 'semi_annually' ? 'selected' : '' }}>Semi-Annually</option>
                                        <option value="yearly" {{ $apartment->getDefaultRentalType() == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                        <option value="bi_annually" {{ $apartment->getDefaultRentalType() == 'bi_annually' ? 'selected' : '' }}>Bi-Annually</option>
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

                        <!-- Apartment Images Section -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fa fa-images mr-2"></i>Existing Photos</h5>
                                <div class="row" id="imageGallery">
                                    @if($apartment->images->count() > 0)
                                        @foreach($apartment->images as $image)
                                        <div class="col-6 col-md-3 col-lg-2 mb-4" id="image-container-{{ $image->id }}">
                                            <div class="image-container h-100">
                                                <img src="{{ asset('storage/' . $image->file_path) }}" class="w-100" style="height: 140px; object-fit: cover;">
                                                <button type="button" class="delete-image-btn" onclick="deleteImage({{ $image->id }})" title="Delete Image">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                                @if($image->is_main)
                                                    <div class="badge badge-primary position-absolute" style="bottom: 5px; left: 5px; opacity: 0.9;">Main</div>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="col-12">
                                            <div class="alert alert-light border text-center py-4">
                                                <p class="text-muted mb-0"><i class="fa fa-info-circle mr-2"></i>No specific images uploaded for this apartment yet. (Inheriting from property)</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fa fa-plus-circle mr-2"></i>Add New Photos</h5>
                                
                                <div class="upload-zone" id="dropzone" onclick="document.getElementById('images').click()">
                                    <i class="fa fa-cloud-upload-alt"></i>
                                    <h5>Click or Drag & Drop Images Here</h5>
                                    <p class="mb-0">You can select multiple photos at once (Max 2MB per image)</p>
                                    <input type="file" name="images[]" id="images" class="d-none" multiple accept="image/*" onchange="handleFileSelect(this)">
                                </div>

                                <div id="previewContainer" class="d-none">
                                    <h6 class="text-muted mb-2">New Images to Upload:</h6>
                                    <div class="preview-grid" id="imagePreviews">
                                        <!-- Previews will appear here -->
                                    </div>
                                    <div class="text-right mt-2">
                                        <button type="button" class="btn btn-sm btn-link text-danger" onclick="clearSelectedImages()">
                                            <i class="fa fa-trash-alt mr-1"></i> Clear all selected
                                        </button>
                                    </div>
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
    const durationMonths = parseFloat(duration);

    if (Number.isNaN(durationMonths)) {
        return '';
    }

    // Handle sub-month durations (daily/weekly/hourly) using days
    if (durationMonths > 0 && durationMonths < 1) {
        // Daily/hourly stored as ~0.03-0.04 month
        if (durationMonths <= 0.04) {
            date.setDate(date.getDate() + 1);
        }
        // Weekly stored as 0.25 month
        else if (durationMonths <= 0.25) {
            date.setDate(date.getDate() + 7);
        }
        // Fallback: convert month fraction to days (30-day month assumption)
        else {
            date.setDate(date.getDate() + Math.round(durationMonths * 30));
        }

        return date.toISOString().split('T')[0];
    }

    // Month-based durations
    date.setMonth(date.getMonth() + Math.round(durationMonths));
    return date.toISOString().split('T')[0];
}

$(document).ready(function() {
    // Setup Drag and Drop
    const dropzone = document.getElementById('dropzone');
    if (dropzone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'), false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            const input = document.getElementById('images');
            input.files = files;
            handleFileSelect(input);
        }, false);
    }
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
            if (!rateInput.prop('readonly')) {
                rateInput.prop('required', true);
            }
        } else {
            rateInputGroup.hide();
            rateInput.prop('required', false);
            if (!rateInput.prop('readonly')) {
                rateInput.val('');
            }
        }
        
        updateDefaultRentalTypeOptions();
        updateCalculatedRates();
    });

    // Update calculated rates when monthly rate changes
    $('input[name="monthly_rate"]').on('input', function() {
        updateCalculatedRates();
    });

    // Function to update calculated rates
    function updateCalculatedRates() {
        const monthlyRate = parseFloat($('input[name="monthly_rate"]').val()) || 0;
        
        if (monthlyRate > 0) {
            // Update quarterly rate (3 months)
            $('input[name="quarterly_rate"]').val((monthlyRate * 3).toFixed(2));
            
            // Update semi-annual rate (6 months)
            $('input[name="semi_annually_rate"]').val((monthlyRate * 6).toFixed(2));
            
            // Update bi-annual rate (24 months)
            $('input[name="bi_annually_rate"]').val((monthlyRate * 24).toFixed(2));
        } else {
            // Clear calculated rates if no monthly rate
            $('input[name="quarterly_rate"]').val('');
            $('input[name="semi_annually_rate"]').val('');
            $('input[name="bi_annually_rate"]').val('');
        }
    }

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
        
        const typeLabels = {
            'hourly': 'Hourly',
            'daily': 'Daily',
            'weekly': 'Weekly',
            'monthly': 'Monthly',
            'quarterly': 'Quarterly',
            'semi_annually': 'Semi-Annually',
            'yearly': 'Yearly',
            'bi_annually': 'Bi-Annually'
        };
        
        selectedTypes.forEach(function(type) {
            const label = typeLabels[type] || type.charAt(0).toUpperCase() + type.slice(1);
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
    
    // Initialize calculated rates
    updateCalculatedRates();

    // Handle apartment update
    $('#editApartmentForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);
        
        // Show loading state
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '/dashboard/apartment/{{ $apartment->apartment_id }}',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
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
                    submitBtn.prop('disabled', false).html(originalBtnHtml);
                    $('#updateMessage').html('<div class="alert alert-danger">' + data.messages + '</div>');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                let errorMsg = 'An error occurred while updating the apartment. Please try again.';
                if (xhr.status === 419) {
                    errorMsg = 'Session expired. The page will reload to refresh your session.';
                    setTimeout(() => window.location.reload(), 2000);
                }
                $('#updateMessage').html('<div class="alert alert-danger">' + errorMsg + '</div>');
            }
        });
    });
});

let selectedFiles = [];

function handleFileSelect(input) {
    const previewContainer = document.getElementById('previewContainer');
    const imagePreviews = document.getElementById('imagePreviews');
    
    if (!input.files || input.files.length === 0) {
        previewContainer.classList.add('d-none');
        return;
    }

    previewContainer.classList.remove('d-none');
    imagePreviews.innerHTML = '';
    
    selectedFiles = Array.from(input.files);
    
    selectedFiles.forEach((file, index) => {
        if (!file.type.startsWith('image/')) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${e.target.result}">
                <button type="button" class="remove-preview" onclick="removeSelectedFile(${index})">
                    <i class="fa fa-times"></i>
                </button>
            `;
            imagePreviews.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeSelectedFile(index) {
    selectedFiles.splice(index, 1);
    
    // Re-sync input files
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    document.getElementById('images').files = dt.files;
    
    // Refresh previews
    handleFileSelect(document.getElementById('images'));
}

function clearSelectedImages() {
    document.getElementById('images').value = '';
    selectedFiles = [];
    handleFileSelect(document.getElementById('images'));
}

function deleteImage(imageId) {
    if (!confirm('Are you sure you want to delete this image?')) return;

    $.ajax({
        url: '/dashboard/property/image/' + imageId,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (data) {
            if (data.success) {
                $('#image-container-' + imageId).fadeOut(300, function() {
                    $(this).remove();
                    if ($('#imageGallery .image-container').length === 0) {
                        $('#imageGallery').html('<div class="col-12"><p class="text-muted italic">No specific images uploaded for this apartment yet.</p></div>');
                    }
                });
            } else {
                alert(data.messages || 'Failed to delete image');
            }
        },
        error: function () {
            alert('An error occurred while deleting the image.');
        }
    });
}
</script>

@include('footer')

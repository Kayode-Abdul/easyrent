<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
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
        width: 32px;
        height: 32px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 10;
        transition: all 0.2s;
    }
    .delete-image-btn:hover {
        background: #dc3545;
        transform: scale(1.1);
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
@include('header')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Edit Property</h4>
                        <a href="{{ url('/dashboard/property/'.$property->property_id) }}"
                            class="btn btn-primary btn-round">
                            <i class="fa fa-arrow-left"></i> Back to Details
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="editPropertyForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="property-type">Property Type</label>
                                    <select name="propertyType" id="property-type" class="form-control" required>
                                        <option value="" disabled {{ !$property->prop_type ? 'selected' : '' }}>--
                                            Select Property Type --</option>
                                        <optgroup label="Residential">
                                            <option value="1" {{ $property->prop_type == 1 ? 'selected' : '' }}>Mansion
                                            </option>
                                            <option value="2" {{ $property->prop_type == 2 ? 'selected' : '' }}>Duplex
                                            </option>
                                            <option value="3" {{ $property->prop_type == 3 ? 'selected' : '' }}>Flat
                                            </option>
                                            <option value="4" {{ $property->prop_type == 4 ? 'selected' : '' }}>Terrace
                                            </option>
                                        </optgroup>
                                        <optgroup label="Commercial">
                                            <option value="5" {{ $property->prop_type == 5 ? 'selected' : ''
                                                }}>Warehouse</option>
                                            <option value="8" {{ $property->prop_type == 8 ? 'selected' : '' }}>Store
                                            </option>
                                            <option value="9" {{ $property->prop_type == 9 ? 'selected' : '' }}>Shop
                                            </option>
                                        </optgroup>
                                        <optgroup label="Land/Agricultural">
                                            <option value="6" {{ $property->prop_type == 6 ? 'selected' : '' }}>Land
                                            </option>
                                            <option value="7" {{ $property->prop_type == 7 ? 'selected' : '' }}>Farm
                                            </option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="propertyAdd">Property Address</label>
                                    <textarea class="form-control" name="address" id="propertyAdd" rows="3"
                                        required>{{ $property->address }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <select name="country" id="country" class="form-control"
                                        onchange="getStatesForCountry()" required>
                                        <option value="" disabled>Select Country</option>
                                        @foreach ($countries as $c)
                                        <option value="{{ $c['name'] }}" {{ ($property->country ?? 'Nigeria') ==
                                            $c['name'] ? 'selected' : '' }}>
                                            {{ $c['name'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="currency">Currency *</label>
                                    <select name="currency_id" id="currency" class="form-control" required>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}" data-code="{{ $currency->code }}" {{ $property->currency_id == $currency->id ? 'selected' : '' }}>
                                            {{ $currency->name }} ({{ $currency->symbol }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="states">State</label>
                                    <select name="state" id="states" class="form-control" onchange="getCities()"
                                        required>
                                        <option value="" disabled>Select State</option>
                                        @foreach ($locations as $location)
                                        <option value="{{ $location['name'] }}" {{ $property->state == $location['name']
                                            ? 'selected' : '' }}>
                                            {{ $location['name'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cities" id="cityLabel">{{ ($property->country ?? 'Nigeria') ===
                                        'Nigeria' ? 'L.G.A' : 'City' }}</label>
                                    <select name="city" id="cities" class="form-control" required>
                                        <option value="" disabled>Select {{ ($property->country ?? 'Nigeria') ===
                                            'Nigeria' ? 'L.G.A' : 'City' }}</option>
                                        <!-- Cities will be populated by JavaScript -->
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="noOfApartment">Number of Apartments</label>
                                    <input type="number" class="form-control" name="noOfApartment" id="noOfApartment"
                                        min="1" value="{{ $property->no_of_apartment }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Image Management Section -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="mb-3"><i class="fa fa-images mr-2"></i>Existing Photos</h5>
                                <div class="row" id="imageGallery">
                                    @if($property->images->count() > 0)
                                        @foreach($property->images as $image)
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
                                                <p class="text-muted mb-0"><i class="fa fa-info-circle mr-2"></i>No images uploaded for this property yet.</p>
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
                                    <i class="fa fa-save"></i> Update Property
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php @include(app_path().'/footer.php'); ?>

<script>
    // Cached location data for current country
    let cachedLocationData = null;
    const savedState = "{{ $property->state }}";
    const savedCity = "{{ $property->lga }}";
    const savedCountry = "{{ $property->country ?? 'Nigeria' }}";

    // Fetch states for a given country from the API
    function getStatesForCountry(preserveSelection) {
        const country = document.getElementById('country').value;
        const stateSelect = document.getElementById('states');
        const citySelect = document.getElementById('cities');
        const cityLabel = document.getElementById('cityLabel');

        // Update label
        if (cityLabel) {
            cityLabel.textContent = (country === 'Nigeria') ? 'L.G.A' : 'City';
        }

        stateSelect.innerHTML = '<option value="" disabled selected>Select State</option>';
        citySelect.innerHTML = '<option value="" disabled selected>Select ' + (country === 'Nigeria' ? 'L.G.A' : 'City') + '</option>';

        if (!country) return;

        fetch('/api/location-data?country=' + encodeURIComponent(country))
            .then(r => r.json())
            .then(data => {
                cachedLocationData = data.states || [];
                cachedLocationData.forEach(function (state) {
                    const opt = document.createElement('option');
                    opt.value = state.name;
                    opt.textContent = state.name;
                    if (preserveSelection && state.name === savedState) {
                        opt.selected = true;
                    }
                    stateSelect.appendChild(opt);
                });
                // If preserving, also populate cities
                if (preserveSelection && savedState) {
                    getCities(true);
                }
                
                // Auto-select currency based on country unless we are preserving the initial load
                if (!preserveSelection && data.currency_code) {
                    const currencySelect = document.getElementById('currency');
                    for (let i = 0; i < currencySelect.options.length; i++) {
                        if (currencySelect.options[i].getAttribute('data-code') === data.currency_code) {
                            currencySelect.selectedIndex = i;
                            break;
                        }
                    }
                }
            });
    }

    function getCities(preserveSelection) {
        const stateSelect = document.getElementById("states");
        const citySelect = document.getElementById("cities");
        const country = document.getElementById("country").value;
        const selectedState = stateSelect.value;

        citySelect.innerHTML = '<option value="" disabled>Select ' + (country === 'Nigeria' ? 'L.G.A' : 'City') + '</option>';

        if (!selectedState || !cachedLocationData) return;

        const found = cachedLocationData.find(s => s.name === selectedState);
        if (found && found.lgas) {
            found.lgas.forEach(function (cityObj) {
                const cityName = cityObj.name;
                const option = document.createElement("option");
                option.value = cityName;
                option.textContent = cityName;
                if (preserveSelection && cityName === savedCity) {
                    option.selected = true;
                }
                citySelect.appendChild(option);
            });
        }
    }

    // Initialize: load states for the property's country and pre-select saved values
    document.addEventListener('DOMContentLoaded', function () {
        getStatesForCountry(true);
        
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
                input.files = files; // Note: This only works for single sets. For multi-add, we'd need a DataTransfer object.
                handleFileSelect(input);
            }, false);
        }
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

    // Handle form submission
    $('#editPropertyForm').off('submit').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);
        
        // Show loading state
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '/dashboard/property/{{ $property->property_id }}',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function (data) {
                if (data.success) {
                    $('#updateMessage').html('<div class="alert alert-success">' + data.messages + '</div>');
                    setTimeout(function () {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = '/dashboard/myproperty';
                        }
                    }, 1500);
                } else {
                    submitBtn.prop('disabled', false).html(originalBtnHtml);
                    $('#updateMessage').html('<div class="alert alert-danger">' + data.messages + '</div>');
                }
            },
            error: function (xhr) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                let errorMsg = 'An error occurred while updating the property. Please try again.';
                if (xhr.status === 419) {
                    errorMsg = 'Session expired. The page will reload to refresh your session.';
                    setTimeout(() => window.location.reload(), 2000);
                }
                $('#updateMessage').html('<div class="alert alert-danger">' + errorMsg + '</div>');
            }
        });
    });

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
                            $('#imageGallery').html('<div class="col-12"><p class="text-muted italic">No images uploaded for this property yet.</p></div>');
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
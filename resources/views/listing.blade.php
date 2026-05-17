@extends('layout')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 ml-auto mr-auto">
                <div class="card card-wizard" id="wizardCard">
                    <div class="card-header text-center">
                        <h3 class="card-title">List Your Property</h3>
                        <p class="description">Fill in the details to list your property on EasyRent.</p>
                    </div>
                    <div class="card-body">
                        <div id="propertyMessage"></div>
                        <form id="propertyForm" method="POST" action="/listing" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Property Type <span class="text-danger">*</span></label>
                                        <select class="form-control" name="propertyType" id="propertyTypeListing" required>
                                            <option value="" disabled selected>-- Select Type --</option>
                                            <option value="1">Self Contain</option>
                                            <option value="2">Mini Flat</option>
                                            <option value="3">Apartment/Flat</option>
                                            <option value="4">House</option>
                                            <option value="5">Warehouse</option>
                                            <option value="6">Land</option>
                                            <option value="7">Farm</option>
                                            <option value="8">Store</option>
                                            <option value="9">Shop</option>
                                            <option value="10">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Currency</label>
                                        <select class="form-control" name="currency_id" required>
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}" {{ $currency->code == 'NGN' ? 'selected' : '' }}>
                                                    {{ $currency->name }} ({{ $currency->symbol }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>State <span class="text-danger">*</span></label>
                                        <select class="form-control" name="state_id" id="stateSelectListing" required>
                                            <option value="" disabled selected>-- Select State --</option>
                                            @foreach($states as $state)
                                                <option value="{{ $state->id }}" data-state-name="{{ $state->state_name }}">{{ $state->state_name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="state" id="stateNameListing">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>LGA <span class="text-danger">*</span></label>
                                        <select class="form-control" name="lga_id" id="lgaSelectListing" required disabled>
                                            <option value="" disabled selected>-- Select LGA --</option>
                                        </select>
                                        <input type="hidden" name="city" id="lgaNameListing">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Address <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="3" placeholder="Enter full property address" required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Property Images</label>
                                <div id="propertyDropzone" class="dropzone">
                                    <div class="dz-message">
                                        <div class="icon"><i class="fa fa-cloud-upload-alt"></i></div>
                                        <h5>Drag & Drop Images Here</h5>
                                        <p class="text-muted">or click to select files from your computer</p>
                                        <small class="text-info d-block mt-2"><i class="fa fa-info-circle"></i> Max size: 2MB per image</small>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-primary btn-fill btn-wd" id="savePropertyListing">List Property</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const statesData = @json($states);
    
    $(document).ready(function() {
        // Initialize Dropzone
        Dropzone.autoDiscover = false;
        const myDropzone = new Dropzone("#propertyDropzone", {
            url: "/listing",
            autoProcessQueue: false,
            uploadMultiple: true,
            parallelUploads: 10,
            maxFiles: 10,
            acceptedFiles: "image/*",
            addRemoveLinks: true
        });

        // State & LGA Logic
        $('#stateSelectListing').on('change', function() {
            const stateId = $(this).val();
            const stateName = $(this).find(':selected').data('state-name');
            $('#stateNameListing').val(stateName);
            
            const lgaSelect = $('#lgaSelectListing');
            lgaSelect.empty().append('<option value="" disabled selected>-- Select LGA --</option>');
            
            const state = statesData.find(s => s.id == stateId);
            if (state && state.lgas) {
                state.lgas.forEach(lga => {
                    lgaSelect.append(`<option value="${lga.id}" data-lga-name="${lga.lga_name}">${lga.lga_name}</option>`);
                });
                lgaSelect.prop('disabled', false);
            } else {
                lgaSelect.prop('disabled', true);
            }
        });

        $('#lgaSelectListing').on('change', function() {
            $('#lgaNameListing').val($(this).find(':selected').data('lga-name'));
        });

        // Submission
        $('#savePropertyListing').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            
            const form = document.getElementById('propertyForm');
            const formData = new FormData(form);
            
            myDropzone.getAcceptedFiles().forEach((file, index) => {
                formData.append('images[' + index + ']', file);
            });

            fetch('/listing', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#propertyMessage').html('<div class="alert alert-success">Property listed successfully! Redirecting...</div>');
                    setTimeout(() => window.location.href = '/dashboard/property', 2000);
                } else {
                    btn.prop('disabled', false).text('List Property');
                    $('#propertyMessage').html('<div class="alert alert-danger">' + (data.message || 'Error occurred') + '</div>');
                }
            })
            .catch(err => {
                btn.prop('disabled', false).text('List Property');
                $('#propertyMessage').html('<div class="alert alert-danger">Network error. Please try again.</div>');
            });
        });
    });
</script>
@endpush

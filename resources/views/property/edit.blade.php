<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@include('header')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Edit Property</h4>
                        <a href="{{ url('/dashboard/property/'.$property->prop_id) }}" class="btn btn-primary btn-round">
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
                                        <option value="" disabled {{ !$property->prop_type ? 'selected' : '' }}>Select Property Type</option>
                                        <option value="1" {{ $property->prop_type == 1 ? 'selected' : '' }}>Mansion</option>
                                        <option value="2" {{ $property->prop_type == 2 ? 'selected' : '' }}>Duplex</option>
                                        <option value="3" {{ $property->prop_type == 3 ? 'selected' : '' }}>Flat</option>
                                        <option value="4" {{ $property->prop_type == 4 ? 'selected' : '' }}>Terrace</option>
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
                                    <label for="states">State</label>
                                    <select name="state" id="states" class="form-control" onchange="getCities()" required>
                                        <option value="" disabled>Select State</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location['name'] }}" 
                                                {{ $property->state == $location['name'] ? 'selected' : '' }}>
                                                {{ $location['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cities">L.G.A</label>
                                    <select name="city" id="cities" class="form-control" required>
                                        <option value="" disabled>Select L.G.A</option>
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
// Convert array of state objects to object with state names as keys
const locationsArray = <?= json_encode($locations) ?>;
const locationsData = {};
locationsArray.forEach(state => {
    locationsData[state.name] = state.cities;
});

function getCities() {
    const stateSelect = document.getElementById("states");
    const citySelect = document.getElementById("cities");
    const selectedState = stateSelect.value;
    const currentCity = "{{ $property->lga }}";

    // Clear existing options
    citySelect.innerHTML = '<option value="" disabled>Select L.G.A</option>';

    // Get cities for the selected state
    const cities = locationsData[selectedState] || [];
    cities.forEach(city => {
        const option = document.createElement("option");
            option.value = city;
            option.textContent = city;
            if (city === currentCity) {
                option.selected = true;
            }
            citySelect.appendChild(option);
        });
    }
// }

// Initialize cities dropdown
document.addEventListener('DOMContentLoaded', function() {
    getCities();
});

// Handle form submission
$('#editPropertyForm').off('submit').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const formData = form.serialize();
    $.ajax({
        url: '/dashboard/property/{{ $property->prop_id }}',
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
                        window.location.href = '/dashboard/myproperty';
                    }
                }, 1500);
            } else {
                $('#updateMessage').html('<div class="alert alert-danger">' + data.messages + '</div>');
            }
        },
        error: function(xhr) {
            $('#updateMessage').html('<div class="alert alert-danger">An error occurred while updating the property. Please try again.</div>');
        }
    });
});
</script>
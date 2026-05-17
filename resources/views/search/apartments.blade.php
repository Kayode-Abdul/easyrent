@include('header')
<link rel="stylesheet" href="/assets/css/search-hero.css">
<style>
    .search-results-section {
        padding: 60px 0;
        background: #f8f9fa;
    }
    .apartment-card {
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        margin-bottom: 30px;
        border: 1px solid #eee;
    }
    .apartment-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    .apartment-img {
        height: 220px;
        background-size: cover;
        background-position: center;
        position: relative;
    }
    .apartment-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: #3e8189;
        color: #fff;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .apartment-price {
        position: absolute;
        bottom: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.9);
        padding: 5px 15px;
        border-radius: 8px;
        font-weight: 800;
        color: #3e8189;
        font-size: 18px;
    }
    .apartment-content {
        padding: 20px;
    }
    .apartment-title {
        font-size: 18px;
        font-weight: 700;
        color: #333;
        margin-bottom: 10px;
        display: block;
    }
    .apartment-location {
        color: #777;
        font-size: 14px;
        margin-bottom: 15px;
    }
    .apartment-features {
        display: flex;
        gap: 15px;
        border-top: 1px solid #eee;
        padding-top: 15px;
        font-size: 13px;
        color: #555;
    }
    .apartment-features i {
        color: #3e8189;
        margin-right: 5px;
    }
    .no-results {
        text-align: center;
        padding: 100px 20px;
    }
    .no-results i {
        font-size: 60px;
        color: #ccc;
        margin-bottom: 20px;
    }
</style>

<section class="search-results-section">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-md-12">
                <div class="search-form-wrap p-4 bg-white shadow rounded" style="margin-top: 0;">
                    <form action="{{ route('search.apartments') }}" method="GET">
                        <div class="row w-100 m-0" id="advanced-location-fields" style="{{ request()->has('country') || request()->has('state') ? '' : 'display: none;' }}">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <select name="country" id="country" class="form-control" onchange="loadStates(this.value)">
                                        <option value="">Any Country</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state">State</label>
                                    <select name="state" id="state" class="form-control" onchange="loadCities(this.value)">
                                        <option value="">Any State</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->name }}" {{ request('state') == $state->name ? 'selected' : '' }}>{{ $state->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lga">City/LGA</label>
                                    <select name="lga" id="lga" class="form-control">
                                        <option value="">Any City</option>
                                        @if(request('lga'))
                                            <option value="{{ request('lga') }}" selected>{{ request('lga') }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="apartment_type">Type</label>
                                    <select name="apartment_type" class="form-control">
                                        <option value="">Any Type</option>
                                        @foreach($apartmentTypes as $type)
                                            <option value="{{ $type->name }}" {{ request('apartment_type') == $type->name ? 'selected' : '' }}>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_price">Max Price ({{ $currencySymbol }})</label>
                                    <input type="number" name="max_price" class="form-control" value="{{ request('max_price') }}" placeholder="Max Price">
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-block mb-3"><i class="bi bi-search"></i> Search</button>
                            </div>
                        </div>
                        <div class="row w-100 m-0 mt-2">
                            <div class="col-12 text-right px-0">
                                <a href="javascript:void(0)" id="toggle-advanced-search" style="color: var(--primary-color, #17a2b8); font-size: 14px; font-weight: 600; text-decoration: underline;">
                                    <i class="bi bi-sliders"></i> {{ request()->has('country') || request()->has('state') ? 'Simple Search' : 'Advanced Search' }}
                                </a>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-4">
                <h3>Showing Results for: 
                    <span class="text-primary">
                        {{ request('q') ? '"'.request('q').'"' : 'All' }} 
                        {{ request('apartment_type') ? ' ('.request('apartment_type').')' : '' }}
                        {{ request('max_price') ? ' under '.$currencySymbol.number_format(request('max_price')) : '' }}
                    </span>
                </h3>
                <p class="text-muted">{{ $apartments->total() }} apartments found</p>
            </div>
        </div>

        <div class="row">
            @forelse($apartments as $apartment)
            <div class="col-md-4">
                <div class="apartment-card">
                    <a href="{{ route('apartment.show.public', $apartment->apartment_id) }}">
                        <div class="apartment-img" style="background-image: url('{{ $apartment->getFirstAvailableImage() }}');">
                            <div class="apartment-badge">{{ $apartment->apartment_type }}</div>
                            <div class="apartment-price">{{ $apartment->getFormattedRentAttribute() }}</div>
                        </div>
                    </a>
                    <div class="apartment-content">
                        <a href="{{ route('apartment.show.public', $apartment->apartment_id) }}" class="apartment-title">{{ $apartment->property->address }}</a>
                        <div class="apartment-location">
                            <i class="bi bi-geo-alt"></i> {{ $apartment->property->lga }}, {{ $apartment->property->state }}, {{ $apartment->property->country_name ?? $apartment->property->country }}
                        </div>
                        <div class="apartment-features">
                            <span><i class="bi bi-door-open"></i> {{ $apartment->apartment_type }}</span>
                            <span><i class="bi bi-check-circle"></i> {{ $apartment->occupied ? 'Occupied' : 'Available' }}</span>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('apartment.show.public', $apartment->apartment_id) }}" class="btn btn-outline-primary btn-block btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-md-12">
                <div class="no-results shadow rounded bg-white">
                    <i class="bi bi-search"></i>
                    <h4>No Apartments Found</h4>
                    <p>We couldn't find any apartments matching your criteria. Try adjusting your filters.</p>
                    <a href="{{ route('search.apartments') }}" class="btn btn-primary mt-3">Reset Filters</a>
                </div>
            </div>
            @endforelse
        </div>

        <div class="row mt-5">
            <div class="col-md-12 d-flex justify-content-center">
                {{ $apartments->links() }}
            </div>
        </div>
    </div>
</section>

@include('footer')

<script>
function loadStates(country) {
    const stateSelect = document.getElementById('state');
    const citySelect = document.getElementById('lga');
    
    stateSelect.innerHTML = '<option value="">Loading...</option>';
    citySelect.innerHTML = '<option value="">Any City</option>';
    
    if (!country) {
        stateSelect.innerHTML = '<option value="">Any State</option>';
        return Promise.resolve();
    }
    
    return fetch(`/api/location-data?country=${encodeURIComponent(country)}`)
        .then(response => response.json())
        .then(data => {
            stateSelect.innerHTML = '<option value="">Any State</option>';
            if (data.states && data.states.length > 0) {
                data.states.forEach(state => {
                    const option = document.createElement('option');
                    option.value = state.name;
                    option.textContent = state.name;
                    option.setAttribute('data-lgas', JSON.stringify(state.lgas));
                    stateSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading states:', error);
            stateSelect.innerHTML = '<option value="">Any State</option>';
        });
}

function loadCities(stateName) {
    const stateSelect = document.getElementById('state');
    const citySelect = document.getElementById('lga');
    
    citySelect.innerHTML = '<option value="">Any City</option>';
    
    if (!stateName) return Promise.resolve();
    
    const selectedOption = stateSelect.options[stateSelect.selectedIndex];
    const lgas = JSON.parse(selectedOption.getAttribute('data-lgas') || '[]');
    
    if (lgas.length > 0) {
        lgas.forEach(lga => {
            const option = document.createElement('option');
            option.value = lga.name;
            option.textContent = lga.name;
            citySelect.appendChild(option);
        });
    }
    
    return Promise.resolve();
}

document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggle-advanced-search');
    const advancedFields = document.getElementById('advanced-location-fields');
    
    if (toggleBtn && advancedFields) {
        toggleBtn.addEventListener('click', function() {
            if (advancedFields.style.display === 'none') {
                advancedFields.style.display = 'flex';
                toggleBtn.innerHTML = '<i class="bi bi-chevron-up"></i> Simple Search';
            } else {
                advancedFields.style.display = 'none';
                toggleBtn.innerHTML = '<i class="bi bi-sliders"></i> Advanced Search';
            }
        });
    }

    const stateSelect = document.getElementById('state');
    const citySelect = document.getElementById('lga');
    const countrySelect = document.getElementById('country');
    
    const hasSearchQuery = {{ request()->has('country') || request()->has('state') ? 'true' : 'false' }};

    // Helper to safely select an option
    function selectOption(selectElement, targetValue) {
        if (!targetValue || !selectElement) return false;
        const target = targetValue.toLowerCase();
        for (let i = 0; i < selectElement.options.length; i++) {
            const optVal = selectElement.options[i].value.toLowerCase();
            if (optVal.includes(target) || target.includes(optVal)) {
                selectElement.selectedIndex = i;
                return true;
            }
        }
        return false;
    }

    if (stateSelect && hasSearchQuery) {
        // Form is already filled by user's previous search, just setup the dependent dropdowns
        const selectedState = "{{ request('state') }}";
        const selectedLga = "{{ request('lga') }}";
        
        @foreach($states as $state)
            const option{{ $loop->index }} = Array.from(stateSelect.options).find(opt => opt.value === "{{ $state->name }}");
            if (option{{ $loop->index }}) {
                option{{ $loop->index }}.setAttribute('data-lgas', '{!! json_encode($state->lgas) !!}');
            }
        @endforeach

        if (selectedState) {
            loadCities(selectedState).then(() => {
                if (citySelect && selectedLga) {
                    Array.from(citySelect.options).forEach(opt => {
                        if (opt.value === selectedLga) opt.selected = true;
                    });
                }
            });
        }
    } else if (!hasSearchQuery) {
        // Auto-fill logic when no search query is present
        @auth
            const userCountry = @json(auth()->user()->country_name ?? '');
            const userState = @json(auth()->user()->state ?? '');
            const userCity = @json(auth()->user()->lga ?? auth()->user()->city ?? '');
            
            if (userCountry && selectOption(countrySelect, userCountry)) {
                loadStates(countrySelect.value).then(() => {
                    if (userState && selectOption(stateSelect, userState)) {
                        loadCities(stateSelect.value).then(() => {
                            if (userCity) selectOption(citySelect, userCity);
                        });
                    }
                });
            }
        @else
            fetch('https://ipapi.co/json/')
                .then(response => response.json())
                .then(data => {
                    if (data.country_name && selectOption(countrySelect, data.country_name)) {
                        loadStates(countrySelect.value).then(() => {
                            if (data.region && selectOption(stateSelect, data.region)) {
                                loadCities(stateSelect.value).then(() => {
                                    if (data.city) selectOption(citySelect, data.city);
                                });
                            }
                        });
                    }
                })
                .catch(err => console.error('GeoIP check failed:', err));
        @endauth
    }
});
</script>

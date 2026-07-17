@include('header')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

    :root {
        --ob-primary: #3e8189;
        --ob-primary-light: #51cbce;
        --ob-accent: #f59e0b;
        --ob-glass-bg: rgba(255, 255, 255, 0.88);
        --ob-glass-border: rgba(255, 255, 255, 0.2);
        --ob-text-primary: #1e293b;
        --ob-text-muted: #64748b;
        --ob-border: #e2e8f0;
        --ob-radius: 16px;
    }

    body { font-family: 'Outfit', sans-serif; background: #f8fafc; overflow-x: hidden; }
    .navbar, footer { display: none !important; }

    /* ── Background ── */
    .ob-bg {
        position: fixed; inset: 0;
        background-image: url('{{ asset('assets/images/pagelayout.jpg') }}');
        background-repeat: repeat; background-size: 500px;
        filter: brightness(0.6);
        z-index: -1;
    }

    /* ── Wrapper ── */
    .ob-wrapper {
        min-height: 100vh;
        display: flex; align-items: center; justify-content: center;
        padding: 3rem 1rem;
    }

    /* ── Card ── */
    .ob-card {
        background: var(--ob-glass-bg);
        backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
        border: 1px solid var(--ob-glass-border);
        border-radius: 32px;
        box-shadow: 0 40px 100px -20px rgba(0,0,0,0.5);
        width: 100%; max-width: 720px;
        padding: 3rem;
        animation: ob-slideIn 0.8s cubic-bezier(0.16,1,0.3,1);
    }
    @keyframes ob-slideIn {
        from { opacity: 0; transform: translateY(40px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Header ── */
    .ob-logo { text-align: center; margin-bottom: 1.25rem; }
    .ob-logo img { width: 72px; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1)); }

    .ob-title {
        color: var(--ob-text-primary); font-weight: 700; font-size: 2rem;
        text-align: center; margin-bottom: 0.35rem; letter-spacing: -0.025em;
    }
    .ob-subtitle {
        color: var(--ob-text-muted); text-align: center;
        margin-bottom: 2rem; font-size: 15px;
    }

    /* ── Step Indicator ── */
    .ob-steps {
        display: flex; align-items: center; justify-content: center;
        gap: 0; margin-bottom: 2.5rem;
    }
    .ob-step {
        display: flex; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 600; color: var(--ob-text-muted);
        transition: color 0.3s;
    }
    .ob-step.active { color: var(--ob-primary); }
    .ob-step.completed { color: #10b981; }
    .ob-step__dot {
        width: 32px; height: 32px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700;
        background: #f1f5f9; border: 2px solid var(--ob-border);
        transition: all 0.3s;
    }
    .ob-step.active .ob-step__dot {
        background: linear-gradient(135deg, var(--ob-primary), var(--ob-primary-light));
        border-color: var(--ob-primary); color: #fff;
    }
    .ob-step.completed .ob-step__dot {
        background: #10b981; border-color: #10b981; color: #fff;
    }
    .ob-step__line {
        width: 48px; height: 2px;
        background: var(--ob-border); margin: 0 12px;
        border-radius: 2px; position: relative;
    }
    .ob-step__line.active { background: linear-gradient(90deg, #10b981, var(--ob-primary)); }

    /* ── Form Groups ── */
    .ob-form-group { margin-bottom: 1.25rem; }
    .ob-form-group label {
        display: block; font-size: 14px; font-weight: 600;
        color: var(--ob-text-primary); margin-bottom: 0.4rem; padding-left: 4px;
    }
    .ob-form-group label .required { color: #ef4444; margin-left: 2px; }

    .ob-input {
        background: rgba(255,255,255,0.7);
        border: 1.5px solid var(--ob-border);
        border-radius: var(--ob-radius);
        padding: 0.8rem 1.15rem; font-size: 15px;
        width: 100%; transition: all 0.3s;
        color: var(--ob-text-primary); font-family: 'Outfit', sans-serif;
    }
    .ob-input:focus {
        background: #fff;
        border-color: var(--ob-primary);
        box-shadow: 0 0 0 4px rgba(62,129,137,0.1);
        outline: none;
    }
    .ob-input:disabled { background: #f1f5f9; cursor: not-allowed; }
    select.ob-input { appearance: none; cursor: pointer; }

    /* ── Dropzone ── */
    .ob-dropzone {
        background: rgba(255,255,255,0.5);
        border: 2px dashed var(--ob-border);
        border-radius: var(--ob-radius);
        padding: 2rem; text-align: center;
        transition: all 0.3s; cursor: pointer;
        min-height: 120px;
    }
    .ob-dropzone:hover, .ob-dropzone.dz-drag-hover {
        border-color: var(--ob-primary);
        background: rgba(62,129,137,0.04);
    }
    .ob-dropzone .dz-message {
        margin: 0; color: var(--ob-text-muted); font-size: 14px;
    }
    .ob-dropzone .dz-message i {
        font-size: 28px; color: var(--ob-primary); display: block; margin-bottom: 8px;
    }

    /* ── Buttons ── */
    .ob-btn {
        border: none; border-radius: var(--ob-radius);
        padding: 0.85rem 2rem; font-weight: 600; font-size: 15px;
        cursor: pointer; transition: all 0.3s;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        font-family: 'Outfit', sans-serif;
    }
    .ob-btn--primary {
        background: linear-gradient(135deg, var(--ob-primary), var(--ob-primary-light));
        color: #fff;
        box-shadow: 0 10px 15px -3px rgba(62,129,137,0.25);
        width: 100%;
    }
    .ob-btn--primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(62,129,137,0.35);
    }
    .ob-btn--primary:disabled {
        opacity: 0.6; cursor: not-allowed; transform: none;
    }
    .ob-btn--outline {
        background: transparent; color: var(--ob-text-muted);
        border: 1.5px solid var(--ob-border); width: 100%;
    }
    .ob-btn--outline:hover {
        background: #f8fafc; border-color: #cbd5e1; color: var(--ob-text-primary);
    }

    /* ── Alerts ── */
    .ob-alert {
        padding: 0.85rem 1.15rem; border-radius: 12px;
        font-size: 14px; font-weight: 500; margin-bottom: 1rem;
        display: flex; align-items: center; gap: 8px;
    }
    .ob-alert--danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .ob-alert--success { background: #ecfdf5; color: #065f46; border: 1px solid #d1fae5; }

    /* ── Section divider ── */
    .ob-divider {
        display: flex; align-items: center; gap: 12px;
        margin: 1.75rem 0 1.25rem; color: var(--ob-text-muted);
        font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;
    }
    .ob-divider::before, .ob-divider::after {
        content: ''; flex: 1; height: 1px; background: var(--ob-border);
    }

    /* ── Password toggle ── */
    .ob-password-wrap { position: relative; }
    .ob-password-toggle {
        position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
        background: none; border: none; color: var(--ob-text-muted);
        cursor: pointer; padding: 4px; font-size: 15px;
    }

    /* ── Footer link ── */
    .ob-footer {
        margin-top: 2rem; text-align: center;
        font-size: 14px; color: var(--ob-text-muted);
    }
    .ob-footer a { color: var(--ob-primary); font-weight: 700; text-decoration: none; }
    .ob-footer a:hover { text-decoration: underline; }

    /* ── Hidden step ── */
    .ob-step-content { display: none; }
    .ob-step-content.active { display: block; animation: ob-fadeIn 0.4s ease; }
    @keyframes ob-fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

    /* ── Row helpers ── */
    .ob-row { display: flex; gap: 16px; }
    .ob-col { flex: 1; min-width: 0; }

    /* ── Mobile ── */
    @media (max-width: 600px) {
        .ob-card { padding: 1.75rem 1.25rem; border-radius: 24px; }
        .ob-wrapper { padding: 1rem 0.5rem; }
        .ob-title { font-size: 1.55rem; }
        .ob-row { flex-direction: column; gap: 0; }
        .ob-step__line { width: 28px; margin: 0 6px; }
        .ob-step span:not(.ob-step__dot) { display: none; }
        .ob-steps { gap: 0; }
    }
    @media (max-width: 400px) {
        .ob-card { padding: 1.5rem 1rem; border-radius: 20px; }
    }
</style>

<div class="ob-bg"></div>

<div class="ob-wrapper">
    <div class="ob-card">
        <!-- Logo -->
        <div class="ob-logo">
            <a href="/"><img src="/assets/images/logo-small.png" alt="EasyRent Logo"></a>
        </div>

        <h1 class="ob-title">List Your Property</h1>
        <p class="ob-subtitle">Add your property details, then create your landlord account.</p>

        <!-- Step Indicator -->
        <div class="ob-steps">
            <div class="ob-step active" id="stepIndicator1">
                <span class="ob-step__dot">1</span>
                <span>Property</span>
            </div>
            <div class="ob-step__line" id="stepLine"></div>
            <div class="ob-step" id="stepIndicator2">
                <span class="ob-step__dot">2</span>
                <span>Account</span>
            </div>
        </div>

        <div id="obAlertArea"></div>

        <form id="onboardingForm" method="POST" action="{{ route('onboarding.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- ═══════ STEP 1: Property Details ═══════ -->
            <div class="ob-step-content active" id="step1">
                <div class="ob-row">
                    <div class="ob-col">
                        <div class="ob-form-group">
                            <label>Property Type <span class="required">*</span></label>
                            <select class="ob-input" name="propertyType" id="propertyTypeListing" required>
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
                                <option value="11">Mall</option>
                                <option value="12">Event Center</option>
                            </select>
                        </div>
                    </div>
                    <div class="ob-col">
                        <div class="ob-form-group">
                            <label>Currency</label>
                            <select class="ob-input" name="currency_id" required>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}" {{ $currency->code == 'NGN' ? 'selected' : '' }}>
                                        {{ $currency->name }} ({{ $currency->symbol }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="ob-form-group">
                    <label>Country <span class="required">*</span></label>
                    <select class="ob-input" name="country" id="countrySelect" required>
                        <option value="" disabled>-- Select Country --</option>
                        @foreach($countries as $c)
                            <option value="{{ $c['name'] }}" {{ $c['name'] === 'Nigeria' ? 'selected' : '' }}>{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ob-row">
                    <div class="ob-col">
                        <div class="ob-form-group">
                            <label id="stateLabelText">State <span class="required">*</span></label>
                            <select class="ob-input" name="state_id" id="stateSelectListing" required>
                                <option value="" disabled selected>-- Select State --</option>
                                @foreach($states as $state)
                                    <option value="{{ $state->id }}" data-state-name="{{ $state->name }}">{{ $state->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="state" id="stateNameListing">
                        </div>
                    </div>
                    <div class="ob-col">
                        <div class="ob-form-group">
                            <label id="lgaLabelText">LGA <span class="required">*</span></label>
                            <select class="ob-input" name="lga_id" id="lgaSelectListing" required disabled>
                                <option value="" disabled selected>-- Select LGA --</option>
                            </select>
                            <input type="hidden" name="city" id="lgaNameListing">
                        </div>
                    </div>
                </div>

                <div class="ob-form-group">
                    <label>Address <span class="required">*</span></label>
                    <textarea name="address" id="propertyAddress" class="ob-input" rows="3" placeholder="Enter full property address" required style="resize: vertical;"></textarea>
                </div>

                <div class="ob-form-group">
                    <label>Property Images <span style="font-weight: 400; color: var(--ob-text-muted);">(Optional)</span></label>
                    <div id="propertyDropzone" class="ob-dropzone dropzone">
                        <div class="dz-message">
                            <i class="fa fa-cloud-upload-alt"></i>
                            <strong>Drag & Drop Images</strong>
                            <p style="margin: 4px 0 0; font-size: 13px;">or click to select — max 2MB per image</p>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 1.75rem;">
                    <button type="button" class="ob-btn ob-btn--primary" id="goToStep2Btn">
                        Continue to Account <i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════ STEP 2: Account Details ═══════ -->
            <div class="ob-step-content" id="step2">

                <div class="ob-divider">Create Your Account</div>

                <div class="ob-row">
                    <div class="ob-col">
                        <div class="ob-form-group">
                            <label>First Name <span class="required">*</span></label>
                            <input type="text" class="ob-input" name="first_name" id="ob_first_name" required placeholder="John">
                        </div>
                    </div>
                    <div class="ob-col">
                        <div class="ob-form-group">
                            <label>Last Name <span class="required">*</span></label>
                            <input type="text" class="ob-input" name="last_name" id="ob_last_name" required placeholder="Doe">
                        </div>
                    </div>
                </div>

                <div class="ob-row">
                    <div class="ob-col">
                        <div class="ob-form-group">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" class="ob-input" name="email" id="ob_email" required placeholder="john@example.com">
                        </div>
                    </div>
                    <div class="ob-col">
                        <div class="ob-form-group">
                            <label>Phone Number <span class="required">*</span></label>
                            <input type="tel" class="ob-input" name="phone" id="ob_phone" required placeholder="+234...">
                        </div>
                    </div>
                </div>

                <div class="ob-row">
                    <div class="ob-col">
                        <div class="ob-form-group">
                            <label>Password <span class="required">*</span></label>
                            <div class="ob-password-wrap">
                                <input type="password" class="ob-input" name="password" id="ob_password" required minlength="8" placeholder="••••••••">
                                <button type="button" class="ob-password-toggle" onclick="obTogglePassword('ob_password', this)">
                                    <i class="far fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="ob-col">
                        <div class="ob-form-group">
                            <label>Confirm Password <span class="required">*</span></label>
                            <div class="ob-password-wrap">
                                <input type="password" class="ob-input" name="password_confirmation" id="ob_password_confirmation" required minlength="8" placeholder="••••••••">
                                <button type="button" class="ob-password-toggle" onclick="obTogglePassword('ob_password_confirmation', this)">
                                    <i class="far fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 1.75rem;">
                    <button type="button" class="ob-btn ob-btn--outline" id="backToStep1Btn" style="flex: 0 0 auto; width: auto; padding: 0.85rem 1.5rem;">
                        <i class="fa fa-arrow-left"></i> Back
                    </button>
                    <button type="button" class="ob-btn ob-btn--primary" id="submitOnboardingBtn" style="flex: 1;">
                        Create Account & List Property <i class="fa fa-check"></i>
                    </button>
                </div>
            </div>
        </form>

        <div class="ob-footer">
            Already have an account? <a href="{{ route('login') }}">Sign In</a>
        </div>
    </div>
</div>

<script>
// Nigeria DB states (pre-loaded for fast initial render)
const nigeriaStatesData = @json($states);
// Cache for API-fetched location data per country
let cachedLocationData = null;
let currentCountry = 'Nigeria';

Dropzone.autoDiscover = false;

$(document).ready(function() {
    // ── Dropzone ──
    const myDropzone = new Dropzone("#propertyDropzone", {
        url: "{{ route('onboarding.store') }}",
        autoProcessQueue: false,
        uploadMultiple: true,
        parallelUploads: 10,
        maxFiles: 10,
        acceptedFiles: "image/*",
        addRemoveLinks: true,
        dictRemoveFile: '✕'
    });

    // ══════════════════════════════════════
    //  Country / State / LGA-City logic
    // ══════════════════════════════════════
    function isNigeria() { return currentCountry === 'Nigeria'; }

    function updateLabels() {
        const cityLabel = isNigeria() ? 'LGA' : 'City';
        $('#stateLabelText').html('State <span class="required">*</span>');
        $('#lgaLabelText').html(cityLabel + ' <span class="required">*</span>');
    }

    function resetStateAndCity() {
        const stateSelect = $('#stateSelectListing');
        const lgaSelect = $('#lgaSelectListing');
        stateSelect.empty().append('<option value="" disabled selected>-- Select State --</option>');
        lgaSelect.empty().append('<option value="" disabled selected>-- Select ' + (isNigeria() ? 'LGA' : 'City') + ' --</option>').prop('disabled', true);
        $('#stateNameListing').val('');
        $('#lgaNameListing').val('');
    }

    // Populate states from pre-loaded Nigeria DB data
    function loadNigeriaStates() {
        const stateSelect = $('#stateSelectListing');
        resetStateAndCity();
        nigeriaStatesData.forEach(function(state) {
            stateSelect.append(
                `<option value="${state.id}" data-state-name="${state.name}">${state.name}</option>`
            );
        });
    }

    // Fetch states from the API for a non-Nigeria country
    function loadCountryStates(country) {
        resetStateAndCity();
        const stateSelect = $('#stateSelectListing');
        stateSelect.empty().append('<option value="" disabled selected><i class="fa fa-spinner"></i> Loading...</option>');

        fetch('/api/location-data?country=' + encodeURIComponent(country))
            .then(r => r.json())
            .then(data => {
                cachedLocationData = data.states || [];
                stateSelect.empty().append('<option value="" disabled selected>-- Select State --</option>');
                cachedLocationData.forEach(function(state) {
                    stateSelect.append(
                        `<option value="${state.id}" data-state-name="${state.name}">${state.name}</option>`
                    );
                });

                // Auto-select currency if the API returns one
                if (data.currency_code) {
                    $('select[name="currency_id"] option').each(function() {
                        if ($(this).text().includes(data.currency_code)) {
                            $(this).prop('selected', true);
                        }
                    });
                }
            })
            .catch(() => {
                stateSelect.empty().append('<option value="" disabled selected>-- Select State --</option>');
            });
    }

    // Country change
    $('#countrySelect').on('change', function() {
        currentCountry = $(this).val();
        console.log("Country changed to:", currentCountry);
        updateLabels();
        if (isNigeria()) {
            console.log("Loading Nigeria states from DB data");
            loadNigeriaStates();
        } else {
            console.log("Fetching states for country from API");
            loadCountryStates(currentCountry);
        }
    });

    // State change → populate LGA/City
    $('#stateSelectListing').on('change', function() {
        const stateVal = $(this).val();
        const stateName = $(this).find(':selected').data('state-name');
        console.log("State changed to:", stateVal, "Name:", stateName);
        $('#stateNameListing').val(stateName);

        const lgaSelect = $('#lgaSelectListing');
        const placeholder = isNigeria() ? 'LGA' : 'City';
        lgaSelect.empty().append(`<option value="" disabled selected>-- Select ${placeholder} --</option>`);

        if (isNigeria()) {
            // Use pre-loaded DB data
            const state = nigeriaStatesData.find(s => s.id == stateVal);
            console.log("Found Nigeria state:", state);
            if (state && state.lgas) {
                state.lgas.forEach(lga => {
                    lgaSelect.append(`<option value="${lga.id}" data-lga-name="${lga.name}">${lga.name}</option>`);
                });
                lgaSelect.prop('disabled', false);
            }
        } else {
            // Use API-cached data
            if (cachedLocationData) {
                const found = cachedLocationData.find(s => s.id == stateVal || s.name === stateName);
                console.log("Found Country state:", found);
                if (found && found.lgas) {
                    found.lgas.forEach(function(city) {
                        const cityName = city.name || city;
                        lgaSelect.append(`<option value="${cityName}" data-lga-name="${cityName}">${cityName}</option>`);
                    });
                    lgaSelect.prop('disabled', false);
                }
            }
        }
    });

    // LGA/City change → update hidden input
    $('#lgaSelectListing').on('change', function() {
        const name = $(this).find(':selected').data('lga-name') || $(this).val();
        $('#lgaNameListing').val(name);
    });

    // Initialize: Nigeria is pre-selected, labels already correct
    updateLabels();

    // ══════════════════════════════════════
    //  Step navigation
    // ══════════════════════════════════════
    function showAlert(msg, type) {
        const icon = type === 'danger' ? 'fa-exclamation-circle' : 'fa-check-circle';
        $('#obAlertArea').html(`<div class="ob-alert ob-alert--${type}"><i class="fa ${icon}"></i> ${msg}</div>`);
        $('html, body').animate({ scrollTop: $('.ob-card').offset().top - 20 }, 300);
    }

    function goToStep(step) {
        $('.ob-step-content').removeClass('active');
        $(`#step${step}`).addClass('active');

        if (step === 2) {
            $('#stepIndicator1').removeClass('active').addClass('completed');
            $('#stepIndicator1 .ob-step__dot').html('<i class="fa fa-check" style="font-size:12px"></i>');
            $('#stepLine').addClass('active');
            $('#stepIndicator2').addClass('active');
        } else {
            $('#stepIndicator1').addClass('active').removeClass('completed');
            $('#stepIndicator1 .ob-step__dot').text('1');
            $('#stepLine').removeClass('active');
            $('#stepIndicator2').removeClass('active');
        }
        $('html, body').animate({ scrollTop: $('.ob-card').offset().top - 20 }, 300);
    }

    // ── Step 1 → 2 ──
    $('#goToStep2Btn').on('click', function() {
        let valid = true;
        if (!$('#propertyTypeListing').val()) valid = false;
        if (!$('#countrySelect').val()) valid = false;
        if (!$('#stateSelectListing').val()) valid = false;
        if (!$('#lgaSelectListing').val()) valid = false;
        if (!$('#propertyAddress').val().trim()) valid = false;

        if (!valid) {
            showAlert('Please fill all required property fields before continuing.', 'danger');
            return;
        }
        $('#obAlertArea').empty();
        goToStep(2);
    });

    // ── Step 2 → 1 ──
    $('#backToStep1Btn').on('click', function() {
        $('#obAlertArea').empty();
        goToStep(1);
    });

    // ── Final submit ──
    $('#submitOnboardingBtn').on('click', function() {
        const btn = $(this);
        const fname = $('#ob_first_name').val().trim();
        const lname = $('#ob_last_name').val().trim();
        const email = $('#ob_email').val().trim();
        const phone = $('#ob_phone').val().trim();
        const password = $('#ob_password').val();
        const passwordConf = $('#ob_password_confirmation').val();

        if (!fname || !lname || !email || !phone || !password) {
            showAlert('Please fill all required fields.', 'danger');
            return;
        }
        if (password.length < 8) {
            showAlert('Password must be at least 8 characters.', 'danger');
            return;
        }
        if (password !== passwordConf) {
            showAlert('Passwords do not match.', 'danger');
            return;
        }

        $('#obAlertArea').empty();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        const form = document.getElementById('onboardingForm');
        const formData = new FormData(form);

        myDropzone.getAcceptedFiles().forEach((file, index) => {
            formData.append('images[' + index + ']', file);
        });

        fetch("{{ route('onboarding.store') }}", {
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
                showAlert('Account & property created! Redirecting to verify your email...', 'success');
                setTimeout(() => {
                    window.location.href = data.messages.redirect || '/login';
                }, 2000);
            } else {
                btn.prop('disabled', false).html('Create Account & List Property <i class="fa fa-check"></i>');
                showAlert(data.messages || 'An error occurred. Please try again.', 'danger');
            }
        })
        .catch(err => {
            btn.prop('disabled', false).html('Create Account & List Property <i class="fa fa-check"></i>');
            showAlert('Network error. Please check your connection and try again.', 'danger');
        });
    });
});

function obTogglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon = btn.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye-slash', 'far');
        icon.classList.add('fa-eye', 'fas');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye', 'fas');
        icon.classList.add('fa-eye-slash', 'far');
    }
}
</script>

@include('footer')

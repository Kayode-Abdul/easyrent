@include('header')

<style>
    .navbar {
        display: none;
    }

    footer {
        display: none;

    }

    .pt-pad {
        margin-top: 0;
        margin-bottom: 0;
        padding-top: 90px;
        padding-bottom: 90px;
    }

    .auth-container {
        min-height: 100vh;
        background: linear-gradient(45deg, #17a2b8, #6bd098) !important;
        padding: 2rem 0;
    }

    .auth-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .auth-header {
        /* background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%) ; */
        color: white;
        padding: 2rem;
        text-align: center;
        border: none;
    }

    .auth-header h2 {
        margin: 0;
        font-weight: 600;
        font-size: 1.8rem;
    }

    .auth-body {
        padding: 2.5rem;
    }

    .invitation-context {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-left: 4px solid #17a2b8;
    }

    .step-indicator {
        background: linear-gradient(45deg, #17a2b8, #6bd098) !important;
        color: white;
        padding: 1rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 2rem;
        box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
    }

    .form-floating {
        margin-bottom: 1.5rem;
    }

    .form-floating>.form-control,
    .form-floating>.form-select {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1rem 0.75rem;
        height: auto;
        transition: all 0.3s ease;
    }

    .form-floating>.form-control:focus,
    .form-floating>.form-select:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }

    .form-floating>label {
        color: #6c757d;
        font-weight: 500;
    }

    .photo-upload {
        text-align: center;
        margin-bottom: 2rem;
    }

    .photo-preview {
        position: relative;
        display: inline-block;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .photo-preview:hover {
        transform: scale(1.05);
    }

    .photo-preview img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #17a2b8;
        box-shadow: 0 10px 20px rgba(23, 162, 184, 0.3);
    }

    .photo-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(23, 162, 184, 0.8);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.3s ease;
        color: white;
        font-size: 1.5rem;
    }

    .photo-preview:hover .photo-overlay {
        opacity: 1;
    }

    .btn-auth {
        background: linear-gradient(45deg, #17a2b8, #6bd098) !important;
        border: none;
        border-radius: 12px;
        padding: 0.875rem 2rem;
        font-weight: 600;
        font-size: 1rem;
        color: white;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-auth:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(23, 162, 184, 0.3);
        background: linear-gradient(135deg, #138496 0%, #5a9f5a 100%);
    }

    .btn-secondary-auth {
        background: #6c757d;
        border: none;
        border-radius: 12px;
        padding: 0.875rem 2rem;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-secondary-auth:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }

    .auth-links {
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e9ecef;
    }

    .auth-links a {
        color: #17a2b8;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .auth-links a:hover {
        color: #138496;
        text-decoration: underline;
    }

    /* Password toggle button styles */
    .password-toggle-btn {
        position: absolute !important;
        right: 15px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        background: none !important;
        border: none !important;
        color: #6c757d !important;
        cursor: pointer !important;
        padding: 8px !important;
        z-index: 1000 !important;
        transition: color 0.3s ease !important;
        font-size: 16px !important;
        width: auto !important;
        height: auto !important;
        display: block !important;
        line-height: 1 !important;
    }

    .password-toggle-btn:hover {
        color: #17a2b8 !important;
    }

    .password-toggle-btn:focus {
        outline: none !important;
        color: #17a2b8 !important;
    }

    .form-floating.position-relative {
        position: relative !important;
    }

    .form-floating .password-toggle-btn {
        right: 12px !important;
    }

    @media (max-width: 768px) {
        .auth-container {
            padding: 1rem;
        }

        .auth-body {
            padding: 1.5rem;
        }

        .auth-header {
            padding: 1.5rem;
        }

        .invitation-context {
            padding: 1rem;
        }
    }
</style>

<div class="pt-pad">
    <div aria-live="polite" aria-atomic="true" class="sticky-top">
        <div id="toast-container" class="modern-toast-container"></div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="text-center mb-4">
                    <a href="/"><img src="/assets/images/logo-small.png" alt="EasyRent Logo" style="width:80px;"></a>

                </div>
                <div class="auth-card">
                    <div class="auth-header">
                        <!--<a href="/"><img src="/assets/images/logo-small.png" alt="EasyRent Logo" style="width:80px;"></a>
                        <p class="mb-0 mt-2 opacity-90">Join EasyRent and secure your apartment</p> -->
                    </div>

                    <div class="auth-body">
                        <!-- Invitation Context Display -->
                        @if(isset($invitation) && $invitation)
                        <div class="invitation-context">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-home fa-2x text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-1 text-primary">{{ $invitation->apartment->property->prop_name }}</h6>
                                    <small class="text-muted">{{ $invitation->apartment->apartment_type }} • ₦{{
                                        number_format($invitation->apartment->amount) }}/month</small>
                                </div>
                            </div>
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-gift me-2"></i>
                                <small><strong>Special Offer:</strong> Create your account now and complete your
                                    apartment application in one seamless process!</small>
                            </div>
                        </div>
                        @endif

                        <div class="text-center mb-4">
                            <span id="step-indicator" class="step-indicator">Step 1 of 2: Account Info</span>
                        </div>

                        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data"
                            id="registerForm">
                            @csrf

                            <!-- Hidden field to preserve invitation context -->
                            @if(request('token'))
                            <input type="hidden" name="invitation_token" value="{{ request('token') }}">
                            @endif

                            <div id="step1">
                                <div class="photo-upload">
                                    <input id="photo" type="file" class="d-none @error('photo') is-invalid @enderror"
                                        name="photo" accept="image/*" onchange="previewPhoto(event)">
                                    <div class="photo-preview" onclick="document.getElementById('photo').click()">
                                        <img src="{{ asset('assets/images/default-avatar.png') }}" alt="Profile Photo"
                                            id="photo-preview-img" />
                                        <div class="photo-overlay">
                                            <i class="fas fa-camera"></i>
                                        </div>
                                    </div>
                                    <p class="text-muted mt-3 mb-0">Click to upload profile photo</p>
                                    <small class="text-muted">JPG, PNG, GIF up to 2MB</small>
                                    @error('photo')
                                    <div class="text-danger mt-2">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <input id="name" type="text"
                                        class="form-control @error('name') is-invalid @enderror" name="name"
                                        value="{{ old('name') }}" required autocomplete="name" autofocus
                                        placeholder="Full Name">
                                    <label for="name"><i class="fas fa-user me-2"></i>Full Name</label>
                                    @error('name')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <input id="email" type="email"
                                        class="form-control @error('email') is-invalid @enderror" name="email"
                                        value="{{ old('email') }}" required autocomplete="email"
                                        placeholder="Email Address">
                                    <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                    @error('email')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-floating position-relative">
                                    <small>Password must be at least 8 characters long</small>
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        required autocomplete="new-password" placeholder="Password">
                                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                                    <button type="button" class="password-toggle-btn"
                                        onclick="togglePasswordVisibility('password')">
                                        <i class="fas fa-eye-slash" id="password-toggle-icon"></i>
                                    </button>
                                    @error('password')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-floating position-relative">
                                    <input id="password-confirm" type="password" class="form-control"
                                        name="password_confirmation" required autocomplete="new-password"
                                        placeholder="Confirm Password">
                                    <label for="password-confirm"><i class="fas fa-lock me-2"></i>Confirm
                                        Password</label>
                                    <button type="button" class="password-toggle-btn"
                                        onclick="togglePasswordVisibility('password-confirm')">
                                        <i class="fas fa-eye-slash" id="password-confirm-toggle-icon"></i>
                                    </button>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-auth" onclick="validateStep1()">
                                        Next Step <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="step2" class="d-none">
                                <div class="form-floating">
                                    <input id="first_name" type="text"
                                        class="form-control @error('first_name') is-invalid @enderror" name="first_name"
                                        value="{{ old('first_name') }}" required placeholder="First Name">
                                    <label for="first_name"><i class="fas fa-user me-2"></i>First Name</label>
                                    @error('first_name')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <input id="last_name" type="text"
                                        class="form-control @error('last_name') is-invalid @enderror" name="last_name"
                                        value="{{ old('last_name') }}" required placeholder="Last Name">
                                    <label for="last_name"><i class="fas fa-user me-2"></i>Last Name</label>
                                    @error('last_name')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <input id="username" type="text"
                                        class="form-control @error('username') is-invalid @enderror" name="username"
                                        value="{{ old('username') }}" required placeholder="Username">
                                    <label for="username"><i class="fas fa-at me-2"></i>Username</label>
                                    @error('username')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <select id="role" name="role"
                                        class="form-select @error('role') is-invalid @enderror" required>
                                        <option value="" disabled selected>Select Your Role</option>
                                        <option value="1" {{ old('role')==1 ? 'selected' : '' }}>🏡 Tenant</option>
                                        <option value="2" {{ old('role')==2 ? 'selected' : '' }}>🏠 Landlord</option>
                                        <option value="5" {{ old('role')==5 ? 'selected' : '' }}>🔧 Artisan</option>
                                        <option value="6" {{ old('role')==6 ? 'selected' : '' }}>🏢 Property Manager
                                        </option>
                                        <option value="3" {{ old('role')==3 ? 'selected' : '' }}>📈 Marketer</option>
                                    </select>
                                    <label for="role"><i class="fas fa-user-tag me-2"></i>Role</label>
                                    @error('role')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <input id="occupation" type="text"
                                        class="form-control @error('occupation') is-invalid @enderror" name="occupation"
                                        value="{{ old('occupation') }}" placeholder="Occupation">
                                    <label for="occupation"><i class="fas fa-briefcase me-2"></i>Occupation
                                        (Optional)</label>
                                    @error('occupation')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <input id="phone" type="tel"
                                        class="form-control @error('phone') is-invalid @enderror" name="phone"
                                        value="{{ old('phone') }}" placeholder="Phone Number">
                                    <label for="phone"><i class="fas fa-phone me-2"></i>Phone Number (Optional)</label>
                                    @error('phone')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <input id="address" type="text"
                                        class="form-control @error('address') is-invalid @enderror" name="address"
                                        value="{{ old('address') }}" placeholder="Address">
                                    <label for="address"><i class="fas fa-map-marker-alt me-2"></i>Address
                                        (Optional)</label>
                                    @error('address')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <select id="country" name="country" class="form-select"
                                        onchange="getStatesForReg()">
                                        <option value="" disabled selected>Select Country</option>
                                        @foreach(json_decode(file_get_contents(resource_path('countries.json')),
                                        true) as $c)
                                        <option value="{{ $c['name'] }}" {{ $c['name']==='Nigeria' ? 'selected' : '' }}>
                                            {{ $c['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <label for="country"><i class="fas fa-globe me-2"></i>Country</label>
                                </div>

                                <div class="form-floating">
                                    <select id="state" name="state"
                                        class="form-select @error('state') is-invalid @enderror" onchange="getCities()"
                                        required>
                                        <option value="" disabled selected>Select State</option>
                                        @foreach(json_decode(file_get_contents(resource_path('countries.json')),
                                        true) as $c)
                                        @if($c['name'] === 'Nigeria')
                                        @foreach($c['states'] as $item)
                                        <option value="{{ $item['name'] }}" {{ old('state')==$item['name'] ? 'selected'
                                            : '' }}>{{ $item['name'] }}</option>
                                        @endforeach
                                        @endif
                                        @endforeach
                                    </select>
                                    <label for="state"><i class="fas fa-map me-2"></i>State</label>
                                    @error('state')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-floating">
                                    <select id="lga" name="lga" class="form-select @error('lga') is-invalid @enderror"
                                        required>
                                        <option value="" disabled selected>Select LGA</option>
                                        <!-- LGAs will be populated by JS -->
                                    </select>
                                    <label for="lga" id="lgaLabel"><i class="fas fa-map-pin me-2"></i>Local Government
                                        Area</label>
                                    @error('lga')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-between gap-3">
                                    <button type="button" class="btn btn-secondary-auth flex-fill"
                                        onclick="showStep(1)">
                                        <i class="fas fa-arrow-left me-2"></i>Previous
                                    </button>
                                    <button type="submit" class="btn btn-auth flex-fill">
                                        <i class="fas fa-user-plus me-2"></i>Create Account & Continue
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="auth-links">
                            <span class="text-muted">Already have an account?</span>
                            <a href="{{ route('login') }}{{ request('token') ? '?token=' . request('token') : '' }}"
                                class="ms-1">
                                <i class="fas fa-sign-in-alt me-1"></i>Sign In
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showStep(step) {
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const indicator = document.getElementById('step-indicator');

        if (step === 1) {
            step1.classList.remove('d-none');
            step2.classList.add('d-none');
            indicator.textContent = 'Step 1 of 2: Account Info';
            step1.style.opacity = '0';
            step1.style.transform = 'translateX(-20px)';
            setTimeout(() => {
                step1.style.transition = 'all 0.3s ease';
                step1.style.opacity = '1';
                step1.style.transform = 'translateX(0)';
            }, 50);
        } else {
            step1.classList.add('d-none');
            step2.classList.remove('d-none');
            indicator.textContent = 'Step 2 of 2: Personal Details';
            step2.style.opacity = '0';
            step2.style.transform = 'translateX(20px)';
            setTimeout(() => {
                step2.style.transition = 'all 0.3s ease';
                step2.style.opacity = '1';
                step2.style.transform = 'translateX(0)';
            }, 50);
        }
    }

    // Password visibility toggle function
    function togglePasswordVisibility(fieldId) {
        const passwordField = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(fieldId + '-toggle-icon');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        }
    }

    // Enhanced client-side validation for step 1
    function validateStep1() {
        let valid = true;
        let errorMessages = [];
        const requiredFields = ['name', 'email', 'password', 'password-confirm'];

        // Clear previous validation states
        requiredFields.forEach(function (id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.remove('is-invalid');
            }
        });

        // Validate required fields
        requiredFields.forEach(function (id) {
            const el = document.getElementById(id);
            if (el && !el.value.trim()) {
                el.classList.add('is-invalid');
                valid = false;
            }
        });

        // Validate email format
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && email.value && !emailRegex.test(email.value)) {
            email.classList.add('is-invalid');
            valid = false;
            errorMessages.push('Please enter a valid email address');
        }

        // Validate password strength
        const password = document.getElementById('password');
        if (password && password.value && password.value.length < 8) {
            password.classList.add('is-invalid');
            valid = false;
            errorMessages.push('Password must be at least 8 characters long');
        }

        // Validate password confirmation
        const pw = document.getElementById('password');
        const pwc = document.getElementById('password-confirm');
        if (pw && pwc && pw.value !== pwc.value) {
            pwc.classList.add('is-invalid');
            valid = false;
            errorMessages.push('Passwords do not match');
        }

        // Show appropriate message
        if (!valid) {
            if (errorMessages.length > 0) {
                showToast(errorMessages[0], 'error');
            } else {
                showToast('Please fill all required fields correctly', 'error');
            }
        } else {
            showToast('Step 1 completed successfully!', 'success');
            setTimeout(() => showStep(2), 500);
        }
    }

    // Show first step by default
    document.addEventListener('DOMContentLoaded', function () {
        showStep(1);
        if (document.getElementById('state').value) getCities();

        // Show server-side session messages as toast
        @if (session('status'))
            showToast("{{ session('status') }}", 'success');
        @endif
        @if (session('message'))
            showToast("{{ session('message') }}", 'success');
        @endif
        @if (session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif
        @if (session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif

        // Handle form submission
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function () {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
                submitBtn.disabled = true;
            });
        }
    });

    function previewPhoto(event) {
        const input = event.target;
        const img = document.getElementById('photo-preview-img');

        if (input.files && input.files[0]) {
            // Validate file size (2MB max)
            if (input.files[0].size > 2 * 1024 * 1024) {
                showToast('File size must be less than 2MB', 'error');
                input.value = '';
                return;
            }

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml'];
            if (!allowedTypes.includes(input.files[0].type)) {
                showToast('Please select a valid image file (JPEG, PNG, GIF, SVG)', 'error');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            img.src = "{{ asset('assets/images/default-avatar.png') }}";
        }
    }

    // API-based cascading location selection for registration
    let cachedRegLocationData = null;

    function getStatesForReg() {
        const country = document.getElementById('country').value;
        const stateSelect = document.getElementById('state');
        const lgaSelect = document.getElementById('lga');
        const lgaLabel = document.getElementById('lgaLabel');

        if (lgaLabel) {
            lgaLabel.innerHTML = '<i class="fas fa-map-pin me-2"></i>' + (country === 'Nigeria' ? 'Local Government Area' : 'City');
        }

        stateSelect.innerHTML = '<option value="" disabled selected>Select State</option>';
        lgaSelect.innerHTML = '<option value="" disabled selected>Select ' + (country === 'Nigeria' ? 'LGA' : 'City') + '</option>';

        if (!country) return;

        fetch('/api/location-data?country=' + encodeURIComponent(country))
            .then(r => r.json())
            .then(data => {
                cachedRegLocationData = data.states || [];
                cachedRegLocationData.forEach(function (state) {
                    const opt = document.createElement('option');
                    opt.value = state.name;
                    opt.textContent = state.name;
                    stateSelect.appendChild(opt);
                });
            });
    }

    function getCities() {
        const state = document.getElementById('state').value;
        const country = document.getElementById('country').value;
        const lgaSelect = document.getElementById('lga');
        lgaSelect.innerHTML = '<option value="" disabled selected>Select ' + (country === 'Nigeria' ? 'LGA' : 'City') + '</option>';

        if (!state || !cachedRegLocationData) return;

        const found = cachedRegLocationData.find(s => s.name === state);
        if (found && found.cities) {
            found.cities.forEach(function (city) {
                const opt = document.createElement('option');
                opt.value = city;
                opt.text = city;
                lgaSelect.appendChild(opt);
            });
        }
    }

    // Initialize: load states for default Nigeria selection
    document.addEventListener('DOMContentLoaded', function () {
        const countrySelect = document.getElementById('country');
        if (countrySelect && countrySelect.value) {
            getStatesForReg();
        }
    });
</script>

@include('footer')
@include('header')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>
                <div class="card-body">
                    <div aria-live="polite" aria-atomic="true" class="position-relative">
                        <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>
                    </div>
                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="registerForm">
                        @csrf
                        <div class="mb-4 text-center">
                            <span id="step-indicator" class="fw-bold">Step 1 of 2: Account Info</span>
                        </div>
                        <div id="step1">
                            <div class="row mb-3">
                                <label for="photo" class="col-md-4 col-form-label text-md-end">Profile Photo</label>
                                <div class="col-md-6">
                                    <input id="photo" type="file" class="form-control d-none @error('photo') is-invalid @enderror" name="photo" accept="image/*" onchange="previewPhoto(event)">
                                    <div id="photo-preview" class="mt-2" style="cursor:pointer; display:flex; align-items:center;" onclick="document.getElementById('photo').click()">
                                        <img src="{{ asset('assets/images/default-avatar.png') }}" alt="Preview" id="photo-preview-img" class="img-thumbnail" style="max-width:120px; max-height:120px; border-radius:50%; object-fit:cover;" />
                                        <span class="ms-3 text-muted">Click to select photo</span>
                                    </div>
                                    <small class="form-text text-muted">Allowed file types: jpeg, png, jpg, gif, svg. Max size: 2MB.</small>
                                    @error('photo')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>
                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>
                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>
                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>
                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary" onclick="validateStep1()">Next</button>
                            </div>
                        </div>
                        <div id="step2" class="d-none">
                            <div class="mb-4 text-center">
                                <span class="fw-bold">Step 2 of 2: Personal Details</span>
                            </div>
                            <div class="row mb-3">
                                <label for="first_name" class="col-md-4 col-form-label text-md-end">First Name</label>
                                <div class="col-md-6">
                                    <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name') }}" required autofocus>
                                    @error('first_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="last_name" class="col-md-4 col-form-label text-md-end">Last Name</label>
                                <div class="col-md-6">
                                    <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name') }}" required>
                                    @error('last_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="username" class="col-md-4 col-form-label text-md-end">Username</label>
                                <div class="col-md-6">
                                    <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required>
                                    @error('username')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="role" class="col-md-4 col-form-label text-md-end">Role</label>
                                <div class="col-md-6">
                                    <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
                                        <option value="" disabled selected>Select Role</option>
                                        <option value="2" {{ old('role') == 2 ? 'selected' : '' }}>Landlord</option>
                                        <option value="1" {{ old('role') == 1 ? 'selected' : '' }}>Tenant</option>
                                        <option value="5" {{ old('role') == 5 ? 'selected' : '' }}>Artisan</option>
                                        <option value="6" {{ old('role') == 6 ? 'selected' : '' }}>Property Manager</option>
                                        <option value="3" {{ old('role') == 3 ? 'selected' : '' }}>Marketer</option>
                                    </select>
                                    @error('role')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="occupation" class="col-md-4 col-form-label text-md-end">Occupation</label>
                                <div class="col-md-6">
                                    <input id="occupation" type="text" class="form-control @error('occupation') is-invalid @enderror" name="occupation" value="{{ old('occupation') }}">
                                    @error('occupation')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="phone" class="col-md-4 col-form-label text-md-end">Phone</label>
                                <div class="col-md-6">
                                    <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="address" class="col-md-4 col-form-label text-md-end">Address</label>
                                <div class="col-md-6">
                                    <input id="address" type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address') }}">
                                    @error('address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="state" class="col-md-4 col-form-label text-md-end">State</label>
                                <div class="col-md-6">
                                    <select id="state" name="state" class="form-control @error('state') is-invalid @enderror" onchange="getCities()" required>
                                        <option value="" disabled selected>Select State</option>
                                        @foreach(json_decode(file_get_contents(resource_path('states-and-cities.json')), true) as $item)
                                            <option value="{{ $item['name'] }}" {{ old('state') == $item['name'] ? 'selected' : '' }}>{{ $item['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('state')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="lga" class="col-md-4 col-form-label text-md-end">LGA</label>
                                <div class="col-md-6">
                                    <select id="lga" name="lga" class="form-control @error('lga') is-invalid @enderror" required>
                                        <option value="" disabled selected>Select LGA</option>
                                        <!-- LGAs will be populated by JS -->
                                    </select>
                                    @error('lga')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary" onclick="showStep(1)">Previous</button>
                                <button type="submit" class="btn btn-primary">{{ __('Register') }}</button>
                            </div>
                        </div>
                    </form>
                    <div class="mt-3">
                        <a href="{{ route('login') }}">Already have an account? Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function showStep(step) {
        if(step === 1) {
            document.getElementById('step1').classList.remove('d-none');
            document.getElementById('step2').classList.add('d-none');
            document.getElementById('step-indicator').textContent = 'Step 1 of 2: Account Info';
        } else {
            document.getElementById('step1').classList.add('d-none');
            document.getElementById('step2').classList.remove('d-none');
            document.getElementById('step-indicator').textContent = 'Step 2 of 2: Personal Details';
        }
    }
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-bg-${type === 'error' ? 'danger' : 'success'} border-0 show`;
        toast.id = toastId;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close" onclick="document.getElementById('${toastId}').remove()"></button></div>`;
        container.appendChild(toast);
        setTimeout(() => { toast.remove(); }, 4000);
    }
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
    @if(session('success'))
        showToast("{{ session('success') }}", 'success');
    @endif
    // Show client-side validation error for step 1
    function validateStep1() {
        let valid = true;
        const requiredFields = ['name', 'email', 'password', 'password-confirm'];
        requiredFields.forEach(function(id) {
            const el = document.getElementById(id);
            if(el && !el.value) {
                el.classList.add('is-invalid');
                valid = false;
            } else if(el) {
                el.classList.remove('is-invalid');
            }
        });
        const pw = document.getElementById('password');
        const pwc = document.getElementById('password-confirm');
        if(pw && pwc && pw.value !== pwc.value) {
            pwc.classList.add('is-invalid');
            valid = false;
            showToast('Passwords do not match', 'error');
        }
        if(!valid) {
            showToast('Please fill all required fields in Step 1', 'error');
        }
        if(valid) {
            showStep(2);
        }
    }
    // Show first step by default
    document.addEventListener('DOMContentLoaded', function() {
        showStep(1);
        if(document.getElementById('state').value) getCities();
    });
    function previewPhoto(event) {
        const input = event.target;
        const img = document.getElementById('photo-preview-img');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            img.src = "{{ asset('assets/images/default-avatar.png') }}";
        }
    }
    // Populate LGAs based on selected state
    // Convert array of state objects to object with state names as keys
    const statesArray = @json(json_decode(file_get_contents(resource_path('states-and-cities.json')), true));
    const statesData = {};
    statesArray.forEach(state => {
        statesData[state.name] = state.cities;
    });
    
    function getCities() {
         const state = document.getElementById('state').value;
         const lgaSelect = document.getElementById('lga');
         lgaSelect.innerHTML = '<option value="" disabled selected>Select LGA</option>';
         const cities = statesData[state] || [];
         cities.forEach(function(city) {
             const opt = document.createElement('option');
             opt.value = city;
             opt.text = city;
             lgaSelect.appendChild(opt);
         });
     }
    // On page load, if state is selected, populate LGAs
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('state').value) getCities();
    });
</script>

@include('footer')

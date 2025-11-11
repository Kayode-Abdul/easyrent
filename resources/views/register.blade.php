<!-- Header area start -->
@include('header')
<!-- Header area end -->         
<?php //print_r($locations); 
  //echo($locations); 
  $location = json_decode($locations, true);
  //foreach ($location['Abia'] as $list) {
    //foreach (array_values($list)[0] as $card) {
    //    echo $card['name'];
    //}
//}
?>
<!-- Enhanced Registration Section -->
<section class="register-section">
    <div class="register-container">
        <div class="register-wrapper">
            <!-- Header -->
            <div class="register-header">
                <div class="logo-section">
                    <img src="/assets/images/logo.png" alt="EasyRent" class="register-logo">
                    <h1>EasyRent</h1>
                </div>
                <div class="header-content">
                    <h2>Create Your Account</h2>
                    <p>Join thousands of property owners and tenants who trust EasyRent</p>
                </div>
            </div>

            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <span>Personal Info</span>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <span>Account Details</span>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <span>Location</span>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="form-container">
                <div id="anchor"></div>
                <div id="message"></div>
                
                <form method="post" action="/register" id="registration-Form" class="enhanced-register-form" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    
                    <!-- Step 1: Personal Information -->
                    <div class="form-step active" data-step="1">
                        <h3>Personal Information</h3>
                        <p>Tell us about yourself</p>
                        
                        <div class="photo-upload">
                            <div class="photo-preview">
                                <img id="photo-preview-img" src="/assets/images/default-avatar.png" alt="Profile Preview">
                                <div class="photo-overlay">
                                    <i class="fas fa-camera"></i>
                                    <span>Upload Photo</span>
                                </div>
                            </div>
                            <input type="file" name="photo" id="photo" accept="image/*" hidden>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="username" id="username" class="form-input" placeholder="Username" required>
                                    <label class="floating-label">Username</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group half">
                                <div class="input-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="f_name" id="f-name" class="form-input" placeholder="First Name" required>
                                    <label class="floating-label">First Name</label>
                                </div>
                            </div>
                            <div class="input-group half">
                                <div class="input-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="l_name" id="l-name" class="form-input" placeholder="Last Name" required>
                                    <label class="floating-label">Last Name</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" name="email" id="email" class="form-input" placeholder="Email Address" required>
                                    <label class="floating-label">Email Address</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-briefcase input-icon"></i>
                                    <select name="role" id="user-role" class="form-select" required>
                                        <option value="">Select Your Role</option>
                                        @php
                                            $publicRoles = \DB::table('roles')->whereIn('name', ['tenant', 'landlord', 'marketer', 'Artisan', 'property_manager'])->get();
                                        @endphp
                                        @foreach($publicRoles as $role)
                                            <option value="{{ $role->id }}">{{ ucfirst($role->display_name ?? $role->name) }}</option>
                                        @endforeach
                                    </select>
                                    <label class="floating-label">Role</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Account Details -->
                    <div class="form-step" data-step="2">
                        <h3>Account Security</h3>
                        <p>Create a secure password for your account</p>

                        <div class="form-row">
                            <div class="input-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" name="password" id="password" class="form-input" placeholder="Password" required>
                                    <label class="floating-label">Password</label>
                                    <button type="button" class="password-toggle" onclick="togglePasswordField('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill"></div>
                                    </div>
                                    <span class="strength-text">Password strength</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" name="repassword" id="repassword" class="form-input" placeholder="Confirm Password" required>
                                    <label class="floating-label">Confirm Password</label>
                                    <button type="button" class="password-toggle" onclick="togglePasswordField('repassword')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group half">
                                <div class="input-wrapper">
                                    <i class="fas fa-briefcase input-icon"></i>
                                    <input type="text" name="occupation" id="occupation" class="form-input" placeholder="Occupation">
                                    <label class="floating-label">Occupation</label>
                                </div>
                            </div>
                            <div class="input-group half">
                                <div class="input-wrapper">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="tel" name="phone" id="phone" class="form-input" placeholder="Phone Number">
                                    <label class="floating-label">Phone Number</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-map-marker-alt input-icon"></i>
                                    <textarea name="address" id="address" class="form-textarea" placeholder="Address" rows="3"></textarea>
                                    <label class="floating-label">Address</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Location -->
                    <div class="form-step" data-step="3">
                        <h3>Location Details</h3>
                        <p>Help us serve you better by providing your location</p>

                        <div class="form-row">
                            <div class="input-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-map input-icon"></i>
                                    <select name="state" id="states" class="form-select" onchange="getCities()" required>
                                        <option value="">Select State</option>
                                        <?php foreach ($location as $key => $item) { ?>
                                            <option value="<?= $item['name'] ?>"><?= $item['name'] ?></option>
                                        <?php } ?>
                                    </select>
                                    <label class="floating-label">State</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-city input-icon"></i>
                                    <select name="city" id="cities" class="form-select" required>
                                        <option value="">Select City</option>
                                    </select>
                                    <label class="floating-label">City</label>
                                </div>
                            </div>
                        </div>

                        <div class="terms-agreement">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" name="terms" required>
                                <span class="checkmark"></span>
                                I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="form-navigation">
                        <button type="button" class="btn-secondary" id="prevBtn" onclick="changeStep(-1)">Previous</button>
                        <button type="button" class="btn-primary" id="nextBtn" onclick="changeStep(1)">Next</button>
                        <button type="submit" class="btn-primary" id="submitBtn" style="display: none;">
                            <span class="btn-text">Create Account</span>
                            <i class="fas fa-arrow-right btn-icon"></i>
                        </button>
                    </div>
                </form>

                <div class="login-link">
                    <p>Already have an account? <a href="/login">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
        <script src="assets/js/custom/register.js"></script>

<style>
/* Enhanced Registration Page Styles */
.register-section {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 40px 20px;
}

.register-container {
    max-width: 800px;
    margin: 0 auto;
}

.register-wrapper {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.register-header {
    background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
    color: white;
    padding: 40px;
    text-align: center;
}

.logo-section {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.register-logo {
    width: 40px;
    height: 40px;
    margin-right: 10px;
}

.logo-section h1 {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
}

.header-content h2 {
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 10px;
}

.header-content p {
    font-size: 16px;
    opacity: 0.9;
    margin: 0;
}

.progress-steps {
    display: flex;
    justify-content: center;
    padding: 30px 40px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0 30px;
    opacity: 0.5;
    transition: all 0.3s ease;
}

.step.active {
    opacity: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step.active .step-number {
    background: #3e8189;
    color: white;
}

.step span {
    font-size: 14px;
    font-weight: 500;
    color: #666;
}

.step.active span {
    color: #3e8189;
}

.form-container {
    padding: 40px;
}

.form-step {
    display: none;
}

.form-step.active {
    display: block;
}

.form-step h3 {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-step p {
    color: #666;
    margin-bottom: 30px;
}

.photo-upload {
    display: flex;
    justify-content: center;
    margin-bottom: 30px;
}

.photo-preview {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    border: 4px solid #e9ecef;
    transition: all 0.3s ease;
}

.photo-preview:hover {
    border-color: #3e8189;
}

.photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.photo-preview:hover .photo-overlay {
    opacity: 1;
}

.photo-overlay i {
    font-size: 24px;
    margin-bottom: 5px;
}

.photo-overlay span {
    font-size: 12px;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
}

.input-group {
    flex: 1;
}

.input-group.half {
    flex: 0.5;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 16px;
    z-index: 2;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 16px 16px 16px 50px;
    border: 2px solid #e1e5e9;
    border-radius: 12px;
    font-size: 16px;
    background: #f8f9fa;
    transition: all 0.3s ease;
    outline: none;
    font-family: inherit;
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    border-color: #3e8189;
    background: white;
    box-shadow: 0 0 0 3px rgba(62, 129, 137, 0.1);
}

.floating-label {
    position: absolute;
    left: 50px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 16px;
    pointer-events: none;
    transition: all 0.3s ease;
    background: white;
    padding: 0 5px;
}

.form-input:focus + .floating-label,
.form-input:not(:placeholder-shown) + .floating-label,
.form-select:focus + .floating-label,
.form-select:not([value=""]) + .floating-label,
.form-textarea:focus + .floating-label,
.form-textarea:not(:placeholder-shown) + .floating-label {
    transform: translateY(-28px) scale(0.85);
    color: #3e8189;
}

.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    font-size: 16px;
    padding: 5px;
}

.password-toggle:hover {
    color: #3e8189;
}

.password-strength {
    margin-top: 8px;
}

.strength-bar {
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 5px;
}

.strength-fill {
    height: 100%;
    width: 0%;
    background: #dc3545;
    transition: all 0.3s ease;
}

.strength-text {
    font-size: 12px;
    color: #666;
}

.terms-agreement {
    margin: 30px 0;
}

.checkbox-wrapper {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    font-size: 14px;
    color: #666;
    line-height: 1.5;
}

.checkbox-wrapper input {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-radius: 4px;
    margin-right: 12px;
    margin-top: 2px;
    position: relative;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.checkbox-wrapper input:checked + .checkmark {
    background: #3e8189;
    border-color: #3e8189;
}

.checkbox-wrapper input:checked + .checkmark::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.checkbox-wrapper a {
    color: #3e8189;
    text-decoration: none;
}

.checkbox-wrapper a:hover {
    text-decoration: underline;
}

.form-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #e9ecef;
}

.btn-secondary,
.btn-primary {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-secondary {
    background: #f8f9fa;
    color: #666;
    border: 2px solid #e9ecef;
}

.btn-secondary:hover {
    background: #e9ecef;
}

.btn-primary {
    background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(62, 129, 137, 0.3);
}

.btn-icon {
    transition: transform 0.3s ease;
}

.btn-primary:hover .btn-icon {
    transform: translateX(5px);
}

.login-link {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.login-link p {
    color: #666;
    font-size: 14px;
    margin: 0;
}

.login-link a {
    color: #3e8189;
    text-decoration: none;
    font-weight: 600;
}

.login-link a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .register-section {
        padding: 20px 10px;
    }
    
    .register-header {
        padding: 30px 20px;
    }
    
    .progress-steps {
        padding: 20px;
    }
    
    .step {
        margin: 0 15px;
    }
    
    .form-container {
        padding: 30px 20px;
    }
    
    .form-row {
        flex-direction: column;
        gap: 15px;
    }
    
    .input-group.half {
        flex: 1;
    }
}

@media (max-width: 480px) {
    .progress-steps {
        flex-direction: column;
        gap: 15px;
    }
    
    .step {
        flex-direction: row;
        margin: 0;
    }
    
    .step-number {
        margin-right: 10px;
        margin-bottom: 0;
    }
}
</style></script>
<script>
// Enhanced Registration JavaScript
let currentStep = 1;
const totalSteps = 3;

// Step navigation
function changeStep(direction) {
    const steps = document.querySelectorAll('.form-step');
    const progressSteps = document.querySelectorAll('.progress-steps .step');
    
    // Hide current step
    steps[currentStep - 1].classList.remove('active');
    progressSteps[currentStep - 1].classList.remove('active');
    
    // Update current step
    currentStep += direction;
    
    // Show new step
    steps[currentStep - 1].classList.add('active');
    progressSteps[currentStep - 1].classList.add('active');
    
    // Update navigation buttons
    updateNavigationButtons();
    
    // Validate current step
    validateCurrentStep();
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
    
    if (currentStep === totalSteps) {
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'flex';
    } else {
        nextBtn.style.display = 'block';
        submitBtn.style.display = 'none';
    }
}

function validateCurrentStep() {
    const currentStepElement = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
        }
    });
    
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    if (currentStep < totalSteps) {
        nextBtn.disabled = !isValid;
    } else {
        submitBtn.disabled = !isValid;
    }
}

// Photo upload functionality
function setupPhotoUpload() {
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo-preview-img');
    const photoUploadArea = document.querySelector('.photo-preview');
    
    photoUploadArea.addEventListener('click', () => {
        photoInput.click();
    });
    
    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

// Password strength checker
function checkPasswordStrength(password) {
    const strengthBar = document.querySelector('.strength-fill');
    const strengthText = document.querySelector('.strength-text');
    
    let strength = 0;
    let feedback = '';
    
    if (password.length >= 8) strength += 25;
    if (/[a-z]/.test(password)) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/[0-9]/.test(password)) strength += 25;
    
    if (strength < 50) {
        strengthBar.style.background = '#dc3545';
        feedback = 'Weak';
    } else if (strength < 75) {
        strengthBar.style.background = '#ffc107';
        feedback = 'Fair';
    } else if (strength < 100) {
        strengthBar.style.background = '#17a2b8';
        feedback = 'Good';
    } else {
        strengthBar.style.background = '#28a745';
        feedback = 'Strong';
    }
    
    strengthBar.style.width = strength + '%';
    strengthText.textContent = feedback + ' password';
}

// Password toggle functionality
function togglePasswordField(fieldId) {
    const field = document.getElementById(fieldId);
    const toggleBtn = field.parentElement.querySelector('.password-toggle i');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggleBtn.classList.remove('fa-eye');
        toggleBtn.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        toggleBtn.classList.remove('fa-eye-slash');
        toggleBtn.classList.add('fa-eye');
    }
}

// Form validation
function validatePasswords() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('repassword').value;
    const confirmField = document.getElementById('repassword');
    
    if (password && confirmPassword) {
        if (password === confirmPassword) {
            confirmField.style.borderColor = '#28a745';
        } else {
            confirmField.style.borderColor = '#dc3545';
        }
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupPhotoUpload();
    updateNavigationButtons();
    
    // Password strength checking
    const passwordField = document.getElementById('password');
    passwordField.addEventListener('input', function() {
        checkPasswordStrength(this.value);
        validatePasswords();
        validateCurrentStep();
    });
    
    // Password confirmation
    const confirmPasswordField = document.getElementById('repassword');
    confirmPasswordField.addEventListener('input', function() {
        validatePasswords();
        validateCurrentStep();
    });
    
    // Real-time validation for all required fields
    const requiredFields = document.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('input', validateCurrentStep);
        field.addEventListener('change', validateCurrentStep);
    });
    
    // Form submission
    const form = document.getElementById('registration-Form');
    form.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnIcon = submitBtn.querySelector('.btn-icon');
        
        submitBtn.disabled = true;
        btnText.textContent = 'Creating Account...';
        btnIcon.className = 'fas fa-spinner fa-spin btn-icon';
    });
    
    // Floating labels
    const inputs = document.querySelectorAll('.form-input, .form-select, .form-textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
});

// Cities function (existing)
function getCities(){
        var stet = document.getElementById("states").value;
        var dayArr = <?php echo json_encode($location); ?>;
        //console.log(dayArr);
        var findDay =stet; //find price for day 1

        var price = $.map(dayArr, function(value, key) {
         if (value.name === findDay)
         {
             //console.log( value.cities);
             //const select_elem = document.getElementById('');  
              var dynamicSelect = document.getElementById("cities");
              dynamicSelect.innerHTML = "";
              value.cities.forEach(function(item){ 
                  var newOption = document.createElement("option");
                  newOption.text = item.toString();//item.whateverProperty
                  newOption.value = item.toString();
                  //append acquired data
                  dynamicSelect.add(newOption);
            });
         }
     });
  }
    </script>
<!-- Footer area start -->
@include('footer')
<!-- Footer area end -->

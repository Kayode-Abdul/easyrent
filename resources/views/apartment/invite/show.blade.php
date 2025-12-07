@extends('layout')

@section('title', 'Apartment Details - ' . $property->prop_name)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Property Images -->
            <div class="property-gallery mb-4">
                @if($property->prop_photos && count(json_decode($property->prop_photos, true)) > 0)
                    <div class="card">
                        <div class="card-body p-0">
                            <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    @foreach(json_decode($property->prop_photos, true) as $index => $photo)
                                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                            <img src="{{ asset('storage/' . $photo) }}" 
                                                 class="d-block w-100" 
                                                 alt="Property Image {{ $index + 1 }}"
                                                 style="height: 400px; object-fit: cover;">
                                        </div>
                                    @endforeach
                                </div>
                                @if(count(json_decode($property->prop_photos, true)) > 1)
                                    <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon"></span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                                        <span class="carousel-control-next-icon"></span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-home fa-5x text-primary mb-3"></i>
                            <h5>Property Images</h5>
                            <p class="text-muted">No images available for this property</p>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Property Details -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-home me-2"></i>{{ $property->prop_name }}
                    </h3>
                    <p class="mb-0 opacity-75">
                        <i class="fas fa-map-marker-alt me-1"></i>{{ $property->prop_address }}
                    </p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary">
                                <i class="fas fa-info-circle me-2"></i>Apartment Details
                            </h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>Type:</strong> 
                                    <span class="badge bg-info">{{ $apartment->apartment_type }}</span>
                                </li>
                                <li class="mb-2">
                                    <strong>Monthly Rent:</strong> 
                                    <span class="text-success fw-bold">₦{{ number_format($apartment->amount) }}</span>
                                </li>
                                <li class="mb-2">
                                    <strong>Property Type:</strong> {{ $property->prop_type }}
                                </li>
                                <li class="mb-2">
                                    <strong>Location:</strong> {{ $property->prop_address }}
                                </li>
                                <li class="mb-2">
                                    <strong>State:</strong> {{ $property->prop_state }}
                                </li>
                                <li class="mb-2">
                                    <strong>LGA:</strong> {{ $property->prop_lga }}
                                </li>
                                @if($property->prop_size)
                                <li class="mb-2">
                                    <strong>Size:</strong> {{ $property->prop_size }}
                                </li>
                                @endif
                                @if($property->bedrooms)
                                <li class="mb-2">
                                    <strong>Bedrooms:</strong> {{ $property->bedrooms }}
                                </li>
                                @endif
                                @if($property->bathrooms)
                                <li class="mb-2">
                                    <strong>Bathrooms:</strong> {{ $property->bathrooms }}
                                </li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-primary">
                                <i class="fas fa-user-tie me-2"></i>Landlord Information
                            </h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <strong>Name:</strong> {{ $landlord->first_name }} {{ $landlord->last_name }}
                                        </li>
                                        <li class="mb-2">
                                            <strong>Email:</strong> 
                                            <a href="mailto:{{ $landlord->email }}">{{ $landlord->email }}</a>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Phone:</strong> 
                                            <a href="tel:{{ $landlord->phone }}">{{ $landlord->phone }}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($property->prop_description)
                    <div class="mt-4">
                        <h5 class="text-primary">
                            <i class="fas fa-file-alt me-2"></i>Property Description
                        </h5>
                        <p class="text-muted">{{ $property->prop_description }}</p>
                    </div>
                    @endif
                    
                    <!-- Amenities Section -->
                    @if($property->amenities && $property->amenities->count() > 0)
                    <div class="mt-4">
                        <h5 class="text-primary">
                            <i class="fas fa-star me-2"></i>Amenities & Features
                        </h5>
                        <div class="row">
                            @foreach($property->amenities as $amenity)
                                <div class="col-md-6 mb-2">
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-check-circle text-success me-1"></i>{{ $amenity->name }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <!-- Additional Property Information -->
                    <div class="mt-4">
                        <h5 class="text-primary">
                            <i class="fas fa-map-marker-alt me-2"></i>Location Details
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <strong>Full Address:</strong><br>
                                        <span class="text-muted">{{ $property->prop_address }}</span>
                                    </li>
                                    <li class="mb-2">
                                        <strong>State:</strong> {{ $property->prop_state }}
                                    </li>
                                    <li class="mb-2">
                                        <strong>Local Government:</strong> {{ $property->prop_lga }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                @if($property->nearest_landmark)
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <strong>Nearest Landmark:</strong><br>
                                        <span class="text-muted">{{ $property->nearest_landmark }}</span>
                                    </li>
                                </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Application Form -->
            <div class="card sticky-top shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-key me-2"></i>Apply for this Apartment
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                        </div>
                    @endif
                    
                    @auth
                        <!-- Authenticated User - Show Application Form -->
                        <form action="{{ route('apartment.invite.apply', $invitation->invitation_token) }}" method="POST" id="applicationForm">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-calendar me-1 text-primary"></i>Lease Duration (months) *
                                </label>
                                <select name="duration" class="form-select form-select-lg" required id="durationSelect">
                                    <option value="6">6 months</option>
                                    <option value="12" selected>12 months</option>
                                    <option value="18">18 months</option>
                                    <option value="24">24 months</option>
                                </select>
                                <small class="text-muted">Choose your preferred lease duration</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-calendar-check me-1 text-primary"></i>Preferred Move-in Date *
                                </label>
                                <input type="date" name="move_in_date" class="form-control form-control-lg" 
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                       value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
                                <small class="text-muted">Select when you'd like to move in</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-comment me-1 text-primary"></i>Additional Notes (Optional)
                                </label>
                                <textarea name="additional_notes" class="form-control" rows="3" 
                                          placeholder="Any special requests or questions about the apartment..."></textarea>
                                <small class="text-muted">Share any special requirements or questions</small>
                            </div>
                            
                            <div class="total-calculation mb-4">
                                <div class="card bg-gradient" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary mb-3">
                                            <i class="fas fa-calculator me-2"></i>Payment Summary
                                        </h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Monthly Rent:</span>
                                            <span class="fw-bold">₦{{ number_format($apartment->amount) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Duration:</span>
                                            <span id="duration-display" class="fw-bold">12 months</span>
                                        </div>
                                        <hr class="my-3">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold text-success fs-6">Total Amount:</span>
                                            <span id="total-amount" class="fw-bold text-success fs-4">₦{{ number_format($apartment->amount * 12) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle fa-lg text-info me-3"></i>
                                    <div>
                                        <small class="fw-semibold">Secure Application Process</small>
                                        <div class="small">By applying, you agree to proceed with secure payment to reserve this apartment.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-lg w-100 py-3" style="border-radius: 12px; font-weight: 600;">
                                <i class="fas fa-credit-card me-2"></i>Proceed to Secure Payment
                            </button>
                        </form>
                    @else
                        <!-- Unauthenticated User - Show Application Preview Form -->
                        <div class="unauthenticated-application">
                            <div class="mb-4 text-center">
                                <div class="mb-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 50%; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                    <i class="fas fa-user-lock fa-2x text-primary"></i>
                                </div>
                                <h6 class="text-primary fw-semibold">Preview Your Application</h6>
                                <p class="small text-muted mb-0">
                                    Configure your rental preferences below, then create an account or login to complete your secure application.
                                </p>
                            </div>
                            
                            <!-- Application Preview Form -->
                            <form id="unauthenticatedApplicationForm" class="mb-4">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-calendar me-1 text-primary"></i>Preferred Lease Duration *
                                    </label>
                                    <select id="unauthDurationSelect" class="form-select form-select-lg">
                                        <option value="6">6 months</option>
                                        <option value="12" selected>12 months</option>
                                        <option value="18">18 months</option>
                                        <option value="24">24 months</option>
                                    </select>
                                    <small class="text-muted">Choose your preferred lease duration</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-calendar-check me-1 text-primary"></i>Preferred Move-in Date *
                                    </label>
                                    <input type="date" id="unauthMoveInDate" class="form-control form-control-lg" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                           value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                                    <small class="text-muted">Select when you'd like to move in</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-comment me-1 text-primary"></i>Additional Notes (Optional)
                                    </label>
                                    <textarea id="unauthNotes" class="form-control" rows="3" 
                                              placeholder="Any special requests or questions about the apartment..."></textarea>
                                    <small class="text-muted">Share any special requirements or questions</small>
                                </div>
                            </form>
                            
                            <!-- Payment Summary for Unauthenticated Users -->
                            <div class="total-calculation mb-4">
                                <div class="card bg-gradient" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary mb-3">
                                            <i class="fas fa-calculator me-2"></i>Payment Summary
                                        </h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Monthly Rent:</span>
                                            <span class="fw-bold">₦{{ number_format($apartment->amount) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Duration:</span>
                                            <span id="unauth-duration-display" class="fw-bold">12 months</span>
                                        </div>
                                        <hr class="my-3">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold text-success fs-6">Total Amount:</span>
                                            <span id="unauth-total-amount" class="fw-bold text-success fs-4">₦{{ number_format($apartment->amount * 12) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-3">
                                <button type="button" id="proceedToRegisterBtn" class="btn btn-success btn-lg py-3" style="border-radius: 12px; font-weight: 600;">
                                    <i class="fas fa-user-plus me-2"></i>Create Account & Apply Now
                                </button>
                                <button type="button" id="proceedToLoginBtn" class="btn btn-outline-primary btn-lg py-3" style="border-radius: 12px; font-weight: 600;">
                                    <i class="fas fa-sign-in-alt me-2"></i>Already Have Account? Login
                                </button>
                            </div>
                            
                            <div class="alert alert-info border-0 mt-3" style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-save fa-lg text-info me-3"></i>
                                    <div>
                                        <small class="fw-semibold">Your Preferences Are Saved</small>
                                        <div class="small">Your application preferences will be automatically filled when you return after creating an account or logging in.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
            
            <!-- Additional Info -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-shield-alt me-2 text-success"></i>Secure & Protected
                    </h6>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li><i class="fas fa-check text-success me-2"></i>SSL encrypted payments</li>
                        <li><i class="fas fa-check text-success me-2"></i>Verified landlord</li>
                        <li><i class="fas fa-check text-success me-2"></i>24/7 support available</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Mobile-First Responsive Enhancements */
@media (max-width: 768px) {
    .container {
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .card {
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
    
    .card-header {
        padding: 20px;
        border-radius: 16px 16px 0 0;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .property-gallery .carousel-item img {
        height: 250px !important;
        border-radius: 12px;
    }
    
    .form-control, .form-select {
        padding: 12px 16px;
        border-radius: 12px;
        border: 2px solid #e9ecef;
        font-size: 16px; /* Prevents zoom on iOS */
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .btn-lg {
        padding: 16px 24px;
        font-size: 18px;
        border-radius: 12px;
    }
    
    .sticky-top {
        position: relative !important; /* Remove sticky on mobile for better UX */
    }
    
    /* Touch-friendly spacing */
    .mb-3 {
        margin-bottom: 1.5rem !important;
    }
    
    /* Better text readability */
    .small {
        font-size: 14px !important;
    }
    
    /* Improved button spacing */
    .d-grid.gap-3 > * {
        margin-bottom: 12px;
    }
    
    /* Enhanced alert styling */
    .alert {
        border-radius: 12px;
        padding: 16px;
    }
    
    /* Better badge styling */
    .badge {
        padding: 6px 12px;
        border-radius: 8px;
    }
    
    /* Improved list styling */
    .list-unstyled li {
        padding: 8px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .list-unstyled li:last-child {
        border-bottom: none;
    }
}

/* Enhanced hover effects for desktop */
@media (min-width: 769px) {
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
}

/* Loading states */
.btn.loading {
    position: relative;
    color: transparent !important;
}

.btn.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    margin-left: -10px;
    margin-top: -10px;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Enhanced form styling */
.form-floating > label {
    font-weight: 500;
    color: #6c757d;
}

.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:not(:placeholder-shown) ~ label {
    color: #007bff;
}

/* Better visual hierarchy */
.text-primary {
    color: #007bff !important;
}

.fw-semibold {
    font-weight: 600 !important;
}

/* Improved spacing for mobile */
@media (max-width: 576px) {
    .py-5 {
        padding-top: 2rem !important;
        padding-bottom: 2rem !important;
    }
    
    .col-lg-4 {
        margin-top: 2rem;
    }
}
</style>

<script>
// Calculate total amount dynamically for authenticated users
@auth
if (document.getElementById('durationSelect')) {
    document.getElementById('durationSelect').addEventListener('change', function() {
        const duration = parseInt(this.value);
        const monthlyRent = {{ $apartment->amount }};
        const total = monthlyRent * duration;
        
        document.getElementById('duration-display').textContent = duration + ' months';
        document.getElementById('total-amount').textContent = '₦' + total.toLocaleString();
    });
}

// Form submission handling for authenticated users
if (document.getElementById('applicationForm')) {
    document.getElementById('applicationForm').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        submitBtn.disabled = true;
    });
}
@endauth

// Handle unauthenticated user application form
@guest
// Calculate total amount dynamically for unauthenticated users
document.getElementById('unauthDurationSelect').addEventListener('change', function() {
    const duration = parseInt(this.value);
    const monthlyRent = {{ $apartment->amount }};
    const total = monthlyRent * duration;
    
    document.getElementById('unauth-duration-display').textContent = duration + ' months';
    document.getElementById('unauth-total-amount').textContent = '₦' + total.toLocaleString();
});

// Store application data in session and redirect to login
document.getElementById('proceedToLoginBtn').addEventListener('click', function() {
    storeApplicationDataAndRedirect('{{ route("login") }}');
});

// Store application data in session and redirect to register
document.getElementById('proceedToRegisterBtn').addEventListener('click', function() {
    storeApplicationDataAndRedirect('{{ route("register") }}');
});

function storeApplicationDataAndRedirect(redirectUrl) {
    const applicationData = {
        duration: document.getElementById('unauthDurationSelect').value,
        move_in_date: document.getElementById('unauthMoveInDate').value,
        additional_notes: document.getElementById('unauthNotes').value,
        apartment_id: {{ $apartment->id }},
        property_name: '{{ $property->prop_name }}',
        monthly_rent: {{ $apartment->amount }},
        landlord_id: {{ $landlord->user_id }},
        invitation_token: '{{ $invitation->invitation_token }}',
        stored_at: new Date().toISOString()
    };
    
    // Store in session storage for client-side persistence
    sessionStorage.setItem('easyrent_application_data', JSON.stringify(applicationData));
    
    // Also send to server to store in session
    fetch('{{ route("apartment.invite.store-session") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            token: '{{ $invitation->invitation_token }}',
            application_data: applicationData
        })
    }).then(response => {
        // Redirect regardless of server response to ensure user flow continues
        window.location.href = redirectUrl + '?invitation_redirect=true&token={{ $invitation->invitation_token }}';
    }).catch(error => {
        console.log('Session storage failed, but continuing with redirect:', error);
        // Still redirect even if server storage fails
        window.location.href = redirectUrl + '?invitation_redirect=true&token={{ $invitation->invitation_token }}';
    });
}

// Auto-populate form if returning from failed login/registration
document.addEventListener('DOMContentLoaded', function() {
    const storedData = sessionStorage.getItem('easyrent_application_data');
    if (storedData) {
        try {
            const data = JSON.parse(storedData);
            if (data.invitation_token === '{{ $invitation->invitation_token }}') {
                document.getElementById('unauthDurationSelect').value = data.duration || '12';
                document.getElementById('unauthMoveInDate').value = data.move_in_date || '{{ date("Y-m-d", strtotime("+7 days")) }}';
                document.getElementById('unauthNotes').value = data.additional_notes || '';
                
                // Trigger change event to update totals
                document.getElementById('unauthDurationSelect').dispatchEvent(new Event('change'));
            }
        } catch (e) {
            console.log('Error parsing stored application data:', e);
        }
    }
});
@endguest
</script>
@endsection
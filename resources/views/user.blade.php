
    @include('header')
<div class="content">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-user mt-5">
                <!-- <div class="image">
                    <img src="{{ asset('assets/img/damir-bosnjak.jpg') }}" alt="...">
                </div> -->
                <div class="card-body">
                    <div class="author text-center">
                        <input id="profile-photo-input" type="file" class="d-none" name="photo" accept="image/*" onchange="previewProfilePhoto(event)">
                        <div style="display:inline-block; cursor:pointer;text-align: -webkit-center;" onclick="document.getElementById('profile-photo-input').click()">
                            <img class="avatar border-gray user-img" id="profile-photo-preview" src="{{ auth()->user()->photo ? asset(auth()->user()->photo) : asset('assets/images/default-avatar.png') }}" alt="..." style="object-fit:cover; border-radius:50%; max-width:120px; max-height:120px;">
                            <div class="text-muted" style="font-size:0.9em;">Click to change photo</div>
                            <small class="form-text text-muted">Upload a new profile photo (optional)</small>
                            <small class="form-text text-muted">Allowed file types: jpeg, png, jpg, gif, svg. Max size: 2MB.</small>
                        </div>
                    </div>
                    <p class="description text-center">
                        {{ auth()->user()->bio ?? "Welcome to your profile!" }}
                    </p>
                </div>
                <div class="card-footer">
                    <hr>
                    <div class="mb-3">
                        <label class="text-center d-block"><strong>Your Referral Link:</strong></label>
                        
                        <!-- @.  php
                            $userRoles = [];
                            $currentRole = auth()->user()->role;
                            
                            // Check if user is a marketer (role 3)
                            if ($currentRole == 3) {
                                $userRoles[] = ['id' => 3, 'name' => 'Marketer', 'param' => 'marketer'];
                            }
                            
                            // Check if user is a landlord (role 2 or has properties)
                            if ($currentRole == 2 || \App\Models\Property::where('user_id', auth()->user()->user_id)->exists()) {
                                $userRoles[] = ['id' => 2, 'name' => 'Landlord', 'param' => 'landlord'];
                            }
                            
                            // Check if user is a property manager (role 6)
                            if ($currentRole == 6) {
                                $userRoles[] = ['id' => 6, 'name' => 'Property Manager', 'param' => 'property_manager'];
                            }
                            
                            // Check if user is a tenant (role 1)
                            if ($currentRole == 1) {
                                $userRoles[] = ['id' => 1, 'name' => 'Tenant', 'param' => 'tenant'];
                            }
                            
                            // If no specific roles, add default
                            if (empty($userRoles)) {
                                $userRoles[] = ['id' => $currentRole, 'name' => 'User', 'param' => 'user'];
                            }
                        @.  endphp -->
                        
                        <!-- @    if(count($userRoles) > 1)
                            <div class="referral-role-selector mb-3">
                                <label class="d-block text-center mb-2"><small>Share as:</small></label>
                                <div class="btn-group btn-group-toggle d-flex justify-content-center flex-wrap" data-toggle="buttons">
                                    @. foreach($userRoles as $index => $role)
                                        <label class="btn btn-sm btn-outline-primary { { $index === 0 ? 'active' : '' } }" style="margin: 2px;">
                                            <input type="radio" name="referral_role" value="{ { $role['param'] } }" { {  $index === 0 ? 'checked' : '' }} onchange="updateReferralLink()">
                                            <i class="nc-icon nc-single-02"></i> { { $role['name'] } }
                                        </label>
                                    @.  endforeach
                                </div>
                            </div>
                        @ endif -->
                        
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ auth()->user()->getReferralLink() }}" readonly id="referralLinkInput">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" onclick="copyReferralLink()" title="Copy to clipboard">
                                    <i class="nc-icon nc-single-copy-04"></i> Copy
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted text-center d-block mt-2">
                            <span id="referralRoleText">Share this link to invite new users and earn rewards!</span>
                        </small>
                    </div>
                    
                    <style>
                    .referral-role-selector .btn-outline-primary {
                        border-color: #28a745;
                        color: #28a745;
                    }
                    
                    .referral-role-selector .btn-outline-primary.active {
                        background-color: #28a745;
                        color: white;
                    }
                    
                    .referral-role-selector .btn-outline-primary:hover {
                        background-color: #28a745;
                        color: white;
                    }
                    
                    #referralLinkInput {
                        font-size: 0.85rem;
                        background-color: #f4f3ef;
                    }
                    </style>
                    
                    <script>
                    function updateReferralLink() {
                        const selectedRole = document.querySelector('input[name="referral_role"]:checked');
                        if (!selectedRole) return;
                        
                        const roleParam = selectedRole.value;
                        const baseUrl = "{{ url('/register') }}";
                        const referralCode = "{{ auth()->user()->referral_code ?? auth()->user()->user_id }}";
                        
                        // Build the referral link with role parameter
                        const referralLink = `${baseUrl}?ref=${referralCode}&source=${roleParam}`;
                        
                        // Update the input field
                        document.getElementById('referralLinkInput').value = referralLink;
                        
                        // Update the description text
                        const roleTexts = {
                            'marketer': 'Share as a Marketer to earn commissions on referred landlords!',
                            'landlord': 'Share as a Landlord to invite other property owners!',
                            'property_manager': 'Share as a Property Manager to grow your network!',
                            'tenant': 'Share as a Tenant to help others find great properties!',
                            'user': 'Share this link to invite new users and earn rewards!'
                        };
                        
                        document.getElementById('referralRoleText').textContent = roleTexts[roleParam] || roleTexts['user'];
                    }
                    
                    function copyReferralLink() {
                        const copyText = document.getElementById('referralLinkInput');
                        copyText.select();
                        copyText.setSelectionRange(0, 99999); // For mobile devices
                        
                        // Modern clipboard API
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(copyText.value).then(function() {
                                showToast('Referral link copied to clipboard!', 'success');
                            }).catch(function() {
                                // Fallback
                                document.execCommand('copy');
                                showToast('Referral link copied!', 'success');
                            });
                        } else {
                            // Fallback for older browsers
                            document.execCommand('copy');
                            showToast('Referral link copied!', 'success');
                        }
                    }
                    
                    // Initialize the referral link on page load
                    document.addEventListener('DOMContentLoaded', function() {
                        updateReferralLink();
                    });
                    </script>
                    <div class="button-container">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-6 ml-auto">
                                <h5>{{ auth()->user()->user_id }}<br><small>User ID</small></h5>
                            </div>
                            <div class="col-lg-6 col-md-6 col-6 ml-auto mr-auto">
                                <h5>
                                    <span class="badge badge-success">Active</span>
                                    <br><small>Status</small>
                                </h5>
                            </div>
                        </div>
                        <!-- @.  if(isset($profile) && auth()->id() !== $profile->id)
                            <div class="row mt-3">
                                <div class="col text-center">
                                    <a href="{ { url('/messages/compose?to=' . $profile->user_id) }. }" class="btn btn-success">
                                        <i class="fa fa-envelope"></i> Message
                                    </a>
                                </div>
                            </div>
                        @.  endif -->
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-user">
                <div class="card-header">
                    <h5 class="card-title">Edit Profile</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <form method="POST" action="{{ url('/user/update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-5 pr-1">
                                <div class="form-group">
                                    <label>Role</label>
                                    <input type="text" class="form-control" disabled value="{{ $roles[auth()->user()->role] ?? 'Unknown' }}">
                                </div>
                            </div>
                            <div class="col-md-3 px-1">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" class="form-control" name="username" value="{{ auth()->user()->username }}">
                                </div>
                            </div>
                            <div class="col-md-4 pl-1">
                                <div class="form-group">
                                    <label>Email address</label>
                                    <input type="email" class="form-control" disabled value="{{ auth()->user()->email }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 pr-1">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" class="form-control" name="first_name" value="{{ auth()->user()->first_name }}">
                                </div>
                            </div>
                            <div class="col-md-6 pl-1">
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" class="form-control" name="last_name" value="{{ auth()->user()->last_name }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 pr-1">
                                <div class="form-group">
                                    <label>Occupation</label>
                                    <input type="text" class="form-control" disabled value="{{ auth()->user()->occupation }}">
                                </div>
                            </div>
                            <div class="col-md-6 pl-1">
                                <div class="form-group">
                                    <label>Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" value="{{ auth()->user()->phone }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Address</label>
                                    <input type="text" class="form-control" name="address" value="{{ auth()->user()->address }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 pr-1">
                                <div class="form-group">
                                    <label>L.G.A</label>
                                    <input type="text" class="form-control" name="lga" value="{{ auth()->user()->lga }}">
                                </div>
                            </div>
                            <div class="col-md-6 pl-1">
                                <div class="form-group">
                                    <label>State</label>
                                    <input type="text" class="form-control" name="state" value="{{ auth()->user()->state }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong>Note:</strong> Leave password fields empty if you don't want to change your password.
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 pr-1">
                                <div class="form-group position-relative">
                                    <label>New Password</label>
                                    <input type="password" class="form-control" name="password" id="new-password">
                                    <button type="button" class="password-toggle-btn-user" onclick="togglePasswordVisibility('new-password')">
                                        <i class="fas fa-eye" id="new-password-toggle-icon"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 pl-1">
                                <div class="form-group position-relative">
                                    <label>Confirm New Password</label>
                                    <input type="password" class="form-control" name="password_confirmation" id="confirm-password">
                                    <button type="button" class="password-toggle-btn-user" onclick="togglePasswordVisibility('confirm-password')">
                                        <i class="fas fa-eye" id="confirm-password-toggle-icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div> 
                        <div class="row">
                            <div class="update ml-auto mr-auto">
                                <button type="submit" class="btn btn-primary btn-round">Update Profile</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Recent Activity</h5>
                </div>
                <div class="card-body">
                    @php
                        $activities = auth()->user()->activityLogs()->latest()->take(10)->get();
                    @endphp
                    @if($activities->isEmpty())
                        <p class="text-muted">No recent activity.</p>
                    @else
                        <ul class="list-group">
                            @foreach($activities as $activity)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</span>
                                    <span class="text-muted small">{{ $activity->created_at->diffForHumans() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Footer area end -->
<style>
.password-toggle-btn-user {
    position: absolute;
    right: 10px;
    top: 35px;
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 5px;
    z-index: 10;
    transition: color 0.3s ease;
}

.password-toggle-btn-user:hover {
    color: #28a745;
}

.password-toggle-btn-user:focus {
    outline: none;
    color: #28a745;
}
</style>

<script>
// Password visibility toggle function
function togglePasswordVisibility(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(fieldId + '-toggle-icon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

function previewProfilePhoto(event) {
    const input = event.target;
    const img = document.getElementById('profile-photo-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@include('footer')
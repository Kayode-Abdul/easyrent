
    @include('header')
<div class="content">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-user">
                <div class="image">
                    <img src="{{ asset('assets/img/damir-bosnjak.jpg') }}" alt="...">
                </div>
                <div class="card-body">
                    <div class="author text-center">
                        <input id="profile-photo-input" type="file" class="d-none" name="photo" accept="image/*" onchange="previewProfilePhoto(event)">
                        <div style="display:inline-block; cursor:pointer;" onclick="document.getElementById('profile-photo-input').click()">
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
                    <div class="mb-3 text-center">
                        <label><strong>Your Referral Link:</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ auth()->user()->getReferralLink() }}" readonly id="referralLinkInput">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyReferralLink()">Copy</button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Share this link to invite new users and earn rewards!</small>
                    </div>
                    <script>
                    function copyReferralLink() {
                        var copyText = document.getElementById('referralLinkInput');
                        copyText.select();
                        copyText.setSelectionRange(0, 99999); // For mobile devices
                        document.execCommand('copy');
                        alert('Referral link copied!');
                    }
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
                        @if(isset($profile) && auth()->id() !== $profile->id)
                            <div class="row mt-3">
                                <div class="col text-center">
                                    <a href="{{ url('/messages/compose?to=' . $profile->user_id) }}" class="btn btn-success">
                                        <i class="fa fa-envelope"></i> Message
                                    </a>
                                </div>
                            </div>
                        @endif
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
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" class="form-control" name="password">
                                </div>
                            </div>
                            <div class="col-md-6 pl-1">
                                <div class="form-group">
                                    <label>Confirm New Password</label>
                                    <input type="password" class="form-control" name="password_confirmation">
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
<script>
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
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&color=7F9CF5&background=EBF4FF" 
                             alt="Avatar" class="rounded-circle" width="100">
                        <div class="mt-3">
                            <h4>{{ auth()->user()->name }}</h4>
                            <p class="text-secondary mb-1">Marketer</p>
                            <p class="text-muted font-size-sm">{{ $profile->business_name ?? 'No business name' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="d-flex align-items-center mb-3">
                        <i class="material-icons text-info mr-2">assignment</i>Quick Stats
                    </h6>
                    <small>Commission Rate</small>
                    <div class="progress mb-3" style="height: 5px">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ auth()->user()->commission_rate * 10 }}%" aria-valuenow="{{ auth()->user()->commission_rate }}" aria-valuemin="0" aria-valuemax="10"></div>
                    </div>
                    <small class="text-muted">{{ auth()->user()->commission_rate }}%</small>
                    
                    <hr>
                    
                    <small>KYC Status</small>
                    <div class="mt-2">
                        @if($profile && $profile->kyc_status === 'verified')
                            <span class="badge badge-success">Verified</span>
                        @elseif($profile && $profile->kyc_status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @else
                            <span class="badge badge-danger">Not Submitted</span>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <small>Marketer Status</small>
                    <div class="mt-2">
                        @if(auth()->user()->marketer_status === 'approved')
                            <span class="badge badge-success">Approved</span>
                        @elseif(auth()->user()->marketer_status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @else
                            <span class="badge badge-secondary">{{ ucfirst(auth()->user()->marketer_status) }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Marketer Profile</h5>
                    @if($profile)
                        <a href="{{ route('marketer.profile.edit') }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    @else
                        <a href="{{ route('marketer.profile.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Complete Profile
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($profile)
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Personal Information</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Full Name:</strong></td>
                                        <td>{{ auth()->user()->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>{{ auth()->user()->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td>{{ auth()->user()->phone }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Referral Code:</strong></td>
                                        <td>
                                            <code>{{ auth()->user()->referral_code }}</code>
                                            <button class="btn btn-sm btn-outline-primary ml-2" onclick="copyToClipboard('{{ auth()->user()->referral_code }}')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Business Information</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Business Name:</strong></td>
                                        <td>{{ $profile->business_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Registration No:</strong></td>
                                        <td>{{ $profile->business_registration }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Business Type:</strong></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $profile->business_type)) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Experience:</strong></td>
                                        <td>{{ $profile->experience_years }} years</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Contact Details</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Address:</strong></td>
                                        <td>{{ $profile->address }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Website:</strong></td>
                                        <td>
                                            @if($profile->website)
                                                <a href="{{ $profile->website }}" target="_blank">{{ $profile->website }}</a>
                                            @else
                                                <span class="text-muted">Not provided</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Marketing Information</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Target Market:</strong></td>
                                        <td>{{ ucfirst($profile->target_market) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Marketing Channels:</strong></td>
                                        <td>
                                            @if($profile->marketing_channels)
                                                @foreach(json_decode($profile->marketing_channels) as $channel)
                                                    <span class="badge badge-info mr-1">{{ ucfirst(str_replace('_', ' ', $channel)) }}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        @if(auth()->user()->bank_name)
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6>Banking Information</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Bank Name:</strong></td>
                                            <td>{{ auth()->user()->bank_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Account Number:</strong></td>
                                            <td>{{ auth()->user()->account_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Account Name:</strong></td>
                                            <td>{{ auth()->user()->account_name }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @endif
                        
                        @if($profile->kyc_documents)
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6>KYC Documents</h6>
                                    <div class="row">
                                        @foreach(json_decode($profile->kyc_documents, true) as $type => $path)
                                            <div class="col-md-3">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                                                        <p class="card-text">{{ ucfirst(str_replace('_', ' ', $type)) }}</p>
                                                        <a href="{{ asset($path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                            <h5>Complete Your Profile</h5>
                            <p class="text-muted">Please complete your marketer profile to start earning commissions.</p>
                            <a href="{{ route('marketer.profile.create') }}" class="btn btn-primary">Complete Profile</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Referral code copied to clipboard!');
    });
}
</script>
@endsection

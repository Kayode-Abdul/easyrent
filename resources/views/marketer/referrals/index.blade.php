@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Referrals</h5>
                    <div>
                        <!-- Filter Dropdown -->
                        <div class="btn-group mr-2">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                                Filter by Status
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('marketer.referrals.index') }}">All Referrals</a>
                                <a class="dropdown-item" href="{{ route('marketer.referrals.index', ['status' => 'pending']) }}">Pending</a>
                                <a class="dropdown-item" href="{{ route('marketer.referrals.index', ['status' => 'approved']) }}">Approved</a>
                                <a class="dropdown-item" href="{{ route('marketer.referrals.index', ['status' => 'paid']) }}">Paid</a>
                                <a class="dropdown-item" href="{{ route('marketer.referrals.index', ['status' => 'rejected']) }}">Rejected</a>
                            </div>
                        </div>
                        
                        <a href="{{ route('marketer.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $stats['total'] }}</h4>
                                    <p class="mb-0">Total Referrals</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $stats['pending'] }}</h4>
                                    <p class="mb-0">Pending</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>{{ $stats['approved'] }}</h4>
                                    <p class="mb-0">Approved</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>KSh {{ number_format($stats['total_commission']) }}</h4>
                                    <p class="mb-0">Total Commission</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Referrals Table -->
                    @if($referrals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Landlord</th>
                                        <th>Contact</th>
                                        <th>Campaign</th>
                                        <th>Registration Date</th>
                                        <th>Commission</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($referrals as $referral)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($referral->referred->name) }}&color=7F9CF5&background=EBF4FF" 
                                                         alt="Avatar" class="rounded-circle mr-2" width="40" height="40">
                                                    <div>
                                                        <strong>{{ $referral->referred->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">ID: #{{ $referral->referred->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <i class="fas fa-envelope text-muted"></i> {{ $referral->referred->email }}
                                                    <br>
                                                    <i class="fas fa-phone text-muted"></i> {{ $referral->referred->phone ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($referral->campaign)
                                                    <div>
                                                        <strong>{{ $referral->campaign->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            <code>{{ $referral->campaign->campaign_code }}</code>
                                                        </small>
                                                        <br>
                                                        @if($referral->referral_source === 'qr_code')
                                                            <span class="badge badge-info badge-sm">QR Scan</span>
                                                        @else
                                                            <span class="badge badge-success badge-sm">Link Click</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">Direct Referral</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($referral->conversion_date)
                                                    <strong>{{ $referral->conversion_date->format('M d, Y') }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $referral->conversion_date->format('h:i A') }}</small>
                                                @else
                                                    <span class="text-muted">Not completed</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong class="text-success">KSh {{ number_format($referral->commission_amount ?? ($referral->reward->amount ?? 0)) }}</strong>
                                                <br>
                                                <small class="text-muted">{{ auth()->user()->commission_rate }}%</small>
                                            </td>
                                            <td>
                                                @switch($referral->commission_status)
                                                    @case('pending')
                                                        <span class="badge badge-warning">Pending Review</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge badge-success">Approved</span>
                                                        @break
                                                    @case('paid')
                                                        <span class="badge badge-primary">Paid</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge badge-danger">Rejected</span>
                                                        @break
                                                @endswitch
                                                
                                                @if($referral->commission_status === 'paid' && $referral->reward && $referral->reward->paid_at)
                                                    <br>
                                                    <small class="text-muted">{{ $referral->reward->paid_at->format('M d, Y') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewReferralDetails({{ $referral->id }})" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    @if($referral->commission_status === 'pending')
                                                        <button class="btn btn-sm btn-outline-info" 
                                                                onclick="contactSupport('{{ $referral->id }}')" title="Contact Support">
                                                            <i class="fas fa-question-circle"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($referral->campaign)
                                                        <a href="{{ route('marketer.campaigns.show', $referral->campaign->id) }}" 
                                                           class="btn btn-sm btn-outline-secondary" title="View Campaign">
                                                            <i class="fas fa-bullhorn"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $referrals->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No Referrals Yet</h5>
                            <p class="text-muted">
                                @if(request('status'))
                                    No referrals found with status: <strong>{{ ucfirst(request('status')) }}</strong>
                                @else
                                    Start sharing your referral links to attract landlords and earn commissions.
                                @endif
                            </p>
                            <div class="mt-3">
                                <a href="{{ route('marketer.campaigns.index') }}" class="btn btn-primary mr-2">
                                    <i class="fas fa-bullhorn"></i> View Campaigns
                                </a>
                                <a href="{{ route('marketer.campaigns.create') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-plus"></i> Create Campaign
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Referral Details Modal -->
<div class="modal fade" id="referralModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Referral Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="referralDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewReferralDetails(referralId) {
    $('#referralModal').modal('show');
    
    fetch(`/marketer/referrals/${referralId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const referral = data.referral;
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Landlord Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>${referral.referred.name}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>${referral.referred.email}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>${referral.referred.phone || 'Not provided'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Registration:</strong></td>
                                    <td>${new Date(referral.referred.created_at).toLocaleDateString()}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Referral Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Referral Code:</strong></td>
                                    <td><code>${referral.referral_code}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Source:</strong></td>
                                    <td>${referral.referral_source === 'qr_code' ? 'QR Code Scan' : 'Referral Link'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Campaign:</strong></td>
                                    <td>${referral.campaign ? referral.campaign.name : 'Direct Referral'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Conversion Date:</strong></td>
                                    <td>${referral.conversion_date ? new Date(referral.conversion_date).toLocaleDateString() : 'Not completed'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2">Commission Details</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h5 class="text-success">KSh ${referral.commission_amount.toLocaleString()}</h5>
                                        <small class="text-muted">Commission Amount</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h5 class="text-info">${referral.commission_percentage}%</h5>
                                        <small class="text-muted">Commission Rate</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <span class="badge badge-${getStatusColor(referral.commission_status)} badge-lg">${referral.commission_status.charAt(0).toUpperCase() + referral.commission_status.slice(1)}</span>
                                        <br><small class="text-muted">Status</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h5>${referral.reward ? new Date(referral.reward.created_at).toLocaleDateString() : 'N/A'}</h5>
                                        <small class="text-muted">Reward Created</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('referralDetailsContent').innerHTML = content;
            } else {
                document.getElementById('referralDetailsContent').innerHTML = 
                    '<p class="text-danger">Error loading referral details</p>';
            }
        })
        .catch(error => {
            document.getElementById('referralDetailsContent').innerHTML = 
                '<p class="text-danger">Error loading referral details</p>';
        });
}

function getStatusColor(status) {
    switch(status) {
        case 'pending': return 'warning';
        case 'approved': return 'success';
        case 'paid': return 'primary';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}

function contactSupport(referralId) {
    const message = `Hello, I need help with my referral #${referralId}. Please review the commission status.`;
    const subject = `Support Request - Referral #${referralId}`;
    const email = 'support@easyrent.com';
    
    window.location.href = `mailto:${email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(message)}`;
}
</script>
{{-- TODO: Replace JS usage of referral.commission_amount with reward.amount after backend migration --}}
@endsection

@extends('layout')

@section('title', 'Marketer Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fas fa-chart-line text-success me-2"></i>
                                Marketer Dashboard
                            </h4>
                            <p class="text-muted mb-0">Welcome back, {{ $marketer->first_name }}! Track your referrals and earnings.</p>
                        </div>
                        <div>
                            @if($marketer->marketer_status === 'pending')
                                <span class="badge bg-warning fs-6">
                                    <i class="fas fa-clock"></i> Pending Approval
                                </span>
                            @elseif($marketer->marketer_status === 'active')
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-check-circle"></i> Active Marketer
                                </span>
                            @else
                                <span class="badge bg-danger fs-6">
                                    <i class="fas fa-times-circle"></i> {{ ucfirst($marketer->marketer_status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($marketer->marketer_status === 'pending')
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Application Under Review:</strong> Your marketer application is being reviewed by our team. 
                    You'll be notified once approved. 
                    @if(!$profile)
                        <a href="{{ route('marketer.profile.create') }}" class="alert-link">Complete your profile</a> to speed up the process.
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Referrals</h6>
                            <h3 class="mb-0">{{ $stats['total_referrals'] ?? 0 }}</h3>
                            <small class="opacity-75">All time</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Successful Referrals</h6>
                            <h3 class="mb-0">{{ $stats['successful_referrals'] ?? 0 }}</h3>
                            <small class="opacity-75">Landlord registrations</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Earnings</h6>
                            <h3 class="mb-0">₦{{ number_format($stats['total_commission'] ?? 0, 0) }}</h3>
                            <small class="opacity-75">Paid commissions</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Pending Commission</h6>
                            <h3 class="mb-0">₦{{ number_format($stats['pending_commission'] ?? 0, 0) }}</h3>
                            <small class="opacity-75">Awaiting payment</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        Conversion Rate
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 text-primary">{{ $stats['conversion_rate'] ?? 0 }}%</div>
                    <p class="text-muted mb-0">Clicks to registrations</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-mouse-pointer text-info me-2"></i>
                        Total Clicks
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 text-info">{{ $stats['total_clicks'] ?? 0 }}</div>
                    <p class="text-muted mb-0">Link clicks</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-exchange-alt text-success me-2"></i>
                        Conversions
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 text-success">{{ $stats['total_conversions'] ?? 0 }}</div>
                    <p class="text-muted mb-0">Successful signups</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Hierarchy Section -->
    @if($referringSuperMarketer || !empty($referralChain))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-sitemap text-info me-2"></i>
                        Referral Hierarchy
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($referringSuperMarketer)
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user-tie me-2"></i>
                                        Your Super Marketer
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-circle bg-info text-white me-3">
                                            {{ strtoupper(substr($referringSuperMarketer->first_name, 0, 1) . substr($referringSuperMarketer->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $referringSuperMarketer->first_name }} {{ $referringSuperMarketer->last_name }}</h6>
                                            <small class="text-muted">{{ $referringSuperMarketer->email }}</small>
                                        </div>
                                    </div>
                                    @if($referringSuperMarketer->marketerProfile)
                                    <div class="small text-muted">
                                        <div><strong>Business:</strong> {{ $referringSuperMarketer->marketerProfile->business_name }}</div>
                                        <div><strong>Experience:</strong> {{ $referringSuperMarketer->marketerProfile->years_of_experience }} years</div>
                                    </div>
                                    @endif
                                    <button class="btn btn-sm btn-outline-info mt-2" onclick="viewSuperMarketerDetails()">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chart-network me-2"></i>
                                        Referral Chain Visualization
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="referralChainViz" class="text-center">
                                        <div class="hierarchy-chain">
                                            @if($referringSuperMarketer)
                                            <div class="hierarchy-level">
                                                <div class="hierarchy-node super-marketer">
                                                    <i class="fas fa-user-tie"></i>
                                                    <span>Super Marketer</span>
                                                </div>
                                                <div class="hierarchy-arrow">
                                                    <i class="fas fa-arrow-down"></i>
                                                </div>
                                            </div>
                                            @endif
                                            <div class="hierarchy-level">
                                                <div class="hierarchy-node marketer active">
                                                    <i class="fas fa-user"></i>
                                                    <span>You (Marketer)</span>
                                                </div>
                                                <div class="hierarchy-arrow">
                                                    <i class="fas fa-arrow-down"></i>
                                                </div>
                                            </div>
                                            <div class="hierarchy-level">
                                                <div class="hierarchy-node landlord">
                                                    <i class="fas fa-home"></i>
                                                    <span>Landlords ({{ $stats['successful_referrals'] ?? 0 }})</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-success mt-2" onclick="viewFullChain()">
                                        <i class="fas fa-expand me-1"></i>View Full Chain
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Commission Breakdown Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-calculator text-warning me-2"></i>
                        Commission Breakdown
                    </h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshCommissionBreakdown()">
                        <i class="fas fa-refresh me-1"></i>Refresh
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Current Commission Structure</h6>
                            <div id="commissionStructure">
                                @if(!empty($commissionBreakdown))
                                    @foreach($commissionBreakdown as $tier => $rate)
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                        <span class="fw-medium">
                                            @if($tier === 'super_marketer_rate')
                                                <i class="fas fa-user-tie text-info me-2"></i>Super Marketer
                                            @elseif($tier === 'marketer_rate')
                                                <i class="fas fa-user text-success me-2"></i>Marketer (You)
                                            @elseif($tier === 'regional_manager_rate')
                                                <i class="fas fa-user-cog text-primary me-2"></i>Regional Manager
                                            @else
                                                <i class="fas fa-building text-secondary me-2"></i>{{ ucfirst(str_replace('_', ' ', $tier)) }}
                                            @endif
                                        </span>
                                        <span class="badge bg-primary">{{ number_format($rate, 2) }}%</span>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="text-muted text-center py-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Commission structure will be displayed once you have referrals
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Recent Commission Payments</h6>
                            <div id="recentCommissions">
                                @if($recentPayments->count() > 0)
                                    @foreach($recentPayments->take(5) as $payment)
                                    <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                        <div>
                                            <div class="fw-medium">₦{{ number_format($payment->amount, 0) }}</div>
                                            <small class="text-muted">{{ $payment->created_at->format('M d, Y') }}</small>
                                        </div>
                                        <div class="text-end">
                                            @if($payment->commission_tier)
                                                <div class="badge bg-info mb-1">{{ ucfirst(str_replace('_', ' ', $payment->commission_tier)) }}</div>
                                            @endif
                                            <div>
                                                @if($payment->status === 'completed')
                                                    <span class="badge bg-success">Paid</span>
                                                @elseif($payment->status === 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    <div class="text-center mt-3">
                                        <a href="{{ route('marketer.payments') }}" class="btn btn-sm btn-outline-primary">
                                            View All Payments
                                        </a>
                                    </div>
                                @else
                                    <div class="text-muted text-center py-3">
                                        <i class="fas fa-credit-card fa-2x mb-2 d-block opacity-25"></i>
                                        <div>No commission payments yet</div>
                                        <small>Start referring landlords to earn commissions</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Performance Trends (Last 12 Months)
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active" data-chart="referrals">Referrals</button>
                        <button class="btn btn-outline-primary" data-chart="commissions">Commissions</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Referrals -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-user-plus text-success me-2"></i>
                        Recent Referrals
                    </h6>
                    <a href="{{ route('marketer.referrals') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentReferrals->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentReferrals->take(5) as $referral)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $referral->referred->first_name }} {{ $referral->referred->last_name }}</h6>
                                        <small class="text-muted">
                                            {{ $referral->referred->email }} • 
                                            {{ $referral->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <div>
                                        @if($referral->referred->role == 2)
                                            <span class="badge bg-success">Landlord</span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($referral->referred->role == 3 ? 'Tenant' : 'User') }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-user-plus fa-3x mb-3 d-block opacity-25"></i>
                            <h6>No Referrals Yet</h6>
                            <p class="small">Start sharing your referral links to see results here</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Active Campaigns -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-bullhorn text-warning me-2"></i>
                        Active Campaigns
                    </h6>
                    <a href="{{ route('marketer.campaigns') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                </div>
                <div class="card-body p-0">
                    @if($activeCampaigns->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($activeCampaigns->take(5) as $campaign)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $campaign->campaign_name }}</h6>
                                            <small class="text-muted">
                                                {{ $campaign->clicks_count }} clicks • 
                                                {{ $campaign->conversions_count }} conversions •
                                                {{ number_format($campaign->conversion_rate, 1) }}% rate
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success">Active</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-bullhorn fa-3x mb-3 d-block opacity-25"></i>
                            <h6>No Active Campaigns</h6>
                            <p class="small">
                                <a href="{{ route('marketer.campaigns.create') }}" class="text-decoration-none">Create your first campaign</a>
                                to start tracking referrals
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Performance Comparison -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar text-info me-2"></i>
                        Performance Comparison
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary active" data-period="30" onclick="loadPerformanceComparison(30)">30d</button>
                        <button class="btn btn-outline-secondary" data-period="90" onclick="loadPerformanceComparison(90)">90d</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="performanceComparison">
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            Loading comparison data...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    @if($marketer->marketer_status === 'active')
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-rocket text-primary me-2"></i>
                            Quick Actions
                        </h6>
                        <div class="row">
                            <div class="col-md-3">
                                <a href="{{ route('marketer.campaigns.create') }}" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-plus me-2"></i>Create Campaign
                                </a>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-success w-100" onclick="copyReferralLink()">
                                    <i class="fas fa-copy me-2"></i>Copy Referral Link
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('marketer.profile') }}" class="btn btn-outline-info w-100">
                                    <i class="fas fa-user me-2"></i>Update Profile
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('marketer.payments') }}" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-credit-card me-2"></i>View Payments
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Hidden input for referral link -->
<input type="hidden" id="referralLink" value="{{ $marketer->getReferralLink() }}">
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}

.hierarchy-chain {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.hierarchy-level {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.hierarchy-node {
    padding: 15px 20px;
    border-radius: 10px;
    text-align: center;
    min-width: 120px;
    border: 2px solid #e9ecef;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.hierarchy-node.super-marketer {
    border-color: #17a2b8;
    background: #e7f3ff;
    color: #17a2b8;
}

.hierarchy-node.marketer.active {
    border-color: #28a745;
    background: #e8f5e8;
    color: #28a745;
    font-weight: bold;
}

.hierarchy-node.landlord {
    border-color: #6c757d;
    background: #f1f3f4;
    color: #6c757d;
}

.hierarchy-node i {
    display: block;
    font-size: 24px;
    margin-bottom: 5px;
}

.hierarchy-arrow {
    color: #6c757d;
    font-size: 18px;
    margin: 5px 0;
}

.commission-tier-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
</style>
<script>


function copyReferralLink() {
    const referralLink = document.getElementById('referralLink').value;
    navigator.clipboard.writeText(referralLink).then(function() {
        // Show success message
        const alert = `
            <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">
                <i class="fas fa-check-circle me-2"></i>Referral link copied to clipboard!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('body').append(alert);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 3000);
    }).catch(function() {
        alert('Failed to copy referral link. Please copy manually: ' + referralLink);
    });
}

function viewSuperMarketerDetails() {
    $.ajax({
        url: '{{ route("marketer.super-marketer-info") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const superMarketer = response.super_marketer;
                const stats = response.referral_stats;
                
                const modalContent = `
                    <div class="modal fade" id="superMarketerModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-user-tie me-2"></i>Super Marketer Details
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-muted mb-3">Contact Information</h6>
                                            <div class="mb-2"><strong>Name:</strong> ${superMarketer.name}</div>
                                            <div class="mb-2"><strong>Email:</strong> ${superMarketer.email}</div>
                                            <div class="mb-2"><strong>Phone:</strong> ${superMarketer.phone || 'Not provided'}</div>
                                            <div class="mb-2"><strong>Joined:</strong> ${superMarketer.joined_date}</div>
                                            ${superMarketer.profile ? `
                                                <hr>
                                                <h6 class="text-muted mb-3">Business Information</h6>
                                                <div class="mb-2"><strong>Business:</strong> ${superMarketer.profile.business_name}</div>
                                                <div class="mb-2"><strong>Type:</strong> ${superMarketer.profile.business_type}</div>
                                                <div class="mb-2"><strong>Experience:</strong> ${superMarketer.profile.years_of_experience} years</div>
                                            ` : ''}
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted mb-3">Your Performance</h6>
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <div class="row text-center">
                                                        <div class="col-6">
                                                            <div class="h4 text-primary">${stats.total_referrals}</div>
                                                            <small class="text-muted">Total Referrals</small>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="h4 text-success">${stats.successful_referrals}</div>
                                                            <small class="text-muted">Successful</small>
                                                        </div>
                                                        <div class="col-6 mt-3">
                                                            <div class="h4 text-info">₦${stats.total_commission.toLocaleString()}</div>
                                                            <small class="text-muted">Total Earned</small>
                                                        </div>
                                                        <div class="col-6 mt-3">
                                                            <div class="h4 text-warning">₦${stats.pending_commission.toLocaleString()}</div>
                                                            <small class="text-muted">Pending</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('body').append(modalContent);
                $('#superMarketerModal').modal('show');
                
                $('#superMarketerModal').on('hidden.bs.modal', function() {
                    $(this).remove();
                });
            }
        },
        error: function() {
            alert('Failed to load Super Marketer details');
        }
    });
}

function viewFullChain() {
    $.ajax({
        url: '{{ route("marketer.referral-chain") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const chain = response.chain;
                
                const modalContent = `
                    <div class="modal fade" id="referralChainModal" tabindex="-1">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-sitemap me-2"></i>Complete Referral Chain
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div id="fullChainVisualization" class="text-center">
                                        ${renderFullChain(chain)}
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('body').append(modalContent);
                $('#referralChainModal').modal('show');
                
                $('#referralChainModal').on('hidden.bs.modal', function() {
                    $(this).remove();
                });
            }
        },
        error: function() {
            alert('Failed to load referral chain');
        }
    });
}

function renderFullChain(chain) {
    // This would render a more detailed visualization of the referral chain
    // For now, return a simple representation
    return `
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Detailed referral chain visualization will be displayed here.
            <br><small>Chain data: ${JSON.stringify(chain)}</small>
        </div>
    `;
}

function refreshCommissionBreakdown() {
    $.ajax({
        url: '{{ route("marketer.commission-breakdown") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                updateCommissionStructure(response.breakdown);
                updateRecentCommissions(response.recent_commissions);
                
                // Show success message
                const alert = `
                    <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999;" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Commission breakdown refreshed!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('body').append(alert);
                
                setTimeout(() => {
                    $('.alert').fadeOut();
                }, 3000);
            }
        },
        error: function() {
            alert('Failed to refresh commission breakdown');
        }
    });
}

function updateCommissionStructure(breakdown) {
    const container = $('#commissionStructure');
    let html = '';
    
    if (Object.keys(breakdown).length > 0) {
        Object.entries(breakdown).forEach(([tier, rate]) => {
            let icon = 'fas fa-building';
            let color = 'secondary';
            let label = tier.replace('_', ' ');
            
            if (tier === 'super_marketer_rate') {
                icon = 'fas fa-user-tie';
                color = 'info';
                label = 'Super Marketer';
            } else if (tier === 'marketer_rate') {
                icon = 'fas fa-user';
                color = 'success';
                label = 'Marketer (You)';
            } else if (tier === 'regional_manager_rate') {
                icon = 'fas fa-user-cog';
                color = 'primary';
                label = 'Regional Manager';
            }
            
            html += `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                    <span class="fw-medium">
                        <i class="${icon} text-${color} me-2"></i>${label}
                    </span>
                    <span class="badge bg-primary">${parseFloat(rate).toFixed(2)}%</span>
                </div>
            `;
        });
    } else {
        html = `
            <div class="text-muted text-center py-3">
                <i class="fas fa-info-circle me-2"></i>
                Commission structure will be displayed once you have referrals
            </div>
        `;
    }
    
    container.html(html);
}

function updateRecentCommissions(commissions) {
    const container = $('#recentCommissions');
    let html = '';
    
    if (commissions.length > 0) {
        commissions.forEach(commission => {
            let statusClass = 'secondary';
            let statusText = commission.status;
            
            if (commission.status === 'completed') {
                statusClass = 'success';
                statusText = 'Paid';
            } else if (commission.status === 'pending') {
                statusClass = 'warning';
                statusText = 'Pending';
            }
            
            html += `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                    <div>
                        <div class="fw-medium">₦${commission.amount.toLocaleString()}</div>
                        <small class="text-muted">${commission.date}</small>
                    </div>
                    <div class="text-end">
                        ${commission.tier ? `<div class="badge bg-info mb-1">${commission.tier.replace('_', ' ')}</div>` : ''}
                        <div>
                            <span class="badge bg-${statusClass}">${statusText}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `
            <div class="text-center mt-3">
                <a href="{{ route('marketer.payments') }}" class="btn btn-sm btn-outline-primary">
                    View All Payments
                </a>
            </div>
        `;
    } else {
        html = `
            <div class="text-muted text-center py-3">
                <i class="fas fa-credit-card fa-2x mb-2 d-block opacity-25"></i>
                <div>No commission payments yet</div>
                <small>Start referring landlords to earn commissions</small>
            </div>
        `;
    }
    
    container.html(html);
}

function loadPerformanceComparison(period = 30) {
    // Update active button
    $('[data-period]').removeClass('active');
    $(`[data-period="${period}"]`).addClass('active');
    
    // Show loading
    $('#performanceComparison').html(`
        <div class="text-center text-muted py-3">
            <i class="fas fa-spinner fa-spin me-2"></i>
            Loading comparison data...
        </div>
    `);
    
    $.ajax({
        url: '{{ route("marketer.performance-comparison") }}',
        method: 'GET',
        data: { period: period },
        success: function(response) {
            if (response.success) {
                renderPerformanceComparison(response);
            }
        },
        error: function() {
            $('#performanceComparison').html(`
                <div class="text-center text-muted py-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load comparison data
                </div>
            `);
        }
    });
}

function renderPerformanceComparison(data) {
    const myStats = data.marketer_stats;
    const avgStats = data.regional_average;
    
    const html = `
        <div class="small mb-3 text-muted text-center">
            vs ${data.comparison_count} marketers in ${data.region}
        </div>
        
        <div class="row text-center">
            <div class="col-6 mb-3">
                <div class="fw-bold text-primary">${myStats.referrals}</div>
                <div class="small text-muted">Your Referrals</div>
                <div class="small ${myStats.referrals >= avgStats.referrals ? 'text-success' : 'text-warning'}">
                    ${myStats.referrals >= avgStats.referrals ? '↑' : '↓'} Avg: ${avgStats.referrals}
                </div>
            </div>
            <div class="col-6 mb-3">
                <div class="fw-bold text-success">${myStats.successful_referrals}</div>
                <div class="small text-muted">Successful</div>
                <div class="small ${myStats.successful_referrals >= avgStats.successful_referrals ? 'text-success' : 'text-warning'}">
                    ${myStats.successful_referrals >= avgStats.successful_referrals ? '↑' : '↓'} Avg: ${avgStats.successful_referrals}
                </div>
            </div>
            <div class="col-6 mb-3">
                <div class="fw-bold text-info">₦${myStats.commission_earned.toLocaleString()}</div>
                <div class="small text-muted">Commission</div>
                <div class="small ${myStats.commission_earned >= avgStats.commission_earned ? 'text-success' : 'text-warning'}">
                    ${myStats.commission_earned >= avgStats.commission_earned ? '↑' : '↓'} Avg: ₦${avgStats.commission_earned.toLocaleString()}
                </div>
            </div>
            <div class="col-6 mb-3">
                <div class="fw-bold text-warning">${myStats.conversion_rate.toFixed(1)}%</div>
                <div class="small text-muted">Conversion</div>
                <div class="small ${myStats.conversion_rate >= avgStats.conversion_rate ? 'text-success' : 'text-warning'}">
                    ${myStats.conversion_rate >= avgStats.conversion_rate ? '↑' : '↓'} Avg: ${avgStats.conversion_rate}%
                </div>
            </div>
        </div>
        
        <div class="text-center mt-2">
            <small class="text-muted">Last ${data.period} days</small>
        </div>
    `;
    
    $('#performanceComparison').html(html);
}

// Load initial performance comparison on page load
$(document).ready(function() {
    // Initialize performance chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceData = @json($performanceData);
    
    let currentChart = 'referrals';
    let chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: performanceData.months,
            datasets: [{
                label: 'Referrals',
                data: performanceData.referrals,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Chart toggle buttons
    $('[data-chart]').on('click', function() {
        const chartType = $(this).data('chart');
        $('[data-chart]').removeClass('active');
        $(this).addClass('active');
        
        if (chartType === 'referrals') {
            chart.data.datasets[0] = {
                label: 'Referrals',
                data: performanceData.referrals,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            };
        } else {
            chart.data.datasets[0] = {
                label: 'Commissions (₦)',
                data: performanceData.commissions,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            };
        }
        
        chart.update();
    });
    
    // Load performance comparison after a short delay
    setTimeout(() => {
        loadPerformanceComparison(30);
    }, 1000);
});
</script>
@endsection

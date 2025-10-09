@extends('layout')
@section('content')
<div class="content">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                @if($marketer->photo)
                                    <img src="{{ asset('storage/' . $marketer->photo) }}" 
                                         alt="{{ $marketer->first_name }}" 
                                         class="rounded-circle" width="60" height="60">
                                @else
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 60px; height: 60px;">
                                        <span class="text-white h4 mb-0">
                                            {{ substr($marketer->first_name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h4 class="mb-1">
                                    {{ $marketer->first_name }} {{ $marketer->last_name }}
                                    <span class="badge bg-{{ $marketer->marketer_status === 'active' ? 'success' : 'warning' }} ms-2">
                                        {{ ucfirst($marketer->marketer_status) }}
                                    </span>
                                </h4>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-envelope me-1"></i>{{ $marketer->email }}
                                    @if($marketer->phone)
                                        <span class="ms-3"><i class="fas fa-phone me-1"></i>{{ $marketer->phone }}</span>
                                    @endif
                                </p>
                                @if($marketer->marketerProfile && $marketer->marketerProfile->business_name)
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-building me-1"></i>{{ $marketer->marketerProfile->business_name }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('super-marketer.referred-marketers') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Marketers
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Referrals
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $performanceMetrics['total_referrals'] }}
                            </div>
                            <div class="text-xs text-success">
                                {{ $performanceMetrics['successful_referrals'] }} successful
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-handshake fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Commission
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₦{{ number_format($performanceMetrics['total_commission'], 2) }}
                            </div>
                            <div class="text-xs text-muted">
                                Lifetime earnings
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Conversion Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $performanceMetrics['conversion_rate'] }}%
                            </div>
                            <div class="text-xs text-info">
                                Success rate
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Properties Referred
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $performanceMetrics['properties_referred'] }}
                            </div>
                            <div class="text-xs text-warning">
                                Active listings
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-home fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Performance Insights -->
    <div class="row mb-4">
        <div class="col-xl-4 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Insights</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Average Monthly Referrals</span>
                            <span class="font-weight-bold">{{ $performanceMetrics['average_monthly_referrals'] }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Best Performing Month</span>
                            <span class="font-weight-bold">{{ $performanceMetrics['best_month']['month'] }}</span>
                        </div>
                        <div class="text-end">
                            <small class="text-success">{{ $performanceMetrics['best_month']['count'] }} referrals</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">This Month Referrals</span>
                            <span class="font-weight-bold">{{ $performanceMetrics['this_month_referrals'] }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Member Since</span>
                            <span class="font-weight-bold">{{ $performanceMetrics['join_date']->format('M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Commission History</h6>
                </div>
                <div class="card-body">
                    @if($commissionHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Reference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($commissionHistory->take(10) as $payment)
                                        <tr>
                                            <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                            <td class="font-weight-bold">₦{{ number_format($payment->total_amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    $payment->payment_status === 'completed' ? 'success' : 
                                                    ($payment->payment_status === 'pending' ? 'warning' : 'danger') 
                                                }}">
                                                    {{ ucfirst($payment->payment_status) }}
                                                </span>
                                            </td>
                                            <td class="text-muted small">{{ $payment->payment_reference ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-receipt fa-3x mb-3"></i>
                            <p>No commission payments yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Chains -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Active Referral Chains</h6>
                </div>
                <div class="card-body">
                    @if($referralChains->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Chain ID</th>
                                        <th>Landlord</th>
                                        <th>Property</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Commission Tier</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($referralChains as $chain)
                                        <tr>
                                            <td class="font-weight-bold">#{{ $chain->id }}</td>
                                            <td>
                                                @if($chain->landlord)
                                                    <div>{{ $chain->landlord->first_name }} {{ $chain->landlord->last_name }}</div>
                                                    <div class="text-muted small">{{ $chain->landlord->email }}</div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($chain->landlord && $chain->landlord->managedProperties->count() > 0)
                                                    <div class="small">
                                                        {{ $chain->landlord->managedProperties->count() }} properties
                                                    </div>
                                                @else
                                                    <span class="text-muted">No properties</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $chain->status === 'active' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($chain->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $chain->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <i class="fas fa-crown text-warning" title="Super Marketer"></i>
                                                    </div>
                                                    <div class="me-2">
                                                        <i class="fas fa-arrow-right text-muted"></i>
                                                    </div>
                                                    <div class="me-2">
                                                        <i class="fas fa-user text-primary" title="Marketer"></i>
                                                    </div>
                                                    <div class="me-2">
                                                        <i class="fas fa-arrow-right text-muted"></i>
                                                    </div>
                                                    <div>
                                                        <i class="fas fa-home text-success" title="Landlord"></i>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-sitemap fa-3x mb-3"></i>
                            <p>No active referral chains</p>
                            <small>Referral chains are created when this marketer refers landlords</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Business Profile Information -->
    @if($marketer->marketerProfile)
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Business Profile</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Business Information</h6>
                                <p><strong>Business Name:</strong> {{ $marketer->marketerProfile->business_name ?? 'N/A' }}</p>
                                <p><strong>Business Type:</strong> {{ $marketer->marketerProfile->business_type ?? 'N/A' }}</p>
                                <p><strong>Years of Experience:</strong> {{ $marketer->marketerProfile->years_of_experience ?? 'N/A' }} years</p>
                                <p><strong>Marketing Channels:</strong> {{ $marketer->marketerProfile->marketing_channels ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Contact & Preferences</h6>
                                <p><strong>Website:</strong> 
                                    @if($marketer->marketerProfile->website)
                                        <a href="{{ $marketer->marketerProfile->website }}" target="_blank">{{ $marketer->marketerProfile->website }}</a>
                                    @else
                                        N/A
                                    @endif
                                </p>
                                <p><strong>Social Media:</strong> {{ $marketer->marketerProfile->social_media_handles ?? 'N/A' }}</p>
                                <p><strong>Target Regions:</strong> 
                                    @if($marketer->marketerProfile->target_regions)
                                        {{ is_array($marketer->marketerProfile->target_regions) ? implode(', ', $marketer->marketerProfile->target_regions) : $marketer->marketerProfile->target_regions }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                                <p><strong>KYC Status:</strong> 
                                    <span class="badge bg-{{ $marketer->marketerProfile->kyc_status === 'approved' ? 'success' : 'warning' }}">
                                        {{ ucfirst($marketer->marketerProfile->kyc_status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        @if($marketer->marketerProfile->bio)
                            <hr>
                            <h6>Bio</h6>
                            <p class="text-muted">{{ $marketer->marketerProfile->bio }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('head')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    color: #6c757d;
}

.avatar img, .avatar div {
    object-fit: cover;
}
</style>
@endpush
@endsection
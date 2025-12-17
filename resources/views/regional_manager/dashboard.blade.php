@extends('layouts.admin')

@section('title', 'Regional Manager Dashboard')

@push('styles')
<style>
  
    .welcome-content {
        position: relative;
        z-index: 1;
    }
    
    .stat-card-modern {
        background: white;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #3e8189 0%, #51cbce 100%);
    }
    
    .stat-card-modern:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    }
    
    .stat-card-modern.purple::before {
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    }
    
    .stat-card-modern.orange::before {
        background: linear-gradient(180deg, #f093fb 0%, #f5576c 100%);
    }
    
    .stat-card-modern.blue::before {
        background: linear-gradient(180deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .stat-icon-modern {
        width: 70px;
        height: 70px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(62, 129, 137, 0.3);
    }
    
    .stat-card-modern.purple .stat-icon-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    .stat-card-modern.orange .stat-icon-modern {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        box-shadow: 0 8px 20px rgba(240, 147, 251, 0.3);
    }
    
    .stat-card-modern.blue .stat-icon-modern {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        box-shadow: 0 8px 20px rgba(79, 172, 254, 0.3);
    }
    
    .stat-value-modern {
        font-size: 42px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 8px;
        line-height: 1;
    }
    
    .stat-label-modern {
        font-size: 15px;
        color: #718096;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-change {
        font-size: 13px;
        margin-top: 12px;
        padding: 6px 12px;
        border-radius: 20px;
        display: inline-block;
    }
    
    .stat-change.positive {
        background: #d4edda;
        color: #155724;
    }
    
    .stat-change.negative {
        background: #f8d7da;
        color: #721c24;
    }
    
    .region-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        margin-bottom: 24px;
    }
    
    .region-badge-large {
        display: inline-flex;
        align-items: center;
        padding: 10px 18px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        color: #1976d2;
        border: 2px solid #90caf9;
        margin: 6px;
        transition: all 0.3s ease;
    }
    
    .region-badge-large:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
    }
    
    .region-badge-large i {
        margin-right: 8px;
    }
    
    .quick-action-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    .quick-action-card:hover {
        transform: translateY(-5px);
        border-color: #3e8189;
        box-shadow: 0 8px 30px rgba(62, 129, 137, 0.2);
    }
    
    .quick-action-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3e8189 0%, #51cbce 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin: 0 auto 16px;
    }
    
    .quick-action-title {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 8px;
    }
    
    .quick-action-desc {
        font-size: 13px;
        color: #718096;
    }
    
    .activity-item {
        padding: 16px;
        border-left: 3px solid #e2e8f0;
        margin-bottom: 16px;
        background: #f7fafc;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .activity-item:hover {
        border-left-color: #3e8189;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .activity-time {
        font-size: 12px;
        color: #a0aec0;
    }
    
    .empty-state-modern {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    .empty-state-icon-modern {
        font-size: 80px;
        color: #e2e8f0;
        margin-bottom: 24px;
    }
</style>
@endpush

@section('content')
<div class="content">
    <!-- Welcome Banner -->
    <div class="welcome-banner page-header-custom">
        <div class="welcome-content">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">
                        <i class="fa fa-chart-line me-3"></i>Welcome, {{ auth()->user()->first_name }}!
                    </h1>
                    <p class="mb-0 fs-5 opacity-90">
                        Regional Manager Dashboard - Manage your territories and track performance
                    </p>
                </div>
                <div class="text-end">
                    <div class="fs-6 opacity-75 mb-1">{{ now()->format('l') }}</div>
                    <div class="fs-4 fw-bold">{{ now()->format('M d, Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="stat-icon-modern">
                    <i class="fa fa-building"></i>
                </div>
                <div class="stat-value-modern">{{ $propertyCount ?? 0 }}</div>
                <div class="stat-label-modern">Properties in Region</div>
                <div class="stat-change positive">
                    <i class="fa fa-arrow-up me-1"></i>12% this month
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern purple">
                <div class="stat-icon-modern">
                    <i class="fa fa-users"></i>
                </div>
                <div class="stat-value-modern">--</div>
                <div class="stat-label-modern">Active Marketers</div>
                <div class="stat-change positive">
                    <i class="fa fa-arrow-up me-1"></i>5% this month
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern orange">
                <div class="stat-icon-modern">
                    <i class="fa fa-clock"></i>
                </div>
                <div class="stat-value-modern">--</div>
                <div class="stat-label-modern">Pending Approvals</div>
                <div class="stat-change negative">
                    <i class="fa fa-arrow-down me-1"></i>3 new today
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern blue">
                <div class="stat-icon-modern">
                    <i class="fa fa-dollar-sign"></i>
                </div>
                <div class="stat-value-modern">--</div>
                <div class="stat-label-modern">Total Revenue</div>
                <div class="stat-change positive">
                    <i class="fa fa-arrow-up me-1"></i>8% this month
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Assigned Regions -->
        <div class="col-md-8">
            <div class="region-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <i class="fa fa-map-marked-alt me-2 text-primary"></i>Your Assigned Regions
                    </h4>
                    <span class="badge bg-primary" style="font-size: 14px; padding: 8px 16px;">
                        {{ $scopes->count() }} Region{{ $scopes->count() != 1 ? 's' : '' }}
                    </span>
                </div>
                
                @if($scopes->count() > 0)
                    <div class="d-flex flex-wrap">
                        @foreach($scopes as $scope)
                            <div class="region-badge-large">
                                <i class="fa fa-map-pin"></i>
                                <strong>{{ $scope->scope_value }}</strong>
                                @if($scope->scope_type === 'lga')
                                    <span class="ms-1">(LGA)</span>
                                @else
                                    <span class="ms-1">(All LGAs)</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-modern">
                        <div class="empty-state-icon-modern">
                            <i class="fa fa-map-marked"></i>
                        </div>
                        <h4 class="text-muted mb-3">No Regions Assigned</h4>
                        <p class="text-muted mb-4">Contact your administrator to get regions assigned to your account.</p>
                        <button class="btn btn-primary btn-lg">
                            <i class="fa fa-envelope me-2"></i>Contact Admin
                        </button>
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="region-card">
                <h4 class="mb-4">
                    <i class="fa fa-bolt me-2 text-warning"></i>Quick Actions
                </h4>
                <div class="row">
                    <div class="col-md-4">
                        <div class="quick-action-card" onclick="window.location='{{ route('regional.properties') }}'">
                            <div class="quick-action-icon">
                                <i class="fa fa-building"></i>
                            </div>
                            <div class="quick-action-title">View Properties</div>
                            <div class="quick-action-desc">Browse all properties in your region</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="quick-action-card" onclick="window.location='{{ route('regional.marketers') }}'">
                            <div class="quick-action-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fa fa-users"></i>
                            </div>
                            <div class="quick-action-title">Manage Marketers</div>
                            <div class="quick-action-desc">View and manage marketers</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="quick-action-card" onclick="window.location='{{ route('regional.analytics') }}'">
                            <div class="quick-action-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <i class="fa fa-chart-bar"></i>
                            </div>
                            <div class="quick-action-title">Analytics</div>
                            <div class="quick-action-desc">View performance reports</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-md-4">
            <div class="region-card">
                <h4 class="mb-4">
                    <i class="fa fa-history me-2 text-info"></i>Recent Activity
                </h4>
                
                <div class="activity-item">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong>New Property Listed</strong>
                        <span class="activity-time">2 hours ago</span>
                    </div>
                    <p class="mb-0 text-muted small">3-bedroom apartment in Lagos</p>
                </div>
                
                <div class="activity-item">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong>Marketer Approved</strong>
                        <span class="activity-time">5 hours ago</span>
                    </div>
                    <p class="mb-0 text-muted small">John Doe verified as marketer</p>
                </div>
                
                <div class="activity-item">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong>Property Approved</strong>
                        <span class="activity-time">1 day ago</span>
                    </div>
                    <p class="mb-0 text-muted small">Commercial space in Abuja approved</p>
                </div>
                
                <div class="activity-item">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong>Commission Paid</strong>
                        <span class="activity-time">2 days ago</span>
                    </div>
                    <p class="mb-0 text-muted small">₦50,000 commission processed</p>
                </div>
                
                <div class="text-center mt-3">
                    <a href="#" class="btn btn-outline-primary btn-sm">
                        View All Activity <i class="fa fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Performance Summary -->
            <div class="region-card">
                <h4 class="mb-4">
                    <i class="fa fa-trophy me-2 text-warning"></i>This Month
                </h4>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Properties Approved</span>
                        <strong>--</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 75%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Marketers Onboarded</span>
                        <strong>--</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: 60%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Revenue Generated</span>
                        <strong>--</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" style="width: 85%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Add any interactive features here
document.addEventListener('DOMContentLoaded', function() {
    console.log('Regional Manager Dashboard loaded');
});
</script>
@endpush
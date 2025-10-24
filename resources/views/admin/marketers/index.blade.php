@extends('layout')

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
                                <i class="fas fa-users-cog text-primary me-2"></i>
                                Marketer Management
                            </h4>
                            <p class="text-muted mb-0">Manage marketer applications, commissions, and performance</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.marketers.analytics') }}" class="btn btn-outline-primary">
                                <i class="fas fa-chart-bar"></i> Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Marketers</h6>
                            <h3 class="mb-0">{{ e($stats['total_marketers']) }}</h3>
                            <small class="opacity-75">All registered marketers</small>
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
                            <h6 class="card-title">Active Marketers</h6>
                            <h3 class="mb-0">{{ $stats['active_marketers'] }}</h3>
                            <small class="opacity-75">Approved and active</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x opacity-75"></i>
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
                            <h6 class="card-title">Pending Approval</h6>
                            <h3 class="mb-0">{{ $stats['pending_marketers'] }}</h3>
                            <small class="opacity-75">Awaiting review</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-clock fa-2x opacity-75"></i>
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
                            <h6 class="card-title">Total Referrals</h6>
                            <h3 class="mb-0">{{ $stats['total_referrals'] }}</h3>
                            <small class="opacity-75">{{ $stats['successful_referrals'] }} successful</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-handshake fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Overview -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-gradient-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Commission Paid</h6>
                            <h3 class="mb-0">₦{{ number_format($stats['total_commission_paid'], 0) }}</h3>
                            <small class="opacity-75">Lifetime payments</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill-wave fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-gradient-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Pending Commission</h6>
                            <h3 class="mb-0">₦{{ number_format($stats['pending_commission'], 0) }}</h3>
                            <small class="opacity-75">Awaiting approval</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-3x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Applications -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-file-alt text-warning me-2"></i>
                        Recent Applications
                    </h6>
                    <a href="{{ route('admin.marketers.list', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentApplications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentApplications as $application)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $application->first_name }} {{ $application->last_name }}</h6>
                                            <p class="mb-1 text-muted small">{{ $application->email }}</p>
                                            <small class="text-muted">
                                                {{ $application->marketerProfile->business_name ?? 'No business name' }} • 
                                                {{ $application->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-warning">Pending</span>
                                            <div class="mt-1">
                                                <a href="{{ route('admin.marketers.show', $application) }}" class="btn btn-xs btn-outline-primary">Review</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-file-alt fa-3x mb-3 d-block opacity-25"></i>
                            <h6>No Pending Applications</h6>
                            <p class="small">New marketer applications will appear here</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Performing Marketers -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-trophy text-success me-2"></i>
                        Top Performing Marketers
                    </h6>
                    <a href="{{ route('admin.marketers.list') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($topMarketers->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topMarketers as $index => $marketer)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                @if($index < 3)
                                                    <i class="fas fa-medal text-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'dark') }}"></i>
                                                @else
                                                    <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-1">{{ $marketer->first_name }} {{ $marketer->last_name }}</h6>
                                                <small class="text-muted">{{ $marketer->referrals_count }} referrals</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <a href="{{ route('admin.marketers.show', $marketer) }}" class="btn btn-xs btn-outline-info">View</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-trophy fa-3x mb-3 d-block opacity-25"></i>
                            <h6>No Active Marketers</h6>
                            <p class="small">Top performers will appear here</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Commission Approvals -->
    @if($pendingRewards->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            Pending Commission Approvals ({{ $pendingRewards->count() }})
                        </h6>
                        <a href="{{ route('admin.marketers.rewards', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Marketer</th>
                                        <th>Referred User</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingRewards->take(10) as $reward)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $reward->marketer->first_name }} {{ $reward->marketer->last_name }}</strong>
                                                    <br><small class="text-muted">{{ $reward->marketer->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $reward->referral->referred->first_name ?? 'N/A' }} {{ $reward->referral->referred->last_name ?? '' }}</strong>
                                                    <br><small class="text-muted">{{ $reward->referral->referred->email ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">₦{{ number_format($reward->amount, 0) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($reward->reward_type) }}</span>
                                            </td>
                                            <td>
                                                <small>{{ $reward->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <form action="{{ route('admin.marketers.rewards.approve', $reward) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success" onclick="return confirm('Approve this commission?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-outline-danger" onclick="rejectReward({{ $reward->id }})">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-bolt text-primary me-2"></i>
                        Quick Actions
                    </h6>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('admin.marketers.list', ['status' => 'pending']) }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-clock me-2"></i>Review Applications
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.marketers.rewards', ['status' => 'pending']) }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-money-bill me-2"></i>Review Commissions
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.marketers.payments') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-credit-card me-2"></i>Process Payments
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.marketers.analytics') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-chart-bar me-2"></i>View Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Reward Modal -->
<div class="modal fade" id="rejectRewardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Commission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectRewardForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Commission</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function rejectReward(rewardId) {
    const form = document.getElementById('rejectRewardForm');
    form.action = `/admin/marketers/rewards/${rewardId}/reject`;
    const modal = new bootstrap.Modal(document.getElementById('rejectRewardModal'));
    modal.show();
}

$(document).ready(function() {
    // Auto-refresh pending items every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);
});
</script>
@endsection

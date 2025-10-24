@extends('layout')
@section('content')
<div class="content">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fas fa-crown text-warning me-2"></i>
                                Super Marketer Dashboard
                            </h4>
                            <p class="text-muted mb-0">Welcome back, {{ $superMarketer->first_name }}! Manage your marketer network and track performance.</p>
                        </div>
                        <div>
                            <span class="badge bg-warning fs-6">
                                <i class="fas fa-star"></i> Super Marketer
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards --&gt;
    &lt;!-- Statistics Cards --&gt;
    &lt;div class=&quot;row mb-4&quot;&gt;
    &lt;div class=&quot;col-xl-3 col-md-6 mb-4&quot;&gt;
    &lt;x-stat-card
    title=&quot;Referred Marketers&quot;
    :value=&quot;$stats['total_referred_marketers']&quot;
    subtext=&quot;{{ $stats['active_marketers'] }} Active&quot;
    subicon=&quot;fas fa-check-circle&quot;
    color=&quot;primary&quot;
    icon=&quot;fas fa-users&quot;
    subtextClass=&quot;text-success&quot;
    /&gt;
    &lt;/div&gt;
    &lt;div class=&quot;col-xl-3 col-md-6 mb-4&quot;&gt;
    &lt;x-stat-card
    title=&quot;Total Commission Earned&quot;
    :value=&quot;'₦' . number_format($stats['total_commission_earned'], 2)&quot;
    :subtext=&quot;'₦' . number_format($stats['pending_commission'], 2) . ' Pending'&quot;
    subicon=&quot;fas fa-clock&quot;
    color=&quot;success&quot;
    icon=&quot;fas fa-dollar-sign&quot;
    subtextClass=&quot;text-muted&quot;
    /&gt;
    &lt;/div&gt;
    &lt;div class=&quot;col-xl-3 col-md-6 mb-4&quot;&gt;
    &lt;x-stat-card
    title=&quot;Active Referral Chains&quot;
    :value=&quot;$stats['total_referral_chains']&quot;
    subtext=&quot;Multi-tier Network&quot;
    subicon=&quot;fas fa-link&quot;
    color=&quot;info&quot;
    icon=&quot;fas fa-sitemap&quot;
    subtextClass=&quot;text-info&quot;
    /&gt;
    &lt;/div&gt;
    &lt;div class=&quot;col-xl-3 col-md-6 mb-4&quot;&gt;
    &lt;x-stat-card
    title=&quot;This Month Commission&quot;
    :value=&quot;'₦' . number_format($stats['this_month_commission'], 2)&quot;
    :subtext=&quot;abs($stats['commission_growth']) . '% vs last month'&quot;
    :subicon=&quot;'fas fa-' . ($stats['commission_growth'] &gt;= 0 ? 'arrow-up' : 'arrow-down')&quot;
    color=&quot;warning&quot;
    icon=&quot;fas fa-chart-line&quot;
    :subtextClass=&quot;$stats['commission_growth'] &gt;= 0 ? 'text-success' : 'text-danger'&quot;
    /&gt;
    &lt;/div&gt;
    &lt;/div&gt;

    <!-- Performance Charts and Quick Actions -->
    <div class="row mb-4">
        <!-- Performance Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Network Performance Overview</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Chart Options:</div>
                            <a class="dropdown-item" href="{{ route('super-marketer.commission-analytics') }}">View Detailed Analytics</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="generateReferralLink()">
                            <i class="fas fa-link me-2"></i>
                            Generate Referral Link
                        </button>
                        <a href="{{ route('super-marketer.referred-marketers') }}" class="btn btn-outline-primary">
                            <i class="fas fa-users me-2"></i>
                            View All Marketers
                        </a>
                        <a href="{{ route('super-marketer.commission-analytics') }}" class="btn btn-outline-success">
                            <i class="fas fa-chart-bar me-2"></i>
                            Commission Analytics
                        </a>
                    </div>

                    <!-- Referral Link Generation Form -->
                    <div class="mt-4" id="referralLinkForm" style="display: none;">
                        <h6 class="font-weight-bold">Generate Referral Link</h6>
                        <form id="generateLinkForm">
                            @csrf
                            <div class="mb-3">
                                <label for="campaign_name" class="form-label">Campaign Name (Optional)</label>
                                <input type="text" class="form-control" id="campaign_name" name="campaign_name" placeholder="e.g., Q1 2024 Campaign">
                            </div>
                            <div class="mb-3">
                                <label for="target_audience" class="form-label">Target Audience (Optional)</label>
                                <input type="text" class="form-control" id="target_audience" name="target_audience" placeholder="e.g., Real Estate Agents">
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Campaign notes..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-magic me-1"></i>
                                Generate Link
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="hideReferralForm()">
                                Cancel
                            </button>
                        </form>
                    </div>

                    <!-- Generated Link Display -->
                    <div class="mt-4" id="generatedLinkDisplay" style="display: none;">
                        <h6 class="font-weight-bold text-success">
                            <i class="fas fa-check-circle me-1"></i>
                            Referral Link Generated!
                        </h6>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" id="generatedLink" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <div class="text-center">
                            <img id="qrCode" src="" alt="QR Code" class="img-fluid" style="max-width: 150px; display: none;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Referred Marketers and Recent Activities -->
    <div class="row mb-4">
        <!-- Top Performing Marketers -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performing Marketers</h6>
                    <a href="{{ route('super-marketer.referred-marketers') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    @if($topPerformers->count() > 0)
                        @foreach($topPerformers as $marketer)
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar me-3">
                                    @if($marketer->photo)
                                        <img src="{{ asset('storage/' . $marketer->photo) }}" alt="{{ $marketer->first_name }}" class="rounded-circle" width="40" height="40">
                                    @else
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="text-white font-weight-bold">{{ substr($marketer->first_name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ $marketer->first_name }} {{ $marketer->last_name }}</div>
                                    <div class="text-muted small">
                                        {{ $marketer->total_referrals }} referrals • ₦{{ number_format($marketer->total_commission, 2) }}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $marketer->marketer_status === 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($marketer->marketer_status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <p>No marketers referred yet. Start building your network!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                </div>
                <div class="card-body">
                    @if($recentActivities->count() > 0)
                        @foreach($recentActivities as $activity)
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    @if($activity['type'] === 'marketer_referral')
                                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <i class="fas fa-user-plus text-white"></i>
                                        </div>
                                    @elseif($activity['type'] === 'commission_payment')
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <i class="fas fa-dollar-sign text-white"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold small">{{ $activity['message'] }}</div>
                                    <div class="text-muted small">{{ $activity['date']->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clock fa-3x mb-3"></i>
                            <p>No recent activities</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Breakdown -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Commission Breakdown</h6>
                    <a href="{{ route('super-marketer.commission-analytics') }}" class="btn btn-sm btn-outline-primary">
                        Detailed Analytics
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h5 class="text-success">₦{{ number_format($commissionBreakdown['super_marketer_total'], 2) }}</h5>
                                <p class="text-muted">Your Commission</p>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $commissionBreakdown['total_generated'] > 0 ? ($commissionBreakdown['super_marketer_total'] / $commissionBreakdown['total_generated']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h5 class="text-primary">₦{{ number_format($commissionBreakdown['marketer_total'], 2) }}</h5>
                                <p class="text-muted">Network Commission</p>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: {{ $commissionBreakdown['total_generated'] > 0 ? ($commissionBreakdown['marketer_total'] / $commissionBreakdown['total_generated']) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h5 class="text-info">₦{{ number_format($commissionBreakdown['total_generated'], 2) }}</h5>
                                <p class="text-muted">Total Generated</p>
                                <div class="progress mb-2">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if(count($commissionBreakdown['regional_breakdown']) > 0)
                        <hr>
                        <h6 class="font-weight-bold mb-3">Regional Performance</h6>
                        <div class="row">
                            @foreach($commissionBreakdown['regional_breakdown'] as $region)
                                <div class="col-md-3 mb-2">
                                    <div class="text-center">
                                        <strong>{{ $region['region'] ?? 'Unknown' }}</strong>
                                        <div class="text-muted">₦{{ number_format($region['total'], 2) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Referral Link Modal -->
<div class="modal fade" id="referralLinkModal" tabindex="-1" aria-labelledby="referralLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="referralLinkModalLabel">
                    <i class="fas fa-link me-2"></i>
                    Your Referral Link
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="modalReferralLink" class="form-label">Referral Link</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="modalReferralLink" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyModalLink()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="text-center">
                    <img id="modalQrCode" src="" alt="QR Code" class="img-fluid" style="max-width: 200px;">
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        Share this link with potential marketers to grow your network and earn commissions from their referrals.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="shareReferralLink()">
                    <i class="fas fa-share me-1"></i>
                    Share Link
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Performance Chart
const ctx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($performanceData['months']),
        datasets: [{
            label: 'New Marketers',
            data: @json($performanceData['marketers']),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Network Referrals',
            data: @json($performanceData['referrals']),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.1
        }, {
            label: 'Commission (₦)',
            data: @json($performanceData['commissions']),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});

// Referral Link Generation
function generateReferralLink() {
    document.getElementById('referralLinkForm').style.display = 'block';
}

function hideReferralForm() {
    document.getElementById('referralLinkForm').style.display = 'none';
    document.getElementById('generatedLinkDisplay').style.display = 'none';
}

document.getElementById('generateLinkForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("super-marketer.generate-referral-link") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('generatedLink').value = data.referral_link;
            document.getElementById('qrCode').src = data.qr_code_url;
            document.getElementById('qrCode').style.display = 'block';
            document.getElementById('generatedLinkDisplay').style.display = 'block';
            document.getElementById('referralLinkForm').style.display = 'none';
            
            // Also populate modal
            document.getElementById('modalReferralLink').value = data.referral_link;
            document.getElementById('modalQrCode').src = data.qr_code_url;
        } else {
            alert('Error generating referral link. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating referral link. Please try again.');
    });
});

function copyToClipboard() {
    const linkInput = document.getElementById('generatedLink');
    linkInput.select();
    linkInput.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(linkInput.value);
    
    // Show feedback
    const button = event.target;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}

function copyModalLink() {
    const linkInput = document.getElementById('modalReferralLink');
    linkInput.select();
    linkInput.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(linkInput.value);
    
    // Show feedback
    const button = event.target;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        button.innerHTML = originalHTML;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}

function shareReferralLink() {
    const link = document.getElementById('modalReferralLink').value;
    
    if (navigator.share) {
        navigator.share({
            title: 'Join EasyRent as a Marketer',
            text: 'Join my marketer network and start earning commissions!',
            url: link
        });
    } else {
        // Fallback - copy to clipboard
        copyModalLink();
    }
}
</script>
@endpush

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

.avatar img, .avatar div {
    object-fit: cover;
}

.chart-area {
    position: relative;
    height: 300px;
}
</style>
@endpush
@endsection
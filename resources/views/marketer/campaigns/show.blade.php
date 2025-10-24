@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Campaign Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $campaign->name }}</h5>
                        <small class="text-muted">Campaign Code: <code>{{ $campaign->campaign_code }}</code></small>
                    </div>
                    <div>
                        <a href="{{ route('marketer.campaigns.index') }}" class="btn btn-secondary mr-2">
                            <i class="fas fa-arrow-left"></i> Back to Campaigns
                        </a>
                        @if($campaign->status === 'active')
                            <button class="btn btn-warning mr-2" onclick="pauseCampaign()">
                                <i class="fas fa-pause"></i> Pause
                            </button>
                        @elseif($campaign->status === 'paused')
                            <button class="btn btn-success mr-2" onclick="resumeCampaign()">
                                <i class="fas fa-play"></i> Resume
                            </button>
                        @endif
                        
                        @if($campaign->campaign_type === 'qr_code')
                            <button class="btn btn-info mr-2" onclick="showQRCode()">
                                <i class="fas fa-qrcode"></i> QR Code
                            </button>
                        @endif
                        
                        <button class="btn btn-primary" onclick="copyReferralLink()">
                            <i class="fas fa-link"></i> Copy Link
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <span class="badge badge-{{ $campaign->status === 'active' ? 'success' : ($campaign->status === 'paused' ? 'warning' : 'secondary') }} badge-lg">
                                    {{ ucfirst($campaign->status) }}
                                </span>
                                <p class="mt-2 mb-0">Status</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary">{{ number_format($campaign->clicks) }}</h4>
                                <p class="mb-0">Total Clicks</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success">{{ number_format($campaign->conversions) }}</h4>
                                <p class="mb-0">Conversions</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info">
                                    {{ $campaign->clicks > 0 ? number_format(($campaign->conversions / $campaign->clicks) * 100, 2) : 0 }}%
                                </h4>
                                <p class="mb-0">Conversion Rate</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campaign Details -->
            <div class="row">
                <div class="col-md-8">
                    <!-- Performance Chart -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Performance Over Time</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="performanceChart" height="100"></canvas>
                        </div>
                    </div>

                    <!-- Recent Referrals -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Recent Referrals</h6>
                            <a href="{{ route('marketer.referrals.index', ['campaign' => $campaign->campaign_code]) }}" 
                               class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            @if($recentReferrals->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Landlord</th>
                                                <th>Registration Date</th>
                                                <th>Commission</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentReferrals as $referral)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $referral->referred->name }}</strong><br>
                                                        <small class="text-muted">{{ $referral->referred->email }}</small>
                                                    </td>
                                                    <td>{{ $referral->conversion_date ? $referral->conversion_date->format('M d, Y') : 'N/A' }}</td>
                                                    <td>
                                                        <strong>KSh {{ number_format($referral->commission_amount) }}</strong>
                                                    </td>
                                                    <td>
                                                        @switch($referral->commission_status)
                                                            @case('pending')
                                                                <span class="badge badge-warning">Pending</span>
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
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No referrals yet for this campaign</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Campaign Info -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Campaign Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>
                                        @if($campaign->campaign_type === 'qr_code')
                                            <span class="badge badge-info">QR Code</span>
                                        @else
                                            <span class="badge badge-success">Link</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Target:</strong></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $campaign->target_audience)) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Budget:</strong></td>
                                    <td>{{ $campaign->budget ? 'KSh ' . number_format($campaign->budget) : 'Not set' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Start Date:</strong></td>
                                    <td>{{ $campaign->start_date ? $campaign->start_date->format('M d, Y') : 'Immediate' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>End Date:</strong></td>
                                    <td>{{ $campaign->end_date ? $campaign->end_date->format('M d, Y') : 'Ongoing' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $campaign->created_at->format('M d, Y') }}</td>
                                </tr>
                            </table>

                            @if($campaign->description)
                                <hr>
                                <h6>Description</h6>
                                <p class="text-muted">{{ $campaign->description }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Commission Summary -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Commission Summary</h6>
                        </div>
                        <div class="card-body">
                            @php
                                $totalCommission = $campaign->referrals()->sum('commission_amount');
                                $pendingCommission = $campaign->referrals()->where('commission_status', 'pending')->sum('commission_amount');
                                $approvedCommission = $campaign->referrals()->where('commission_status', 'approved')->sum('commission_amount');
                                $paidCommission = $campaign->referrals()->where('commission_status', 'paid')->sum('commission_amount');
                            @endphp
                            {{-- TODO: Replace commission_amount sums with rewards->sum('amount') once migration complete --}}
                            <div class="row text-center">
                                <div class="col-6">
                                    <h6 class="text-primary">KSh {{ number_format($totalCommission) }}</h6>
                                    <small class="text-muted">Total Earned</small>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-success">KSh {{ number_format($paidCommission) }}</h6>
                                    <small class="text-muted">Paid Out</small>
                                </div>
                                <div class="col-6 mt-3">
                                    <h6 class="text-warning">KSh {{ number_format($pendingCommission) }}</h6>
                                    <small class="text-muted">Pending</small>
                                </div>
                                <div class="col-6 mt-3">
                                    <h6 class="text-info">KSh {{ number_format($approvedCommission) }}</h6>
                                    <small class="text-muted">Approved</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Referral Link -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Referral Link</h6>
                        </div>
                        <div class="card-body">
                            <div class="input-group">
                                <input type="text" class="form-control" id="referralLink" 
                                       value="{{ $campaign->getReferralLink() }}" readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" onclick="copyReferralLink()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                Share this link to earn {{ auth()->user()->commission_rate }}% commission on successful referrals
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Campaign QR Code</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="qrCodeContainer">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <p class="mt-3 text-muted">Scan this QR code to access the referral link</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="downloadQRBtn">Download QR Code</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Performance Chart
const ctx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($chartData['labels']),
        datasets: [{
            label: 'Clicks',
            data: @json($chartData['clicks']),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1
        }, {
            label: 'Conversions',
            data: @json($chartData['conversions']),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

function copyReferralLink() {
    const linkInput = document.getElementById('referralLink');
    linkInput.select();
    navigator.clipboard.writeText(linkInput.value).then(function() {
        showAlert('Referral link copied to clipboard!', 'success');
    });
}

function showQRCode() {
    $('#qrModal').modal('show');
    
    // Load QR code
    fetch(`/marketer/campaigns/{{ $campaign->id }}/qr-code`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('qrCodeContainer').innerHTML = 
                    `<img src="${data.qr_url}" alt="QR Code" class="img-fluid" style="max-width: 300px;">`;
                document.getElementById('downloadQRBtn').onclick = function() {
                    window.open(data.download_url, '_blank');
                };
            } else {
                document.getElementById('qrCodeContainer').innerHTML = 
                    '<p class="text-danger">Error loading QR code</p>';
            }
        })
        .catch(error => {
            document.getElementById('qrCodeContainer').innerHTML = 
                '<p class="text-danger">Error loading QR code</p>';
        });
}

function pauseCampaign() {
    if (confirm('Are you sure you want to pause this campaign?')) {
        fetch(`/marketer/campaigns/{{ $campaign->id }}/pause`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}

function resumeCampaign() {
    if (confirm('Are you sure you want to resume this campaign?')) {
        fetch(`/marketer/campaigns/{{ $campaign->id }}/resume`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        }).then(response => {
            if (response.ok) {
                location.reload();
            }
        });
    }
}

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', alertHtml);
    
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) alert.remove();
    }, 3000);
}
</script>
@endsection

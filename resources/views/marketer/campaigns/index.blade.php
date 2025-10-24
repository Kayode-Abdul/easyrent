@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Campaigns</h5>
                    <a href="{{ route('marketer.campaigns.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Campaign
                    </a>
                </div>
                <div class="card-body">
                    @if($campaigns->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Campaign Name</th>
                                        <th>Type</th>
                                        <th>Code</th>
                                        <th>Status</th>
                                        <th>Duration</th>
                                        <th>Performance</th>
                                        <th>Commission</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($campaigns as $campaign)
                                        <tr>
                                            <td>
                                                <strong>{{ $campaign->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ Str::limit($campaign->description, 50) }}</small>
                                            </td>
                                            <td>
                                                @if($campaign->campaign_type === 'qr_code')
                                                    <span class="badge badge-info">QR Code</span>
                                                @else
                                                    <span class="badge badge-success">Link</span>
                                                @endif
                                            </td>
                                            <td>
                                                <code>{{ $campaign->campaign_code }}</code>
                                                <button class="btn btn-sm btn-outline-primary ml-1" 
                                                        onclick="copyToClipboard('{{ $campaign->campaign_code }}')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </td>
                                            <td>
                                                @switch($campaign->status)
                                                    @case('active')
                                                        <span class="badge badge-success">Active</span>
                                                        @break
                                                    @case('paused')
                                                        <span class="badge badge-warning">Paused</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge badge-info">Completed</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge badge-danger">Cancelled</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>
                                                <small>
                                                    <strong>Start:</strong> {{ $campaign->start_date ? $campaign->start_date->format('M d, Y') : 'N/A' }}<br>
                                                    <strong>End:</strong> {{ $campaign->end_date ? $campaign->end_date->format('M d, Y') : 'N/A' }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <small><strong>Clicks:</strong> {{ number_format($campaign->clicks) }}</small>
                                                    <small><strong>Conversions:</strong> {{ number_format($campaign->conversions) }}</small>
                                                    <small><strong>Rate:</strong> {{ $campaign->clicks > 0 ? number_format(($campaign->conversions / $campaign->clicks) * 100, 2) : 0 }}%</small>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $totalCommission = $campaign->referrals()->sum('commission_amount');
                                                @endphp
                                                {{-- TODO: migrate $campaign->referrals()->sum('commission_amount') to rewards sum('amount') --}}
                                                <strong>KSh {{ number_format($totalCommission) }}</strong>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('marketer.campaigns.show', $campaign->id) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($campaign->campaign_type === 'qr_code')
                                                        <button class="btn btn-sm btn-outline-info" 
                                                                onclick="downloadQR('{{ $campaign->id }}')" title="Download QR">
                                                            <i class="fas fa-qrcode"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    <button class="btn btn-sm btn-outline-success" 
                                                            onclick="copyLink('{{ $campaign->getReferralLink() }}')" title="Copy Link">
                                                        <i class="fas fa-link"></i>
                                                    </button>
                                                    
                                                    @if($campaign->status === 'active')
                                                        <form method="POST" action="{{ route('marketer.campaigns.pause', $campaign->id) }}" 
                                                              class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                                    title="Pause" onclick="return confirm('Pause this campaign?')">
                                                                <i class="fas fa-pause"></i>
                                                            </button>
                                                        </form>
                                                    @elseif($campaign->status === 'paused')
                                                        <form method="POST" action="{{ route('marketer.campaigns.resume', $campaign->id) }}" 
                                                              class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                    title="Resume" onclick="return confirm('Resume this campaign?')">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{ $campaigns->links() }}
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                            <h5>No Campaigns Yet</h5>
                            <p class="text-muted">Create your first marketing campaign to start attracting landlords.</p>
                            <a href="{{ route('marketer.campaigns.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Your First Campaign
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel">Campaign QR Code</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="qrCodeContainer"></div>
                <p class="mt-3 text-muted">Scan this QR code to access the referral link</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="downloadQRBtn">Download QR Code</button>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showAlert('Campaign code copied to clipboard!', 'success');
    });
}

function copyLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        showAlert('Referral link copied to clipboard!', 'success');
    });
}

function downloadQR(campaignId) {
    // Fetch QR code and show in modal
    fetch(`/marketer/campaigns/${campaignId}/qr-code`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('qrCodeContainer').innerHTML = 
                    `<img src="${data.qr_url}" alt="QR Code" class="img-fluid" style="max-width: 300px;">`;
                document.getElementById('downloadQRBtn').onclick = function() {
                    window.open(data.download_url, '_blank');
                };
                $('#qrModal').modal('show');
            } else {
                showAlert('Error generating QR code', 'error');
            }
        })
        .catch(error => {
            showAlert('Error loading QR code', 'error');
        });
}

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Add alert to top of card body
    $('.card-body').prepend(alertHtml);
    
    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 3000);
}
</script>
@endsection
